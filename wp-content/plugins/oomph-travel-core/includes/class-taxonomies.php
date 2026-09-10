<?php
/**
 * Taxonomies.
 *
 * Destinations (oomph_place) — one term per Destination record, kept in step
 * by CPT_Destination::sync_term(). Attached to tours, operators, inquiries
 * and journal posts so each can say which destination it belongs to. No
 * public archive: the destination page itself is the landing page.
 * Hierarchical only so the admin shows checkboxes for a fixed set rather
 * than a free-text tag box.
 *
 * Region and Trip Style are the pre-rebuild taxonomies on Destination and
 * Itinerary records. Left as they were.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomies {

	/** The destination taxonomy (plan §8.1). Named oomph_place so it cannot be confused with the oomph_destination post type. */
	public const DESTINATION = 'oomph_place';

	public const REGION     = 'oomph_region';
	public const TRIP_STYLE = 'oomph_trip_style';

	public static function register(): void {
		register_taxonomy(
			self::DESTINATION,
			array(
				CPT_Tour::POST_TYPE,
				CPT_Operator::POST_TYPE,
				CPT_Inquiry::POST_TYPE,
				'post',
			),
			array(
				'labels' => array(
					'name'              => __( 'Destinations', 'oomph-travel-core' ),
					'singular_name'     => __( 'Destination', 'oomph-travel-core' ),
					'all_items'         => __( 'All destinations', 'oomph-travel-core' ),
					'search_items'      => __( 'Search destinations', 'oomph-travel-core' ),
					'not_found'         => __( 'No destinations yet. Add a Destination record and its term appears here.', 'oomph-travel-core' ),
				),
				'description'       => __( 'One per Destination record. Managed from the destination, not here.', 'oomph-travel-core' ),
				'hierarchical'      => true,
				'public'            => false,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_nav_menus' => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'show_tagcloud'     => false,
				'rewrite'           => false,
				'query_var'         => false,
				'capabilities'      => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'do_not_allow',
					'delete_terms' => 'do_not_allow',
					'assign_terms' => 'edit_posts',
				),
			)
		);

		$legacy_types = array(
			CPT_Destination::POST_TYPE,
			CPT_Itinerary::POST_TYPE,
		);

		register_taxonomy(
			self::REGION,
			$legacy_types,
			array(
				'labels' => array(
					'name'          => __( 'Regions', 'oomph-travel-core' ),
					'singular_name' => __( 'Region', 'oomph-travel-core' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array(
					'slug'       => 'regions',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			self::TRIP_STYLE,
			$legacy_types,
			array(
				'labels' => array(
					'name'          => __( 'Trip Styles', 'oomph-travel-core' ),
					'singular_name' => __( 'Trip Style', 'oomph-travel-core' ),
				),
				'hierarchical' => false,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array(
					'slug'       => 'trip-styles',
					'with_front' => false,
				),
			)
		);
	}
}
