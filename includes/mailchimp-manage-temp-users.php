<?php


function mro_cit_mailchimp_members_shortcode($atts, $content = null ) {

	global $current_user, $wp_roles;

	extract( shortcode_atts( array(
		'afiliado' => ''
	), $atts ) );

	$output = '';

	if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) ) {

		$members = mro_cit_get_mailchimp_list_members();

		// Get the queried object and sanitize it
		$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
		// Get the page slug
		$slug = $current_page->post_name;

		// var_dump($complete_url);

		if (isset($_GET)) {
			var_dump($_GET['mc_remove']);
		}


		if ( count( $members ) > 0 ) {
			$output .= '<table>
				<tr>
					<th>E-mail</th>
					<th>Nombre</th>
					<th>Apellidos</th>
					<th>Estado</th>
					<th></th>
				</tr>';

			foreach ($members as $key => $member) {

				$nonce = wp_create_nonce('cit-nonce');

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
			$output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
		}

		$output .= mro_cit_add_temp_member_form();

	}

	



	return $output;
}
add_shortcode('cit-mailchimp-members', 'mro_cit_mailchimp_members_shortcode');


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
			mro_cit_subscribe_email($email, $mc_merge_fields, $status);

			$success_message = 'El correo '.$email.' ha sido agregado a la lista.';
		}

    }
}
add_action('init', 'mro_cit_mc_add_temp_member');


/*
 * Handle the cmb-frontend-form shortcode
 *
 * @param  array  $atts Array of shortcode attributes
 * @return string       Form html
 */
function mro_cit_frontend_manage_temp_subscribers_shortcode( $atts = array() ) {

    global $current_user, $wp_roles;

    // Current user
    $user_id = get_current_user_id();
    $name = $current_user->display_name;


    $page_id = get_the_ID();


    // User is logged in and can manage temp subscribers
    if ( is_user_logged_in() && current_user_can( 'manage_temp_subscribers' ) ) {

        // Use ID of metabox in mro_cit_frontend_contacts_form
        $metabox_id = 'mro_cit_temp_subscribers_frontend';

        // since post ID will not exist yet, just need to pass it something
        $object_id  = $page_id;
        // var_dump($current_user);


        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox( $metabox_id, $page_id );
        // var_dump($cmb);

        // Get $cmb object_types
        $post_types = $cmb->prop( 'object_types' );
        // var_dump($cmb->prop( 'object_types' ));



        // // Parse attributes. These shortcode attributes can be optionally overridden.
        $atts = shortcode_atts( array(
            'user_id'       => $user_id ? $user_id : 1, // Current user, or admin
            // 'post_status' => 'pending',
            'post_type'     => reset( $post_types ), // Only use first object_type in array
            // 'membership'    => $role,
            // 'company'       => $name,
            // 'country'       => $current_user->mro_cit_user_country,
            // 'sector'        => $current_user->mro_cit_user_sector,
        ), $atts, 'cmb-frontend-form' );

        // Initiate our output variable
        $output = '';

        // Handle form saving (if form has been submitted)
        $new_id = wds_handle_frontend_new_post_form_submission( $cmb, $atts );

        if ( $new_id ) {

            if ( is_wp_error( $new_id ) ) {

                // If there was an error with the submission, add it to our ouput.
                $output .= '<p class="callout alert error"><strong>' . __('Error', 'mro-cit-frontend') . '</strong>: ' .  $new_id->get_error_message() . '</p>';

            } else {

                // Add notice of submission
                $output .= '<p class="callout success">' . sprintf( __( 'Your contacts have been updated, %s.', 'mro-cit-frontend' ), esc_html( $name ) ) . '</p>';
            }

        }

        // Get our form
        $output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Save subscribers', 'mro-cit-frontend' ) ) );

    // User is not logged in or can't add contacts
    } else {

        $output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';

    }

    return $output;

}
add_shortcode( 'cit-temp-subscribers', 'mro_cit_frontend_manage_temp_subscribers_shortcode' );


/**
 * Handles form submission on save
 *
 * @param  CMB2  $cmb       The CMB2 object
 * @param  array $post_data Array of post-data for new post
 * @return mixed            New post ID if successful
 */
function mro_cit_handle_frontend_temp_subscribers_form_submission( $cmb, $post_data = array() ) {


    // If no form submission, bail
    if ( empty( $_POST ) ) {
        return false;
    }


    // check required $_POST variables and security nonce
    if (
        ! isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST[ $cmb->nonce() ] )
        || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() )
    ) {
        return new WP_Error( 'security_fail', __( 'Security check failed.', 'mro-cit-frontend' ) );
        // write_log('Security check failed.');
    }


    if ( !is_user_logged_in() ) {
        return new WP_Error( 'user_not_logged_in', __( 'You must log in to do this.' ), 'mro-cit-frontend' );
    }

    if ( !current_user_can( 'manage_temp_subscribers' ) ) {
        return new WP_Error( 'no_permission', __( 'Your account doesn\'t have permission to do this.', 'mro-cit-frontend' ) );
    }


    // Do WordPress insert_post stuff
    // Fetch sanitized values
    $sanitized_values = $cmb->get_sanitized_values( $_POST );


    // Set our post data arguments
    $additional_contacts   = $sanitized_values['mro_cit_temp_subscribers_list'];
    unset( $sanitized_values['mro_cit_temp_subscribers_list'] );
}