<?php
/**
 * Operator CPT — one record per vendor (plan §6.6, §8.1; D25).
 *
 * Slug: oomph_operator · public rewrite: /escorted-tours/[slug]/ (plan §4.2).
 * No archive: the escorted tours index is a page.
 *
 * Kind is a field: escorted tour operator or FIT supplier. Display order is
 * core menu_order (the Order box). Destinations supported is the Destinations
 * taxonomy. Everything else is in acf-json/group_oomph_operator.json.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT_Operator {

	public const POST_TYPE = 'oomph_operator';

	/** Keys of the `kind` field. */
	public const KINDS = array(
		'escorted' => 'Escorted tour operator',
		'fit'      => 'FIT supplier',
	);

	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'          => __( 'Operators', 'oomph-travel-core' ),
					'singular_name' => __( 'Operator', 'oomph-travel-core' ),
					'add_new_item'  => __( 'Add New Operator', 'oomph-travel-core' ),
					'edit_item'     => __( 'Edit Operator', 'oomph-travel-core' ),
					'all_items'     => __( 'All Operators', 'oomph-travel-core' ),
				),
				'description'         => __( 'Escorted tour operators and FIT suppliers, with fit notes in my words.', 'oomph-travel-core' ),
				'public'              => true,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'menu_position'       => 23,
				'menu_icon'           => 'dashicons-groups',
				'supports'            => array( 'title', 'revisions', 'page-attributes' ),
				'rewrite'             => array(
					'slug'       => 'escorted-tours',
					'with_front' => false,
				),
				'capability_type'     => 'post',
			)
		);
	}

	/** The `kind` of an operator, defaulting to escorted. */
	public static function kind( int $post_id ): string {
		$kind = (string) get_post_meta( $post_id, 'kind', true );
		return isset( self::KINDS[ $kind ] ) ? $kind : 'escorted';
	}
}
