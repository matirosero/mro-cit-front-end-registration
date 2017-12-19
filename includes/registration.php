<?php

// Replace registration link
add_filter( 'register', 'mro_cit_register_link' );
function mro_cit_register_link( $link ) {
	/*Required: Replace Register_URL with the URL of registration*/
	// $custom_register_link = 'Register_URL';
	/*Optional: You can optionally change the register text e.g. Signup*/
	$register_text = __('Become a member', 'mro-cit-frontend');
	$link = '<a href="'.get_permalink( 1839 ).'">'.$register_text.'</a>';
    return $link;
}

// user registration login form
function pippin_registration_form($atts) {

	extract(shortcode_atts(array(
        'membership' => 'personal',
    ), $atts));

	// only show the registration form to non-logged-in members
	if(!is_user_logged_in()) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;

		// check to make sure user registration is enabled
		$registration_enabled = get_option('users_can_register');

		// only show the registration form if allowed
		if($registration_enabled) {

			// $membership = array('membership');

			var_dump($membership);

			if ( $membership == 'personal' ) {
				$output = pippin_registration_form_fields();
			} else {
				$output = array('membership');
			}

			
		} else {
			$output = __('User registration is not enabled', 'mro-cit-frontend');
		}
		return $output;
	}
}
add_shortcode('register_form', 'pippin_registration_form');


