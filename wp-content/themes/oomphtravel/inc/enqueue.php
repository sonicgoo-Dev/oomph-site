<?php
/**
 * Front-end and editor asset loading.
 *
 * Fonts are declared in theme.json (fontFace) and emitted by WordPress. This
 * file adds the one preload hint that measured well, and loads the token,
 * type, shell and component stylesheets, plus the small deferred shell script
 * (header state, dropdowns, mobile drawer, deferred pictures, ticker control).
 * Page-specific CSS arrives with later stages.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end stylesheets in dependency order: tokens → type → theme.
 */
function oomphtravel_enqueue_assets(): void {
	$v = OOMPHTRAVEL_THEME_VERSION;
	wp_enqueue_style( 'oomphtravel-style', OOMPHTRAVEL_THEME_URI . 'style.css', array(), $v );
	wp_enqueue_style( 'oomphtravel-tokens', OOMPHTRAVEL_THEME_URI . 'assets/css/tokens.css', array( 'oomphtravel-style' ), $v );
	wp_enqueue_style( 'oomphtravel-type', OOMPHTRAVEL_THEME_URI . 'assets/css/type.css', array( 'oomphtravel-tokens' ), $v );
	wp_enqueue_style( 'oomphtravel-theme', OOMPHTRAVEL_THEME_URI . 'assets/css/theme.css', array( 'oomphtravel-type' ), $v );
	wp_enqueue_style( 'oomphtravel-components', OOMPHTRAVEL_THEME_URI . 'assets/css/components.css', array( 'oomphtravel-theme' ), $v );

	wp_enqueue_script(
		'oomphtravel-shell',
		OOMPHTRAVEL_THEME_URI . 'assets/js/shell.js',
		array(),
		$v,
		array( 'strategy' => 'defer' )
	);
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_assets' );

/**
 * Load the same sheets in the block editor so patterns look right there.
 */
function oomphtravel_editor_styles(): void {
	add_editor_style( array( 'assets/css/tokens.css', 'assets/css/type.css', 'assets/css/theme.css', 'assets/css/components.css' ) );
}
add_action( 'after_setup_theme', 'oomphtravel_editor_styles' );

/**
 * Preload the latin subset of Fraunces only, at low priority.
 *
 * CruiseOomph Phase 10.2 measured a homepage on a 1.6 Mbps phone: preloading
 * both latin files (144 KB) put them ahead of the hero image and cost about a
 * second of LCP. Fraunces is the headline the visitor reads first, so it keeps
 * a low-priority hint; Inter arrives with the stylesheet and swaps in behind.
 *
 * No ?ver here: theme.json's @font-face src has none, and a preload whose URL
 * differs from the stylesheet's downloads the font twice.
 */
function oomphtravel_preload_fonts(): void {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin fetchpriority="low">' . "\n",
		esc_url( OOMPHTRAVEL_THEME_URI . 'assets/fonts/fraunces-latin.woff2' )
	);
}
add_action( 'wp_head', 'oomphtravel_preload_fonts', 1 );

/**
 * Skip link, first focusable element on every page (WCAG 2.4.1).
 */
function oomphtravel_skip_link(): void {
	echo '<a class="ot-skip-link" href="#ot-main">' . esc_html__( 'Skip to content', 'oomphtravel' ) . '</a>' . "\n";
}
add_action( 'wp_body_open', 'oomphtravel_skip_link' );
