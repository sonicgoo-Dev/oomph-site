<?php
/**
 * Inquiry CPT — a Start planning enquiry, stored privately (plan §6.15,
 * §8.1; D30).
 *
 * Slug: oomph_inquiry. Never public, never queryable, never in search or the
 * REST API. Nobody can add one from the admin — the form writes them (Stage
 * P7) and Eric reads, corrects and assigns them here. The answers are in
 * acf-json/group_oomph_inquiry.json; the destinations chosen are the
 * Destinations taxonomy; the timestamp is the post date.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT_Inquiry {

	public const POST_TYPE = 'oomph_inquiry';

	/** Keys of the `trip_type` field — the step 1 radio cards (plan §6.15). "A cruise" is never stored: it sends to CruiseOomph. */
	public const TRIP_TYPES = array(
		'custom'      => 'A custom journey',
		'escorted'    => 'An escorted tour',
		'resort'      => 'A resort or villa stay',
		'multigen'    => 'A multi-generational trip',
		'cruise-land' => 'A cruise plus land',
		'not-sure'    => 'Not sure yet',
	);

	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'          => __( 'Inquiries', 'oomph-travel-core' ),
					'singular_name' => __( 'Inquiry', 'oomph-travel-core' ),
					'edit_item'     => __( 'Inquiry', 'oomph-travel-core' ),
					'all_items'     => __( 'All Inquiries', 'oomph-travel-core' ),
					'search_items'  => __( 'Search Inquiries', 'oomph-travel-core' ),
					'not_found'     => __( 'No inquiries yet.', 'oomph-travel-core' ),
				),
				'description'         => __( 'Start planning enquiries. Private.', 'oomph-travel-core' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_position'       => 25,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
				'delete_with_user'    => false,
				'can_export'          => true,
			)
		);
	}

	/** Human label for a stored trip type, or the raw value. */
	public static function trip_type_label( string $key ): string {
		return self::TRIP_TYPES[ $key ] ?? $key;
	}
}
