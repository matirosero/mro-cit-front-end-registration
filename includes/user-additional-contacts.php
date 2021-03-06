<?php

/*
 * Register the form and fields for our front-end submission form
 */
function mro_cit_frontend_contacts_form() {
    $prefix = 'mro_cit_user_';

    $cmb = new_cmb2_box( array(
        'id'           => $prefix . 'frontend_additional_contacts',
        'object_types' => array( 'user' ),
        'hookup'       => false,
        'save_fields'  => false,
    ) );

    $group_contacts = $cmb->add_field( array(
        'id'          => $prefix . 'additional_contacts',
        'type'        => 'group',
        'description' => __( 'Agregue los contactos de personas en la organización a quienes quiere suscribir al boletín informativo. A estos contactos les llegarán invitaciones a eventos, pero no les llegarán las notificaciones administrativas (cambios de contraseña, etc).', 'mro-cit-frontend' ),
        'repeatable'  => true, 
        'options'     => array(
            'group_title'   => __( 'Contact {#}', 'mro-cit-frontend' ), 
            'add_button'    => __( 'Add Another Contact', 'mro-cit-frontend' ),
            'remove_button' => __( 'Remove Contact', 'mro-cit-frontend' ),
            'sortable'      => true, // beta
        ),
    ) );

    // Id's for group's fields only need to be unique for the group. Prefix is not needed.
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact name', 'mro-cit-frontend' ),
        'id'   => 'name',
        'type' => 'text',
    ) );
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact last name', 'mro-cit-frontend' ),
        'id'   => 'lastname',
        'type' => 'text',
    ) );
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact email', 'mro-cit-frontend' ),
        'id'   => 'email',
        'type' => 'text_email',
    ) );

}
add_action( 'cmb2_init', 'mro_cit_frontend_contacts_form' );


/*
 * Handle the cmb-frontend-form shortcode
 *
 * @param  array  $atts Array of shortcode attributes
 * @return string       Form html
 */
function mro_cit_frontend_manage_contacts_form_shortcode( $atts = array() ) {

    global $current_user, $wp_roles;

    // Current user
    $user_id = get_current_user_id();


    // User is logged in and can add contacts
    if ( is_user_logged_in() && ( current_user_can( 'add_contacts' ) || current_user_can( 'manage_temp_subscribers' ) ) ) {

        // Use ID of metabox in mro_cit_frontend_contacts_form
        $metabox_id = 'mro_cit_user_frontend_additional_contacts';

        $role = '';

        // Initiate our output variable
        $output = '';

        //If editing someone else's profile
        if ( current_user_can( 'manage_temp_subscribers' ) && isset( $_REQUEST['username'] ) && username_exists( $_REQUEST['username'] ) ) {

            $user = get_user_by('login',$_REQUEST['username']);
            $object_id  = $user->ID;

            if ( members_user_has_role( $user->ID, 'afiliado_empresarial_pendiente' ) || members_user_has_role( $user->ID, 'afiliado_empresarial') ) {
                $role = 'Empresarial';
            } elseif ( members_user_has_role( $user->ID, 'afiliado_institucional_pendiente' ) || members_user_has_role( $user->ID, 'afiliado_institucional' ) ) {
                $role = 'Institucional';
            }

            $country =$user->mro_cit_user_country;
            $sector = $user->mro_cit_user_sector;
            $name = $user->display_name;
            $phone = $user->mro_cit_user_phone;

            $output .= '<a class="button secondary" href="'.get_permalink( get_page_by_title( 'Administrar afiliados' ) ).'"><i class="icon-angle-double-left"></i> Regresar a la lista de afiliados</a>';
            $output .= '<h3>'.$name.'</h3>';



        //If editing one's own profile
        } else {
            $object_id  = $user_id;

            if ( members_current_user_has_role( 'afiliado_empresarial_pendiente' ) || members_current_user_has_role( 'afiliado_empresarial') ) {
                $role = 'Empresarial';
            } elseif ( members_current_user_has_role( 'afiliado_institucional_pendiente' ) || members_current_user_has_role( 'afiliado_institucional' ) ) {
                $role = 'Institucional';
            }

            $country = $current_user->mro_cit_user_country;
            $sector = $current_user->mro_cit_user_sector;
            $phone = $current_user->mro_cit_user_phone;
            $name = $current_user->display_name;

        }

        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox( $metabox_id, $object_id );

        // Get $cmb object_types
        $post_types = $cmb->prop( 'object_types' );

        // // Parse attributes. These shortcode attributes can be optionally overridden.
        $atts = shortcode_atts( array(
            'user_id'       => $object_id ? $object_id : 1, // Current user, or admin
            // 'post_status' => 'pending',
            'post_type'     => reset( $post_types ), // Only use first object_type in array
            'membership'    => $role,
            'company'       => $name,
            'country'       => $country,
            'sector'        => $sector,
            'phone'         => $phone,
        ), $atts, 'cmb-frontend-form' );

        // Handle form saving (if form has been submitted)
        $previous_result = wds_handle_frontend_new_post_form_submission( $cmb, $atts );


        // If there is a previous submission
        if ( $previous_result ) {

            if ( is_wp_error( $previous_result ) ) {

                // If there was an error with the submission, add it to our ouput.
                $output .= '<p class="callout alert error"><strong>' . __('Error', 'mro-cit-frontend') . '</strong>: ' .  $previous_result->get_error_message() . '</p>';

            } else {

                // Add results of previous submisseion
                $output .= '<p class="callout success">' . sprintf( __( 'Your contacts have been updated, %s.', 'mro-cit-frontend' ), esc_html( $name ) ) . '</p>'.
                $previous_result;
            }
        }

        // Get our form
        $output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Save contacts', 'mro-cit-frontend' ) ) );

    // User is not logged in or can't add contacts
    } else {
        $output = '<p class="callout warning">' . __('Your account doesn\'t have permission to see this page.', 'mro-cit-frontend') . '</p>';
    }

    return $output;

}
add_shortcode( 'cit-manage-contacts', 'mro_cit_frontend_manage_contacts_form_shortcode' );



