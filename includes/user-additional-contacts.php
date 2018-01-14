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
        'description' => __( 'Manage additional contacts by adding, removing or editing them here.', 'mro-cit-frontend' ),
        'repeatable'  => true, // use false if you want non-repeatable group
        'options'     => array(
            'group_title'   => __( 'Contact {#}', 'mro-cit-frontend' ), // since version 1.1.4, {#} gets replaced by row number
            'add_button'    => __( 'Add Another Contact', 'mro-cit-frontend' ),
            'remove_button' => __( 'Remove Contact', 'mro-cit-frontend' ),
            'sortable'      => true, // beta
            // 'closed'     => true, // true to have the groups closed by default
        ),
    ) );

    // Id's for group's fields only need to be unique for the group. Prefix is not needed.
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact name', 'mro-cit-frontend' ),
        'id'   => 'name',
        'type' => 'text',
        // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
    ) );
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact last name', 'mro-cit-frontend' ),
        'id'   => 'lastname',
        'type' => 'text',
        // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
    ) );
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact email', 'mro-cit-frontend' ),
        'id'   => 'email',
        'type' => 'text_email',
        // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
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
    $name = $current_user->display_name;


    // User is logged in and can add contacts
    if ( is_user_logged_in() && current_user_can( 'add_contacts' ) ) {

        // Use ID of metabox in mro_cit_frontend_contacts_form
        $metabox_id = 'mro_cit_user_frontend_additional_contacts';

        // since post ID will not exist yet, just need to pass it something
        $object_id  = $user_id;
        // var_dump($user_id);


        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox( $metabox_id, $user_id );
        // var_dump($cmb);

        // Get $cmb object_types
        $post_types = $cmb->prop( 'object_types' );
        // var_dump($cmb->prop( 'object_types' ));

        // // Parse attributes. These shortcode attributes can be optionally overridden.
        $atts = shortcode_atts( array(
            'user_id' => $user_id ? $user_id : 1, // Current user, or admin
            // 'post_status' => 'pending',
            'post_type'   => reset( $post_types ), // Only use first object_type in array
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

    // write_log('handle running');

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
        return new WP_Error( 'no_permission', __( 'Your account doesn\'t have permission to do this.', 'mro-cit-frontend' ) );
    }


    // Do WordPress insert_post stuff
    // Fetch sanitized values
    $sanitized_values = $cmb->get_sanitized_values( $_POST );

    // write_log('passed');


    // Set our post data arguments
    $additional_contacts   = $sanitized_values['mro_cit_user_additional_contacts'];
    unset( $sanitized_values['mro_cit_user_additional_contacts'] );

    // var_dump( $additional_contacts);

    foreach ($additional_contacts as $contact) {
        // var_dump($contact);
        if ( isset( $contact['email'] ) ) {
            $email = sanitize_email( $contact['email'] );

            if(!is_email($email)) {
                return new WP_Error( 'invalid_email', __( 'Invalid email.' ) );
                // write_log('Not a valid email.');
            }
        } else {
            return new WP_Error( 'missing_email', __( 'All contacts must have an email.' ) );
            // write_log('No email :(');
        }

    }

    $post_data['additional_contacts'] = $additional_contacts;

    // var_dump($post_data); //*

    // write_log('additonal contacts form submitted');

    $new_submission_id = update_user_meta( $post_data['user_id'], 'mro_cit_user_additional_contacts', $post_data['additional_contacts'] );

    return $new_submission_id;


}