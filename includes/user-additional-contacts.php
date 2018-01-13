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
        'description' => __( 'Additional contacts', 'cmb2' ),
        'repeatable'  => true, // use false if you want non-repeatable group
        'options'     => array(
            'group_title'   => __( 'Contact {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
            'add_button'    => __( 'Add Another Contact', 'cmb2' ),
            'remove_button' => __( 'Remove Contact', 'cmb2' ),
            'sortable'      => true, // beta
            // 'closed'     => true, // true to have the groups closed by default
        ),
    ) );

    // Id's for group's fields only need to be unique for the group. Prefix is not needed.
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact name', 'cmb2' ),
        'id'   => 'name',
        'type' => 'text',
        // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
    ) );
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact last name', 'cmb2' ),
        'id'   => 'lastname',
        'type' => 'text',
        // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
    ) );
    $cmb->add_group_field( $group_contacts, array(
        'name' => __( 'Contact email', 'cmb2' ),
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
function wds_do_frontend_form_submission_shortcode( $atts = array() ) {

    // Current user
    $user_id = get_current_user_id();



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
            $output .= '<h3>' . sprintf( __( 'There was an error in the submission: %s', 'wds-post-submit' ), '<strong>'. $new_id->get_error_message() .'</strong>' ) . '</h3>';

        } else {

            // Get submitter's name
            // $name = isset( $_POST['submitted_author_name'] ) && $_POST['submitted_author_name']
                // ? ' '. $_POST['submitted_author_name']
                // : '';

            // Add notice of submission
            $output .= '<h3>' . sprintf( __( 'Thank you %s, your new post has been submitted and is pending review by a site administrator.', 'wds-post-submit' ), esc_html( $user_id ) ) . '</h3>';
        }

    }

    // Get our form
    $output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Submit Post', 'wds-post-submit' ) ) );

    // Our CMB2 form stuff goes here

    return $output;
}
add_shortcode( 'cmb-frontend-form', 'wds_do_frontend_form_submission_shortcode' );



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
        return new WP_Error( 'security_fail', __( 'Security check failed.' ) );
        // write_log('Security check failed.');
    }

    // if ( empty( $_POST['submitted_post_title'] ) ) {
    //     return new WP_Error( 'post_data_missing', __( 'New post requires a title.' ) );
    // }

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