<?php

// user registration login form
function mro_cit_edit_profile_form() {

	// only show the registration form to non-logged-in members
	if ( is_user_logged_in() ) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;

		// check to make sure user registration is enabled
		// $registration_enabled = get_option('users_can_register');

		// only show the registration form if allowed
		// if($registration_enabled) {
			$output = mro_cit_edit_profile_form_fields();
		// } else {
		// 	$output = __('User registration is not enabled', 'mro-cit-frontend');
		// }
		return $output;
	}
}
add_shortcode('edit_profile_form', 'mro_cit_edit_profile_form');


// registration form fields
function mro_cit_edit_profile_form_fields() {

	global $current_user, $wp_roles;
	// $user = get_userdata( $current_user->ID );
	// var_dump($wp_roles);

	if ( is_user_logged_in() ) {

		ob_start(); ?>
			<h1>Hi <?php echo $current_user->user_firstname; ?></h1>
			<h3><?php _e('Edit your profile', 'mro-cit-frontend'); ?></h3>

			<?php
			// show any error messages after form submission
			pippin_show_error_messages(); 


			//Show any messages
			if ( mro_cit_frontend_messages() != '' ) {
				echo mro_cit_frontend_messages();
			}

			?>

			<form id="mro_edit_profile_form" class="pippin_form" action="" method="POST">
				<fieldset class="register-main-info">
					<p>
						<label for="pippin_user_Login"><?php _e('Username', 'mro-cit-frontend'); ?></label>
						<input name="pippin_user_login" id="pippin_user_login" class="required" type="text" value="<?php echo $current_user->user_login; ?>" disabled="disabled" />
					</p>
					<p>
						<label for="pippin_user_email"><?php _e('Email', 'mro-cit-frontend'); ?></label>
						<input name="pippin_user_email" id="pippin_user_email" class="required" type="email" value="<?php echo $current_user->user_email; ?>" />
					</p>
					<p>
						<label for="pippin_user_first"><?php _e('First Name', 'mro-cit-frontend'); ?></label>
						<input name="pippin_user_first" id="pippin_user_first" type="text" value="<?php echo $current_user->user_firstname; ?>" />
					</p>
					<p>
						<label for="pippin_user_last"><?php _e('Last Name', 'mro-cit-frontend'); ?></label>
						<input name="pippin_user_last" id="pippin_user_last" type="text" value="<?php echo $current_user->user_lastname; ?>" />
					</p>
					<p>
			            <label for="mro_cit_user_phone"><?php _e( 'Phone', 'mro-cit-frontend' ) ?></label>
		                <input type="text" name="mro_cit_user_phone" id="mro_cit_user_phone" class="input" value="<?php echo $current_user->mro_cit_user_phone; ?>" size="25" />
			        </p>
			        <p>
			            <label for="mro_cit_user_country"><?php _e( 'Country', 'mro-cit-frontend' ) ?><br />

		                <select class="cmb2_select" name="mro_cit_user_country" id="mro_cit_user_country">

		                    <option value="<?php echo $current_user->mro_cit_user_country; ?>" selected="selected" ><?php echo $current_user->mro_cit_user_country; ?></option>

		                    <?php
		                    $countries = country_list();

		                    foreach ($countries as $key => $country) {
		                        echo '<option value="' . $key . '">' . $country . '</option>';
		                    }
		                    ?>

		                </select>
			             </label>
			        </p>


			    </fieldset>
			    <fieldset class="register-extra-info">

					<p>
			            <label for="mro_cit_user_occupation"><?php _e( 'Occupation', 'mro-cit-frontend' ) ?></label>
		                <input type="text" name="mro_cit_user_occupation" id="mro_cit_user_occupation" class="input" value="<?php echo $current_user->mro_cit_user_occupation; ?>" size="25" />
			        </p>
			    	<p>
			            <label for="mro_cit_user_company"><?php _e( 'Company', 'mro-cit-frontend' ) ?></label>
			                <input type="text" name="mro_cit_user_company" id="mro_cit_user_company" class="input" value="<?php echo $current_user->mro_cit_user_company; ?>" size="25" />
			        </p>

			    </fieldset>
			    <fieldset class="register-password">
					<h5><?php _e('New Password', 'mro-cit-frontend'); ?></h5>
					<p><?php _e('Leave blank to keep password unchanged.', 'mro-cit-frontend'); ?></p>
					<p>
						<label for="password"><?php _e('New Password', 'mro-cit-frontend'); ?></label>
						<input name="pippin_user_pass" id="password" class="required" type="password"/>
					</p>
					<p>
						<label for="password_again"><?php _e('New Password Again', 'mro-cit-frontend'); ?></label>
						<input name="pippin_user_pass_confirm" id="password_again" class="required" type="password"/>
					</p>

					<p>
						<input type="hidden" name="mro_edit_profile_nonce" value="<?php echo wp_create_nonce('mro-edit-profile-nonce'); ?>"/>
						<input type="submit" class="button button-primary" value="<?php _e('Edit profile', 'mro-cit-frontend'); ?>"/>

					</p>
				</fieldset>
			</form>
		<?php
		return ob_get_clean();
	}
}


