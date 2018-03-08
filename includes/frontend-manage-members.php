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
				<p>¿Está seguro que quiere eliminar el afiliado <strong class="nickname"></strong>?</p>
				<p><a href="#" class="button secondary" data-close>Cancelar</a> <a class="button confirm-delete-member" data-action="cit_remove_member" href="#">Sí, eliminarlo</a></p>
				</div>';

			$output .= '<div class="reveal text-center" id="confirm-approve-member" data-reveal>
				<button class="close-button" data-close aria-label="Close modal" type="button">
					<i class="icon-cancel"></i>
				</button>
				<p class="confirm-ask"></p>
				<p><a href="#" class="button secondary" data-close>Cancelar</a> <a class="button confirm-approve-member" data-action="cit_approve_member" href="#"></a></p>
				</div>';

			$output .= '<div class="large reveal" id="edit-member" data-reveal>
				<button class="close-button" data-close aria-label="Close modal" type="button">
					<i class="icon-cancel"></i>
				</button>
				<p>Desde aquí puede editar la cuenta del afiliado <strong class="user-name"></strong></p>

				<form id="edit-member-form">

					<p><a href="#" class="button secondary" data-close>Cancelar</a> <a class="button save-member" data-action="cit_edit_member" href="#">Guardar</a></p>

				</form>
			</div>';
		}

		// $output .= mro_cit_add_temp_member_form();

	} else {
		$output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
	}

	return $output;
}
add_shortcode('cit-manage-members', 'mro_cit_manage_members_shortcode');


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
						<th>Tipo</th>
						<th></th>
						<th></th>
						<th></th>
					</tr>';

		foreach ($users as $key => $user) {

			$edit_nonce = wp_create_nonce('cit-edit-member-nonce');
			$edit_link = admin_url('admin-ajax.php?action=cit_edit_member&username='. $user->user_login .'&nonce='.$edit_nonce);

			$approve_nonce = wp_create_nonce('cit-approve-member-nonce');
			$approve_link = admin_url('admin-ajax.php?action=cit_approve_member&username='. $user->user_login .'&nonce='.$approve_nonce);

			$delete_nonce = wp_create_nonce('cit-delete-member-nonce');
			$delete_link = admin_url('admin-ajax.php?action=cit_remove_member&username='. $user->user_login .'&nonce='.$delete_nonce);

			$nonce = '';

			$output .= '<tr>
				<td>'.esc_html( $user->user_login ).'</td>
				<td>'.esc_html( $user->nickname ).'</td>';

			// $output .= <td>'.esc_html( $user->user_email ).'</td>
				// '<td>'.esc_html( $user->user_firstname ).'</td>
				// <td>'.esc_html( $user->user_lastname ).'</td>';

			$output .= '<td>'.mro_cit_premium_member_type( $user->ID ).'</td>';

			$output .= '<td><a class="edit-member button" data-nonce="' . $edit_nonce . '" data-nickname="' . esc_html( $user->nickname ) . '" data-username="' . esc_html( $user->user_login ) . '" href="#" data-open="edit-member">Editar</a></td><td>';

			if ( mro_cit_member_is_pending( $user->ID ) ) {
				$checked_status = '';
			} else {
				$checked_status = ' checked';
			}
			$output .= '<input type="checkbox" name="user-is-approved" value="1" data-nonce="' . $approve_nonce . '" data-nickname="' . esc_html( $user->nickname ) . '" data-username="' . esc_html( $user->user_login ) . '" data-open="confirm-approve-member"' . $checked_status . '> Aprovado';


			$output .= '</td>
				<td><a class="delete-member" data-nonce="' . $delete_nonce . '" data-username="' . $user->user_login . '" data-nickname="' . esc_html( $user->nickname ) . '" href="#" data-open="confirm-delete-member"><i class="icon-cancel"></i></a></td>
			</tr>';
		}

		$output .= '</table>';

	} else {
		$output .= '<p class="callout alert">No hay afiliados.</p>';
	}

	return $output;
}


