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
    var_dump($user_id);


    // Get CMB2 metabox object
    $cmb = cmb2_get_metabox( $metabox_id, $user_id );
    var_dump($cmb);

    // Get $cmb object_types
    $post_types = $cmb->prop( 'object_types' );

    var_dump($cmb->prop( 'object_types' ));

    // // Parse attributes. These shortcode attributes can be optionally overridden.
    $atts = shortcode_atts( array(
        'post_author' => $user_id ? $user_id : 1, // Current user, or admin
        'post_status' => 'pending',
        'post_type'   => reset( $post_types ), // Only use first object_type in array
    ), $atts, 'cmb-frontend-form' );

    // Initiate our output variable
    $output = '';

    // Get our form
    $output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Submit Post', 'wds-post-submit' ) ) );

    // Our CMB2 form stuff goes here

    return $output;
}
add_shortcode( 'cmb-frontend-form', 'wds_do_frontend_form_submission_shortcode' );