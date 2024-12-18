<?php
/*
Plugin Name: Post/Event Redirect
Plugin URI: https://github.com/kartoffelkaese/redirect-posts
Description: This plugin redirects clicks on 'post', 'event', and 'page' to a user-defined URL, if provided. If not, it uses the given PERMALINK.
Author: Martin Urban
Author URI: https://github.com/kartoffelkaese/
Version: 2.0
*/

// Prevent direct access to the plugin file
if (!defined('ABSPATH')) {
    die('Invalid request.');
}

define('MCR_META_KEY', '_mcr_custom_redirect_url');
define('MCR_INPUT_NAME', 'mcr_custom_redirect_url');
define('MCR_META_BOX_ID', 'mcr_box_id');

function mcr_add_custom_box(): void {
    $screens = ['post', 'event', 'page'];
    foreach ($screens as $screen) {
        add_meta_box(
            MCR_META_BOX_ID,
            'Custom Redirect URL',
            'mcr_custom_box_html',
            $screen
        );
    }
}
add_action('add_meta_boxes', 'mcr_add_custom_box');

function mcr_custom_box_html(WP_Post $post): void {
    $value = get_post_meta($post->ID, MCR_META_KEY, true);
    ?>
    <label>
        <input type="url" name="<?php echo esc_attr(MCR_INPUT_NAME); ?>" 
               value="<?php echo esc_attr((string)$value); ?>" 
               style="width: 100%;"
               placeholder="https://"
        />
    </label>
    <?php
}

function mcr_save_postdata(int $post_id): void {
    if (!array_key_exists(MCR_INPUT_NAME, $_POST)) {
        return;
    }
    
    $url = sanitize_text_field($_POST[MCR_INPUT_NAME]);
    if (empty($url) || filter_var($url, FILTER_VALIDATE_URL)) {
        update_post_meta($post_id, MCR_META_KEY, $url);
    }
}
add_action('save_post', 'mcr_save_postdata');

function mcr_redirect_to_custom_url(string $url, ?WP_Post $post = null): string {
    if (!$post) {
        $post = get_post();
    }
    
    if (!$post) {
        return $url;
    }

    $custom_url = get_post_meta($post->ID, MCR_META_KEY, true);
    return $custom_url ? esc_url((string)$custom_url) : $url;
}
add_filter('post_link', 'mcr_redirect_to_custom_url', 10, 2);
add_filter('post_type_link', 'mcr_redirect_to_custom_url', 10, 2);
add_filter('page_link', 'mcr_redirect_to_custom_url', 10, 2);
?>
