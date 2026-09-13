<?php
/**
 * Itinerary CPT — published sample/skeleton itineraries.
 *
 * Slug: oomph_itinerary · public rewrite: /itineraries/[slug]/
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT_Itinerary {

	public const POST_TYPE = 'oomph_itinerary';

	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'          => __( 'Itineraries', 'oomph-travel-core' ),
					'singular_name' => __( 'Itinerary', 'oomph-travel-core' ),
				),
				// Cruise-era records (Stage 12): off the public site, kept in
				// the admin. Destination pages replaced them (plan §6.3); the
				// /itineraries/ archive was an empty page in the sitemap.
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'menu_position'       => 22,
				'menu_icon'           => 'dashicons-list-view',
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
				'rewrite'             => false,
				'capability_type'     => 'post',
			)
		);
	}
}
