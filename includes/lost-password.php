<?php


/**
 * Redirects the user to the custom "Forgot your password?" page instead of
 * wp-login.php?action=lostpassword.
 */

add_filter( 'lostpassword_url', 'mro_cit_lost_password_page', 10, 2 );
function mro_cit_lost_password_page( $lostpassword_url, $redirect ) {
    return home_url( '/contrasena-perdida/' );
}



function mro_cit_lost_password_form() {
	if(!is_user_logged_in()) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;

		$output = mro_cit_lost_password_form_fields();

	} else {
		$output = '<p class="callout warning">' . __('A password can\'t be reset if the user is logged in.', 'mro-cit-frontend') . '</p>';
	}

	return $output;
}
add_shortcode('lost_password', 'mro_cit_lost_password_form');


function mro_cit_lost_password_form_fields() {

	// global $current_user, $wp_roles;
	// $user = get_userdata( $current_user->ID );//use this for email or it wont update

	if ( !is_user_logged_in() ) {
		ob_start(); ?>
			<h3><?php _e('Lost password?', 'mro-cit-frontend'); ?></h3>

			<?php
			// show any error messages after form submission
			pippin_show_error_messages();

			//Show any messages
			if ( mro_cit_frontend_messages() != '' ) {
				echo mro_cit_frontend_messages();
			}
			?>

			<form id="lostpasswordform" action="<?php echo wp_lostpassword_url(); ?>" method="post">
				<fieldset>
					<p><?php _e('Please enter your username or email address. You will receive a link to create a new password via email.', 'mro-cit-frontend'); ?></p>

					<p>
						<label for="user_login"><?php _e('Username or E-mail', 'mro-cit-frontend'); ?></label>
						<?php $user_login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : ''; ?>
						<input name="user_login" id="user_login" class="required" type="text" value="<?php echo $user_login; ?>" />
					</p>

					<p>
						<input type="hidden" name="action" value="reset" />

						<input type="hidden" name="mro_lost_password_nonce" value="<?php echo wp_create_nonce('mro-lost-password-nonce'); ?>"/>

						<input type="submit" value="<?php _e('Get New Password', 'mro-cit-frontend'); ?>" class="button" id="submit" />
					</p>
				</fieldset>
			</form>

		<?php
		return ob_get_clean();		
	}
}

function mro_reset_password() {
	if ( !is_user_logged_in() && isset( $_POST['user_login'] ) && isset( $_POST['mro_lost_password_nonce'] ) && wp_verify_nonce( $_POST['mro_lost_password_nonce'], 'mro-lost-password-nonce' ) ) {

		write_log('Step 1: reset process starts');

		if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {

			pippin_errors()->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
			write_log('Empty username');

		} elseif ( strpos( $_POST['user_login'], '@' ) ) {
			write_log('Step 1.5: This is an email!');
			$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
			if ( empty( $user_data ) ) {
				pippin_errors()->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
				write_log('Invalid email! No one with that email');
			}

		} else {
			write_log('Step 1.5: this is a username, trim it');
			$login = trim($_POST['user_login']);
			$user_data = get_user_by('login', $login);
			if ( empty( $user_data ) ) {
				pippin_errors()->add('invalid_username', __('<strong>ERROR</strong>: There is no user registered with that username.'));
				write_log('Invalid username! No one with that name');
			}
		}

		$errors = pippin_errors()->get_error_messages();

		if(empty($errors)) {

			write_log('Step 2: no errors, can move on');
	
			// Redefining user_login ensures we return the right case in the email.
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key = get_password_reset_key( $user_data );

			write_log('User login: '.$user_login);
			write_log('User email: '.$user_email);
			write_log('Reset key: '.$key);

			if ( is_wp_error( $key ) ) {
				return $key;
			}

			if ( is_multisite() ) {
				$site_name = get_network()->site_name;
			} else {
				/*
				 * The blogname option is escaped with esc_html on the way into the database
				 * in sanitize_option we want to reverse this for the plain text arena of emails.
				 */
				$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			write_log('Sitename: '.$site_name);

			$message = __( 'Someone has requested a password reset for the following account:', 'mro-cit-frontend' ) . "\r\n\r\n";
			/* translators: %s: site name */
			$message .= sprintf( __( 'Site Name: %s', 'mro-cit-frontend'), $site_name ) . "\r\n\r\n";
			/* translators: %s: user login */
			$message .= sprintf( __( 'Username: %s', 'mro-cit-frontend'), $user_login ) . "\r\n\r\n";
			$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'mro-cit-frontend' ) . "\r\n\r\n";
			$message .= __( 'To reset your password, visit the following address:', 'mro-cit-frontend' ) . "\r\n\r\n";
			$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

			/* translators: Password reset email subject. %s: Site name */
			$title = sprintf( __( '[%s] Password Reset', 'mro-cit-frontend' ), $site_name );


			/**
			 * Filters the subject of the password reset email.
			 *
			 * @since 2.8.0
			 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
			 *
			 * @param string  $title      Default email title.
			 * @param string  $user_login The username for the user.
			 * @param WP_User $user_data  WP_User object.
			 */
			$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

			/**
			 * Filters the message body of the password reset mail.
			 *
			 * If the filtered message is empty, the password reset email will not be sent.
			 *
			 * @since 2.8.0
			 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
			 *
			 * @param string  $message    Default mail message.
			 * @param string  $key        The activation key.
			 * @param string  $user_login The username for the user.
			 * @param WP_User $user_data  WP_User object.
			 */
			$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

			if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
				write_log('Something went wrong');
				wp_die( __('The email could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.') );
			} else {
				mro_cit_frontend_messages( '<p class="callout success">' . __('Check your email for your confirmation link.', 'mro-cit-frontend') . '</p>' );
				write_log('Email was sent');
			}

			write_log($message);

		}

	}
}
add_action('init', 'mro_reset_password');