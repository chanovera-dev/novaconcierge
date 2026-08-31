<?php
/**
 * Nova Concierge child theme functions and definitions
 *
 * Child theme of Stories providing real estate listings with EasyBroker synchronization,
 * interactive AJAX filtering, and native WordPress meta boxes.
 *
 * @package NovaConcierge
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define Theme Constants
 */
define( 'NOVACONCIERGE_VERSION', '1.0.0' );
define( 'NOVACONCIERGE_DIR', get_stylesheet_directory() );
define( 'NOVACONCIERGE_URI', get_stylesheet_directory_uri() );

/**
 * Helper function to retrieve the version of a theme asset based on its modification time.
 *
 * @param string $file_path Relative path to the file from theme root.
 * @return int|string File modification time or theme version fallback.
 */
function novaconcierge_get_asset_version( $file_path ) {
	$full_path = NOVACONCIERGE_DIR . $file_path;
	return file_exists( $full_path ) ? filemtime( $full_path ) : NOVACONCIERGE_VERSION;
}

/**
 * Enqueue Parent & Child Theme Scripts and Styles.
 */
function novaconcierge_enqueue_scripts() {
	// Parent theme stylesheet
	$parent_style_ver = function_exists( 'stories_get_asset_version' ) ? stories_get_asset_version( '/style.css' ) : '1.0.0';
	wp_enqueue_style( 'stories-parent-style', get_template_directory_uri() . '/style.css', array(), $parent_style_ver );

	// Ensure parent posts/loop stylesheet is loaded for .posts-grid reuse
	if ( file_exists( get_template_directory() . '/assets/css/posts.css' ) ) {
		$parent_posts_ver = function_exists( 'stories_get_asset_version' ) ? stories_get_asset_version( '/assets/css/posts.css' ) : '1.0.0';
		wp_enqueue_style( 'stories-posts', get_template_directory_uri() . '/assets/css/posts.css', array( 'stories-parent-style' ), $parent_posts_ver );
	}

	// Ensure parent pagination stylesheet is loaded
	if ( file_exists( get_template_directory() . '/assets/css/pagination.css' ) ) {
		$parent_pagination_ver = function_exists( 'stories_get_asset_version' ) ? stories_get_asset_version( '/assets/css/pagination.css' ) : '1.0.0';
		wp_enqueue_style( 'stories-pagination', get_template_directory_uri() . '/assets/css/pagination.css', array( 'stories-parent-style' ), $parent_pagination_ver );
	}

	// Child theme stylesheet
	wp_enqueue_style( 'novaconcierge-style', get_stylesheet_uri(), array( 'stories-parent-style' ), novaconcierge_get_asset_version( '/style.css' ) );

	// Real Estate Properties CSS
	wp_enqueue_style(
		'novaconcierge-properties',
		NOVACONCIERGE_URI . '/assets/css/properties.css',
		array( 'novaconcierge-style', 'stories-posts', 'stories-pagination' ),
		novaconcierge_get_asset_version( '/assets/css/properties.css' )
	);

	// Property Gallery & Card Slideshow JS
	wp_enqueue_script(
		'novaconcierge-property-gallery',
		NOVACONCIERGE_URI . '/assets/js/property-gallery.js',
		array(),
		novaconcierge_get_asset_version( '/assets/js/property-gallery.js' ),
		true
	);

	// Property AJAX Filter JS
	wp_enqueue_script(
		'novaconcierge-properties-filter',
		NOVACONCIERGE_URI . '/assets/js/properties-filter.js',
		array( 'novaconcierge-property-gallery' ),
		novaconcierge_get_asset_version( '/assets/js/properties-filter.js' ),
		true
	);

	// Localize AJAX object for filters
	wp_localize_script(
		'novaconcierge-properties-filter',
		'novaconcierge_ajax',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'novaconcierge_filter_nonce' ),
		)
	);

	// Backward compatibility object
	wp_localize_script(
		'novaconcierge-properties-filter',
		'ajax_object',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'novaconcierge_enqueue_scripts', 20 );

/**
 * Enqueue Admin Scripts for Meta Boxes.
 *
 * @param string $hook_suffix Current admin page.
 */
function novaconcierge_admin_enqueue_scripts( $hook_suffix ) {
	global $post_type;

	if ( 'property' === $post_type || in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_script(
			'novaconcierge-admin-properties',
			NOVACONCIERGE_URI . '/assets/js/admin-properties.js',
			array( 'jquery' ),
			novaconcierge_get_asset_version( '/assets/js/admin-properties.js' ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'novaconcierge_admin_enqueue_scripts' );

/**
 * Include modular files from inc/ directory.
 */
$novaconcierge_includes = array(
	'/inc/cpt-property.php',      // CPT Registration & Native Meta Boxes
	'/inc/real-estate-tools.php', // Helper tools, formatting, icons, and map renderer
	'/inc/easybroker-sync.php',   // EasyBroker API sync client and WP-Cron schedule
	'/inc/options-page.php',      // Admin settings and manual sync dashboard
	'/inc/ajax-filters.php',      // Dynamic AJAX filter endpoints
);

foreach ( $novaconcierge_includes as $inc_file ) {
	$inc_path = NOVACONCIERGE_DIR . $inc_file;
	if ( file_exists( $inc_path ) ) {
		require_once $inc_path;
	}
}

/**
 * Automatically create the "Propiedades" catalog page if it does not exist.
 */
function novaconcierge_auto_create_properties_page() {
	$page_slug = 'propiedades';
	$page_obj  = get_page_by_path( $page_slug );

	if ( ! $page_obj ) {
		$page_id = wp_insert_post( array(
			'post_title'     => __( 'Propiedades', 'novaconcierge' ),
			'post_name'      => $page_slug,
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => '',
			'comment_status' => 'closed',
		) );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-properties.php' );
		}
	}
}
add_action( 'after_switch_theme', 'novaconcierge_auto_create_properties_page' );
add_action( 'init', 'novaconcierge_auto_create_properties_page' );
