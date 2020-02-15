<?php

/*
 * Replace registration link
 */
add_filter( 'register', 'mro_cit_register_link' );
function mro_cit_register_link( $link ) {
	/*Required: Replace Register_URL with the URL of registration*/
	// $custom_register_link = 'Register_URL';
	/*Optional: You can optionally change the register text e.g. Signup*/
	$register_text = __('Become a member', 'mro-cit-frontend');
	$link = '<a href="'.get_permalink( 1839 ).'">'.$register_text.'</a>';
    return $link;
}


/*
 * Registration form
 */
function mro_cit_registration_form($atts) {

	extract(shortcode_atts(array(
        'membership' => 'personal',
    ), $atts));

	// only show the registration form to non-logged-in members, or to admins
	if( !is_user_logged_in() || current_user_can( 'manage_temp_subscribers' ) ) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;

		// check to make sure user registration is enabled
		$registration_enabled = get_option('users_can_register');

		// only show the registration form if allowed
		if($registration_enabled) {
			$output = mro_cit_registration_form_fields($membership);
		} else {
			$output = __('User registration is not enabled', 'mro-cit-frontend');
		}

	} else {
		$output = '<p class="callout warning">Por favor <a href="'.wp_logout_url().'">cierre su sesión</a> para realizar una nueva afiliación.</p>';
	}
	return $output;
}
add_shortcode('register_form', 'mro_cit_registration_form');


/*
 * registration form fields
 */
function mro_cit_registration_form_fields($membership = 'personal' ) {


	ob_start();
	?>

		<?php
		// Show messages to new users to make sure they are on the correct form
		if ( $membership == 'personal' ) { ?>

			<p><?php _e('Use this form if you wish to sign up for a Personal membership.', 'mro-cit-frontend'); ?>
			</p>

		<?php } elseif ( $membership == 'empresarial' || $membership == 'institucional' ) { ?>

			<p>Utilice este formulario si desea inscribirse como Afiliado <?php echo ucfirst( $membership ); ?>. <strong>Estaremos en contacto para coordinar el pago y finalizar la afiliación.</strong>
			</p>

		<?php } ?>

		<?php
		// show any error messages after form submission
		pippin_show_error_messages();
		?>

		<form id="mro_cit_registration_form" class="pippin_form" action="" method="POST">


			<?php
			if ( $membership == 'choose' ) { ?>

				<fieldset class="choose_membership">
					<?php cit_print_field('choose_membership'); ?>
				</fieldset>

			<?php } ?>



			<fieldset class="register-main-info">
				
				<?php cit_print_field('username',$membership); ?>

				<?php
				if ( $membership != 'personal' ) { 

					cit_print_field('name_business',$membership);
				
				} 

				cit_print_field('email',$membership);

				cit_print_field('name_first',$membership);

				cit_print_field('name_last',$membership);

				cit_print_field('phone');


				if ( $membership != 'personal' ) { 
					cit_print_field('business_sector',$membership);
				} 


				// If personal, occupation and company info
				if ( $membership != 'empresarial' && $membership != 'institucional' ) { 

					cit_print_field('occupation');

					cit_print_field('workplace');

				} 

				cit_print_field('country');

				?>

			</fieldset>

		    <fieldset class="register-password">
				
		    	<?php cit_print_field('password'); ?>

			</fieldset>

			<fieldset class="submit-button">
				
				<?php cit_print_field('submit', $membership); ?>

			</fieldset>

		</form>
	<?php
	return ob_get_clean();
}



/*
 * Handle register form
 */
