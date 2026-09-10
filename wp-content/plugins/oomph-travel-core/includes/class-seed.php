<?php
/**
 * Seed the launch records: the eleven destinations (D07 + D33) and the seven
 * operators (D25). Run from WP-CLI: `wp oomph seed destinations`.
 *
 * Idempotent and additive. Each record is looked up by slug; an existing one
 * is left exactly as it is, so re-running after Eric has edited a page
 * changes nothing. New records are created as drafts with only the fields
 * the handoff fixes — slug, title, variant, kind, order, the CruiseOomph
 * region where it is unambiguous — and nothing that reads as copy. Every
 * field value is written the way the fields plugin writes it (value under
 * the field name, field key under the underscored name) so the admin form
 * shows it straight away.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Seed {

	/**
	 * The eleven destinations in index order (plan §6.2; Africa per D33).
	 * cruiseoomph_region is set only where the CruiseOomph filter value is
	 * beyond doubt; Portugal, Hawaii, Mexico and Africa are left for Eric.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function destinations(): array {
		return array(
			array( 'slug' => 'italy',      'title' => 'Italy',                  'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean',   'headline' => 'Italy, planned by someone who keeps going back.' ),
			array( 'slug' => 'uk-ireland', 'title' => 'UK & Ireland',           'variant' => 'custom', 'cruiseoomph_region' => 'Northern Europe' ),
			array( 'slug' => 'france',     'title' => 'France',                 'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'spain',      'title' => 'Spain',                  'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'portugal',   'title' => 'Portugal',               'variant' => 'custom', 'cruiseoomph_region' => '' ),
			array( 'slug' => 'greece',     'title' => 'Greece',                 'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'croatia',    'title' => 'Croatia & the Adriatic', 'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'hawaii',     'title' => 'Hawaii',                 'variant' => 'resort', 'cruiseoomph_region' => '' ),
			array( 'slug' => 'mexico',     'title' => 'Mexico',                 'variant' => 'resort', 'cruiseoomph_region' => '' ),
			array( 'slug' => 'caribbean',  'title' => 'Caribbean',              'variant' => 'resort', 'cruiseoomph_region' => 'Caribbean' ),
			array( 'slug' => 'africa',     'title' => 'Africa',                 'variant' => 'guided', 'cruiseoomph_region' => '' ),
		);
	}

	/**
	 * The seven operators in display order (D25, D32).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function operators(): array {
		return array(
			array( 'slug' => 'globus',                          'title' => 'Globus',                          'kind' => 'escorted' ),
			array( 'slug' => 'tauck',                           'title' => 'Tauck',                           'kind' => 'escorted' ),
			array( 'slug' => 'insight-vacations',               'title' => 'Insight Vacations',               'kind' => 'escorted' ),
			array( 'slug' => 'abercrombie-kent',                'title' => 'Abercrombie & Kent',              'kind' => 'escorted' ),
			array( 'slug' => 'national-geographic-expeditions', 'title' => 'National Geographic Expeditions', 'kind' => 'escorted', 'cruiseoomph_line' => true ),
			array( 'slug' => 'classic-vacations',               'title' => 'Classic Vacations',               'kind' => 'fit', 'ways' => array( 'custom', 'resorts' ) ),
			array( 'slug' => 'avanti-destinations',             'title' => 'Avanti Destinations',             'kind' => 'fit', 'ways' => array( 'custom' ) ),
		);
	}

	/**
	 * Seed one set. Returns one row per record: slug, title, action, id.
	 *
	 * @param string $what    'destinations' or 'operators'.
	 * @param bool   $dry_run Report without writing.
	 * @return array<int,array{slug:string,title:string,action:string,id:int}>
	 */
	public static function run( string $what, bool $dry_run = false ): array {
		switch ( $what ) {
			case 'destinations':
				$records   = self::destinations();
				$post_type = CPT_Destination::POST_TYPE;
				break;
			case 'operators':
				$records   = self::operators();
				$post_type = CPT_Operator::POST_TYPE;
				break;
			default:
				throw new \InvalidArgumentException( 'Unknown seed set: ' . $what );
		}

		$rows  = array();
		$order = 0;
		foreach ( $records as $record ) {
			++$order;
			$existing = get_page_by_path( $record['slug'], OBJECT, $post_type );
			if ( $existing instanceof \WP_Post ) {
				$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => 'exists (' . $existing->post_status . ')', 'id' => (int) $existing->ID );
				continue;
			}
			if ( $dry_run ) {
				$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => 'would create', 'id' => 0 );
				continue;
			}

			$id = 'destinations' === $what
				? self::create_destination( $record, $order )
				: self::create_operator( $record, $order );

			$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => $id ? 'created (draft)' : 'failed', 'id' => $id );
		}

		return $rows;
	}

	/** @param array<string,mixed> $record */
	private static function create_destination( array $record, int $order ): int {
		$id = self::insert( CPT_Destination::POST_TYPE, $record, $order );
		if ( ! $id ) {
			return 0;
		}
		self::field( $id, 'variant', 'field_oomph_dest_variant', $record['variant'] );
		self::field( $id, 'cruiseoomph_region', 'field_oomph_dest_cruiseoomph_region', (string) ( $record['cruiseoomph_region'] ?? '' ) );
		if ( ! empty( $record['headline'] ) ) {
			self::field( $id, 'headline', 'field_oomph_dest_headline', (string) $record['headline'] );
		}
		// wp_insert_post already fired save_post, which made the taxonomy term.
		return $id;
	}

	/** @param array<string,mixed> $record */
	private static function create_operator( array $record, int $order ): int {
		$id = self::insert( CPT_Operator::POST_TYPE, $record, $order );
		if ( ! $id ) {
			return 0;
		}
		self::field( $id, 'kind', 'field_oomph_op_kind', $record['kind'] );
		self::field( $id, 'cruiseoomph_line', 'field_oomph_op_cruiseoomph_line', empty( $record['cruiseoomph_line'] ) ? '0' : '1' );
		if ( 'fit' === $record['kind'] ) {
			self::field( $id, 'ways', 'field_oomph_op_ways', (array) ( $record['ways'] ?? array( 'custom' ) ) );
		}
		return $id;
	}

	/** @param array<string,mixed> $record */
	private static function insert( string $post_type, array $record, int $order ): int {
		$id = wp_insert_post(
			array(
				'post_type'   => $post_type,
				'post_status' => 'draft',
				'post_title'  => $record['title'],
				'post_name'   => $record['slug'],
				'post_author' => self::author(),
				'menu_order'  => $order,
			),
			true
		);
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/**
	 * Write a field the way the fields plugin does: value under the name,
	 * field key under the underscored name.
	 *
	 * @param mixed $value
	 */
	private static function field( int $post_id, string $name, string $key, $value ): void {
		update_post_meta( $post_id, $name, $value );
		update_post_meta( $post_id, '_' . $name, $key );
	}

	/** The current user under WP-CLI, else the first administrator, else 0. */
	private static function author(): int {
		$current = get_current_user_id();
		if ( $current ) {
			return $current;
		}
		$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ) );
		return $admins ? (int) $admins[0] : 0;
	}
}
