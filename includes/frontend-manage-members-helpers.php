<?php

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