<?php


function mro_cit_manage_members_shortcode($atts, $content = null ) {

	global $current_user, $wp_roles;

	extract( shortcode_atts( array(
		'afiliado' => ''
	), $atts ) );

	$output = '';

	if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) ) {

		//Show any messages
		if ( mro_cit_frontend_messages() != '' ) {
			echo mro_cit_frontend_messages();
			// write_log('frontend messages: '.mro_cit_frontend_messages());
		}

		if ( mro_cit_build_premium_members_list() ) {
			$output .= '<div class="members-table" id="premium-members-table">';
			$output .= mro_cit_build_premium_members_list();
			$output .= '</div>';

			$output .= '<div class="reveal text-center" id="confirm-delete-member" data-reveal>
				<button class="close-button" data-close aria-label="Close modal" type="button">
					<i class="icon-cancel"></i>
				</button>
				<p>¿Está seguro que quiere eliminar el afiliado <strong class="user-name"></strong>?
				<p><a href="#" class="button secondary" data-close>Cancelar</a> <a class="button confirm-delete-member" data-action="cit_remove_member" href="#">Si, eliminarlo</a></p>
				</div>';

			$output .= '<div class="reveal text-center" id="manage-member-contacts" data-reveal>
				<button class="close-button" data-close aria-label="Close modal" type="button">
					<i class="icon-cancel"></i>
				</button>
				<p>Desde aquí puede administrar la cuenta del afiliado <strong class="confirm-username"></strong>?
				<p><a href="#" class="button secondary" data-close>Cancelar</a> <a class="button confirm-delete" data-action="cit_aprove_member" href="#">Si, eliminarlo</a></p>
				</div>';
		}

		// $output .= mro_cit_add_temp_member_form();

	} else {
		$output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
	}

	return $output;
}
add_shortcode('cit-manage-members', 'mro_cit_manage_members_shortcode');

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

function mro_cit_build_premium_members_list() {
	$output = '';

	$users = get_users( array( 
		'role__in' => array(
			'afiliado_empresarial',
			'afiliado_empresarial_pendiente',
			'afiliado_institucional',
			'afiliado_institucional_pendiente',
		),
	) );

	// var_dump($users);

	// foreach ( $members as $user ) {
	// 	echo '<span>' . esc_html( $user->user_email ) . '</span>';
	// }

	// $members = mro_cit_get_mailchimp_list_members();

	if ( count( $users ) > 0 ) {
		$output .= '<h3>Suscriptores empresariales/institucionales</h3>
				<table>
					<tr>
						<th>Usuario</th>
						<th>Compañía</th>
						<th>Contactos</th>
						<th>Tipo</th>
						<th>Aprobado</th>
						<th>Eliminar</th>
					</tr>';

		foreach ($users as $key => $user) {

			// var_dump($user);

			$aprove_nonce = wp_create_nonce('cit-aprove-member-nonce');
			$aprove_link = admin_url('admin-ajax.php?action=cit_aprove_member&id='. $user->ID .'&nonce='.$aprove_nonce);

			$delete_nonce = wp_create_nonce('cit-manage-nonce');
			$delete_link = admin_url('admin-ajax.php?action=cit_remove_member&id='. $user->ID .'&nonce='.$delete_nonce);

			$nonce = '';

			$output .= '<tr>
				<td>'.esc_html( $user->user_login ).'</td>
				<td>'.esc_html( $user->nickname ).'</td>';

			// $output .= <td>'.esc_html( $user->user_email ).'</td>
				// '<td>'.esc_html( $user->user_firstname ).'</td>
				// <td>'.esc_html( $user->user_lastname ).'</td>';

			$output .= '<td><a class="manage-members button secondary" data-nonce="' . $nonce . '" data-id="' . esc_html( $user->ID ) . '" href="#"  data-open="manage-member-contacts">Contactos</a></td>';

			$output .= '<td>'.mro_cit_premium_member_type( $user->ID ).'</td>
				<td>';

			if ( mro_cit_member_is_pending( $user->ID ) ) {
				$output .= '<a href="#" class="button">Aprobar</a>';
			} else {
				$output .= '<i class="approved icon-ok"></i>';
			}

			$output .= '</td>
				<td><a class="delete-member" data-nonce="' . $delete_nonce . '" data-id="' . $user->ID . '" data-user="' . esc_html( $user->nickname ) . '" href="#" data-open="confirm-delete-member"><i class="icon-cancel"></i></a></td>
			</tr>';
		}

		$output .= '</table>';

	} else {
		$output .= '<p class="callout alert">No hay suscriptores temporales.</p>';
	}

	return $output;
}