<?php

add_shortcode( 'cmb-form', 'cmb2_do_frontend_form_shortcode' );
/**
 * Shortcode to display a CMB2 form for a post ID.
 * @param  array  $atts Shortcode attributes
 * @return string       Form HTML markup
 */
function cmb2_do_frontend_form_shortcode( $atts = array() ) {
    global $post, $current_user, $wp_roles;

    if ( is_user_logged_in() ) {
    	$user_id = $current_user->ID;
	$metabox_id = 'mro_cit_user_edit';

		$form = cmb2_get_metabox_form( 'mro_cit_page_metabox', 1 );
        // var_dump($form);
    }

    /**
     * Depending on your setup, check if the user has permissions to edit_posts
     */
    // if ( ! current_user_can( 'edit_posts' ) ) {
    //     return __( 'You do not have permissions to edit this post.', 'lang_domain' );
    // }

    /**
     * Make sure a WordPress post ID is set.
     * We'll default to the current post/page
     */
    // if ( ! isset( $atts['post_id'] ) ) {
    //     $atts['post_id'] = $post->ID;
    // }

    // If no metabox id is set, yell about it
    // if ( empty( $atts['id'] ) ) {
    //     return __( "Please add an 'id' attribute to specify the CMB2 form to display.", 'lang_domain' );
    // }

    // $metabox_id = esc_attr( $atts['id'] );
    // $object_id = absint( $atts['post_id'] );
    // Get our form
    // $form = 'hi';
    // $form = cmb2_get_metabox_form( $metabox_id, $object_id );

    // return $form;
}