// register a new user
function mro_edit_member() {
	$current_user = wp_get_current_user();

  	if ( is_user_logged_in() && isset( $_POST['mro_edit_profile_nonce'] ) && wp_verify_nonce( $_POST['mro_edit_profile_nonce'], 'mro-edit-profile-nonce' ) ) {

  		write_log('Edit form function works!');

		if ( !empty( $_POST["pippin_user_email"] ) ) {
			$user_email = sanitize_email( $_POST["pippin_user_email"] );

	        if ( !is_email( $user_email ) ) {
	        	//Invalid email
	        	pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
	        	// wp_redirect( home_url() . '?validation=emailnotvalid' );
				// exit;
	        } elseif ( email_exists( $user_email ) && ( email_exists( $user_email ) != $current_user->ID ) ) {
	        	//Email address already registered
				pippin_errors()->add('email_used', __('Email already registered', 'mro-cit-frontend'));
				// wp_redirect( home_url() . '?validation=emailexists' );
				// exit;
	        }
		}


		if ( !empty( $_POST["pippin_user_first"] ) ) {
			$user_first = sanitize_text_field( $_POST["pippin_user_first"] );
		}

		if ( !empty( $_POST["pippin_user_last"] ) ) {
			$user_last	 	= sanitize_text_field( $_POST["pippin_user_last"] );
		}

		//MRo custom user fields
		if ( !empty( $_POST["mro_cit_user_phone"] ) ) {
			$mro_cit_user_phone = sanitize_text_field( $_POST["mro_cit_user_phone"] );
		}


		if ( !empty( $_POST["mro_cit_user_country"] ) ) {
			$mro_cit_user_country = $_POST["mro_cit_user_country"];

		    // Valid country
		    if ( ! mro_cit_validate_country( $mro_cit_user_country ) ) {
		        pippin_errors()->add( 'country_error', __( 'Please choose a valid country.', 'mro-cit-frontend' ) );
		    } else {
		    	$mro_cit_user_country = sanitize_meta( 'mro_cit_user_country', $mro_cit_user_country, 'user' );
		    }
		}


		if ( !empty( $_POST["mro_cit_user_occupation"] ) ) {
			$mro_cit_user_occupation = sanitize_text_field( $_POST["mro_cit_user_occupation"] );
		}


		if ( !empty( $_POST["mro_cit_user_company"] ) ) {
			$mro_cit_user_company = sanitize_text_field( $_POST["mro_cit_user_company"] );
		}


		if ( !empty($_POST['pippin_user_pass'] ) || !empty( $_POST['pippin_user_pass_confirm'] ) ) {
			$new_user_pass		= $_POST["pippin_user_pass"];
			$new_pass_confirm 	= $_POST["pippin_user_pass_confirm"];			

			if($user_pass == '') {
				// passwords do not match
				pippin_errors()->add('password_empty', __('Please enter a password', 'mro-cit-frontend'));
			}
			if($user_pass != $pass_confirm) {
				// passwords do not match
				pippin_errors()->add('password_mismatch', __('Passwords do not match', 'mro-cit-frontend'));
			}
		}



		$errors = pippin_errors()->get_error_messages();

		if(empty($errors)) {

			write_log('No errors, can edit user!');
			mro_cit_frontend_messages( '<p class="callout success">Your profile has been succesfully edited!</p>' );

			write_log(mro_cit_frontend_messages());

		}

  	}
}
add_action('init', 'mro_edit_member');