function pippin_add_new_member() {
  	if (isset( $_POST["pippin_user_login"] ) && isset( $_POST['pippin_register_nonce'] ) && wp_verify_nonce($_POST['pippin_register_nonce'], 'pippin-register-nonce')) {


  		//Array to hold meta values
  		$updated_meta = array();
  		$mc_merge_fields  = array();
  		$subscribe_mailchimp = false;


		// Process username
		$user_login = sanitize_user( $_POST["pippin_user_login"] );
		// write_log('1. login: '.$user_login);

		if(username_exists($user_login)) {
			// Username already registered
			pippin_errors()->add('username_unavailable', __('Username already taken', 'mro-cit-frontend'));
			// write_log('LOGIN ERROR: Username already taken');
		}
		if(!validate_username($user_login)) {
			// invalid username
			pippin_errors()->add('username_invalid', __('Invalid username', 'mro-cit-frontend'));
			// write_log('LOGIN Error Invalid username');
		}
		if($user_login == '') {
			// empty username
			pippin_errors()->add('username_empty', __('Please enter a username', 'mro-cit-frontend'));
			// write_log('LOGIN ERROR: Empty username');
		}


		//Process email
		$user_email = sanitize_email( $_POST["pippin_user_email"] );
		// write_log('2. Email is '.$user_email);

		if(!is_email($user_email)) {
			//invalid email
			pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
			// write_log('Email error: Invalid email');
		}
		if(email_exists($user_email)) {
			//Email address already registered
			pippin_errors()->add('email_used', __('Email already registered', 'mro-cit-frontend'));
			// write_log('Email error: Email already in use');
		}


		// Process membership type
		$mro_cit_user_membership = $_POST["mro_cit_user_membership"];
		// write_log('3. Membership type: '.$mro_cit_user_membership);
	    // Valid membership type
	    if ( ! mro_cit_validate_membership( $mro_cit_user_membership ) ) {
            pippin_errors()->add( 'membership_error', __( 'Please enter a valid membership type.', 'mro-cit-frontend' ) );
            // write_log('Membership error: INVALID ACCORDING TO mro_cit_validate_membership()');
	    } else {
	    	$mro_cit_user_membership = sanitize_meta( 'mro_cit_user_membership', $mro_cit_user_membership, 'user' );
	    	// write_log('Sanitized membership type: '.$mro_cit_user_membership );


	    	// Set subscribe variable true according to membership (if not pending)
	    	if ( $mro_cit_user_membership == 'afiliado_personal' || $mro_cit_user_membership == 'afiliado_empresarial' || $mro_cit_user_membership == 'afiliado_institucional' || $mro_cit_user_membership == 'junta_directiva' ) {

	    		$subscribe_mailchimp = true;
	    		// write_log('Change subscribe variable to TRUE');
	    	}

	    	// Add appropriate merge fields for membership type
	    	// Pending enterprise are NOT subscribed
	    	if ( $subscribe_mailchimp == true ) {
		    	if ( $mro_cit_user_membership == 'afiliado_personal' ) {
		    		$mc_merge_fields['AFILIADO'] = 'Personal';
		    	} elseif ( $mro_cit_user_membership == 'afiliado_empresarial' ) {
		    		$mc_merge_fields['AFILIADO'] = 'Empresarial';
		    	} elseif ( $mro_cit_user_membership == 'afiliado_institucional' ) {
		    		$mc_merge_fields['AFILIADO'] = 'Institucional';
		    	} elseif ( $mro_cit_user_membership == 'junta_directiva' ) {
		    		$mc_merge_fields['AFILIADO'] = 'CIT';
		    	}


		    	// write_log('MERGE FIELD: afiliado: '.$mc_merge_fields['AFILIADO']);

		    	$mc_merge_fields['USERNAME'] = $user_login;
		    	// write_log('mergefield USERNAME: '.$user_login);
	    	}
	    }



		if ( isset( $_POST["mro_cit_user_phone"] ) ) {
			$mro_cit_user_phone = sanitize_text_field( $_POST["mro_cit_user_phone"] );
			$updated_meta['mro_cit_user_phone'] = $mro_cit_user_phone;

			if ( $subscribe_mailchimp == true ) {
				$mc_merge_fields['PHONE'] = $mro_cit_user_phone;
			}
			// write_log('4. Phone is '.$mro_cit_user_phone);
			// write_log('MERGE FIELD: phone: '.$mc_merge_fields['PHONE']);
		}

		if ( isset( $_POST["mro_cit_user_sector"] ) ) {
			$mro_cit_user_sector = sanitize_text_field( $_POST["mro_cit_user_sector"] );
			$updated_meta['mro_cit_user_sector'] = $mro_cit_user_sector;

			if ( $subscribe_mailchimp == true ) {
				$mc_merge_fields['SECTOR'] = $mro_cit_user_sector;
			}
			// write_log('5. Sector is '.$mro_cit_user_sector);
		}

		if ( isset( $_POST["mro_cit_user_occupation"] ) ) {
			$mro_cit_user_occupation = sanitize_text_field( $_POST["mro_cit_user_occupation"] );
			$updated_meta['mro_cit_user_occupation'] = $mro_cit_user_occupation;

			if ( $subscribe_mailchimp == true ) {
				$mc_merge_fields['OCUPACION'] = $mro_cit_user_occupation;
			}
			// write_log('5. Occupation is '.$mro_cit_user_occupation);
		}

		if ( isset( $_POST["mro_cit_user_company"] ) ) {
			$mro_cit_user_company = sanitize_text_field( $_POST["mro_cit_user_company"] );
			$updated_meta['mro_cit_user_company'] = $mro_cit_user_company;

			if ( $subscribe_mailchimp == true ) {
				$mc_merge_fields['EMPRESA'] = $mro_cit_user_company;
			}
			// write_log('6. COmpany is '.$mro_cit_user_company);
			// write_log('MERGE FIELD: EMPRESA: '.$mc_merge_fields['EMPRESA']. '(from custom field)');
		}


		//Process country
		$mro_cit_user_country = $_POST["mro_cit_user_country"];
		// write_log('7. Country is '.$mro_cit_user_country);
	    // Valid country
	    if ( ! mro_cit_validate_country( $mro_cit_user_country ) ) {
	        pippin_errors()->add( 'country_error', __( 'Please choose a valid country.', 'mro-cit-frontend' ) );
	        // write_log('Country error: invalid due to mro_cit_validate_country()');
	    } else {
	    	$mro_cit_user_country = sanitize_meta( 'mro_cit_user_country', $mro_cit_user_country, 'user' );
	    	$updated_meta['mro_cit_user_country'] = $mro_cit_user_country;

	    	if ( $subscribe_mailchimp == true ) {
		    	$mc_merge_fields['PAIS'] = $mro_cit_user_country;
		    }
	    	// write_log('Sanitized country is '.$mro_cit_user_country);
	    	// write_log('MERGE FIELD: country: '.$mc_merge_fields['PAIS']);
	    }


		//Process password
		$user_pass		= $_POST["pippin_user_pass"];
		// write_log('8. Password is '.$user_pass);
		$pass_confirm 	= $_POST["pippin_user_pass_confirm"];
		// write_log('9. Confirmed password is '.$user_pass);

		if($user_pass == '') {
			// passwords do not match
			pippin_errors()->add('password_empty', __('Please enter a password', 'mro-cit-frontend'));
			// write_log('Password error: empty');
		}
		if($user_pass != $pass_confirm) {
			// passwords do not match
			pippin_errors()->add('password_mismatch', __('Passwords do not match', 'mro-cit-frontend'));
			// write_log('Password error: mismatch');
		}


		$user_first 	= sanitize_text_field( $_POST["pippin_user_first"] );
		// write_log('10. First name is '.$user_first);

		$user_last	 	= sanitize_text_field( $_POST["pippin_user_last"] );
		// write_log('11. Last name is '.$user_last);

		if ( $subscribe_mailchimp == true ) {
			$mc_merge_fields['FNAME'] = $user_first;
			// write_log('MERGE FIELD: FNAME: '.$mc_merge_fields['FNAME']);

			$mc_merge_fields['LNAME'] = $user_last;
			// write_log('MERGE FIELD: LNAME: '.$mc_merge_fields['LNAME']);
		}


		if ( $mro_cit_user_membership != 'afiliado_personal' && $mro_cit_user_membership != 'junta_directiva' ) {

			// write_log('11.5 EMPRESARIAL/INSTITUCIONAL IS CHOSEN: '.$mro_cit_user_membership);

			if ( !isset( $_POST["mro_cit_user_nickname"] ) || empty( $_POST["mro_cit_user_nickname"] ) ) {
				pippin_errors()->add( 'nickname_error', __( 'Please fill in your company\'s name.', 'mro-cit-frontend' ) );
				// write_log('Nickname error: nickname not set');
			} else {
				$user_nickname 	= sanitize_text_field( $_POST["mro_cit_user_nickname"] );
				$user_display_name 	= $user_nickname;

				if ( $subscribe_mailchimp == true ) {
					$mc_merge_fields['EMPRESA'] = $user_nickname;
					// write_log('MERGE FIELD: EMPRESA: '.$mc_merge_fields['EMPRESA']. '(from nickname)');
				}
				// write_log('Sanitized company nick is'.$user_display_name);

			}
		} elseif ( $mro_cit_user_membership == 'afiliado_personal' || $mro_cit_user_membership == 'junta_directiva' ) {

			// write_log('11.5 PERSONAL OR JUNTA IS CHOSEN: '.$mro_cit_user_membership);

			$user_nickname 	= '';
			// write_log('Personal nick is '.$user_nickname. ' (should be blank');

			if ( $user_first != '' && $user_last != '' ) {
				$user_display_name 	= $user_first.' '.$user_last;
				// write_log('Diplay name is '.$user_display_name).' (Should be name lastname)';
			} elseif ( $user_first != '' ) {
				$user_display_name 	= $user_first;
				// write_log('Diplay name is '.$user_display_name).' (Should be name)';
			} else {
				$user_display_name 	= $user_login;
				// write_log('Diplay name is '.$user_display_name).' (Should be username)';
			}
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
					'nickname'       	=> $user_nickname,
				    'display_name'   	=> $user_display_name,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> $mro_cit_user_membership,
				)
			);
			if($new_user_id) {

				// MRo: Update user meta
				foreach ($updated_meta as $key => $value) {
					update_user_meta( $new_user_id, $key, $value );
				}

				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id, null, 'both');


				//Subscribe to mailchimp if it's a personal account
				if ( $subscribe_mailchimp == true ) {

					$status = 'subscribed';

					// Send to mailchimp function
					// write_log('MERGE FIELDS: '.implode(",",$mc_merge_fields));
					mro_cit_subscribe_email($user_email, $mc_merge_fields, $status);

				}

				//If new user (not logged in), log in and redirect
				if( !is_user_logged_in() ) {

					// write_log('User is not logged in, so log in');

					// https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
					wp_set_auth_cookie( $new_user_id, true);

					wp_set_current_user($new_user_id, $user_login);
					do_action('wp_login', $user_login);

					// send the newly created user to the home page after logging them in
					// write_log('Redirect to '.get_edit_user_link() . "?registration=complete");

					wp_redirect( get_edit_user_link() . "?registration=complete" ); exit;

				} else {
					// write_log( 'Redirect to ' . get_permalink( get_page_by_path( 'administrar-afiliados' ) ) );

					wp_redirect( get_permalink( get_page_by_path( 'administrar-afiliados' ) ) ); exit;
				}
			}
		}
	}
}
add_action('init', 'pippin_add_new_member');