<?php
/**
 * Class: Upscale_Demo
 * Description: Handles UI rendering and Replicate API interaction.
 */

defined('ABSPATH') || exit;

class Upscale_Demo {

    public static function render_shortcode() {
        ob_start(); ?>

        <div id="upscale-dropbox">
            <p><strong>Drag and drop your image here</strong> or</p>
            <p><button type="button" id="select-file-btn" class="blue-button">Click to select</button></p>
            <p style="font-size: 0.9em; color: #000;">(JPG, PNG, WEBP, etc.)</p>
        </div>
        <input type="file" id="upscale-file-input" accept="image/*" style="display:none" />

        <div id="progress-container">
            <div id="progress-bar"></div>
        </div>

        <div class="spinner" id="upscale-spinner"></div>
        <div id="upscale-status"></div>
        <div id="upscale-results"></div>

        <?php
        return ob_get_clean();
    }

    public static function call_replicate_api($image_url) {
        $use_mock = defined('UPSCALE_DEMO_USE_MOCK') && UPSCALE_DEMO_USE_MOCK;

        error_log('[Upscale Demo] UPSCALE_DEMO_USE_MOCK is: ' . ($use_mock ? 'true' : 'false'));

        if ($use_mock) {
            $mock_url = 'http://wp-upscale-demo.local/wp-content/uploads/2025/07/upscaled-fake.jpg';
            error_log('[Upscale Demo] MOCK mode active. Returning fake upscaled image: ' . $mock_url);
            return $mock_url;
        }

        $token = defined('REPLICATE_API_TOKEN') ? REPLICATE_API_TOKEN : '';
        if (!$token) {
            error_log('[Upscale Demo] Replicate token not set.');
            return false;
        }

        error_log('[Upscale Demo] Using token prefix: ' . substr($token, 0, 6) . '...');
        error_log('[Upscale Demo] Image URL: ' . $image_url);

        $body = json_encode([
            'version' => '2fdc3b86a01d338ae89ad58e5d9241398a8a01de9b0dda41ba8a0434c8a00dc3', // topazlabs/image-upscale id
            'input' => [
                'image' => $image_url,
                'scale' => 2, // Available: 2 or 4
                'mode'  => 'standard' // Options: standard, graphics, lines
            ],
        ]);

        $response = wp_remote_post('https://api.replicate.com/v1/predictions', [
            'headers' => [
                'Authorization' => 'Token ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => $body,
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            error_log('[Upscale Demo] Initial request failed: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $raw_response = wp_remote_retrieve_body($response);
        error_log('[Upscale Demo] HTTP status code: ' . $status_code);
        error_log('[Upscale Demo] Raw initial response: ' . $raw_response);

        $result = json_decode($raw_response, true);
        if (empty($result['urls']['get'])) {
            error_log('[Upscale Demo] No prediction URL returned.');
            return false;
        }

        $prediction_url = $result['urls']['get'];
        error_log('[Upscale Demo] Prediction URL: ' . $prediction_url);

        // Polling loop to wait for result
        for ($i = 0; $i < 10; $i++) {
            sleep(2); // wait 2s before polling
            $res = wp_remote_get($prediction_url, [
                'headers' => ['Authorization' => 'Token ' . $token],
                'timeout' => 15,
            ]);

            if (is_wp_error($res)) {
                error_log('[Upscale Demo] Polling error: ' . $res->get_error_message());
                break;
            }

            $poll_body_raw = wp_remote_retrieve_body($res);
            $poll_body = json_decode($poll_body_raw, true);
            $status = $poll_body['status'] ?? 'unknown';

            error_log("[Upscale Demo] Polling attempt #{$i}: Status = {$status}");

            if ($status === 'succeeded') {
                $output = $poll_body['output'];
                $final_url = is_array($output) ? $output[0] : $output;
                error_log('[Upscale Demo] Upscaling succeeded. Output URL: ' . $final_url);
                return $final_url;
            } elseif ($status === 'failed') {
                error_log('[Upscale Demo] Upscaling failed: ' . json_encode($poll_body));
                break;
            }
        }

        return false;
    }
}
