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

		$output .= mro_cit_show_premium_members_table();

		// $output .= mro_cit_add_temp_member_form();

	} else {
		$output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
	}

	return $output;
}
add_shortcode('cit-manage-members', 'mro_cit_manage_members_shortcode');


function mro_cit_show_premium_members_table() {
	$output = '';

	$users = get_users( array( 
		'role__in' => array(
			'afiliado_empresarial',
			'afiliado_empresarial_pendiente',
			'afiliado_institucional',
			'afiliado_institucional_pendiente',
		),
	) );

	var_dump($users);

	// foreach ( $members as $user ) {
	// 	echo '<span>' . esc_html( $user->user_email ) . '</span>';
	// }

	// $members = mro_cit_get_mailchimp_list_members();

	if ( count( $users ) > 0 ) {
		$output .= '<h3>Suscriptores temporales</h3>
				<table>
					<tr>
						<th>Usuario</th>
						<th>Compañía</th>
						<th>E-mail</th>
						<th>Nombre</th>
						<th>Apellidos</th>
						<th>Estado</th>
						<th></th>
					</tr>';

		foreach ($users as $key => $user) {

			$nonce = wp_create_nonce('cit-manage-nonce');
			$link = '#';

			$pending = false;
			if ( members_current_user_has_role( 'afiliado_empresarial_pendiente' ) || members_current_user_has_role( 'afiliado_institucional_pendiente' ) ) {
				$pending = true;
			}
			// $link = admin_url('admin-ajax.php?action=mc_unsubscribe&email='.urlencode($member['email']).'&nonce='.$nonce);

			$output .= '<tr>
				<td>'.esc_html( $user->user_login ).'</td>
				<td>'.esc_html( $user->nickname ).'</td>
				<td>'.esc_html( $user->user_email ).'</td>
				<td>'.esc_html( $user->user_firstname ).'</td>
				<td>'.esc_html( $user->user_lastname ).'</td>
				<td>';

			if ( members_current_user_has_role( 'afiliado_empresarial_pendiente' ) || members_current_user_has_role( 'afiliado_institucional_pendiente' ) ) {
				$output .= '<input type="radio" name="pending" value="pending" id="pending" checked><label for="pending">PPendiente</label>
					<input type="radio" name="approved" value="approved" id="approved"><label for="approved">Aprobado</label>';
			} else {
				$output .= '<input type="radio" name="pending" value="pending" id="pending"><label for="pending">Pendiente</label>
					<input type="radio" name="approved" value="approved" id="approved" checked><label for="approved">Aprobado</label>';
			}

			$output .= '</td>
				<td><a class="delete" data-nonce="' . $nonce . '" data-email="' . esc_html( $user->user_email ) . '" href="#"  data-open="confirm-unsubscribe-email"><i class="icon-cancel"></i></a></td>
			</tr>';
		}

		$output .= '</table>';

	} else {
		$output .= '<p class="callout alert">No hay suscriptores temporales.</p>';
	}

	return $output;
}