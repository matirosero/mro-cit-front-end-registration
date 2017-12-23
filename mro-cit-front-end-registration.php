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
 * Helper functions.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/helpers.php' );


/**
 * Registration.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/registration.php' );
require_once( dirname( __FILE__ ) . '/includes/registration-helpers.php' );
require_once( dirname( __FILE__ ) . '/includes/emails.php' );


/**
 * Login.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/login.php' );


/**
 * Lost password.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/lost-password.php' );


/**
 * Edit profile.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/edit-profile.php' );


/**
 * Mailchimp.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/includes/mailchimp-settings.php' );
require_once( dirname( __FILE__ ) . '/includes/mailchimp-manage-users.php' );