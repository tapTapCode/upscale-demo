<?php
/**
 * Plugin Name: Upscale Demo (Replicate)
 * Description: Upload → Unblur/Enlarge → Preview using Replicate's topazlabs/image-upscale API with drag & drop and AJAX.
 * Version: 1.2
 * Author: Jumar
 */

defined('ABSPATH') || exit;

// Prevent re-defining constant if already defined (avoid PHP warning)
if (!defined('UPSCALE_DEMO_USE_MOCK')) {
    define('UPSCALE_DEMO_USE_MOCK', false); // set true to enable mock mode
}

require_once __DIR__ . '/includes/class-upscale-demo.php';

// Enqueue styles and scripts
function upscale_demo_assets() {
    wp_enqueue_style('upscale-demo-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('upscale-demo-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], null, true);
    wp_localize_script('upscale-demo-script', 'UpscaleDemoAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('upscale_demo_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'upscale_demo_assets');

// Register shortcode
add_shortcode('upscale_demo', ['Upscale_Demo', 'render_shortcode']);

// AJAX handlers
add_action('wp_ajax_upscale_image', 'upscale_demo_ajax_handler');
add_action('wp_ajax_nopriv_upscale_image', 'upscale_demo_ajax_handler');

function upscale_demo_ajax_handler() {
    check_ajax_referer('upscale_demo_nonce', 'nonce');

    if (empty($_FILES['file'])) {
        error_log('[Upscale Demo] AJAX upload error: No file uploaded.');
        wp_send_json_error('No file uploaded.');
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $upload_id = media_handle_upload('file', 0);

    if (is_wp_error($upload_id)) {
        error_log('[Upscale Demo] Upload failed: ' . $upload_id->get_error_message());
        wp_send_json_error('Upload failed: ' . $upload_id->get_error_message());
    }    

    $image_url = wp_get_attachment_url($upload_id);
    if (!$image_url) {
        error_log('[Upscale Demo] Could not get image URL after upload.');
        wp_send_json_error('Could not get uploaded image URL.');
    }

    // Call Replicate API to upscale the image
    $enhanced_url = Upscale_Demo::call_replicate_api($image_url);

    if (!$enhanced_url) {
        error_log('[Upscale Demo] Upscaling failed.');
        wp_send_json_error('Upscaling failed.');
    }

    // Download and save the upscaled image to the media library
    $tmp = download_url($enhanced_url);
    if (is_wp_error($tmp)) {
        error_log('[Upscale Demo] Failed to download upscaled image. Returning external URL.');
        // Fallback to external URL
        wp_send_json_success([
            'original' => $image_url,
            'upscaled' => $enhanced_url,
        ]);
    }

    $upscaled_file = [
        'name'     => basename(parse_url($enhanced_url, PHP_URL_PATH)),
        'type'     => mime_content_type($tmp),
        'tmp_name' => $tmp,
        'error'    => 0,
        'size'     => filesize($tmp),
    ];

    $upscaled_id = media_handle_sideload($upscaled_file, 0);

    if (is_wp_error($upscaled_id)) {
        error_log('[Upscale Demo] media_handle_sideload failed: ' . $upscaled_id->get_error_message());
        // Fallback again if sideload fails
        wp_send_json_success([
            'original' => $image_url,
            'upscaled' => $enhanced_url,
        ]);
    }

    $upscaled_saved_url = wp_get_attachment_url($upscaled_id);

    wp_send_json_success([
        'original' => $image_url,
        'upscaled' => $upscaled_saved_url,
    ]);
}
