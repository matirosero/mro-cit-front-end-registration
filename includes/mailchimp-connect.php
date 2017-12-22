<?php
/*
 * Srcs:
 * https://pippinsplugins.com/create-a-simple-mail-chimp-sign-up-form/
 */


$mc_options = get_option('mro_cit_mailchimp_settings');


// register the plugin settings
function mro_cit_mailchimp_register_settings() {

	// create our plugin settings
	register_setting( 'mro_cit_mailchimp_settings_group', 'mro_cit_mailchimp_settings' );
}
add_action( 'admin_init', 'mro_cit_mailchimp_register_settings', 100 );


function mro_cit_mailchimp_settings_menu() {
	// add settings page
	add_options_page(__('Mail Chimp', 'mro-cit-frontend'), __('Mail Chimp', 'mro-cit-frontend'),'manage_options', 'mro-cit-mailchimp', 'mro_cit_mailchimp_settings_page');
}
add_action('admin_menu', 'mro_cit_mailchimp_settings_menu', 100);


function mro_cit_mailchimp_settings_page() {

	global $mc_options;

	?>
	<div class="wrap">
		<h2><?php _e('Mail Chimp Settings', 'mro-cit-frontend'); ?></h2>

		<form method="post" action="options.php" class="mro_cit_options_form">
	 
			<?php settings_fields( 'mro_cit_mailchimp_settings_group' ); ?>
			<p>
				<label for="mro_cit_mailchimp_settings[mailchimp_api]"><?php _e( 'Mail Chimp API Key', 'pippin' ); ?></label><br/>		
				<input class="regular-text" id="mro_cit_mailchimp_settings[mailchimp_api]" style="width: 300px;" name="mro_cit_mailchimp_settings[mailchimp_api]" value="<?php if(isset($mc_options['mailchimp_api'])) { echo $mc_options['mailchimp_api']; } ?>"/>
				<div class="description"><?php _e('Enter your Mail Chimp API key to enable a newsletter signup option with the registration form.', 'pippin'); ?></div>
			</p>
			<?php
			/*
			<p>
				<?php $lists = mro_cit_get_mailchimp_lists(); ?>
				<select id="mro_cit_mailchimp_settings[mailchimp_list]" name="mro_cit_mailchimp_settings[mailchimp_list]">
					<?php
						if($lists) :
							foreach($lists as $list) :
								echo '<option value="' . $list['id'] . '"' . selected($mc_options['mailchimp_list'], $list['id'], false) . '>' . $list['name'] . '</option>';
							endforeach;
						else :
					?>
					<option value="no list"><?php _e('no lists', 'pippin'); ?></option>
				<?php endif; ?>
				</select>
				<label for="mro_cit_mailchimp_settings[mailchimp_list]"><?php _e( 'Newsletter List', 'pippin' ); ?></label><br/>		
				<div class="description"><?php _e('Choose the list to subscribe users to', 'pippin'); ?></div>
			</p>
			*/
			?>
			<!-- save the options -->
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'pippin' ); ?>" />
			</p>
 
		</form>
	</div><!--end .wrap-->
	<?php
}


// displays the mailchimp signup form
function mro_cit_mailchimp_form($redirect) {
	global $mc_options;
	ob_start(); 
		if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) { ?>
		<form id="mro_cit_mailchimp" action="" method="post">
			<p>
				<label for="mro_cit_mailchimp_email"><?php _e('Enter your email to subscribe to our newsletter', 'pippin'); ?></label><br/>
				<input name="mro_cit_mailchimp_email" id="mro_cit_mailchimp_email" type="email" placeholder="<?php _e('Email . . .', 'pippin'); ?>"/>
			</p>
			<p>
				<input type="hidden" name="redirect" value="<?php echo $redirect; ?>"/>
				<input type="hidden" name="action" value="mro_cit_mailchimp"/>
				<input type="submit" class="button button-primary" value="<?php _e('Sign Up', 'pippin'); ?>"/>
			</p>
		</form>
		<?php
	}
	return ob_get_clean();
}

