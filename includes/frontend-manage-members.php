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

			$output .= '<div class="reveal" id="edit-contact" data-reveal>
				<button class="close-button" data-close aria-label="Close modal" type="button">
					<i class="icon-cancel"></i>
				</button>
				<p>Editar información del contacto principal de <strong class="nickname"></strong></p>
				<form id="edit-contact" action="" method="POST">
					<input type="hidden" name="nonce" value="">
					<input type="hidden" name="id" value="">
					<input type="hidden" name="username" value="">
					<input type="hidden" name="nickname" value="">
					<p><label for="firstname">Nombre</label><input type="text" name="firstname" value=""></p>
					<p><label for="lastname">Apellidos</label><input type="text" name="lastname" value=""></p>
					<p><label for="email">Email</label><input type="email" name="email" value=""></p>
					<p><a href="#" class="button secondary" data-close>Cancelar</a> <input type="submit" class="button save-contact" data-action="cit_save_contact" value="Guardar" /></p>
				</form>
				</div>';

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

	if ( count( $users ) > 0 ) {
		$output .= '<h3>Empresariales/Institucionales</h3>
				<table>
					<tr>
						<th>Nombre</th>
						<th>Tipo</th>
						<th>Contacto</th>
						<th>Emails adicionales</th>
						<th>Estado</th>
						<th>Borrar</th>
					</tr>';

		foreach ($users as $key => $user) {

			// $edit_nonce = wp_create_nonce('cit-edit-member-nonce');
			$edit_link = get_permalink( get_page_by_title( 'Añadir contactos adicionales' ) ).'?username='. $user->user_login;

			$approve_nonce = wp_create_nonce('cit-approve-member-nonce');
			$approve_link = admin_url('admin-ajax.php?action=cit_approve_member&username='. $user->user_login .'&nonce='.$approve_nonce);

			$edit_contact_nonce = wp_create_nonce('cit-edit-contact-nonce');

			$edit_contact_link = admin_url('admin-ajax.php?action=cit_edit_contact&username='. $user->user_login .'&nonce='.$edit_contact_nonce);

			$delete_nonce = wp_create_nonce('cit-delete-member-nonce');
			$delete_link = admin_url('admin-ajax.php?action=cit_remove_member&username='. $user->user_login .'&nonce='.$delete_nonce);

			$nonce = '';

			$output .= '<tr>
				<td>'.esc_html( $user->nickname ).'</td>';

			$output .= '<td>'.mro_cit_premium_member_type( $user->ID ).'</td>';

			$output .= '<td>';

			$output .= '<div class="main-contact-info">'.esc_html( $user->user_firstname ).' '.esc_html( $user->user_lastname ).'<br />'.esc_html( $user->user_email );

			$output .= '<a class="edit-contact" data-nonce="' . $edit_contact_nonce . '" data-id="' . esc_html( $user->ID ) . '" data-username="' . $user->user_login . '" data-nickname="' . esc_html( $user->nickname ) . '" data-firstname="' . esc_html( $user->user_firstname ) . '" data-lastname="' . esc_html( $user->user_lastname ) . '" data-email="' . esc_html( $user->user_email ) . '" href="#" data-open="edit-contact"><i class="icon-pencil"></i></a>';

			$output .= '</td>';

			$output .= '<td align="center"><a class="edit-member button" href="'.$edit_link.'" data-open="edit-member">Editar</a></td><td nowrap align="center">';

			if ( mro_cit_member_is_pending( $user->ID ) ) {
				$checked_status = '';
			} else {
				$checked_status = ' checked';
			}
			$output .= '<input type="checkbox" name="user-is-approved" value="1" data-nonce="' . $approve_nonce . '" data-nickname="' . esc_html( $user->nickname ) . '" data-username="' . esc_html( $user->user_login ) . '" data-open="confirm-approve-member"' . $checked_status . '> Activo';


			$output .= '</td>
				<td align="center"><a class="delete-member" data-nonce="' . $delete_nonce . '" data-username="' . $user->user_login . '" data-nickname="' . esc_html( $user->nickname ) . '" href="#" data-open="confirm-delete-member"><i class="icon-cancel"></i></a></td>
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


add_action("wp_ajax_cit_edit_main_contact", "cit_edit_main_contact");
// add_action("wp_ajax_nopriv_cit_mc_unsubscribe", "cit_mc_unsubscribe");

function cit_edit_main_contact() {

	write_log('edit main contact function triggered');
	write_log('username '.$_REQUEST['username']);
	write_log('nonce '.$_REQUEST['nonce']);
	write_log('firstname '.$_REQUEST['firstname']);
	write_log('lastname '.$_REQUEST['lastname']);

	if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) && isset( $_REQUEST['nonce'] ) && wp_verify_nonce($_REQUEST['nonce'], 'cit-edit-contact-nonce') ) {

		write_log('check passed');

		$username = sanitize_user( $_REQUEST['username'] );

    	if ( username_exists( $username ) ) {
    		$user = get_user_by('login',$username);
    	} else {
    		pippin_errors()->add('username_invalid', __('Invalid username', 'mro-cit-frontend'));
    	}

    	$email = sanitize_email( $_REQUEST['email'] );

    	if ( !is_email( $email ) ) {
	    	//Invalid email
	    	pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
	    } elseif ( email_exists( $email ) && ( email_exists( $email ) != $user->ID ) ) {
	    	//Email address already registered
			pippin_errors()->add('email_used', __('Email already registered', 'mro-cit-frontend'));
	    }

    	$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {

			$nickname = $user->nickname;
			$id = $user->ID;
			$lastname = sanitize_text_field( $_REQUEST["lastname"] );
			$firstname = sanitize_text_field( $_REQUEST["firstname"] );
			$phone = $user->mro_cit_user_phone;
			$country = $user->mro_cit_user_country;
			$sector = $user->mro_cit_user_sector;

			$updated_info = array(
	  			'ID' => $user->ID,
	  			'user_email' => $email,
	  			'last_name' => $lastname,
	  			'first_name' => $firstname,
	  		);

	  		$old_user_email = $user->user_email;
			// write_log('Old user email is '.$old_user_email);

			$user_data = wp_update_user( $updated_info );

			if ( $user_data == $id ) {
				write_log('User updated');

				$result['message'] = '<p class="callout success">Se actualizó el usuario.</p>';
				$result['type'] = 'success';

				// TODO: check if only need merge fields I'm updating
				//Send to mailchimp only if not pending
				if ( members_user_has_role( $id, 'afiliado_empresarial' ) || members_user_has_role( $id, 'afiliado_institucional' ) ) :

					write_log('Update Mailchimp');

					$status = 'subscribed';

					if ( members_user_has_role( $id, 'afiliado_empresarial' ) ) {
						$type = 'Empresarial';
					} elseif ( members_user_has_role( $id, 'afiliado_institucional' ) ) {
						$type = 'Institucional';
					}

					$mc_merge_fields = array(
						'EMPRESA' => $nickname,
						'LNAME' => $lastname,
						'FNAME' => $firstname,
						'PHONE' => $phone,
						'PAIS' => $country,
						'SECTOR' => $sector,
						'AFILIADO' => $type,
					);

					// Send to mailchimp function
					$subscribe = mro_cit_subscribe_email($email, $mc_merge_fields, $status);

					$result['message'] .= $subscribe;

					if ( $email != $old_user_email ) {
						// write_log('New user email is '.$user_email);
						// write_log('Email is different, so trigger unsubscribe function');
						mro_cit_unsubscribe_email( $old_user_email );
						// $result['message'] .= $unsubscribe;
					}

				endif;


			} else {
				write_log('User NOT updated');

				$result['type'] = 'error';
				$result['message'] = '<p class="callout alert">No se actualizó el usuario.</p>';
			}




			/* Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot */
		    do_action('edit_user_profile_update', $id);


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
    	write_log('check did not pass');
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