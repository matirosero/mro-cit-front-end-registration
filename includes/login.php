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


// login form fields
function pippin_login_form_fields() {

	return wp_login_form( array( 
		'echo' => false 
	) );

}

/*
 * Replace login url
 */
add_filter( 'login_url', 'mro_cit_login_page', 10, 3 );
function mro_cit_login_page( $login_url, $redirect, $force_reauth ) {
    return home_url( '/perfil/?redirect_to=' . $redirect );
}



/**
 * Function Name: front_end_login_fail.
 * Description: This redirects the failed login to the custom login page instead of default login page with a modified url
**/
add_action( 'wp_login_failed', 'mro_cit_front_end_login_fail' );
function mro_cit_front_end_login_fail( $username ) {

// Getting URL of the login page
$referrer = $_SERVER['HTTP_REFERER'];
// if there's a valid referrer, and it's not the default log-in screen
if( !empty( $referrer ) && !strstr( $referrer,'wp-login' ) && !strstr( $referrer,'wp-admin' ) ) {
    wp_redirect( home_url( '/perfil/?login=failed' ));
    exit;
}

}

/**
 * Function Name: check_username_password.
 * Description: This redirects to the custom login page if user name or password is   empty with a modified url
**/
add_action( 'authenticate', 'mro_cit_check_username_password', 1, 3);
function mro_cit_check_username_password( $login, $username, $password ) {

// Getting URL of the login page
$referrer = $_SERVER['HTTP_REFERER'];

// if there's a valid referrer, and it's not the default log-in screen
if( !empty( $referrer ) && !strstr( $referrer,'wp-login' ) && !strstr( $referrer,'wp-admin' ) ) {
    if( $username == "" || $password == "" ){
        wp_redirect( home_url( '/perfil/?login=empty' ));
        exit;
    }
}

}
// Replace my constant 'LOGIN_PAGE_ID' with your custom login page id.