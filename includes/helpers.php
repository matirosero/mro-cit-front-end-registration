<?php

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
		        echo '<p class="callout alert error"><strong>' . __('Error', 'mro-cit-frontend') . '</strong>: ' . $message . '</p>';
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

//http://developer-paradize.blogspot.com/2013/10/how-to-remove-query-string-from-url-in.html
function mro_cit_remove_qs_key($url, $key) {
	return preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
}


//https://maxchadwick.xyz/blog/stripping-a-query-parameter-from-a-url-in-php
function mro_cit_http_strip_query_param($url, $param) {
    $pieces = parse_url($url);
    if (!$pieces['query']) {
        return $url;
    }

    $query = [];
    parse_str($pieces['query'], $query);
    if (!isset($query[$param])) {
        return $url;
    }

    unset($query[$param]);
    $pieces['query'] = http_build_query($query);

    return http_build_url($pieces);
}