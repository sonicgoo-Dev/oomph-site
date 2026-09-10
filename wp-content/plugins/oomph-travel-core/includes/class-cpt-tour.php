<?php
/**
 * Tour CPT — one record per operator trip Eric sells (plan §6.7, §8.1).
 *
 * Slug: oomph_tour · public rewrite: /tours/[slug]/ (plan §4.2). No archive:
 * the tour grid lives on the escorted tours page.
 *
 * A small hand-entered set, three per operator, not a catalogue (D05, D34).
 * No departures, no availability status (D35). From-price is the operator's
 * standard rate only (D36). The featured image is the hero and the card
 * photo. Operator, nights, prices and the itinerary are in
 * acf-json/group_oomph_tour.json; destinations are the taxonomy.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT_Tour {

	public const POST_TYPE = 'oomph_tour';

	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'               => __( 'Tours', 'oomph-travel-core' ),
					'singular_name'      => __( 'Tour', 'oomph-travel-core' ),
					'add_new_item'       => __( 'Add New Tour', 'oomph-travel-core' ),
					'edit_item'          => __( 'Edit Tour', 'oomph-travel-core' ),
					'all_items'          => __( 'All Tours', 'oomph-travel-core' ),
					'featured_image'     => __( 'Hero photo', 'oomph-travel-core' ),
					'set_featured_image' => __( 'Set hero photo', 'oomph-travel-core' ),
				),
				'description'         => __( 'Escorted tours entered by hand. Dates and availability confirmed on request.', 'oomph-travel-core' ),
				'public'              => true,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'menu_position'       => 24,
				'menu_icon'           => 'dashicons-palmtree',
				'supports'            => array( 'title', 'thumbnail', 'revisions' ),
				'rewrite'             => array(
					'slug'       => 'tours',
					'with_front' => false,
				),
				'capability_type'     => 'post',
			)
		);
	}

	/** The operator post ID for a tour, or 0. */
	public static function operator_id( int $post_id ): int {
		return (int) get_post_meta( $post_id, 'operator', true );
	}

	/** The from-price in whole dollars, or null when unpriced. */
	public static function from_price( int $post_id ): ?int {
		$raw = get_post_meta( $post_id, 'from_price', true );
		if ( '' === $raw || null === $raw || ! is_numeric( $raw ) ) {
			return null;
		}
		return (int) $raw;
	}
}
