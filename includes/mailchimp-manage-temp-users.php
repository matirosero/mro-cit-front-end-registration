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
			// write_log('frontend messages: '.mro_cit_frontend_messages());
		}

		$output .= mro_cit_show_temp_members_table();

		$output .= mro_cit_add_temp_member_form();

	} else {
		$output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
	}

	return $output;
}
add_shortcode('cit-mailchimp-members', 'mro_cit_mailchimp_members_shortcode');


function mro_cit_build_temp_subscribers_table() {
	$output = '';
	$members = mro_cit_get_mailchimp_list_members();

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
			$link = admin_url('admin-ajax.php?action=mc_unsubscribe&email='.urlencode($member['email']).'&nonce='.$nonce);

			$output .= '<tr>
				<td>'.$member['email'].'</td>
				<td>'.$member['fname'].'</td>
				<td>'.$member['lname'].'</td>
				<td>'.$member['status'].'</td>
				<td><a class="unsubscribe" data-nonce="' . $nonce . '" data-email="' . $member['email'] . '" href="#"  data-open="confirm-unsubscribe-email"><i class="icon-cancel"></i></a></td>
			</tr>';
		}

		$output .= '</table>';

	} else {
		$output .= '<p class="callout alert">No hay suscriptores temporales.</p>';
	}

	return $output;
}


function mro_cit_show_temp_members_table() {

	if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) )  {
		// $members = mro_cit_get_mailchimp_list_members();

		// Get the queried object and sanitize it
		$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
		// Get the page slug
		$slug = $current_page->post_name;

		$output = '';

		if ( mro_cit_build_temp_subscribers_table() ) {
			$output .= '<div class="temporary-subscribers" id="temporary-subscribers">';

			$output .= mro_cit_build_temp_subscribers_table();

			$output .= '</div>';

			$output .= '<div class="reveal text-center" id="confirm-unsubscribe-email" data-reveal>
				<button class="close-button" data-close aria-label="Close modal" type="button">
					<i class="icon-cancel"></i>
				</button>
				<p>¿Está seguro que quiere eliminar de la lista el correo <strong class="confirm-email-label"></strong>?</p>
				<p><a href="#" class="button secondary" data-close>Cancelar</a> <a class="button confirm-unsubscribe" data-action="confirm-mc-unsubscribe" href="#">Si, eliminarlo</a></p>
				</div>';
		} 

		return $output;
	} else {
		return false;
	}

}


add_action( 'wp_enqueue_scripts', 'mro_cit_mc_temp_users_enqueue', 100 );
function mro_cit_mc_temp_users_enqueue($hook) {

	wp_enqueue_script( 'cit-remove-email', plugin_dir_url( __FILE__ ) . 'js/ajax-remove-email.js', array('jquery'), '', true );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'cit-remove-email', 'ajax_object',
            array( 
            	 'ajax_url' => admin_url( 'admin-ajax.php' ), 
            ) );
}


add_action("wp_ajax_cit_mc_unsubscribe", "cit_mc_unsubscribe");
add_action("wp_ajax_nopriv_cit_mc_unsubscribe", "cit_mc_unsubscribe");



function cit_mc_unsubscribe() {

	// $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
	// $slug = $uri_parts[0];

    if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) && isset( $_REQUEST['nonce'] ) && wp_verify_nonce($_REQUEST['nonce'], 'cit-unsusbcribe-nonce') ) {

    	// write_log('unsubscribe it!');
    	// $current_url = home_url(add_query_arg(array(),$wp->request));

    	$email = sanitize_email( $_REQUEST['email'] );

    	if(!is_email($email)) {
			pippin_errors()->add('email_invalid', __('Invalid email', 'mro-cit-frontend'));
		}

		$errors = pippin_errors()->get_error_messages();

		// only create the user in if there are no errors
		if(empty($errors)) {
			$unsubscribe = mro_cit_unsubscribe_email( $email );
			// write_log($unsubscribe);
			// mro_cit_frontend_messages( $unsubscribe );
			// //Message disappears
			// wp_redirect( $slug ); exit;
			$result['type'] = 'success';
			$result['message'] = $unsubscribe;
		} else {
			$result['type'] = 'error';
			$result['message'] = $errors;
		}

		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	      	// $result['reload'] = mro_cit_build_temp_subscribers_table();
	      	$result['replace'] = mro_cit_build_temp_subscribers_table();
	      	$result = json_encode($result);
	      	echo $result;
	      	// write_log($result);
	      	// var_dump($result);
		} else {
		    header("Location: ".$_SERVER["HTTP_REFERER"]);
		}


    } else {
    	exit("No naughty business please");
    }

    die();
}
// add_action('init', 'mro_cit_mc_unsubscribe_temp_member');


function mro_cit_add_temp_member_form() {

	ob_start();

	// show any error messages after form submission
	pippin_show_error_messages();

	?>

	<div class="add-temporary-subscriber">
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
	</div>
	<?php
	return ob_get_clean();
}


function mro_cit_mc_add_temp_member() {
	// If no form submission, bail
    if ( empty( $_POST ) ) {
        return false;
    }

    if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) && isset( $_POST['mailchimp_email'] ) && isset( $_POST['cit_mc_add_subscriber_nonce'] ) && wp_verify_nonce($_POST['cit_mc_add_subscriber_nonce'], 'cit-mc-add-subscriber-nonce') ) {
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

			// write_log('Response: '.$subscribe);

			//Check success to send this
			mro_cit_frontend_messages( $subscribe );

		}

    }
}
add_action('init', 'mro_cit_mc_add_temp_member');


