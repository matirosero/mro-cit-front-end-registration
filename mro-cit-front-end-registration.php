<?php
/*
Plugin Name: CIT Front End Registration and Login
Plugin URI: https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms
Description: Provides simple front end registration and login forms. Based on the tutorial by Pippin Williamson @ https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms
Version: 1.0
Author: Mat Rosero
Author URI: https://matilderosero.com
*/


/**
 * Load plugin textdomain.
 *
 * @since 0.1.0
 */
function mro_cit_frontend_registration_load_textdomain() {
	load_plugin_textdomain( 'mro-cit-frontend', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'mro_cit_frontend_registration_load_textdomain' );


/**
 * Registration.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/registration.php' );


/**
 * Edit profile.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/edit-profile.php' );



// used for tracking error messages
function pippin_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}


// displays error messages from form submissions
function pippin_show_error_messages() {
	if($codes = pippin_errors()->get_error_codes()) {
		echo '<div class="pippin_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = pippin_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error', 'mro-cit-frontend') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}
}

// Send messages on form submit
function mro_cit_frontend_messages( $new_message = null) {
	static $message = '';
	if ( isset( $new_message ) ) {
		$message = $new_message;
	}
	return $message;
}