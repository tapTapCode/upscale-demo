# Upscale Demo

A WordPress plugin to upload and upscale images using the Replicate `topazlabs/image-upscaler` model.

## Features

- Drag & drop image upload
- AJAX-powered image upscaling
- Before/after preview comparison
- Download upscaled images
- Simple shortcode to embed anywhere: `[upscale_demo]`

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the WordPress admin panel.
3. Use the shortcode `[upscale_demo]` on any post or page to display the upload and upscale interface.

## Usage

- Drag and drop an image or click to select one.
- The plugin sends the image to the Replicate API to upscale.
- Preview the upscaled image alongside the original.
- Download the upscaled image directly.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Valid Replicate API token configured in the plugin settings (if applicable).

## Development

This plugin uses:

- WordPress AJAX for backend processing
- Replicate API for AI upscaling

## License

GPL-2.0-or-later

---

Created by [Your Name]
