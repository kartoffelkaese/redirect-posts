<?php
declare(strict_types=1);

/*
Plugin Name: Post/Event Redirect
Plugin URI: https://github.com/kartoffelkaese/redirect-posts
Description: This plugin redirects clicks on 'post', 'event', and 'page' to a user-defined URL, if provided. If not, it uses the given PERMALINK.
Author: Martin Urban
Author URI: https://github.com/kartoffelkaese/
Version: 2.0+1
*/

// Prevent direct access to the plugin file
if (!defined('ABSPATH')) {
    die('Invalid request.');
}

define('MCR_META_KEY', '_mcr_custom_redirect_url');

function mcr_add_custom_box(): void {
    $screens = ['post', 'event', 'page'];
    foreach ($screens as $screen) {
        add_meta_box(
            id: 'mcr_box_id',
            title: 'Custom Redirect URL',
            callback: 'mcr_custom_box_html',
            screen: $screen
        );
    }
}
add_action('add_meta_boxes', 'mcr_add_custom_box');

function mcr_custom_box_html(WP_Post $post): void {
    wp_nonce_field('mcr_custom_box', 'mcr_custom_box_nonce');
    $value = get_post_meta($post->ID, MCR_META_KEY, true);
    echo '<input type="text" name="mcr_custom_redirect_url" value="' . esc_attr((string)$value) . '"/>';
}

function mcr_save_postdata(int $post_id): void {
    if (!isset($_POST['mcr_custom_box_nonce']) || 
        !wp_verify_nonce($_POST['mcr_custom_box_nonce'], 'mcr_custom_box')) {
        return;
    }
    
    if (isset($_POST['mcr_custom_redirect_url'])) {
        update_post_meta(
            $post_id,
            MCR_META_KEY,
            sanitize_text_field($_POST['mcr_custom_redirect_url'])
        );
    }
}
add_action('save_post', 'mcr_save_postdata');

function mcr_redirect_to_custom_url(string $url, WP_Post $post): string {
    $custom_url = get_post_meta($post->ID, MCR_META_KEY, true);
    return $custom_url ? esc_url((string)$custom_url) : $url;
}
add_filter('post_link', 'mcr_redirect_to_custom_url', 10, 2);
add_filter('post_type_link', 'mcr_redirect_to_custom_url', 10, 2);

function mcr_redirect_page_to_custom_url(string $url): string {
    $post = get_post();
    if ($post?->post_type === 'page') {
        $custom_url = get_post_meta($post->ID, MCR_META_KEY, true);
        if ($custom_url) {
            return esc_url((string)$custom_url);
        }
    }
    return $url;
}
add_filter('page_link', 'mcr_redirect_page_to_custom_url');
?>
