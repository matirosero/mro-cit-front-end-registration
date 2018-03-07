<?php


add_action( 'wp_enqueue_scripts', 'mro_cit_mc_temp_users_enqueue', 100 );
function mro_cit_mc_temp_users_enqueue($hook) {

	wp_enqueue_script( 'cit-manage-members', plugin_dir_url( __FILE__ ) . 'js/ajax-manage-members.js', array('jquery'), '', true );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'cit-manage-members', 'ajax_object',
            array( 
            	 'ajax_url' => admin_url( 'admin-ajax.php' ), 
            ) );
}


function mro_cit_edit_member_form( $user_ID) {

}


function mro_cit_premium_member_type( $user_id ) {
	if ( members_user_has_role( $user_id, 'afiliado_empresarial' ) || members_user_has_role( $user_id, 'afiliado_empresarial_pendiente' ) ) {
		$type = 'Empresarial';
	} elseif ( members_user_has_role( $user_id, 'afiliado_institucional' ) || members_user_has_role( $user_id, 'afiliado_institucional_pendiente' ) ) {
		$type = 'Institucional';
	} else {
		return false;
	}
	return $type;
}

function mro_cit_member_is_pending( $user_id ) {
	if ( members_user_has_role( $user_id, 'afiliado_institucional_pendiente' ) || members_user_has_role( $user_id, 'afiliado_empresarial_pendiente' ) ) {
		return true;
	} else {
		return false;
	}
}