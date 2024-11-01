<?php
/*
Plugin Name: WP Create Shortcode
Plugin URI:  https://wordpress.org/plugins/wp-create-shortcode/
Description: This plugin allows users to create content and save it as a shortcode to display anywhere on the website.
Version: 1.0
Requires at least: 6.0
Requires PHP: 7.4
Author: Ashok Kumar
Text Domain: wp-create-shortcode
License: GPL-2.0-or-later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Function to create a shortcode
function crsh_create_shortcode($atts, $content = null) {
    // Extract shortcode attributes
    $attributes = shortcode_atts(array(
        'id' => '',
    ), $atts);

    // Check if content is passed through the shortcode
    $shortcodes = get_option('crsh_shortcodes');
    if ($attributes['id'] && isset($shortcodes[$attributes['id']])) {
        return $shortcodes[$attributes['id']];
    } else {
        return 'Shortcode content not found.';
    }
}
add_shortcode('cs', 'crsh_create_shortcode');

// Add a menu to manage shortcodes in the admin dashboard
function crsh_add_menu() {
    add_menu_page(
        'Create Shortcode',      // Page title
        'Create Shortcode',      // Menu title
        'manage_options',        // Capability
        'create-shortcode',      // Menu slug
        'crsh_manage_shortcodes',// Callback function
        'dashicons-edit',        // Icon
        20                       // Position
    );
}
add_action('admin_menu', 'crsh_add_menu');

// Shortcode management page in the admin area
function crsh_manage_shortcodes() {
    // Handle form submission to save shortcode content
    if (isset($_POST['crsh_shortcode_content']) && isset($_POST['crsh_shortcode_id']) && check_admin_referer('crsh_save_shortcode', 'crsh_nonce')) {
        $shortcode_id = sanitize_text_field(wp_unslash($_POST['crsh_shortcode_id']));
        $shortcode_content = sanitize_textarea_field(wp_unslash($_POST['crsh_shortcode_content']));

        // Get existing shortcodes from the database
        $shortcodes = get_option('crsh_shortcodes', array());

        // Add or update the shortcode content
        $shortcodes[$shortcode_id] = $shortcode_content;

        // Save updated shortcodes back to the database
        update_option('crsh_shortcodes', $shortcodes);

        echo '<div class="notice notice-success is-dismissible"><p>Shortcode saved successfully!</p></div>';
    }

    // Get the list of shortcodes from the database
    $shortcodes = get_option('crsh_shortcodes', array());

    ?>
    <div class="wrap">
        <h1>Create Shortcode</h1>

        <!-- Shortcode Creation Form -->
        <form method="post" action="">
            <?php wp_nonce_field('crsh_save_shortcode', 'crsh_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="crsh_shortcode_id">Shortcode ID</label>
                    </th>
                    <td>
                        <input type="text" name="crsh_shortcode_id" id="crsh_shortcode_id" class="regular-text" required />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="crsh_shortcode_content">Shortcode Content</label>
                    </th>
                    <td>
                        <textarea name="crsh_shortcode_content" id="crsh_shortcode_content" class="large-text" rows="5" required></textarea>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Shortcode'); ?>
        </form>

        <!-- List of Existing Shortcodes -->
        <h2>Existing Shortcodes</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Shortcode</th>
                    <th>Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($shortcodes)) : ?>
                    <?php foreach ($shortcodes as $id => $content) : ?>
                        <tr>
                            <td><?php echo esc_html($id); ?></td>
                            <td>[cs id="<?php echo esc_html($id); ?>"]</td> <!-- Display Shortcode -->
                            <td><?php echo esc_html($content); ?></td>
                            <td>
                                <form method="post" action="">
                                    <?php wp_nonce_field('crsh_delete_shortcode', 'crsh_delete_nonce'); ?>
                                    <input type="hidden" name="delete_shortcode_id" value="<?php echo esc_attr($id); ?>">
                                    <?php submit_button('Delete', 'delete', '', false); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No shortcodes found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Handle shortcode deletion
function crsh_handle_shortcode_deletion() {
    if (isset($_POST['delete_shortcode_id']) && check_admin_referer('crsh_delete_shortcode', 'crsh_delete_nonce')) {
        $shortcode_id = sanitize_text_field(wp_unslash($_POST['delete_shortcode_id']));

        // Get existing shortcodes
        $shortcodes = get_option('crsh_shortcodes', array());

        // Remove the shortcode
        if (isset($shortcodes[$shortcode_id])) {
            unset($shortcodes[$shortcode_id]);
            update_option('crsh_shortcodes', $shortcodes);
            echo '<div class="notice notice-success is-dismissible"><p>Shortcode deleted successfully!</p></div>';
        }
    }
}
add_action('admin_init', 'crsh_handle_shortcode_deletion');
