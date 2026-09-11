<?php
/**
 * WP-CLI commands: wp oomph status · wp oomph seed
 *
 * `status` reports environment, active theme, plugin version. Used as a smoke
 * check after deploys ("wp @stage oomph status" should return staging).
 *
 * `seed` creates the launch records as drafts (Seed class).
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

final class CLI {

	/**
	 * Print environment + active theme + plugin version.
	 *
	 * ## EXAMPLES
	 *
	 *     wp oomph status
	 */
	public function status(): void {
		$active = wp_get_theme();
		$rows   = array(
			array( 'key' => 'Environment',    'value' => Environment::type() ),
			array( 'key' => 'Active theme',   'value' => $active->get( 'Name' ) . ' (' . $active->get_stylesheet() . ')' ),
			array( 'key' => 'Theme version',  'value' => (string) $active->get( 'Version' ) ),
			array( 'key' => 'Parent theme',   'value' => $active->parent() ? $active->parent()->get( 'Name' ) : '—' ),
			array( 'key' => 'Plugin version', 'value' => OOMPH_CORE_VERSION ),
			array( 'key' => 'Home URL',       'value' => (string) home_url( '/' ) ),
		);

		\WP_CLI\Utils\format_items( 'table', $rows, array( 'key', 'value' ) );
	}

	/**
	 * Seed the launch records as drafts. Safe to re-run: existing slugs are left alone.
	 *
	 * ## OPTIONS
	 *
	 * <what>
	 * : Which set. `destinations` — the eleven (D07, D33), each with its
	 * Destinations term. `operators` — the seven (D25). `tours` — three per
	 * escorted operator. `pages` — the four ways-to-travel pages, published
	 * on staging (their copy is in the theme), drafts on production.
	 * ---
	 * options:
	 *   - destinations
	 *   - operators
	 *   - tours
	 *   - pages
	 * ---
	 *
	 * [--dry-run]
	 * : Report what would be created without writing anything.
	 *
	 * ## EXAMPLES
	 *
	 *     wp oomph seed destinations --dry-run
	 *     wp @stage oomph seed destinations
	 *     wp @stage oomph seed operators
	 *     wp @stage oomph seed tours
	 *     wp @stage oomph seed pages
	 *
	 * @param string[]             $args
	 * @param array<string,string> $assoc_args
	 */
	public function seed( array $args, array $assoc_args ): void {
		$what    = $args[0] ?? '';
		$dry_run = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );

		if ( ! in_array( $what, array( 'destinations', 'operators', 'tours', 'pages' ), true ) ) {
			\WP_CLI::error( 'Seed set must be "destinations", "operators", "tours" or "pages".' );
		}

		if ( Environment::is_production() && ! $dry_run ) {
			\WP_CLI::warning( 'This is production. Records are created as drafts, nothing is published.' );
		}

		$rows = Seed::run( $what, (bool) $dry_run );
		\WP_CLI\Utils\format_items( 'table', $rows, array( 'slug', 'title', 'action', 'id' ) );

		$created = count( array_filter( $rows, static fn( array $row ): bool => 0 === strpos( $row['action'], 'created' ) ) );
		$failed  = count( array_filter( $rows, static fn( array $row ): bool => 'failed' === $row['action'] ) );

		if ( $failed ) {
			\WP_CLI::error( sprintf( '%d record(s) failed to insert.', $failed ) );
		}
		if ( $dry_run ) {
			\WP_CLI::success( 'Dry run — nothing written.' );
		} else {
			\WP_CLI::success( sprintf( '%d created, %d already existed.', $created, count( $rows ) - $created ) );
		}
	}
}

\WP_CLI::add_command( 'oomph', CLI::class );
