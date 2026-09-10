<?php
/**
 * Theme supports, menu locations, and the one Rank Math fix a block theme needs.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * Declare theme supports. A block theme gets most implicitly; these are the
 * ones WordPress still expects a theme to opt into.
 */
function oomphtravel_theme_supports(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );

	// A rendition between core's 1024 and 1536 for full-bleed heroes: 3× phones
	// (1170 device px) were choosing the 1536 copy. Carried from CruiseOomph.
	add_image_size( 'oomphtravel-hero-1200', 1200, 1200 );
}
add_action( 'after_setup_theme', 'oomphtravel_theme_supports' );

/**
 * Classic menu locations keep menus editable in Appearance → Menus and
 * available to the plugin even though the header renders a navigation block.
 */
function oomphtravel_register_menus(): void {
	register_nav_menus(
		array(
			'primary' => __( 'Primary', 'oomphtravel' ),
			'footer'  => __( 'Footer', 'oomphtravel' ),
		)
	);
}
add_action( 'after_setup_theme', 'oomphtravel_register_menus' );

/**
 * Print exactly one <title>.
 *
 * Block themes swap core's conditional `_wp_render_title_tag` for the
 * unconditional `_block_template_render_title_tag` at wp_head 1. Rank Math
 * moves the classic renderer inside its own head block, so without this a
 * block theme with Rank Math printed the title twice. (From CruiseOomph.)
 */
function oomphtravel_single_title_tag(): void {
	if ( false !== has_action( 'rank_math/head', '_wp_render_title_tag' ) ) {
		remove_action( 'wp_head', '_block_template_render_title_tag', 1 );
	}
}
add_action( 'wp_head', 'oomphtravel_single_title_tag', 0 );

/**
 * One pattern category for the Stage 3 components, so they sit together in
 * the inserter. Header and footer are `Inserter: no` and never appear there.
 */
function oomphtravel_pattern_category(): void {
	register_block_pattern_category(
		'oomphtravel',
		array(
			'label'       => __( 'OomphTravel', 'oomphtravel' ),
			'description' => __( 'Bands, cards and headings from the component inventory.', 'oomphtravel' ),
		)
	);
}
add_action( 'init', 'oomphtravel_pattern_category' );