add_action("wp_ajax_cit_approve_member", "cit_approve_member");
function cit_approve_member() {

    if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) && isset( $_REQUEST['nonce'] ) && wp_verify_nonce($_REQUEST['nonce'], 'cit-approve-member-nonce') ) {

    	$username = sanitize_user( $_REQUEST['username'] );

    	if ( username_exists( $username ) ) {
    		$user = get_user_by('login',$username);
    	} else {
    		pippin_errors()->add('username_invalid', __('Invalid username', 'mro-cit-frontend'));
    	}

    	$approve = ($_REQUEST['approve'] === 'true');

		if ( $approve === true && !mro_cit_member_is_pending( $user->ID ) ) {
			pippin_errors()->add('user is already approved', __('Member is already approved', 'mro-cit-frontend'));
			// write_log('mismatch between request and state');
		} elseif ( $approve === false && mro_cit_member_is_pending( $user->ID ) ) {
			pippin_errors()->add('user is not approved', __('Member is not approved (can\'t unapprove)', 'mro-cit-frontend'));
			// write_log('mismatch between request and state');
		}

    	$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {

			$nickname = $user->nickname;

			$additional_contacts = get_user_meta( $user->ID, 'mro_cit_user_additional_contacts', true );

			if ( $approve === true ) {
				// write_log('Approve it!');

				$status = 'subscribed';

				$mc_merge_fields  = array();

				$mc_merge_fields['PHONE'] = $user->mro_cit_user_phone;
				$mc_merge_fields['PAIS'] = $user->mro_cit_user_country;
				$mc_merge_fields['SECTOR'] = $user->mro_cit_user_sector;
				$mc_merge_fields['FNAME'] = $user->user_firstname;
				$mc_merge_fields['LNAME'] = $user->user_lastname;
				$mc_merge_fields['EMPRESA'] = $user->nickname;

				//Check which role
				if ( members_user_has_role( $user->ID, 'afiliado_institucional_pendiente' ) ) {

					// write_log('Is institucional pendiente');

					$mc_merge_fields['AFILIADO'] = 'Institucional';

					$old_role = 'afiliado_institucional_pendiente';
					$new_role = 'afiliado_institucional';

				} elseif ( members_user_has_role( $user->ID, 'afiliado_empresarial_pendiente' ) ) {

					// write_log('Is empresarial pendiente');

					$mc_merge_fields['AFILIADO'] = 'Empresarial';

					$old_role = 'afiliado_empresarial_pendiente';
					$new_role = 'afiliado_empresarial';

				}

				// write_log(implode($mc_merge_fields));

				//Change role
				$user->remove_role( $old_role );
				$user->set_role( $new_role );

				// Send to mailchimp function
				write_log('Subscribing '.$mc_merge_fields['FNAME'].' '.$mc_merge_fields['LNAME'].' '.$user->user_email);

				$subscribe = mro_cit_subscribe_email($user->user_email, $mc_merge_fields, $status);
				$result['message'] = $subscribe;

				//Get additionals and send to mailchimp
				if (is_array($additional_contacts)) {

					foreach ($additional_contacts as $contact) {
						write_log('Subscribing additional '.$contact['name'].' '.$contact['lastname'].' '.$contact['email']);
						$mc_merge_fields['FNAME'] = $contact['name'];
						$mc_merge_fields['LNAME'] = $contact['lastname'];

						$subscribe = mro_cit_subscribe_email($contact['email'], $mc_merge_fields, $status);
						$result['message'] .= $subscribe;

					}
				}

				$result['message'] .= '<p class="callout success">'.$user->nickname.' aprobado como Afiliado Empresarial/Institucional</p>';


			} else {
				// write_log('Unapprove it!');

				//Check which role
				if ( members_user_has_role( $user->ID, 'afiliado_institucional' ) ) {

					// write_log('Is institucional pendiente');

					$old_role = 'afiliado_institucional';
					$new_role = 'afiliado_institucional_pendiente';

				} elseif ( members_user_has_role( $user->ID, 'afiliado_empresarial' ) ) {

					// write_log('Is empresarial pendiente');

					$mc_merge_fields['AFILIADO'] = 'Empresarial';

					$old_role = 'afiliado_empresarial';
					$new_role = 'afiliado_empresarial_pendiente';

				}

				//Change role
				$user->remove_role( $old_role );
				$user->add_role( $new_role );

				//Get mail email and unsubscribe from mailchimp
				$unsubscribe = mro_cit_unsubscribe_email( $user->user_email );
				$result['message'] = $unsubscribe;

				//Get additionals and unsubscribe from mailchimp
				if (is_array($additional_contacts)) {

					foreach ($additional_contacts as $contact) {
						write_log('Unsubscribing additional '.$contact['name'].' '.$contact['lastname'].' '.$contact['email']);

						$unsubscribe = mro_cit_unsubscribe_email( $contact['email'] );
						$result['message'] .= $unsubscribe;

					}
				}

				$result['message'] .= '<p class="callout success">'.$user->nickname.' pendiente de aprobación.</p>';
			}
			$result['type'] = 'success';

		} else {
			$result['type'] = 'error';
			$result['message'] = $errors;
		}

		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	      	// $result['re place'] = mro_cit_build_temp_subscribers_table();
	      	$result = json_encode($result);
	      	echo $result;
	      	// write_log($result);
	      	// var_dump($result);
		} else {
		    header("Location: ".$_SERVER["HTTP_REFERER"]);
		}


    } else {
    	// write_log('NOT LOGGED IN');
    	exit("No naughty business please");
    }

    die();
}



