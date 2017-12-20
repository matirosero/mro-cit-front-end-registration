<?php

add_filter( 'edit_profile_url', 'mro_cit_modify_profile_url', 10, 3 );

/**
 * http://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/link-template.php#L2284
 *
 * @param string $scheme The scheme to use. 
 * Default is 'admin'. 'http' or 'https' can be passed to force those schemes.
*/
function mro_cit_modify_profile_url( $url, $user_id, $scheme ) {
    // Makes the link to http://example.com/custom-profile
    $url = get_permalink( 1844 );
    return $url;
}


// user registration login form
function mro_cit_edit_profile_form() {

	// only show the registration form to non-logged-in members
	if ( is_user_logged_in() ) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;

		$output = mro_cit_edit_profile_form_fields();

		return $output;
	}
}
add_shortcode('edit_profile_form', 'mro_cit_edit_profile_form');


// registration form fields
function mro_cit_edit_profile_form_fields() {

	global $current_user, $wp_roles;
	$user = get_userdata( $current_user->ID );//use this for email or it wont update

	// var_dump($current_user);

	// $user_info = get_userdata($current_user->ID);
	// var_dump($user_info);

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
						<input name="pippin_user_email" id="pippin_user_email" class="required" type="email" value="<?php echo $user->user_email; ?>" />
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

  		$updated_info = array(
  			'ID' => $current_user->ID,
  		);

  		$updated_meta = array();


		if ( !empty( $_POST["pippin_user_email"] ) ) {
			$user_email = sanitize_email( $_POST["pippin_user_email"] );

	        if ( !is_email( $user_email ) ) {
	        	//Invalid email
	        	pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
	        } elseif ( email_exists( $user_email ) && ( email_exists( $user_email ) != $current_user->ID ) ) {
	        	//Email address already registered
				pippin_errors()->add('email_used', __('Email already registered', 'mro-cit-frontend'));
	        }

	        $updated_info['user_email'] = $user_email;
		}


		if ( !empty( $_POST["pippin_user_first"] ) ) {
			$user_first = sanitize_text_field( $_POST["pippin_user_first"] );
			$updated_info['first_name'] = $user_first;
			$updated_info['nickname'] = $user_first;
			$updated_info['display_name'] = $user_first;
		}

		if ( !empty( $_POST["pippin_user_last"] ) ) {
			$user_last = sanitize_text_field( $_POST["pippin_user_last"] );
			$updated_info['last_name'] = $user_last;
		}

		//MRo custom user fields
		if ( !empty( $_POST["mro_cit_user_phone"] ) ) {
			$mro_cit_user_phone = sanitize_text_field( $_POST["mro_cit_user_phone"] );
		} else {
			$mro_cit_user_phone = '';
		}
		$updated_meta['mro_cit_user_phone'] = $mro_cit_user_phone;


		if ( !empty( $_POST["mro_cit_user_country"] ) ) {
			$mro_cit_user_country = $_POST["mro_cit_user_country"];

		    // Valid country
		    if ( ! mro_cit_validate_country( $mro_cit_user_country ) ) {
		        pippin_errors()->add( 'country_error', __( 'Please choose a valid country.', 'mro-cit-frontend' ) );
		    } else {
		    	$mro_cit_user_country = sanitize_meta( 'mro_cit_user_country', $mro_cit_user_country, 'user' );
		    	$updated_meta['mro_cit_user_country'] = $mro_cit_user_country;
		    }
		}


		if ( !empty( $_POST["mro_cit_user_occupation"] ) ) {
			$mro_cit_user_occupation = sanitize_text_field( $_POST["mro_cit_user_occupation"] );
		} else {
			$mro_cit_user_occupation = '';
		}
		$updated_meta['mro_cit_user_occupation'] = $mro_cit_user_occupation;


		if ( !empty( $_POST["mro_cit_user_company"] ) ) {
			$mro_cit_user_company = sanitize_text_field( $_POST["mro_cit_user_company"] );
		} else {
			$mro_cit_user_company = '';
		}
		$updated_meta['mro_cit_user_company'] = $mro_cit_user_company;


		if ( !empty($_POST['pippin_user_pass'] ) || !empty( $_POST['pippin_user_pass_confirm'] ) ) {
			$new_user_pass		= $_POST["pippin_user_pass"];
			$new_pass_confirm 	= $_POST["pippin_user_pass_confirm"];

			if($new_user_pass != $new_pass_confirm) {
				// passwords do not match
				pippin_errors()->add('password_mismatch', __('Passwords do not match', 'mro-cit-frontend'));
			} else {
				$updated_info['user_pass'] = $new_user_pass;
			}
		}

		$errors = pippin_errors()->get_error_messages();

		if(empty($errors)) {

			write_log('No errors, can edit user!');

			//edit profile
			write_log('USER ID = '.$current_user->ID);

			// write_log(var_dump($updated_info));
			// write_log(var_dump($updated_meta));

			$user_data = wp_update_user( $updated_info );

			foreach ($updated_meta as $key => $value) {
				update_user_meta( $current_user->ID, $key, $value );
			}

			/* Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot */
		    do_action('edit_user_profile_update', $current_user->ID);

			if ( is_wp_error( $user_data ) ) {
				// There was an error, probably that user doesn't exist.
				write_log('error :(');
			} else {
				write_log('YAY! it worked');
				//Send success message
			}

			do_action('edit_user_profile_update', $current_user->ID);

			mro_cit_frontend_messages( '<p class="callout success">' . __('Your profile has been succesfully edited!', 'mro-cit-frontend') . '</p>' );

		}

  	}
}
add_action('init', 'mro_edit_member');