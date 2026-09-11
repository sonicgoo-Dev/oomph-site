<?php
/**
 * OomphTravel theme bootstrap.
 *
 * Loads the include files. All behaviour lives in inc/; this file stays a
 * manifest so it is obvious what the theme actually does.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/** Theme version, used to cache-bust enqueued assets. */
define( 'OOMPHTRAVEL_THEME_VERSION', '0.2.3' );

/** Absolute path to the theme directory, with a trailing slash. */
define( 'OOMPHTRAVEL_THEME_DIR', trailingslashit( get_stylesheet_directory() ) );

/** Public URL of the theme directory, with a trailing slash. */
define( 'OOMPHTRAVEL_THEME_URI', trailingslashit( get_stylesheet_directory_uri() ) );

require_once OOMPHTRAVEL_THEME_DIR . 'inc/setup.php';
require_once OOMPHTRAVEL_THEME_DIR . 'inc/enqueue.php';
require_once OOMPHTRAVEL_THEME_DIR . 'inc/template-tags.php';

// Values the oomph-travel-core plugin reads from the active theme. These
// moved here from kadence-oomph-child so the switch does not lose them.
add_filter( 'oomph_calendly_url', static fn(): string => 'https://calendly.com/eric-oomphtravel/30min' );
add_filter( 'oomph_clarity_id', static fn(): string => 'wwzo7gmy7q' );