add_action("wp_ajax_cit_mc_delete_member", "cit_mc_delete_member");
// add_action("wp_ajax_nopriv_cit_mc_unsubscribe", "cit_mc_unsubscribe");

function cit_mc_delete_member() {

	// $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
	// $slug = $uri_parts[0];

    if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) && isset( $_REQUEST['nonce'] ) && wp_verify_nonce($_REQUEST['nonce'], 'cit-delete-member-nonce') ) {

    	$username = sanitize_user( $_REQUEST['username'] );

    	if ( username_exists( $username ) ) {
    		$user = get_user_by('login',$username);
    	} else {
    		pippin_errors()->add('username_invalid', __('Invalid username', 'mro-cit-frontend'));
    	}

    	$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {

    		//Unsubscribe mail email
    		$unsubscribe = mro_cit_unsubscribe_email( $user->user_email );
    		$result['message'] = $unsubscribe;

    		$additional_contacts = get_user_meta( $user->ID, 'mro_cit_user_additional_contacts', true );

			if (is_array($additional_contacts)) {

				foreach ($additional_contacts as $contact) {

					//Unsubscribe each additional email
					$unsubscribe = mro_cit_unsubscribe_email( $contact['email'] );
		    		$result['message'] .= $unsubscribe;
		    		// write_log($unsubscribe);
				}
			}

			//dete meta
			delete_user_meta($user->ID, 'mro_cit_user_additional_contacts');

			$nickname = $user->nickname;

			//delete user
			if ( wp_delete_user( $user->ID ) ) {
				$result['type'] = 'success';
				$result['message'] .= '<p class="callout success">El afiliado <strong>'.$nickname.'</strong> fue eliminado.</p>';
			} else {
				$result['type'] = 'error';
				$result['message'] = '<p class="callout alert">El afiliado no pudo ser eliminado.</p>';
			}

		} else {
			$result['type'] = 'error';
			$result['message'] = $errors;
		}

		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	      	// $result['re place'] = mro_cit_build_temp_subscribers_table();
	      	$result = json_encode($result);
	      	echo $result;
	      	// write_log($result);
	      	// var_dump($result);
		} else {
		    header("Location: ".$_SERVER["HTTP_REFERER"]);
		}


    } else {
    	// write_log('NOT LOGGED IN');
    	exit("No naughty business please");
    }

    die();
}