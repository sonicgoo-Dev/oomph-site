<?php
/**
 * WP-CLI commands: wp oomph status · wp oomph seed · wp oomph import-tour · wp oomph remove-cruise
 *
 * `status` reports environment, active theme, plugin version. Used as a smoke
 * check after deploys ("wp @stage oomph status" should return staging).
 *
 * `seed` creates the launch records as drafts (Seed class).
 *
 * `import-tour` creates one draft tour from a JSON record (Tour_Import
 * class). The "Add a tour" Actions button runs it (import-tour.yml).
 *
 * `remove-cruise` takes the old cruise content out of the database, as a
 * report until --apply is given (Removals class, plan §8.4).
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

	/**
	 * Create one tour, as a draft, from a JSON record. Never publishes.
	 *
	 * The record shape is documented in content/tours/README.md: slug, title,
	 * operator and destination slugs, nights, months, the card blurb, the
	 * itinerary rows, inclusions, Eric's note and so on. The record is
	 * checked first — unknown operator or destination, a No List word, a
	 * placeholder — and nothing is written while anything is wrong. A tour
	 * whose slug already exists is left alone unless --update is given, and
	 * then only the fields present in the record are rewritten; its status
	 * and photo are never touched.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<path>]
	 * : The JSON file. Use `-` (or --stdin) to read the record from standard input.
	 *
	 * [--stdin]
	 * : Read the record from standard input instead of a file.
	 *
	 * [--update]
	 * : If a tour with this slug exists, rewrite the fields the record carries.
	 *
	 * [--dry-run]
	 * : Check the record and say what would happen, without writing.
	 *
	 * [--production]
	 * : Required when the environment is production.
	 *
	 * ## EXAMPLES
	 *
	 *     wp oomph import-tour --file=content/tours/globus-italian-treasures.json --dry-run
	 *     wp @stage oomph import-tour --stdin < content/tours/globus-italian-treasures.json
	 *     wp @stage oomph import-tour --file=tour.json --update
	 *
	 * @subcommand import-tour
	 *
	 * @param string[]             $args
	 * @param array<string,string> $assoc_args
	 */
	public function import_tour( array $args, array $assoc_args ): void {
		$file    = (string) \WP_CLI\Utils\get_flag_value( $assoc_args, 'file', '' );
		$stdin   = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'stdin', false ) || '-' === $file;
		$update  = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'update', false );
		$dry_run = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
		$prod_ok = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'production', false );

		if ( $stdin ) {
			$json = stream_get_contents( STDIN );
		} elseif ( '' !== $file ) {
			if ( ! is_readable( $file ) ) {
				\WP_CLI::error( sprintf( 'Cannot read %s.', $file ) );
			}
			$json = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file under WP-CLI.
		} else {
			\WP_CLI::error( 'Give the record with --file=<path> or --stdin.' );
			return;
		}

		try {
			$record = Tour_Import::decode( (string) $json );
		} catch ( \InvalidArgumentException $e ) {
			\WP_CLI::error( $e->getMessage() );
			return;
		}

		\WP_CLI::log( sprintf( 'Environment: %s · %s', Environment::type(), $dry_run ? 'dry run, nothing changes' : ( $update ? 'create or update' : 'create only' ) ) );

		$problems = Tour_Import::validate( $record );
		if ( $problems ) {
			foreach ( $problems as $problem ) {
				\WP_CLI::warning( $problem );
			}
			\WP_CLI::error( sprintf( '%d problem(s) in the record. Nothing was written.', count( $problems ) ) );
		}

		if ( ! $dry_run && Environment::is_production() && ! $prod_ok ) {
			\WP_CLI::error( 'This is production. Add --production to confirm, after the same import has been checked on staging.' );
		}

		$row = Tour_Import::import( $record, $update, $dry_run );
		\WP_CLI\Utils\format_items( 'table', array( $row ), array( 'slug', 'title', 'action', 'status', 'id' ) );

		if ( 0 === strpos( $row['action'], 'failed' ) ) {
			\WP_CLI::error( $row['action'] );
		}
		if ( $row['edit'] ) {
			\WP_CLI::log( 'Edit: ' . $row['edit'] );
		}
		if ( $dry_run ) {
			\WP_CLI::success( 'Dry run — the record is sound. Nothing written.' );
		} elseif ( 'updated' === $row['action'] ) {
			\WP_CLI::success( 'Updated. Status unchanged; the photo is untouched.' );
		} elseif ( 0 === strpos( $row['action'], 'created' ) ) {
			\WP_CLI::success( 'Created as a draft. Set the hero photo, check the price, then publish from the edit screen.' );
		} else {
			\WP_CLI::success( $row['action'] . '.' );
		}
	}

	/**
	 * Remove the cruise content from the database (plan §8.4, D02). A report unless --apply is given.
	 *
	 * Trashes the sailing and ship records, the cabin quiz page, the old
	 * guide landing page and the four moved pages whose addresses redirect in
	 * code, deletes the static llms.txt the old build left in the web root,
	 * and clears the orphaned daily sweep. The nine cruise articles go only
	 * with --journal, once they have been copied to CruiseOomph. Media and
	 * anything else it notices are reported, not touched, unless --force.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Do it. Without this flag the command only reports.
	 *
	 * [--journal]
	 * : Include the nine cruise articles (Appendix B). Copy them first.
	 *
	 * [--force]
	 * : Delete outright instead of trashing, and remove the media attached to sailings.
	 *
	 * [--production]
	 * : Required alongside --apply when the environment is production.
	 *
	 * ## EXAMPLES
	 *
	 *     wp oomph remove-cruise
	 *     wp @stage oomph remove-cruise --apply
	 *     wp @stage oomph remove-cruise --apply --journal
	 *     wp @prod oomph remove-cruise --apply --journal --production
	 *
	 * @subcommand remove-cruise
	 *
	 * @param string[]             $args
	 * @param array<string,string> $assoc_args
	 */
	public function remove_cruise( array $args, array $assoc_args ): void {
		$apply   = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'apply', false );
		$journal = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'journal', false );
		$force   = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );
		$prod_ok = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'production', false );

		\WP_CLI::log( sprintf( 'Environment: %s · %s', Environment::type(), $apply ? ( $force ? 'deleting' : 'moving to the trash' ) : 'report only, nothing changes' ) );

		if ( $apply && Environment::is_production() && ! $prod_ok ) {
			\WP_CLI::error( 'This is production. Add --production to confirm, after the same run has been checked on staging.' );
		}

		$rows = Removals::run( $apply, $journal, $force );
		\WP_CLI\Utils\format_items( 'table', $rows, array( 'what', 'found', 'action', 'detail' ) );

		if ( ! $apply ) {
			\WP_CLI::success( 'Report only — nothing was changed. Run again with --apply to do it.' );
		} elseif ( $force ) {
			\WP_CLI::success( 'Done. The records are deleted.' );
		} else {
			\WP_CLI::success( 'Done. The records are in the trash and can be restored for thirty days.' );
		}
	}
}

\WP_CLI::add_command( 'oomph', CLI::class );
