<?php

// user login form
function pippin_login_form() {

	if(!is_user_logged_in()) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;

		$output = pippin_login_form_fields();
	} else {
		// could show some logged in user info here
		// $output = 'user info here';
		$output = '';
	}
	return $output;
}
add_shortcode('login_form', 'pippin_login_form');

add_filter( 'login_url', 'my_login_page', 10, 3 );
function my_login_page( $login_url, $redirect, $force_reauth ) {
    return home_url( '/perfil/?redirect_to=' . $redirect );
}




// login form fields
function pippin_login_form_fields() {


	
	ob_start(); ?>
		<h3 class="pippin_header"><?php _e('Login'); ?></h3>

		<?php
		// show any error messages after form submission
		pippin_show_error_messages(); ?>



		<form id="pippin_login_form"  class="pippin_form" action="" method="post">
			<fieldset>
				<p>
					<label for="pippin_user_Login"><?php _e('Username', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_login" id="pippin_user_login" class="required" type="text"/>
				</p>
				<p>
					<label for="pippin_user_pass"><?php _e('Password', 'mro-cit-frontend'); ?></label>
					<input name="pippin_user_pass" id="pippin_user_pass" class="required" type="password"/>
				</p>
				<p class="mro_user_remember">
					<label><input name="mro_rememberme" id="mro_rememberme" value="forever" type="checkbox"> Recordarme</label>
				</p>
				<p>
					<input type="hidden" name="pippin_login_nonce" value="<?php echo wp_create_nonce('pippin-login-nonce'); ?>"/>
					<input id="pippin_login_submit" type="submit" class="button button-primary" value="<?php _e('Login', 'mro-cit-frontend'); ?>"/>

				</p>
				<a href="<?php echo wp_lostpassword_url( get_permalink() ); ?>" title="Lost Password">Lost Password</a>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
	
}

// logs a member in after submitting a form
function pippin_login_member() {

	/*
	if ( isset( $_GET['login'] ) && $_GET['login'] == true ) {
		?>
		<div class="callout success">You're logged in. Yay!</div>
		<?php
	}
	*/

	if(isset($_POST['pippin_user_login']) && wp_verify_nonce($_POST['pippin_login_nonce'], 'pippin-login-nonce')) {

		// this returns the user ID and other info from the user name
		$user = get_user_by( 'login', $_POST['pippin_user_login'] );

		if(!$user) {
			// if the user name doesn't exist
			pippin_errors()->add('empty_username', __('Invalid username'));
		}

		if(!isset($_POST['pippin_user_pass']) || $_POST['pippin_user_pass'] == '') {
			// if no password was entered
			pippin_errors()->add('empty_password', __('Please enter a password'));
		}

		// check the user's login with their password

		//THIS FAILS WHEN USER IS WRONG
		if(!wp_check_password($_POST['pippin_user_pass'], $user->user_pass, $user->ID)) {
			// if the password is incorrect for the specified user
			pippin_errors()->add('empty_password', __('Incorrect password'));
		}

		if( isset( $_POST['mro_rememberme'] ) ) {
	        $remember = true;
	    } else {
	    	$remember = false;
	    }

		// retrieve all error messages
		$errors = pippin_errors()->get_error_messages();

		// only log the user in if there are no errors
		if(empty($errors)) {

			// wp_setcookie($_POST['pippin_user_login'], $_POST['pippin_user_pass'], true);
			// https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
			wp_set_auth_cookie( $user->ID, $remember);

			wp_set_current_user($user->ID, $_POST['pippin_user_login']);
			do_action('wp_login', $_POST['pippin_user_login']);

			wp_redirect( get_permalink().'?login=true' );
			exit;
		}
	}
}
add_action('init', 'pippin_login_member');