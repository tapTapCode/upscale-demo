<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( get_parent_theme_file_uri( 'assets/css/editor-style.css' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues style.css on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues style.css on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

function custom_upload_size_limit( $file ) {
    $max_size = 1024 * 1024 * 1024; // 1024MB in bytes
    if ( $file['size'] > $max_size ) {
        $file['error'] = 'File size exceeds 1024MB limit.';
    }
    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'custom_upload_size_limit' );

function image_comparison_shortcode($atts) {
    $a = shortcode_atts([
        'before' => '', // URL to before image
        'after'  => '', // URL to after image
        'title'  => 'Compare These Images', // optional title
    ], $atts);

    if (!$a['before'] || !$a['after']) {
        return '<p><strong>Please provide both "before" and "after" image URLs.</strong></p>';
    }

    ob_start();
    ?>
    <style>
    .image-comparison {
		max-width: 700px;
		margin: 20px auto;
		border-radius: 20px;
		overflow: hidden;
		position: relative;
    }

    .image-comparison img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		object-position: left;
		display: block;
    }

    .image-comparison .images-container {
      	position: relative;
      	display: flex;
    }

    .image-comparison .images-container .before-image {
		position: absolute;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%; /* full width */
		object-fit: cover;
		object-position: left;
		z-index: 2;
		transition: clip-path 0.05s ease;
		will-change: clip-path;
		clip-path: inset(0 50% 0 0); /* initially hide half on the right */
	}

    .image-comparison .slider {
		position: absolute;
		inset: 0;
		opacity: 0;
		cursor: pointer;
		z-index: 5;
    }

    .image-comparison .slider-line {
		position: absolute;
		height: 100%;
		width: 4px;
		background: #fff;
		left: 50%;
		transform: translateX(-50%);
		z-index: 4;
    }

    .image-comparison .slider-icon {
		position: absolute;
		left: 50%;
		top: 50%;
		width: 60px;
		height: 60px;
		color: #fff;
		transform: translate(-50%, -50%) rotateZ(90deg);
		z-index: 6;
		pointer-events: none;
    }

	.image-comparison .label {
		position: absolute;
		bottom: 10px;
		background: rgba(0, 0, 0, 0.6);
		color: white;
		font-weight: 600;
		padding: 5px 12px;
		border-radius: 6px;
		font-family: "Poppins", sans-serif;
		font-size: 14px;
		user-select: none;
		z-index: 10;
		pointer-events: none;
	}

	.image-comparison .before-label {
		left: 10px;
	}

	.image-comparison .after-label {
		right: 10px;
	}
    </style>
    <div class="image-comparison">
      	<div class="images-container" style="height:400px;">
        	<img class="before-image" src="<?php echo esc_url($a['before']); ?>" alt="Before Image" />
        	<img class="after-image" src="<?php echo esc_url($a['after']); ?>" alt="After Image" />
			<div class="label before-label">Before</div>
  			<div class="label after-label">After</div>
        	<div class="slider-line"></div>
        	<div class="slider-icon" aria-hidden="true">
          	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:100%; height:100%;">
            	<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/>
          	</svg>
        </div>
        <input type="range" class="slider" min="0" max="100" value="50" aria-label="Image comparison slider"/>
      	</div>
    </div>
    <script>
		(function(){
			const container = document.currentScript.previousElementSibling;
			const slider = container.querySelector(".slider");
			const beforeImage = container.querySelector(".before-image");
			const sliderLine = container.querySelector(".slider-line");
			const sliderIcon = container.querySelector(".slider-icon");

			slider.addEventListener("input", (e) => {
				const sliderValue = e.target.value;
				beforeImage.style.clipPath = `inset(0 ${100 - sliderValue}% 0 0)`;
				sliderLine.style.left = sliderValue + "%";
				sliderIcon.style.left = sliderValue + "%";
			});
		})();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('image_comparison', 'image_comparison_shortcode');