// registration form fields
function pippin_registration_form_fields() {

	ob_start(); ?>
		<h3><?php _e('Register as a Member', 'mro-cit-frontend'); ?></h3>

		<p><?php _e('Use this form if you wish to sign up for a Personal membership.', 'mro-cit-frontend'); ?>
			<br /><a href="<?php echo get_permalink( 1853 ); ?>"><?php _e('Sign up for an Enterprise membership instead.', 'mro-cit-frontend'); ?></a></p>

		<?php
		// show any error messages after form submission
		pippin_show_error_messages(); ?>

		<form id="pippin_registration_form" class="pippin_form" action="" method="POST">
			<fieldset class="register-main-info">
				<p>
					<label for="pippin_user_Login"><?php _e('Username', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_login" id="pippin_user_login" class="required" type="text"/>
				</p>
				<p>
					<label for="pippin_user_email"><?php _e('Email', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_email" id="pippin_user_email" class="required" type="email"/>
				</p>
				<p>
					<label for="pippin_user_first"><?php _e('First Name', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_first" id="pippin_user_first" type="text"/>
				</p>
				<p>
					<label for="pippin_user_last"><?php _e('Last Name', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_last" id="pippin_user_last" type="text"/>
				</p>
				<p>
		            <label for="mro_cit_user_phone"><?php _e( 'Phone', 'mro-cit-frontend' ) ?></label>
	                <input type="text" name="mro_cit_user_phone" id="mro_cit_user_phone" class="input" value="" size="25" />
		        </p>
		        <p>
		            <label for="mro_cit_user_country"><?php _e( 'Country', 'mro-cit-frontend' ) ?><br />

	                <select class="cmb2_select" name="mro_cit_user_country" id="mro_cit_user_country">

	                    <?php
	                    $countries = country_list();

	                    foreach ($countries as $key => $country) {
	                        echo '<option value="' . $key . '">' . $country . '</option>';
	                    }
	                    ?>

	                </select>
		             </label>
		        </p>
		        <?php
		        /*
				<p>
		            <label for="mro_cit_user_membership"><?php _e( 'Membership type', 'mro-cit-frontend' ) ?></label>

	                <select class="cmb2_select" name="mro_cit_user_membership" id="mro_cit_user_membership">

	                    <option value="Afiliado Personal" selected="selected">Afiliado Personal</option>
	                    <option value="Afiliado Enterprise">Afiliado Enterprise</option>

	                </select>

		            <span>La cuota anual para Afiliados Enterprise es $650. Le daremos seguimiento a su inscripción por correo.</span>
		        </p>
		        */
		        ?>

		    </fieldset>
		    <fieldset class="register-extra-info">

				<p>
		            <label for="mro_cit_user_occupation"><?php _e( 'Occupation', 'mro-cit-frontend' ) ?></label>
	                <input type="text" name="mro_cit_user_occupation" id="mro_cit_user_occupation" class="input" value="" size="25" />
		        </p>
		    	<p>
		            <label for="mro_cit_user_company"><?php _e( 'Company', 'mro-cit-frontend' ) ?></label>
		                <input type="text" name="mro_cit_user_company" id="mro_cit_user_company" class="input" value="" size="25" />
		        </p>

		    </fieldset>
		    <fieldset class="register-password">
				<p>
					<label for="password"><?php _e('Password', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_pass" id="password" class="required" type="password"/>
				</p>
				<p>
					<label for="password_again"><?php _e('Password Again', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_pass_confirm" id="password_again" class="required" type="password"/>
				</p>

				<p>
					<label>
						<input type="checkbox" name="mc4wp-subscribe" value="1" checked />
						<?php _e('Subscribe to our newsletter.', 'mro-cit-frontend'); ?></label>
				</p>

				<p>
					<input type="hidden" name="pippin_register_nonce" value="<?php echo wp_create_nonce('pippin-register-nonce'); ?>"/>

					<input type="hidden" name="mro_cit_user_membership" value="afiliado_personal"/>

					<input type="submit" class="button button-primary" value="<?php _e('Become a member', 'mro-cit-frontend'); ?>"/>

				</p>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
}



// register a new user
function pippin_add_new_member() {
  	if (isset( $_POST["pippin_user_login"] ) && isset( $_POST['pippin_register_nonce'] ) && wp_verify_nonce($_POST['pippin_register_nonce'], 'pippin-register-nonce')) {
		$user_login		= sanitize_user( $_POST["pippin_user_login"] );
		$user_email		= sanitize_email( $_POST["pippin_user_email"] );
		$user_first 	= sanitize_text_field( $_POST["pippin_user_first"] );
		$user_last	 	= sanitize_text_field( $_POST["pippin_user_last"] );
		$user_pass		= $_POST["pippin_user_pass"];
		$pass_confirm 	= $_POST["pippin_user_pass_confirm"];

		//MRo custom user fields
		$mro_cit_user_phone = sanitize_text_field( $_POST["mro_cit_user_phone"] );
		$mro_cit_user_country = $_POST["mro_cit_user_country"];
		$mro_cit_user_membership = $_POST["mro_cit_user_membership"];
		$mro_cit_user_occupation = sanitize_text_field( $_POST["mro_cit_user_occupation"] );
		$mro_cit_user_company = sanitize_text_field( $_POST["mro_cit_user_company"] );

		// this is required for username checks
		// require_once(ABSPATH . WPINC . '/registration.php');

		if(username_exists($user_login)) {
			// Username already registered
			pippin_errors()->add('username_unavailable', __('Username already taken', 'mro-cit-frontend'));
		}
		if(!validate_username($user_login)) {
			// invalid username
			pippin_errors()->add('username_invalid', __('Invalid username', 'mro-cit-frontend'));
		}
		if($user_login == '') {
			// empty username
			pippin_errors()->add('username_empty', __('Please enter a username', 'mro-cit-frontend'));
		}
		if(!is_email($user_email)) {
			//invalid email
			pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
		}
		if(email_exists($user_email)) {
			//Email address already registered
			pippin_errors()->add('email_used', __('Email already registered', 'mro-cit-frontend'));
		}
		if($user_pass == '') {
			// passwords do not match
			pippin_errors()->add('password_empty', __('Please enter a password', 'mro-cit-frontend'));
		}
		if($user_pass != $pass_confirm) {
			// passwords do not match
			pippin_errors()->add('password_mismatch', __('Passwords do not match', 'mro-cit-frontend'));
		}

		//MRo custom validation

	    // Valid membership type
	    if ( ! mro_cit_validate_membership( $mro_cit_user_membership ) ) {
            pippin_errors()->add( 'membership_error', __( '<strong>ERROR</strong>: Please enter a valid membership type.', 'mro-cit-frontend' ) );
	    } else {
	    	$mro_cit_user_membership = sanitize_meta( 'mro_cit_user_membership', $mro_cit_user_membership, 'user' );
	    }

	    // Valid country
	    if ( ! mro_cit_validate_country( $mro_cit_user_country ) ) {
	        pippin_errors()->add( 'country_error', __( '<strong>ERROR</strong>: Please choose a valid country.', 'mro-cit-frontend' ) );
	    } else {
	    	$mro_cit_user_country = sanitize_meta( 'mro_cit_user_country', $mro_cit_user_country, 'user' );
	    }

		$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {

			$new_user_id = wp_insert_user(array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_first,
					'last_name'			=> $user_last,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'afiliado_personal'
				)
			);
			if($new_user_id) {

				// MRo: Update user meta
				update_user_meta( $new_user_id, 'mro_cit_user_company', $mro_cit_user_company );
				update_user_meta( $new_user_id, 'mro_cit_user_phone', $mro_cit_user_phone );
				update_user_meta( $new_user_id, 'mro_cit_user_occupation', $mro_cit_user_occupation );
				update_user_meta( $new_user_id, 'mro_cit_user_country', $mro_cit_user_country );
				update_user_meta( $new_user_id, 'mro_cit_user_membership', $mro_cit_user_membership );


				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id);

				// log the new user in
				// wp_setcookie($user_login, $user_pass, true); //obsolete

				// https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
				wp_set_auth_cookie( $new_user_id, true);

				wp_set_current_user($new_user_id, $user_login);
				do_action('wp_login', $user_login);

				// send the newly created user to the home page after logging them in
				wp_redirect(home_url()); exit;
			}

		}

	}
}
add_action('init', 'pippin_add_new_member');