<?php
/**
 * Tour import: one JSON record in, one draft tour out.
 *
 * The record shape is the one the seeder uses (content/tours/README.md):
 * slug, title, operator (operator slug), destination (destination slug),
 * nights, start_city, end_city, months (1–12), from_price, price_note,
 * blurb, group_size, pace, inclusions (one per line), brochure_link,
 * erics_note (HTML), featured, itinerary (rows of day/title/overnight/text).
 *
 * The importer never publishes. A new tour is a draft; an existing one keeps
 * whatever status Eric gave it. The hero photo (featured image) and the
 * gallery are set in the admin, so nothing here touches media.
 *
 * Used by `wp oomph import-tour` (class-cli.php), the "Add a tour" Actions
 * button (.github/workflows/import-tour.yml) and the seeder (class-seed.php).
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Tour_Import {

	/** Scalar text fields: record key (= field name) → field key. */
	private const TEXT = array(
		'blurb'         => 'field_oomph_tour_blurb',
		'nights'        => 'field_oomph_tour_nights',
		'start_city'    => 'field_oomph_tour_start_city',
		'end_city'      => 'field_oomph_tour_end_city',
		'from_price'    => 'field_oomph_tour_from_price',
		'price_note'    => 'field_oomph_tour_price_note',
		'group_size'    => 'field_oomph_tour_group_size',
		'pace'          => 'field_oomph_tour_pace',
		'inclusions'    => 'field_oomph_tour_inclusions',
		'brochure_link' => 'field_oomph_tour_brochure',
		'erics_note'    => 'field_oomph_tour_erics_note',
	);

	/** Itinerary sub-field name → field key. */
	private const ITINERARY = array(
		'day'       => 'field_oomph_tour_itin_day',
		'title'     => 'field_oomph_tour_itin_title',
		'overnight' => 'field_oomph_tour_itin_overnight',
		'text'      => 'field_oomph_tour_itin_text',
	);

	/** Fields whose prose is checked against the No List (docs/voice-guide.md). */
	private const PROSE = array( 'title', 'blurb', 'price_note', 'group_size', 'pace', 'inclusions', 'erics_note' );

	/**
	 * The No List, as the content guard (tests/e2e/content-guard.spec.ts)
	 * spells it. A hyphen or a space between words matches either.
	 */
	private const NO_LIST = array(
		'bespoke', 'wanderlust', 'magical', 'breathtaking', 'curated', 'jaw-dropping',
		'paradise', 'bucket list', 'hidden gem', 'iconic', 'stunning', 'ultimate',
		'epic', 'unforgettable', 'escape', 'getaway', 'luxe', '5-star', 'world-class',
		'indulge', 'pampered', 'transformative', 'white-glove', 'foodie', 'vibes',
		'once in a lifetime', 'dream destination', 'bestie', 'slay', 'obsessed',
		'hubby', 'adventure of a lifetime', 'pop of color', 'main character energy',
		'the best',
	);

	/**
	 * Decode a JSON file (or string) into a record. Throws on bad input so the
	 * caller can print one clear message.
	 *
	 * @return array<string,mixed>
	 */
	public static function decode( string $json ): array {
		$record = json_decode( $json, true );
		if ( ! is_array( $record ) ) {
			throw new \InvalidArgumentException( 'Not a JSON object: ' . json_last_error_msg() );
		}
		return $record;
	}

	/**
	 * Everything wrong with a record, as plain sentences. Empty means it can
	 * be imported. The checks match what the admin form and the content
	 * guard would complain about, so a tour that imports cleanly is one Eric
	 * only has to photograph, price-check and publish.
	 *
	 * @param array<string,mixed> $record
	 * @return string[]
	 */
	public static function validate( array $record ): array {
		$problems = array();

		$slug = (string) ( $record['slug'] ?? '' );
		if ( '' === $slug ) {
			$problems[] = 'slug is missing.';
		} elseif ( $slug !== sanitize_title( $slug ) ) {
			$problems[] = sprintf( 'slug "%s" is not a clean slug (lower-case letters, digits and hyphens); "%s" would be.', $slug, sanitize_title( $slug ) );
		}
		if ( '' === trim( (string) ( $record['title'] ?? '' ) ) ) {
			$problems[] = 'title is missing.';
		}

		$operator = (string) ( $record['operator'] ?? '' );
		if ( '' === $operator ) {
			$problems[] = 'operator is missing (an operator slug, e.g. "globus").';
		} elseif ( ! self::operator_id( $operator ) ) {
			$problems[] = sprintf( 'operator "%s" is not an operator on this site. Known: %s.', $operator, implode( ', ', self::slugs( CPT_Operator::POST_TYPE ) ) );
		}

		$destination = (string) ( $record['destination'] ?? '' );
		if ( '' === $destination ) {
			$problems[] = 'destination is missing (a destination slug, e.g. "italy").';
		} elseif ( ! self::destination_term( $destination ) ) {
			$problems[] = sprintf( 'destination "%s" is not a destination on this site. Known: %s.', $destination, implode( ', ', self::slugs( CPT_Destination::POST_TYPE ) ) );
		}

		$nights = $record['nights'] ?? null;
		if ( null === $nights || '' === $nights ) {
			$problems[] = 'nights is missing.';
		} elseif ( ! is_numeric( $nights ) || (int) $nights < 1 || (int) $nights != $nights ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- numeric string vs int.
			$problems[] = sprintf( 'nights must be a whole number of nights, not "%s".', (string) $nights );
		}

		if ( array_key_exists( 'months', $record ) ) {
			if ( ! is_array( $record['months'] ) ) {
				$problems[] = 'months must be a list of month numbers, 1 to 12.';
			} else {
				foreach ( $record['months'] as $m ) {
					if ( ! is_numeric( $m ) || (int) $m < 1 || (int) $m > 12 ) {
						$problems[] = sprintf( 'months: "%s" is not a month number (1 to 12).', (string) $m );
					}
				}
			}
		}

		if ( isset( $record['from_price'] ) && '' !== $record['from_price'] ) {
			$price = $record['from_price'];
			if ( ! is_numeric( $price ) || (int) $price != $price || (int) $price < 0 ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- numeric string vs int.
				$problems[] = sprintf( 'from_price must be whole US dollars with no symbol or commas, not "%s".', (string) $price );
			}
		}

		if ( isset( $record['brochure_link'] ) && '' !== $record['brochure_link'] && ! wp_http_validate_url( (string) $record['brochure_link'] ) ) {
			$problems[] = sprintf( 'brochure_link "%s" is not a full URL.', (string) $record['brochure_link'] );
		}

		if ( array_key_exists( 'itinerary', $record ) ) {
			if ( ! is_array( $record['itinerary'] ) ) {
				$problems[] = 'itinerary must be a list of rows.';
			} else {
				foreach ( array_values( $record['itinerary'] ) as $i => $row ) {
					$n = $i + 1;
					if ( ! is_array( $row ) ) {
						$problems[] = sprintf( 'itinerary row %d is not an object.', $n );
						continue;
					}
					if ( ! isset( $row['day'] ) || ! is_numeric( $row['day'] ) ) {
						$problems[] = sprintf( 'itinerary row %d needs a day number.', $n );
					}
					if ( '' === trim( (string) ( $row['title'] ?? '' ) ) ) {
						$problems[] = sprintf( 'itinerary row %d needs a title.', $n );
					}
					foreach ( array( 'title', 'overnight', 'text' ) as $sub ) {
						$problems = array_merge( $problems, self::prose_problems( sprintf( 'itinerary row %d %s', $n, $sub ), (string) ( $row[ $sub ] ?? '' ) ) );
					}
				}
			}
		}

		foreach ( self::PROSE as $name ) {
			$problems = array_merge( $problems, self::prose_problems( $name, (string) ( $record[ $name ] ?? '' ) ) );
		}

		foreach ( array_keys( $record ) as $key ) {
			if ( ! in_array( (string) $key, self::known_keys(), true ) ) {
				$problems[] = sprintf( '"%s" is not a tour field and would be ignored. Known: %s.', (string) $key, implode( ', ', self::known_keys() ) );
			}
		}

		return $problems;
	}

	/**
	 * Create or update one tour from a record. Validate first; this trusts
	 * its input.
	 *
	 * @param array<string,mixed> $record
	 * @param bool                $update  Rewrite the fields of a tour whose slug already exists.
	 * @param bool                $dry_run Report without writing.
	 * @return array{slug:string,title:string,action:string,id:int,status:string,edit:string}
	 */
	public static function import( array $record, bool $update = false, bool $dry_run = false ): array {
		$slug     = (string) $record['slug'];
		$title    = trim( (string) $record['title'] );
		$existing = get_page_by_path( $slug, OBJECT, CPT_Tour::POST_TYPE );

		if ( $existing instanceof \WP_Post ) {
			$id = (int) $existing->ID;
			if ( ! $update ) {
				return self::row( $slug, $title, 'exists, left alone (add --update to rewrite its fields)', $id );
			}
			if ( $dry_run ) {
				return self::row( $slug, $title, 'would update', $id );
			}
			if ( $title !== $existing->post_title ) {
				wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
			}
			self::write_fields( $id, $record );
			return self::row( $slug, $title, 'updated', $id );
		}

		if ( $dry_run ) {
			return self::row( $slug, $title, 'would create (draft)', 0 );
		}

		$id = wp_insert_post(
			array(
				'post_type'   => CPT_Tour::POST_TYPE,
				'post_status' => 'draft',
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_author' => self::author(),
				'menu_order'  => self::next_order(),
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return self::row( $slug, $title, 'failed: ' . ( is_wp_error( $id ) ? $id->get_error_message() : 'wp_insert_post returned 0' ), 0 );
		}
		self::write_fields( (int) $id, $record );
		return self::row( $slug, $title, 'created (draft)', (int) $id );
	}

	/**
	 * Write a record's fields onto a tour the way the fields plugin does, so
	 * the admin form shows them straight away. Only keys present in the
	 * record are written; an update leaves the others as they are. The
	 * operator and destination are found by slug whatever their status.
	 *
	 * @param array<string,mixed> $record
	 */
	public static function write_fields( int $id, array $record ): void {
		if ( array_key_exists( 'operator', $record ) ) {
			$operator = self::operator_id( (string) $record['operator'] );
			Fields::write( $id, 'operator', 'field_oomph_tour_operator', $operator ? (string) $operator : '' );
		}
		foreach ( self::TEXT as $name => $key ) {
			if ( array_key_exists( $name, $record ) ) {
				$value = $record[ $name ];
				Fields::write( $id, $name, $key, is_scalar( $value ) || null === $value ? (string) $value : '' );
			}
		}
		if ( array_key_exists( 'months', $record ) ) {
			$months = array_map( 'intval', (array) $record['months'] );
			sort( $months );
			Fields::write( $id, 'months', 'field_oomph_tour_months', array_map( 'strval', array_values( array_unique( $months ) ) ) );
		}
		if ( array_key_exists( 'featured', $record ) ) {
			Fields::write( $id, 'featured', 'field_oomph_tour_featured', empty( $record['featured'] ) ? '0' : '1' );
		}
		if ( array_key_exists( 'itinerary', $record ) ) {
			Fields::write_repeater( $id, 'itinerary', 'field_oomph_tour_itinerary', (array) $record['itinerary'], self::ITINERARY );
		}
		if ( array_key_exists( 'destination', $record ) ) {
			$term_id = self::destination_term( (string) $record['destination'] );
			if ( $term_id ) {
				wp_set_object_terms( $id, array( $term_id ), Taxonomies::DESTINATION );
			}
		}
	}

	/** The operator's post ID for a slug, 0 when there is none. */
	public static function operator_id( string $slug ): int {
		$post = get_page_by_path( $slug, OBJECT, CPT_Operator::POST_TYPE );
		return $post instanceof \WP_Post ? (int) $post->ID : 0;
	}

	/** The Destinations term for a destination slug, 0 when there is none. */
	public static function destination_term( string $slug ): int {
		$post = get_page_by_path( $slug, OBJECT, CPT_Destination::POST_TYPE );
		return $post instanceof \WP_Post ? CPT_Destination::term_id( (int) $post->ID ) : 0;
	}

	/** @return string[] */
	public static function known_keys(): array {
		return array_merge( array( 'slug', 'title', 'operator', 'destination', 'months', 'featured', 'itinerary' ), array_keys( self::TEXT ) );
	}

	/**
	 * No List words and placeholder markers in one string, as sentences.
	 *
	 * @return string[]
	 */
	private static function prose_problems( string $where, string $text ): array {
		if ( '' === trim( $text ) ) {
			return array();
		}
		$plain    = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5 );
		$problems = array();

		$pattern = '/\b(' . implode(
			'|',
			array_map( static fn( string $w ): string => preg_replace( '/[-\s]/', '[\s-]', preg_quote( $w, '/' ) ), self::NO_LIST )
		) . ')\b/iu';
		if ( preg_match_all( $pattern, $plain, $m ) ) {
			$problems[] = sprintf( '%s uses a No List word: %s.', $where, implode( ', ', array_unique( array_map( 'strtolower', $m[1] ) ) ) );
		}
		if ( preg_match_all( '/\[[A-Z][^\]]{2,}\]|\$X,XXX/', $plain, $m ) ) {
			$problems[] = sprintf( '%s has a placeholder: %s.', $where, implode( ' ', array_unique( $m[0] ) ) );
		}
		return $problems;
	}

	/** @return string[] Slugs of every record of a type, any status. */
	private static function slugs( string $post_type ): array {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		);
		return array_values( array_filter( array_map( static fn( $p ): string => (string) get_post_field( 'post_name', (int) $p ), $posts ) ) );
	}

	/** One after the highest menu_order, so a new tour lists last. */
	private static function next_order(): int {
		global $wpdb;
		$max = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_type = %s", CPT_Tour::POST_TYPE ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (int) $max + 1;
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

	/** @return array{slug:string,title:string,action:string,id:int,status:string,edit:string} */
	private static function row( string $slug, string $title, string $action, int $id ): array {
		return array(
			'slug'   => $slug,
			'title'  => $title,
			'action' => $action,
			'id'     => $id,
			'status' => $id ? (string) get_post_status( $id ) : '',
			'edit'   => $id ? admin_url( 'post.php?post=' . $id . '&action=edit' ) : '',
		);
	}
}
