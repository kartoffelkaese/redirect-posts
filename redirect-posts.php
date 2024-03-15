<?php
/*
Plugin Name: Post/Event Redirect
Plugin URI: https://github.com/kartoffelkaese/redirect-posts
Description: This plugin redirects clicks on 'post', 'event', and 'page' to a user-defined URL, if provided. If not, it uses the given PERMALINK.
Author: Martin Urban
Author URI: https://github.com/kartoffelkaese/
Version: 1.1
*/

// Prevent direct access to the plugin file
if (!defined('ABSPATH')) {
    die('Invalid request.');
}

function mcr_add_custom_box() {
    $screens = ['post', 'event', 'page'];
    foreach ($screens as $screen) {
        add_meta_box(
            'mcr_box_id',
            'Custom Redirect URL',
            'mcr_custom_box_html',
            $screen
        );
    }
}
add_action('add_meta_boxes', 'mcr_add_custom_box');

function mcr_custom_box_html($post) {
    $value = get_post_meta($post->ID, '_mcr_custom_redirect_url', true);
    echo '<input type="text" name="mcr_custom_redirect_url" value="'. esc_attr($value). '"/>';
}

function mcr_save_postdata($post_id) {
    if (array_key_exists('mcr_custom_redirect_url', $_POST)) {
        update_post_meta(
            $post_id,
            '_mcr_custom_redirect_url',
            $_POST['mcr_custom_redirect_url']
        );
    }
}
add_action('save_post', 'mcr_save_postdata');

function mcr_redirect_to_custom_url($url, $post) {
    $custom_url = get_post_meta($post->ID, '_mcr_custom_redirect_url', true);
    if ($custom_url) {
        return esc_url($custom_url);
    }
    return $url;
}
add_filter('post_link', 'mcr_redirect_to_custom_url', 10, 2);
add_filter('post_type_link', 'mcr_redirect_to_custom_url', 10, 2);
function mcr_redirect_page_to_custom_url($url) {
    $post = get_post();
    if ($post->post_type === 'page') {
        $custom_url = get_post_meta($post->ID, '_mcr_custom_redirect_url', true);
        if ($custom_url) {
            return esc_url($custom_url);
        }
    }
    return $url;
}
add_filter('page_link', 'mcr_redirect_page_to_custom_url');
?>
