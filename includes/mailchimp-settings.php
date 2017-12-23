<?php
/*
 * Srcs:
 * https://rudrastyh.com/category/mailchimp-api
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


//Settings page
function mro_cit_mailchimp_settings_page() {

	global $mc_options;

	?>
	<div class="wrap">
		<h2><?php _e('Mail Chimp Settings', 'mro-cit-frontend'); ?></h2>

		<form method="post" action="options.php" class="mro_cit_options_form">

			<?php settings_fields( 'mro_cit_mailchimp_settings_group' ); ?>
			<p>
				<label for="mro_cit_mailchimp_settings[mailchimp_api]"><?php _e( 'Mail Chimp API Key', 'mro-cit-frontend' ); ?></label><br/>

				<input class="regular-text" id="mro_cit_mailchimp_settings[mailchimp_api]" style="width: 300px;" name="mro_cit_mailchimp_settings[mailchimp_api]" value="<?php if(isset($mc_options['mailchimp_api'])) { echo $mc_options['mailchimp_api']; } ?>"/>

				<div class="description"><?php _e('Enter your Mail Chimp API key to enable a newsletter signup option with the registration form.', 'mro-cit-frontend'); ?></div>
			</p>

			<p>
				<?php $lists = mro_cit_get_mailchimp_lists(); ?>
				<select id="mro_cit_mailchimp_settings[mailchimp_list]" name="mro_cit_mailchimp_settings[mailchimp_list]">
					<option value="">none</option>
					<?php
						if($lists) :
							foreach($lists as $list) :
								echo '<option value="' . $list['id'] . '"' . selected($mc_options['mailchimp_list'], $list['id'], false) . '>' . $list['name'] . '</option>';
							endforeach;
						else :
					?>
					<option value="no list"><?php _e('no lists', 'mro-cit-frontend'); ?></option>
				<?php endif; ?>
				</select>

				<label for="mro_cit_mailchimp_settings[mailchimp_list]"><?php _e( 'Newsletter List', 'mro-cit-frontend' ); ?></label><br/>

				<div class="description"><?php _e('Choose the list to subscribe users to', 'mro-cit-frontend'); ?></div>
			</p>
			<!-- save the options -->
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'mro-cit-frontend' ); ?>" />
			</p>

		</form>
	</div><!--end .wrap-->
	<?php
}


// get an array of all mailchimp subscription lists
function mro_cit_get_mailchimp_lists() {

	global $mc_options;

	// check that an API key has been entered
	if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) {

		// setup the $lists variable as a blank array
		$lists = array();


		$api_key = $api_key = $mc_options['mailchimp_api'];;
		$dc = substr($api_key,strpos($api_key,'-')+1); // us5, us8 etc
		$args = array(
		 	'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
			)
		);
		$response = wp_remote_get( 'https://'.$dc.'.api.mailchimp.com/3.0/lists/', $args );
		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			foreach ( $body->lists as $key => $list ) {

				$lists[$key]['id'] = $list->id;
				$lists[$key]['name'] = $list->name;
			}

		}
		return $lists;

	}
	return false;
}



//Subscribe new users to mailchimp automatically
// add_action('user_register', 'mro_cit_user_register_hook', 20, 1 );

function mro_cit_user_register_hook( $user_id ){

	write_log('Send new user\'s info to mailchimp');

	global $mc_options;

	if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) {

		write_log('API ok!');

		$list_id = $mc_options['mailchimp_list'];
		$api_key = $mc_options['mailchimp_api'];

		$status = 'subscribed'; // subscribed, cleaned, pending, unsubscribed


		$user = get_userdata($user_id ); // feel fre to use get_userdata() instead
		$user_roles = $user->roles;

		if ( in_array( 'afiliado_enterprise_pendiente', $user_roles ) || in_array( 'afiliado_enterprise', $user_roles ) ) {
			$membership = 'Empresarial';
			$company = $user->nickname;
		} elseif ( in_array( 'afiliado_personal', $user_roles ) ) {
			$membership = 'Personal';
			$company = $user->mro_cit_user_company;
		} else {
			$membership = '';
			$company = '';
		}



		$merge_fields = array(
			'FNAME' 	=> $user->first_name,
			'LNAME' 	=> $user->last_name,
			'AFILIADO' 	=> $membership,
			'EMPRESA'	=> $company,
			// 'PHONE'		=> $user->mro_cit_user_phone,
			// 'PAIS'		=> $user->mro_cit_user_country
		);

		$args = array(
			'method' => 'PUT',
		 	'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
			),
			'body' => json_encode(array(
		    	'email_address' => $user->user_email,
				'status'        => $status, // subscribed, unsubscribed, pending
				'merge_fields'  => $merge_fields // in this post we will use only FNAME and LNAME

			))
		);

		$response = wp_remote_post( 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($user->user_email)), $args );

		$body = json_decode( $response['body'] );

		if ( $response['response']['code'] == 200 && $body->status == $status ) {
			// echo 'The user has been successfully ' . $status . '.';
			write_log('The user has been successfully ' . $status);
		} else {
			// echo '<b>' . $response['response']['code'] . $body->title . ':</b> ' . $body->detail;
			write_log($response['response']['code'] . $body->title . ': ' . $body->detail);
		}





		/*
		 * if user subscription was failed you can try to store the errors the following way
		 */
		if( $body->status != $status )
			update_user_meta( $user_id, '_subscription_error', 'User was not subscribed because:' . $body->detail );
	}

	// return FALSE if any of the above fail
	return false;
}


