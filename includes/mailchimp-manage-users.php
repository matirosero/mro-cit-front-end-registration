<?php

/*
 * Shortcode with mailchimp sign up form
 */

// displays the mailchimp signup form
function mro_cit_mailchimp_form($redirect) {
	global $mc_options;
	ob_start();
		if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) { ?>
		<form id="mro_cit_mailchimp" action="" method="post">
			<p>
				<label for="mro_cit_mailchimp_email"><?php _e('Enter your email to subscribe to our newsletter', 'mro-cit-frontend'); ?></label><br/>
				<input name="mro_cit_mailchimp_email" id="mro_cit_mailchimp_email" type="email" placeholder="<?php _e('Email . . .', 'mro-cit-frontend'); ?>"/>
			</p>
			<p>
				<input type="hidden" name="redirect" value="<?php echo $redirect; ?>"/>
				<input type="hidden" name="action" value="mro_cit_mailchimp"/>
				<input type="submit" class="button button-primary" value="<?php _e('Sign Up', 'mro-cit-frontend'); ?>"/>
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

		//write_log('OK to process mailchimp form');

		// this contains the email address entered in the subscribe form
		$email = $_POST['mro_cit_mailchimp_email'];

		// check for a valid email
		if(!is_email($email)) {
			wp_die(__('Your email address is invalid', 'mro-cit-frontend'), __('Invalid Email', 'mro-cit-frontend'));
		} else {
			//write_log('Email OK');
		}


		// send this email to mailchimp
		mro_cit_subscribe_email($email);

		// send user to the confirmation page
		// wp_redirect($_POST['redirect']); exit;
	}
}
add_action('init', 'mro_cit_check_for_email_signup');


// adds an email to the mailchimp subscription list
function mro_cit_subscribe_email($email, $merge_fields) {

	// write_log('mro_cit_subscribe_email(): Send info to mailchimp');

	global $mc_options;


	// check that the API option is set
	if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) {

		$list_id = $mc_options['mailchimp_list'];
		$api_key = $mc_options['mailchimp_api'];

		// write_log('api key OK: '.$api_key);
		// write_log('list: '.$list_id);
		// write_log('subscribe this email: '.$email);


		// $api_key = 'YOUR API KEY';
		// $email = 'USER EMAIL';
		$status = 'subscribed'; // subscribed, cleaned, pending, unsubscribed

		$args = array(
			'method' => 'PUT',
		 	'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
			),
			'body' => json_encode(array(
		    	'email_address' => $email,
				'status'        => $status, // subscribed, unsubscribed, pending
				'merge_fields'  => $merge_fields // in this post we will use only FNAME and LNAME

			))
		);
		$response = wp_remote_post( 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)), $args );

		$body = json_decode( $response['body'] );

		if ( $response['response']['code'] == 200 && $body->status == $status ) {
			// echo 'The user has been successfully ' . $status . '.';
			//write_log('The user has been successfully ' . $status);
		} else {
			// echo '<b>' . $response['response']['code'] . $body->title . ':</b> ' . $body->detail;
			//write_log($response['response']['code'] . $body->title . ': ' . $body->detail);
		}

	}

	// return FALSE if any of the above fail
	return false;

}

// adds an email to the mailchimp subscription list
function mro_cit_unsubscribe_email($email) {

	write_log('mro_cit_unsubscribe_email(): Send info to mailchimp');

	global $mc_options;


	// check that the API option is set
	if(strlen(trim($mc_options['mailchimp_api'])) > 0 ) {

		$list_id = $mc_options['mailchimp_list'];
		$api_key = $mc_options['mailchimp_api'];

		// write_log('api key OK: '.$api_key);
		// write_log('list: '.$list_id);
		// write_log('subscribe this email: '.$email);


		// $api_key = 'YOUR API KEY';
		// $email = 'USER EMAIL';
		$status = 'unsubscribed'; // subscribed, cleaned, pending, unsubscribed

		$args = array(
			'method' => 'PATCH',
		 	'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
			),
			'body' => json_encode(array(
				'status'        => $status, // subscribed, unsubscribed, pending
			))
		);
		$response = wp_remote_post( 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)), $args );

		$body = json_decode( $response['body'] );

		if ( $response['response']['code'] == 200 && $body->status == $status ) {
			// echo 'The user has been successfully ' . $status . '.';
			//write_log('The user has been successfully ' . $status);
		} else {
			// echo '<b>' . $response['response']['code'] . $body->title . ':</b> ' . $body->detail;
			//write_log($response['response']['code'] . $body->title . ': ' . $body->detail);
		}

	}

	// return FALSE if any of the above fail
	return false;

}