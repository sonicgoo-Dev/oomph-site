<?php
/**
 * Admin list columns for the rebuild's record types (plan §8.5 P1).
 *
 * Tours: operator · nights · from price · destinations. Operators: kind ·
 * order. Inquiries: trip type · contact · destinations · assigned to.
 * "Next departure" from the plan's P1 line is deliberately absent: tours
 * carry no departures (D35).
 *
 * Reads plain post meta, so the columns work whether or not the fields
 * plugin is active.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin_Columns {

	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_filter( 'manage_' . CPT_Tour::POST_TYPE . '_posts_columns', array( self::class, 'tour_columns' ) );
		add_action( 'manage_' . CPT_Tour::POST_TYPE . '_posts_custom_column', array( self::class, 'tour_column' ), 10, 2 );
		add_filter( 'manage_edit-' . CPT_Tour::POST_TYPE . '_sortable_columns', array( self::class, 'tour_sortable' ) );

		add_filter( 'manage_' . CPT_Operator::POST_TYPE . '_posts_columns', array( self::class, 'operator_columns' ) );
		add_action( 'manage_' . CPT_Operator::POST_TYPE . '_posts_custom_column', array( self::class, 'operator_column' ), 10, 2 );
		add_filter( 'manage_edit-' . CPT_Operator::POST_TYPE . '_sortable_columns', array( self::class, 'operator_sortable' ) );

		add_filter( 'manage_' . CPT_Inquiry::POST_TYPE . '_posts_columns', array( self::class, 'inquiry_columns' ) );
		add_action( 'manage_' . CPT_Inquiry::POST_TYPE . '_posts_custom_column', array( self::class, 'inquiry_column' ), 10, 2 );

		add_action( 'pre_get_posts', array( self::class, 'sort_by_meta' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Tours                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public static function tour_columns( array $columns ): array {
		$tax = 'taxonomy-' . Taxonomies::DESTINATION;
		return array(
			'cb'         => $columns['cb'] ?? '<input type="checkbox" />',
			'title'      => $columns['title'] ?? __( 'Title', 'oomph-travel-core' ),
			'operator'   => __( 'Operator', 'oomph-travel-core' ),
			'nights'     => __( 'Nights', 'oomph-travel-core' ),
			'from_price' => __( 'From price', 'oomph-travel-core' ),
			$tax         => $columns[ $tax ] ?? __( 'Destinations', 'oomph-travel-core' ),
			'featured'   => __( 'Featured', 'oomph-travel-core' ),
			'date'       => $columns['date'] ?? __( 'Date', 'oomph-travel-core' ),
		);
	}

	public static function tour_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'operator':
				$operator = CPT_Tour::operator_id( $post_id );
				if ( ! $operator ) {
					echo '<span aria-hidden="true">—</span>';
					return;
				}
				$link = get_edit_post_link( $operator );
				$name = get_the_title( $operator );
				if ( $link ) {
					printf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $name ) );
				} else {
					echo esc_html( $name );
				}
				return;

			case 'nights':
				$nights = get_post_meta( $post_id, 'nights', true );
				echo '' === $nights ? '<span aria-hidden="true">—</span>' : esc_html( (string) (int) $nights );
				return;

			case 'from_price':
				$price = CPT_Tour::from_price( $post_id );
				if ( null === $price ) {
					echo '<span class="description">' . esc_html__( 'unpriced', 'oomph-travel-core' ) . '</span>';
					return;
				}
				echo esc_html( '$' . number_format_i18n( $price ) );
				return;

			case 'featured':
				echo '1' === (string) get_post_meta( $post_id, 'featured', true )
					? '<span class="dashicons dashicons-star-filled" aria-label="' . esc_attr__( 'Featured', 'oomph-travel-core' ) . '"></span>'
					: '';
				return;
		}
	}

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public static function tour_sortable( array $columns ): array {
		$columns['nights']     = 'nights';
		$columns['from_price'] = 'from_price';
		return $columns;
	}

	/* ------------------------------------------------------------------ */
	/* Operators                                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public static function operator_columns( array $columns ): array {
		$tax = 'taxonomy-' . Taxonomies::DESTINATION;
		return array(
			'cb'    => $columns['cb'] ?? '<input type="checkbox" />',
			'title' => $columns['title'] ?? __( 'Title', 'oomph-travel-core' ),
			'kind'  => __( 'Kind', 'oomph-travel-core' ),
			'order' => __( 'Order', 'oomph-travel-core' ),
			$tax    => $columns[ $tax ] ?? __( 'Destinations', 'oomph-travel-core' ),
			'date'  => $columns['date'] ?? __( 'Date', 'oomph-travel-core' ),
		);
	}

	public static function operator_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'kind':
				$kind = CPT_Operator::kind( $post_id );
				echo esc_html( CPT_Operator::KINDS[ $kind ] );
				return;

			case 'order':
				$post = get_post( $post_id );
				echo esc_html( (string) ( $post ? $post->menu_order : 0 ) );
				return;
		}
	}

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public static function operator_sortable( array $columns ): array {
		$columns['order'] = 'menu_order';
		return $columns;
	}

	/* ------------------------------------------------------------------ */
	/* Inquiries                                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public static function inquiry_columns( array $columns ): array {
		$tax = 'taxonomy-' . Taxonomies::DESTINATION;
		return array(
			'cb'          => $columns['cb'] ?? '<input type="checkbox" />',
			'title'       => $columns['title'] ?? __( 'Title', 'oomph-travel-core' ),
			'trip_type'   => __( 'Trip type', 'oomph-travel-core' ),
			'contact'     => __( 'Contact', 'oomph-travel-core' ),
			$tax          => $columns[ $tax ] ?? __( 'Destinations', 'oomph-travel-core' ),
			'assigned_to' => __( 'Assigned to', 'oomph-travel-core' ),
			'date'        => $columns['date'] ?? __( 'Date', 'oomph-travel-core' ),
		);
	}

	public static function inquiry_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'trip_type':
				$type = (string) get_post_meta( $post_id, 'trip_type', true );
				echo '' === $type ? '<span aria-hidden="true">—</span>' : esc_html( CPT_Inquiry::trip_type_label( $type ) );
				return;

			case 'contact':
				$name   = (string) get_post_meta( $post_id, 'name', true );
				$value  = (string) get_post_meta( $post_id, 'contact_value', true );
				$method = (string) get_post_meta( $post_id, 'contact_method', true );
				$parts  = array_filter( array( $name, $value, $method ? '(' . $method . ')' : '' ) );
				echo '' === implode( '', $parts ) ? '<span aria-hidden="true">—</span>' : esc_html( implode( ' · ', $parts ) );
				return;

			case 'assigned_to':
				$user_id = (int) get_post_meta( $post_id, 'assigned_to', true );
				$user    = $user_id ? get_userdata( $user_id ) : false;
				echo $user ? esc_html( $user->display_name ) : '<span class="description">' . esc_html__( 'unassigned', 'oomph-travel-core' ) . '</span>';
				return;
		}
	}

	/* ------------------------------------------------------------------ */
	/* Sorting                                                             */
	/* ------------------------------------------------------------------ */

	public static function sort_by_meta( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || CPT_Tour::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}
		$orderby = (string) $query->get( 'orderby' );
		if ( ! in_array( $orderby, array( 'nights', 'from_price' ), true ) ) {
			return;
		}
		$query->set( 'meta_key', $orderby );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