function mro_cit_mailchimp_form_shortcode($atts, $content = null ) {
	extract( shortcode_atts( array(
		'redirect' => ''
	), $atts ) );
	
	if($redirect == '') {
		$redirect = home_url();
	}
	return mro_cit_mailchimp_form($redirect);
}
add_shortcode('mailchimp', 'mro_cit_mailchimp_form_shortcode');


// process the subscribe to list form
function mro_cit_check_for_email_signup() {

	// only proceed with this function if we are posting from our email subscribe form
	if(isset($_POST['action']) && $_POST['action'] == 'mro_cit_mailchimp') {

		write_log('OK to process mailchimp form');

		// this contains the email address entered in the subscribe form
		$email = $_POST['mro_cit_mailchimp_email'];

		// check for a valid email
		if(!is_email($email)) {
			wp_die(__('Your email address is invalid', 'pippin'), __('Invalid Email', 'pippin'));
		} else {
			write_log('Email OK');
		}


		// send this email to mailchimp
		mro_cit_subscribe_email($email);

		// send user to the confirmation page
		// wp_redirect($_POST['redirect']); exit;
	}
}
add_action('init', 'mro_cit_check_for_email_signup');


// adds an email to the mailchimp subscription list
function mro_cit_subscribe_email($email) {
	
	write_log('Send info to mailchimp');

	global $mc_options;



	// var_dump($list);
	// var_dump($api_key);


	// check that the API option is set
	if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) {

		// Let's start by including the MailChimp API wrapper
	    include('MailChimp.php');
	    // Then call/use the class
	    // use \DrewM\MailChimp\MailChimp;
	    $MailChimp = new MailChimp($api_key);
	    

		write_log('api key OK');
		write_log('subscribe this email: '.$email);

		//TEMP
		$list_id = '270121';
		$api_key = $mc_options['mailchimp_api'];



	    // Submit subscriber data to MailChimp
	    // For parameters doc, refer to: http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	    // For wrapper's doc, visit: https://github.com/drewm/mailchimp-api
	    $result = $MailChimp->post("lists/$list_id/members", [
	                            // 'email_address' => $_POST["email"],
	                            // 'merge_fields'  => ['FNAME'=>$_POST["fname"], 'LNAME'=>$_POST["lname"]],
						    	'email_address' => $email,
	                            'status'        => 'subscribed',
	                        ]);
	 
	    if ($MailChimp->success()) {
	        // Success message
	        echo "<h4>Thank you, you have been added to our mailing list.</h4>";
	        write_log('Added to mailchimp!!!');
	    } else {
	        // Display error
	        echo $MailChimp->getLastError();
	        write_log('Did not work :( ' . $MailChimp->getLastError() );
	        // Alternatively you can use a generic error message like:
	        // echo "<h4>Please try again.</h4>";
	    }
/*
		// load the MCAPI wrapper
		require_once('mailchimp/MCAPI.class.php');

		// setup a new instance of the MCAPI class
		$api = new MCAPI($mc_options['mailchimp_api']);

		// subscribe the email to the list and return TRUE if successful
		if($api->listSubscribe($mc_options['mailchimp_list'], $email, '') === true) {
			return true;
		}
*/
	}

	// return FALSE if any of the above fail
	return false;

}





// get an array of all mailchimp subscription lists
function mro_cit_get_mailchimp_lists() {
	
	global $mc_options;
	
	// check that an API key has been entered
	if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) {
		
		// setup the $lists variable as a blank array
		$lists = array();
		
		// load the Mail Chimp API class
		require_once('mailchimp/MCAPI.class.php');
		
		// load a new instance of the API class with our API key
		$api = new MCAPI($mc_options['mailchimp_api']);
		
		// retrieve an array of all email list data
		$list_data = $api->lists();
		
		// if at least one list was retrieved
		if($list_data) :
			// loop through each list
			foreach($list_data['data'] as $key => $list) :
				// store the list ID in our array ID key
				$lists[$key]['id'] = $list['id'];
				// store the list name our array NAME key
				$lists[$key]['name'] = $list['name'];
			endforeach;
		endif;
		// return an array of the lists with ID and name
		return $lists;
	}
	return false;
}