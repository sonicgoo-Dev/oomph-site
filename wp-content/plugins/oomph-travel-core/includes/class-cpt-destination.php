<?php
/**
 * Destination CPT — one record per destination page (plan §6.3, §8.1).
 *
 * Slug: oomph_destination · public rewrite: /destinations/[slug]/ · archive
 * at /destinations/ (the "All destinations" index, plan §6.2).
 *
 * Fields live in acf-json/group_oomph_destination.json. The hero photograph
 * is the featured image. The page variant (custom / resort / guided) is a
 * field, not a template (readiness doc; D33).
 *
 * Every destination also owns a term in the Destinations taxonomy
 * (Taxonomies::DESTINATION) so tours, operators, inquiries and journal posts
 * can be tagged with it. The term is created and renamed from the post here —
 * Eric never edits terms directly.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT_Destination {

	public const POST_TYPE = 'oomph_destination';

	/** Post meta: the term_id of this destination's taxonomy term. */
	public const TERM_META = '_oomph_destination_term';

	/** Term meta: the post ID of the destination record behind a term. */
	public const POST_META_ON_TERM = 'oomph_destination_post';

	/** Page variants — the keys of the `variant` field (readiness doc). */
	public const VARIANTS = array(
		'custom' => 'Custom-first',
		'resort' => 'Resort-first',
		'guided' => 'Guided-first',
	);

	public static function init(): void {
		add_action( 'save_post_' . self::POST_TYPE, array( self::class, 'sync_term' ), 10, 2 );
		add_action( 'before_delete_post', array( self::class, 'delete_term' ), 10, 2 );
	}

	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'               => __( 'Destinations', 'oomph-travel-core' ),
					'singular_name'      => __( 'Destination', 'oomph-travel-core' ),
					'add_new_item'       => __( 'Add New Destination', 'oomph-travel-core' ),
					'edit_item'          => __( 'Edit Destination', 'oomph-travel-core' ),
					'all_items'          => __( 'All Destinations', 'oomph-travel-core' ),
					'featured_image'     => __( 'Hero photo', 'oomph-travel-core' ),
					'set_featured_image' => __( 'Set hero photo', 'oomph-travel-core' ),
				),
				'description'         => __( 'One record per destination page. The featured image is the hero.', 'oomph-travel-core' ),
				'public'              => true,
				'show_in_rest'        => true,
				'has_archive'         => true,
				'menu_position'       => 21,
				'menu_icon'           => 'dashicons-location',
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
				'rewrite'             => array(
					'slug'       => 'destinations',
					'with_front' => false,
				),
				'capability_type'     => 'post',
			)
		);
	}

	/**
	 * Keep a Destinations term in step with the destination post.
	 *
	 * Runs on every save except revisions, autosaves, auto-drafts and trash.
	 * Adopts an existing term with the same slug rather than creating a
	 * duplicate, so a term made by hand before the post existed is claimed.
	 */
	public static function sync_term( int $post_id, \WP_Post $post ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( in_array( $post->post_status, array( 'auto-draft', 'trash' ), true ) || '' === $post->post_name ) {
			return;
		}
		if ( ! taxonomy_exists( Taxonomies::DESTINATION ) ) {
			return;
		}

		$tax  = Taxonomies::DESTINATION;
		$name = wp_strip_all_tags( $post->post_title );
		if ( '' === $name ) {
			return;
		}

		$term = null;
		$known = (int) get_post_meta( $post_id, self::TERM_META, true );
		if ( $known ) {
			$term = get_term( $known, $tax );
		}
		if ( ! $term instanceof \WP_Term ) {
			$term = get_term_by( 'slug', $post->post_name, $tax );
		}

		if ( $term instanceof \WP_Term ) {
			if ( $term->name !== $name || $term->slug !== $post->post_name ) {
				wp_update_term( $term->term_id, $tax, array( 'name' => $name, 'slug' => $post->post_name ) );
			}
			$term_id = (int) $term->term_id;
		} else {
			$created = wp_insert_term( $name, $tax, array( 'slug' => $post->post_name ) );
			if ( is_wp_error( $created ) ) {
				return;
			}
			$term_id = (int) $created['term_id'];
		}

		update_term_meta( $term_id, self::POST_META_ON_TERM, $post_id );
		update_post_meta( $post_id, self::TERM_META, $term_id );
	}

	/**
	 * When a destination is deleted for good, its term goes too — but only if
	 * nothing is tagged with it, so tours never lose a destination silently.
	 */
	public static function delete_term( int $post_id, ?\WP_Post $post = null ): void {
		$post = $post ?: get_post( $post_id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return;
		}
		$term_id = (int) get_post_meta( $post_id, self::TERM_META, true );
		if ( ! $term_id ) {
			return;
		}
		$term = get_term( $term_id, Taxonomies::DESTINATION );
		if ( $term instanceof \WP_Term && 0 === (int) $term->count ) {
			wp_delete_term( $term_id, Taxonomies::DESTINATION );
		} elseif ( $term instanceof \WP_Term ) {
			delete_term_meta( $term_id, self::POST_META_ON_TERM );
		}
	}

	/** The taxonomy term for a destination post, or 0. */
	public static function term_id( int $post_id ): int {
		return (int) get_post_meta( $post_id, self::TERM_META, true );
	}

	/** The destination post behind a taxonomy term, or 0. */
	public static function post_id_for_term( int $term_id ): int {
		return (int) get_term_meta( $term_id, self::POST_META_ON_TERM, true );
	}
}
