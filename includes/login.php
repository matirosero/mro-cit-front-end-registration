<?php


/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function my_login_redirect( $redirect_to, $request, $user ) {
    //is there a user to check?
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if ( in_array( 'administrator', $user->roles ) ) {
            // redirect them to the default place
            return $redirect_to;
        } else {
            return home_url();
        }
    } else {
        return $redirect_to;
    }
}
 
add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );



/*
 * Replace login url
 */
add_filter( 'login_url', 'mro_cit_login_page', 10, 3 );
function mro_cit_login_page( $login_url, $redirect, $force_reauth ) {
    return home_url( '/wp-login.php' );
}


/*
 * Additional SRC: https://www.hongkiat.com/blog/wordpress-custom-loginpage/
 */

// user login form
function pippin_login_form() {

	if(!is_user_logged_in()) {

		global $pippin_load_css;

		// set this to true so the CSS is loaded
		$pippin_load_css = true;


		//From Hongkiat tut

		//Check login variable
		$login  = (isset($_GET['login']) ) ? $_GET['login'] : 0;

		//Show error message
		if ( $login === "failed" ) {

			$lostpasword_url = wp_lostpassword_url();

			$alert = '<p class="callout alert login-msg">'
				. __( '<strong>ERROR:</strong> Invalid username and/or password.', 'mro-cit-frontend')
				. '<br /><a href="' . $lostpasword_url . '">' . __( 'Lost password?', 'mro-cit-frontend') . '</a>'
				. '</p>';

			echo $alert;

		} elseif ( $login === "empty" ) {
		  	echo '<p class="callout alert login-msg">' . __( '<strong>ERROR:</strong> Username and/or Password is empty.', 'mro-cit-frontend') . '</p>';
		} elseif ( $login === "false" ) {
		  	echo '<p class="callout warning login-msg">' . __( '<strong>ERROR:</strong> You are logged out.', 'mro-cit-frontend') . '</p>';
		}


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

	// See parameters: https://code.tutsplus.com/tutorials/build-a-custom-wordpress-user-flow-part-1-replace-the-login-page--cms-23627


	$return = wp_login_form( array(
		'echo' => false,
		'label_username' => __( 'Username or Email Address', 'mro-cit-frontend' ),
		// 'label_password' => __( 'Username' ),
	) );

	return $return;

}






/**
 * Function Name: front_end_login_fail.
 * Description: This redirects the failed login to the custom login page instead of default login page with a modified url
**/
add_action( 'wp_login_failed', 'mro_cit_front_end_login_fail' );
function mro_cit_front_end_login_fail( $username ) {

	// Getting URL of the login page
	$referrer = $_SERVER['HTTP_REFERER'];

	$referrer = mro_cit_remove_qs_key($referrer, 'login');

	// if there's a valid referrer, and it's not the default log-in screen
	if( !empty( $referrer ) && !strstr( $referrer,'wp-login' ) && !strstr( $referrer,'wp-admin' ) ) {
	    // wp_redirect( home_url( '/perfil/?login=failed' ));
	    // wp_redirect( get_permalink( 66 ) . "?login=failed" );
	    wp_redirect( $referrer . "?login=failed" );
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
	$referrer = mro_cit_remove_qs_key($referrer, 'login');

	// if there's a valid referrer, and it's not the default log-in screen
	if( !empty( $referrer ) && !strstr( $referrer,'wp-login' ) && !strstr( $referrer,'wp-admin' ) ) {
	    if( $username == "" || $password == "" ){
	        // wp_redirect( home_url( '/perfil/?login=empty' ));
	        // wp_redirect( get_permalink( 66 ) . "?login=empty" );
	        wp_redirect( $referrer . "?login=empty" );
	        exit;
	    }
	}

}


/*
 * TODO: Replace redirect plugin with custom code for when logged out, with message
 * (see Hongkiat)
 */