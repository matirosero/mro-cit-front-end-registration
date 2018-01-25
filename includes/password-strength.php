<?php

function mro_cit_password_strength_enqueue_script() {   
    wp_enqueue_script( 'password-strength-meter' );

    wp_enqueue_script( 'mediator', plugin_dir_url( __FILE__ ) . 'js/password-strength-meter-mediator.js', array( 'jquery', 'password-strength-meter' ), '', true );
}
add_action('wp_enqueue_scripts', 'mro_cit_password_strength_enqueue_script');

