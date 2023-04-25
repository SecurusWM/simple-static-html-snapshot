<?php
/**
 * Plugin Name: Simple Static HTML Snapshot
 * Description: A simplified plugin to generate a static HTML snapshot of your WordPress site.
 * Version: 1.0
 * Author: Steve
 */

// Add a menu item for the plugin in the WordPress admin dashboard
function simple_shs_add_menu() {
    add_menu_page('Generate Snapshot', 'Generate Snapshot', 'manage_options', 'simple-static-html-snapshot', 'simple_shs_render_admin_page');
}
add_action('admin_menu', 'simple_shs_add_menu');

// Render the admin page
function simple_shs_render_admin_page() {
    wp_enqueue_script('simple-shs-admin-js', plugin_dir_url(__FILE__) . 'simple-shs-admin.js', array('jquery'), '1.0.0', true);
    wp_localize_script('simple-shs-admin-js', 'simple_shs', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('simple_shs_generate_snapshot_nonce'),
    ));

    echo '<div class="wrap">';
    echo '<h1>Generate Static HTML Snapshot</h1>';
    echo '<p>Click the button below to generate a static HTML snapshot of your website.</p>';
    echo '<button id="simple-shs-generate-btn" class="button button-primary">Generate Snapshot</button>';
    echo '<div id="simple-shs-result"></div>';
    echo '</div>';
}

// AJAX handler for generating the snapshot
function simple_shs_generate_snapshot() {
    check_ajax_referer('simple_shs_generate_snapshot_nonce', 'nonce');

    // Implement snapshot generation logic here

   // Check if the wget command is available
   if (!function_exists('exec') || !exec('/usr/bin/wget')) {
    wp_send_json_error('The wget command is not available on your server. Please contact your hosting provider for assistance.');
    return;
}

// Set snapshot directory and file name
$upload_dir = wp_upload_dir();
$snapshot_dir = $upload_dir['basedir'] . '/static-html-snapshots';
$snapshot_file = $snapshot_dir . '/snapshot-' . date('Y-m-d-H-i-s') . '.zip';

// Create the snapshot directory if it doesn't exist
if (!file_exists($snapshot_dir)) {
    mkdir($snapshot_dir, 0755, true);
}

// Generate the snapshot using wget
$domain = get_site_url();
$command = "wget --recursive --no-clobber --page-requisites --html-extension --convert-links --restrict-file-names=windows --domains {$domain} --no-parent {$domain} -P {$snapshot_dir} && cd {$snapshot_dir} && zip -r {$snapshot_file} * && rm -rf {$domain}";

exec($command, $output, $return);

if ($return === 0) {
    $snapshot_url = $upload_dir['baseurl'] . '/static-html-snapshots/' . basename($snapshot_file);
    wp_send_json_success('<a href="' . esc_url($snapshot_url) . '" target="_blank">Download Snapshot</a>');
} else {
    wp_send_json_error('An error occurred while generating the snapshot. Please try again.');
}

}
add_action('wp_ajax_simple_shs_generate_snapshot', 'simple_shs_generate_snapshot');
