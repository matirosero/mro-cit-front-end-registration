<?php


function mro_cit_mailchimp_members_shortcode($atts, $content = null ) {

	global $current_user, $wp_roles;

	extract( shortcode_atts( array(
		'afiliado' => ''
	), $atts ) );

	$output = '';

	if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) ) {

		//Show any messages
		if ( mro_cit_frontend_messages() != '' ) {
			echo mro_cit_frontend_messages();
		}

		$output .= mro_cit_show_temp_members_table();

		$output .= mro_cit_add_temp_member_form();

	} else {
		$output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
	}

	return $output;
}
add_shortcode('cit-mailchimp-members', 'mro_cit_mailchimp_members_shortcode');


function mro_cit_show_temp_members_table() {

	$members = mro_cit_get_mailchimp_list_members();

	// Get the queried object and sanitize it
	$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
	// Get the page slug
	$slug = $current_page->post_name;

	$output = '';

	if ( count( $members ) > 0 ) {
		$output .= '<h3>Suscriptores temporales</h3>
			<table>
				<tr>
					<th>E-mail</th>
					<th>Nombre</th>
					<th>Apellidos</th>
					<th>Estado</th>
					<th></th>
				</tr>';

		foreach ($members as $key => $member) {

			$nonce = wp_create_nonce('cit-unsusbcribe-nonce');

			$output .= '<tr>
				<td>'.$member['email'].'</td>
				<td>'.$member['fname'].'</td>
				<td>'.$member['lname'].'</td>
				<td>'.$member['status'].'</td>
				<td><a href="/'.$slug.'/?mc_remove='.urlencode($member['email']).'&cit-nonce='.$nonce.'"><i class="icon-cancel"></i></a></td>
			</tr>';
		}

		$output .= '</table>';
	} else {
		$output .= '<p class="callout alert">No hay suscriptores temporales.</p>';
	}

	return $output;
}


function mro_cit_mc_unsubscribe_temp_member() {
	if ( empty( $_GET ) ) {
        return false;
    }

    if ( isset( $_GET['mc_remove'] ) && isset( $_GET['cit-nonce'] ) && wp_verify_nonce($_GET['cit-nonce'], 'cit-unsusbcribe-nonce') ) {

    	write_log('unsubscribe it!');

    	$email = sanitize_email( $_GET['mc_remove'] );

    	if(!is_email($email)) {
			//invalid email
			pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
			// write_log('Email error: Invalid email');
		}

		$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {
			mro_cit_unsubscribe_email( $email );
		}

    	
    }
}
add_action('init', 'mro_cit_mc_unsubscribe_temp_member');


function mro_cit_add_temp_member_form() {

	ob_start();

	// show any error messages after form submission
	pippin_show_error_messages();

	?>

	<h3><?php _e('Add temporary members', 'mro-cit-frontend'); ?></h3>
	<form id="cit-mailchimp-add-temp" class="pippin_form" action="" method="POST">
		<p>
			<label for="mailchimp_email"><?php _e('Email', 'mro-cit-frontend'); ?><span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
			<input name="mailchimp_email" id="mailchimp_email" class="required" type="email"/>
		</p>
		<p>
			<label for="mailchimp_fname"><?php _e('Name', 'mro-cit-frontend'); ?></label>
			<input name="mailchimp_fname" id="mailchimp_fname" type="text"/>
		</p>
		<p>
			<label for="mailchimp_lname"><?php _e('Lastname', 'mro-cit-frontend'); ?></label>
			<input name="mailchimp_lname" id="mailchimp_lname" type="text"/>
		</p>
		<p>
			<input type="hidden" name="cit_mc_add_subscriber_nonce" value="<?php echo wp_create_nonce('cit-mc-add-subscriber-nonce'); ?>"/>

			<input type="hidden" name="mailchimp_type" value="Temporal" />

			<input type="submit" class="button button-primary" value="<?php _e('Add member', 'mro-cit-frontend'); ?>" />
		</p>
	</form>
	<?php
	return ob_get_clean();
}


function mro_cit_mc_add_temp_member() {
	// If no form submission, bail
    if ( empty( $_POST ) ) {
        return false;
    }


    if ( isset( $_POST['mailchimp_email'] ) && isset( $_POST['cit_mc_add_subscriber_nonce'] ) && wp_verify_nonce($_POST['cit_mc_add_subscriber_nonce'], 'cit-mc-add-subscriber-nonce') ) {
    	// write_log( 'Trigger send to mailchimp' );

    	// array to hold merge fields
    	$mc_merge_fields  = array();

		//Process email
		$email = sanitize_email( $_POST["mailchimp_email"] );
		// write_log('Email is '.$email);

		if(!is_email($email)) {
			//invalid email
			pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
			// write_log('Email error: Invalid email');
		}

		$fname 	= sanitize_text_field( $_POST["mailchimp_fname"] );
		$mc_merge_fields['FNAME'] = $fname;
		// write_log('MERGE FIELD: FNAME: '.$mc_merge_fields['FNAME']);

		$lname	 	= sanitize_text_field( $_POST["mailchimp_lname"] );
		$mc_merge_fields['LNAME'] = $lname;
		// write_log('MERGE FIELD: LNAME: '.$mc_merge_fields['LNAME']);

		$type = sanitize_text_field( $_POST["mailchimp_type"] );
		$mc_merge_fields['AFILIADO'] = $type;
		// write_log('MERGE FIELD: AFILIADO: '.$mc_merge_fields['AFILIADO']);

		$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {
			$status = 'subscribed';

			// Send to mailchimp function
			// write_log('MERGE FIELDS: '.implode(",",$mc_merge_fields));
			$subscribe = mro_cit_subscribe_email($email, $mc_merge_fields, $status);


			//Not working
			if ( $subscribe != false ) {
				mro_cit_frontend_messages( '<p class="callout success">' . __('The email address '.$email.' has been succesfully subscribed!', 'mro-cit-frontend') . '</p>' );
			}



		}

    }
}
add_action('init', 'mro_cit_mc_add_temp_member');