/**
 * Handles form submission on save
 *
 * @param  CMB2  $cmb       The CMB2 object
 * @param  array $post_data Array of post-data for new post
 * @return mixed            New post ID if successful
 */
function wds_handle_frontend_new_post_form_submission( $cmb, $post_data = array() ) {

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

    if ( !current_user_can( 'add_contacts' ) ) {
        // write_log('User can not add contacts!!');
        return new WP_Error( 'no_permission', __( 'Your account doesn\'t have permission to do this.', 'mro-cit-frontend' ) );
    }

    // Variable to store results
    $result = '';

    // Array with original contacts to check against
    $original_contacts = get_user_meta( $post_data['user_id'], 'mro_cit_user_additional_contacts', true );

    $user = get_userdata( $post_data['user_id'] );
    $user_login =$user->user_login;

    // Fetch sanitized values
    $sanitized_values = $cmb->get_sanitized_values( $_POST );


    // Set our post data arguments
    $additional_contacts   = $sanitized_values['mro_cit_user_additional_contacts'];
    unset( $sanitized_values['mro_cit_user_additional_contacts'] );


    $status = 'subscribed';

    foreach ($additional_contacts as $key => $contact) {


        if ( isset( $contact['email'] ) ) {

            $email = sanitize_email( $contact['email'] );

            //why this?

            $contact['email'] = $email;

            if(!is_email($email)) {
                return new WP_Error( 'invalid_email', __( 'Invalid email.' ) );
            }


            // If member is not pending, prepare array to send email to Mailchimp
            if( ! mro_cit_member_is_pending( $post_data['user_id'] ) ) {

                $mc_merge_fields  = array();
                $mc_merge_fields['AFILIADO'] = $post_data['membership'];
                $mc_merge_fields['PAIS'] = $post_data['country'];
                $mc_merge_fields['SECTOR'] = $post_data['sector'];
                $mc_merge_fields['EMPRESA'] = $post_data['company'];
                $mc_merge_fields['PHONE'] = $post_data['phone'];
                
                $mc_merge_fields['USERNAME'] = $user_login;

                if ( !empty( $contact['name'] ) ) {
                    $mc_merge_fields['FNAME'] = $contact['name'];
                }
                if ( !empty( $contact['lastname'] ) ) {
                    $mc_merge_fields['LNAME'] = $contact['lastname'];
                }


                // write_log('MERGE FIELDS: '.implode(",",$mc_merge_fields));


                // Subscribe email to mailchimp
                $result .= mro_cit_subscribe_email( $contact['email'], $mc_merge_fields, $status );


                // Compare this email to the original contacts array and remove if it matches
                if ($original_contacts) {
                    foreach ($original_contacts as $old_key => $old_contact) {

                        if ( $old_contact['email'] == $email ) {
                            unset( $original_contacts[$old_key] );
                        }
                    }                    
                }

            }

        } else {
            return new WP_Error( 'missing_email', __( 'All contacts must have an email.' ) );
        }
    }

    // If member is not pending, unsubscribe the leftovers from Mailchimp
    if( ! mro_cit_member_is_pending( $post_data['user_id'] ) ) {

        if ($original_contacts) {
            foreach ($original_contacts as $remove_contact) {

                $remove_email = $remove_contact['email'];
                $result .= mro_cit_unsubscribe_email( $remove_email );
            }
        }
    }


    $post_data['additional_contacts'] = $additional_contacts;


    // Update meta with additional members, if true, return results
    if ( update_user_meta( $post_data['user_id'], 'mro_cit_user_additional_contacts', $post_data['additional_contacts'] ) ) {
        return $result;
    }
}