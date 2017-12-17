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

		return $output;
	}
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


			?>

			<form method="POST">
				<fieldset>
					<p><?php _e('Please enter your username or email address. You will receive a link to create a new password via email.', 'mro-cit-frontend'); ?></p>

					<p>
						<label for="pippin_user_login"><?php _e('Username or E-mail', 'mro-cit-frontend'); ?></label>
						<?php $user_login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : ''; ?>
						<input name="pippin_user_login" id="pippin_user_login" class="required" type="text" value="<?php echo $user_login; ?>" />
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
	if ( !is_user_logged_in() && isset( $_POST['pippin_user_login'] ) && isset( $_POST['mro_lost_password_nonce'] ) && wp_verify_nonce( $_POST['mro_lost_password_nonce'], 'mro-lost-password-nonce' ) ) {

		write_log('Process reset password');

	}
}
add_action('init', 'mro_reset_password');