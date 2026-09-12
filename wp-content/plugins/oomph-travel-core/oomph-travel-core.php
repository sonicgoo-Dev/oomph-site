<?php
/**
 * Plugin Name:       Oomph Travel Core
 * Plugin URI:        https://oomphtravel.com
 * Description:       Data layer for the Oomph Travel rebuild — custom post types, taxonomies, schema injection, environment guards. Presentation belongs in the theme; this lives in a plugin so it survives a theme switch.
 * Version:           1.8.0
 * Requires PHP:      8.1
 * Requires at least: 6.7
 * Tested up to:      6.8
 * Author:            Oomph Travel LLC
 * Author URI:        https://oomphtravel.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       oomph-travel-core
 * Domain Path:       /languages
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OOMPH_CORE_VERSION', '1.8.0' );
define( 'OOMPH_CORE_FILE',    __FILE__ );
define( 'OOMPH_CORE_DIR',     plugin_dir_path( __FILE__ ) );
define( 'OOMPH_CORE_URI',     plugin_dir_url( __FILE__ ) );

// Classes — autoload-free, explicit requires keep the boot order obvious.
require_once OOMPH_CORE_DIR . 'includes/class-environment.php';
require_once OOMPH_CORE_DIR . 'includes/class-cpt-destination.php';
require_once OOMPH_CORE_DIR . 'includes/class-cpt-itinerary.php';
require_once OOMPH_CORE_DIR . 'includes/class-cpt-operator.php';
require_once OOMPH_CORE_DIR . 'includes/class-cpt-tour.php';
require_once OOMPH_CORE_DIR . 'includes/class-cpt-inquiry.php';
require_once OOMPH_CORE_DIR . 'includes/class-taxonomies.php';
require_once OOMPH_CORE_DIR . 'includes/class-admin-columns.php';
require_once OOMPH_CORE_DIR . 'includes/class-fields.php'; // Field access without a fields plugin — read by the seeder, the schema and the theme.
require_once OOMPH_CORE_DIR . 'includes/class-seed.php';
require_once OOMPH_CORE_DIR . 'includes/class-advisor.php'; // Advisor identity — read by class-schema.php and the theme.
require_once OOMPH_CORE_DIR . 'includes/class-schema.php';
require_once OOMPH_CORE_DIR . 'includes/class-clarity-guard.php';
require_once OOMPH_CORE_DIR . 'includes/class-plainsend.php';
require_once OOMPH_CORE_DIR . 'includes/class-inquiry.php'; // Start planning: the POST, the record, the emails (plan §6.15).
require_once OOMPH_CORE_DIR . 'includes/class-acf-config.php';
require_once OOMPH_CORE_DIR . 'includes/class-seo.php';
require_once OOMPH_CORE_DIR . 'includes/class-redirects.php'; // The moved pages and the CruiseOomph hand-off (plan §4.2, §8.4, D43).
require_once OOMPH_CORE_DIR . 'includes/class-removals.php';  // Taking the cruise records out of the database (plan §8.4, D02).

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once OOMPH_CORE_DIR . 'includes/class-cli.php';
}

// Boot. Post types first, then the taxonomies that attach to them.
add_action( 'init', array( \OomphTravel\Core\CPT_Destination::class, 'register' ) );
add_action( 'init', array( \OomphTravel\Core\CPT_Itinerary::class,   'register' ) );
add_action( 'init', array( \OomphTravel\Core\CPT_Operator::class,    'register' ) );
add_action( 'init', array( \OomphTravel\Core\CPT_Tour::class,        'register' ) );
add_action( 'init', array( \OomphTravel\Core\CPT_Inquiry::class,     'register' ) );
add_action( 'init', array( \OomphTravel\Core\Taxonomies::class,      'register' ) );

\OomphTravel\Core\CPT_Destination::init();
\OomphTravel\Core\Admin_Columns::init();
\OomphTravel\Core\Schema::init();
\OomphTravel\Core\Clarity_Guard::init();
\OomphTravel\Core\Plainsend::init();
\OomphTravel\Core\Inquiry::init();
\OomphTravel\Core\ACF_Config::init();
\OomphTravel\Core\SEO::init();
\OomphTravel\Core\Redirects::init();

/**
 * One-time cleanup after the cruise data layer was removed (D02, 2026-09).
 *
 * The daily `oomph_retire_unbookable_sailings` sweep was scheduled by the old
 * CPT_Cruise class on every init. With the class gone the event has no
 * callback, but the row would sit in the cron array forever and WP-Cron would
 * try it daily. Cleared once per site, remembered by option, and the rewrite
 * rules are flushed so /group-cruises/ stops resolving to the removed archive.
 */
add_action( 'init', static function (): void {
	if ( '1' === get_option( 'oomph_core_cruise_cleanup_done' ) ) {
		return;
	}
	wp_clear_scheduled_hook( 'oomph_retire_unbookable_sailings' );
	flush_rewrite_rules( false );
	update_option( 'oomph_core_cruise_cleanup_done', '1', false );
}, 99 );

/**
 * Rewrite rules are flushed once per plugin version, so a deploy that adds a
 * post type (1.2.0 added operators and tours) or a rewrite rule (1.7.0 added
 * /start-planning/received/) resolves its URLs without a manual
 * re-activation. Cheap: one option read per request.
 */
add_action( 'init', static function (): void {
	if ( OOMPH_CORE_VERSION === get_option( 'oomph_core_rewrite_version' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'oomph_core_rewrite_version', OOMPH_CORE_VERSION, false );
}, 100 );

/**
 * Activation — flush rewrite rules so CPT slugs resolve immediately.
 */
register_activation_hook( __FILE__, function (): void {
	\OomphTravel\Core\CPT_Destination::register();
	\OomphTravel\Core\CPT_Itinerary::register();
	\OomphTravel\Core\CPT_Operator::register();
	\OomphTravel\Core\CPT_Tour::register();
	\OomphTravel\Core\CPT_Inquiry::register();
	\OomphTravel\Core\Taxonomies::register();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function (): void {
	flush_rewrite_rules();
} );
