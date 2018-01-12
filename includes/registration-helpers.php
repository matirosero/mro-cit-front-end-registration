<?php

function mro_cit_validate_phone($value) {
	if ( preg_match( '/^[0-9]{4}(-|\s)*[0-9]{4}$/', $value ) ) {
		return true;
	}
}

function mro_cit_validate_from_array($value, $valid_values) {
	$value = sanitize_text_field( $value );
	if( in_array( $value, $valid_values ) ) {
		return true;
    }
}

function mro_cit_validate_membership( $value ) {
	$valid_values = array(
    'afiliado_personal',
    'afiliado_empresarial',
    'afiliado_empresarial_pendiente',
 	);
 	if ( mro_cit_validate_from_array($value, $valid_values) ) {
 		return true;
 	}
}

function mro_cit_validate_country( $value ) {
  $valid_values = countries_plain();
  if ( mro_cit_validate_from_array($value, $valid_values) ) {
    return true;
  }
}

function sanitize_membership( $value ) {

  $value = sanitize_text_field( $value );

  $valid_values = array(
    'afiliado_personal',
    'afiliado_empresarial',
    'afiliado_empresarial_pendiente',
  );

  if( ! in_array( $value, $valid_values ) ) {

    wp_die( 'Invalid value, go back and try again.' );
  }

  return $value;
}
add_filter( 'sanitize_user_meta_mro_cit_user_membership', 'sanitize_membership' );


function sanitize_country( $value ) {

   $value = sanitize_text_field( $value );

   $valid_values = countries_plain();

   if( ! in_array( $value, $valid_values ) ) {

        wp_die( 'Invalid value, go back and try again.' );
    }

    return $value;
}
add_filter( 'sanitize_user_meta_mro_cit_user_country', 'sanitize_country' );

