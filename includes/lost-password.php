<?php


/**
 * Redirects the user to the custom "Forgot your password?" page instead of
 * wp-login.php?action=lostpassword.
 */

add_filter( 'lostpassword_url', 'mro_cit_lost_password_page', 10, 2 );
function mro_cit_lost_password_page( $lostpassword_url, $redirect ) {
    return home_url( '/contrasena-perdida/?redirect_to=' . $redirect );
}
