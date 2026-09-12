<?php
/**
 * Removing the cruise content from the database (plan §8.4, D02).
 *
 * Stage 2 removed the code: the sailing and ship post types, the importer,
 * the quiz, the templates. The records they left behind are still in the
 * database on staging and production, and this is what takes them out.
 *
 * It is a report first. Run without --apply it lists what it would do and
 * changes nothing; with --apply it moves the records to the trash, where they
 * can be put back for thirty days; with --force they are deleted outright and
 * the attached media with them. The nine cruise articles are the one set that
 * needs its own flag, because they must be copied to CruiseOomph before they
 * go here (docs/stage-2-runbook.md, step 1) and no report can tell whether
 * that has happened.
 *
 * Run by `wp oomph remove-cruise` and by the "Remove the cruise content"
 * GitHub workflow, which is the same command with a button on it.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Removals {

	/** The post types Stage 2 unregistered. Their rows are still typed this way. */
	public const CRUISE_TYPES = array( 'oomph_cruise', 'oomph_ship' );

	/** The two pages (plan §4.2): the cabin quiz and the old guide landing. */
	public const PAGES = array( 'trip-quiz', 'cruise-travel-trends' );

	/** Appendix B. `10-day-united-kingdom-itinerary` is not here: it stays. */
	public const JOURNAL = array(
		'norwegian-fjords-vs-baltic',
		'virgin-voyages-rockstar-suite-worth-it',
		'silversea-vs-regent',
		'avoid-cruise-ship-crowds-in-the-mediterranean',
		'barcelona-before-your-cruise',
		'fly-the-drake-passage-or-sail',
		'japan-by-sea-summer-festivals-cruise',
		'first-premium-cruise',
		'the-slow-cruise',
	);

	/** The daily sweep the old CPT_Cruise class scheduled. */
	private const CRON_HOOK = 'oomph_retire_unbookable_sailings';

	/**
	 * Do it, or say what would be done.
	 *
	 * @param bool $apply   False reports only.
	 * @param bool $journal Include the nine cruise articles.
	 * @param bool $force   Delete outright instead of trashing; also removes attached media.
	 * @return array<int,array{what:string,found:int,action:string,detail:string}>
	 */
	public static function run( bool $apply, bool $journal, bool $force ): array {
		$rows = array();
		$verb = $force ? 'delete' : 'trash';
		$did  = $force ? 'deleted' : 'trashed';

		// 1. Sailings and ships.
		$ids = self::ids_of_types( self::CRUISE_TYPES );
		if ( $apply ) {
			foreach ( $ids as $id ) {
				self::remove_post( $id, $force );
			}
		}
		$rows[] = self::row( 'sailing and ship records', count( $ids ), $apply ? $did : "would $verb", '' );

		// Their media: Distinctive Voyages ship art. Trash does not apply to
		// media, so it goes only with --force, and is reported otherwise.
		$media = $ids ? get_posts(
			array(
				'post_type'       => 'attachment',
				'post_status'     => 'inherit',
				'post_parent__in' => $ids,
				'numberposts'     => -1,
				'fields'          => 'ids',
			)
		) : array();
		if ( $apply && $force ) {
			foreach ( $media as $id ) {
				wp_delete_attachment( (int) $id, true );
			}
			$rows[] = self::row( 'media attached to them', count( $media ), 'deleted', '' );
		} else {
			$rows[] = self::row( 'media attached to them', count( $media ), 'kept', $media ? 'goes with --force' : '' );
		}

		// 2. The two pages.
		foreach ( self::PAGES as $slug ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			$live = $page instanceof \WP_Post && 'trash' !== $page->post_status;
			if ( $live && $apply ) {
				self::remove_post( (int) $page->ID, $force );
			}
			$rows[] = self::row( "page /$slug/", $live ? 1 : 0, $live ? ( $apply ? $did : "would $verb" ) : 'already gone', '' );
		}

		// 3. The nine cruise articles, only when asked.
		$present = array();
		foreach ( self::JOURNAL as $slug ) {
			$post = get_page_by_path( $slug, OBJECT, 'post' );
			if ( $post instanceof \WP_Post && 'trash' !== $post->post_status ) {
				$present[] = (int) $post->ID;
			}
		}
		if ( ! $present ) {
			$rows[] = self::row( 'the nine cruise articles', 0, 'already gone', '' );
		} elseif ( ! $journal ) {
			$rows[] = self::row( 'the nine cruise articles', count( $present ), 'kept', 'copy them to CruiseOomph first, then pass --journal' );
		} else {
			if ( $apply ) {
				foreach ( $present as $id ) {
					self::remove_post( $id, $force );
				}
			}
			$rows[] = self::row( 'the nine cruise articles', count( $present ), $apply ? $did : "would $verb", '' );
		}

		// 4. The orphaned cron row, if the one-time cleanup never ran here.
		$scheduled = false !== wp_next_scheduled( self::CRON_HOOK );
		if ( $scheduled && $apply ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
		$rows[] = self::row( 'daily sailing sweep (cron)', $scheduled ? 1 : 0, $scheduled ? ( $apply ? 'cleared' : 'would clear' ) : 'already gone', '' );

		// 5. Things to look at by hand. Reported, never touched.
		$pdfs = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'application/pdf',
				'numberposts'    => -1,
				's'              => 'trend',
				'fields'         => 'ids',
			)
		);
		$rows[] = self::row(
			'old public guide PDF in the media library',
			count( $pdfs ),
			'look',
			$pdfs ? implode( ' ', array_map( static fn( $id ): string => (string) wp_get_attachment_url( (int) $id ), $pdfs ) ) . ' — the guide is delivered by PlainSend now (plan §8.3); delete or restrict this by hand' : ''
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$ff     = is_plugin_active( 'fluentform/fluentform.php' );
		$rows[] = self::row( 'Fluent Forms', $ff ? 1 : 0, $ff ? 'look' : 'not active', $ff ? 'still active; nothing on the new site uses it (D31)' : '' );

		if ( $apply ) {
			flush_rewrite_rules( false );
		}

		return $rows;
	}

	/**
	 * Every post of the given types that is not already in the trash.
	 *
	 * The types are no longer registered, so WordPress cannot resolve
	 * their statuses; a direct query is the reliable way to find them.
	 *
	 * @param string[] $types
	 * @return int[]
	 */
	private static function ids_of_types( array $types ): array {
		global $wpdb;
		$in  = implode( ',', array_fill( 0, count( $types ), '%s' ) );
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($in) AND post_status <> 'trash' AND post_status <> 'auto-draft' ORDER BY ID";
		return array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( $sql, ...$types ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders built above.
	}

	private static function remove_post( int $id, bool $force ): void {
		if ( $force ) {
			wp_delete_post( $id, true );
		} else {
			wp_trash_post( $id );
		}
	}

	/**
	 * @return array{what:string,found:int,action:string,detail:string}
	 */
	private static function row( string $what, int $found, string $action, string $detail ): array {
		return array(
			'what'   => $what,
			'found'  => $found,
			'action' => $action,
			'detail' => $detail,
		);
	}
}
