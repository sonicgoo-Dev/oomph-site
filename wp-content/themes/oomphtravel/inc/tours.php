	// Rank Math falls back to the title when no description is set; a
	// description Eric types in its panel is anything else and wins.
	if ( ! is_post_type_archive( 'oomph_tour' ) || ( '' !== trim( $description ) && false === strpos( $description, 'Tours Archive' ) ) ) {
		return ;
	}<?php
/**
 * Escorted tours (plan §6.5 index, §6.6 operator page, §6.7 tour detail):
 * the data behind the three templates, the filters that live in the URL,
 * the hero sources, and the assets that load only on those pages.
 *
 * Fields are read through the plugin's Fields class so the pages render the
 * same with ACF Pro, with SCF, or with neither (wp-env in CI). Every
 * section hides itself when its field is empty; nothing prints a
 * placeholder. Tour pages carry no live data (D35) and only the operator's
 * standard from-price (D36); the availability caption is fixed copy (D34).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Field access                                                          */
/* ------------------------------------------------------------------ */

/** A scalar field, through the plugin when it is there. */
function oomphtravel_field( int $post_id, string $name ): string {
	if ( class_exists( '\OomphTravel\Core\Fields' ) ) {
		return \OomphTravel\Core\Fields::value( $post_id, $name );
	}
	$v = get_post_meta( $post_id, $name, true );
	return is_scalar( $v ) ? (string) $v : '';
}

/** A repeater's rows, through the plugin when it is there. */
function oomphtravel_field_rows( int $post_id, string $name, array $subs ): array {
	return class_exists( '\OomphTravel\Core\Fields' ) ? \OomphTravel\Core\Fields::repeater( $post_id, $name, $subs ) : array();
}

/** A checkbox field's choices, through the plugin when it is there. */
function oomphtravel_field_choices( int $post_id, string $name ): array {
	return class_exists( '\OomphTravel\Core\Fields' ) ? \OomphTravel\Core\Fields::choices( $post_id, $name ) : array();
}

/** A textarea "one per line" field as a list of trimmed lines. */
function oomphtravel_field_lines( int $post_id, string $name ): array {
	$lines = preg_split( '/\r\n|\r|\n/', oomphtravel_field( $post_id, $name ) ) ?: array();
	return array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
}

/* ------------------------------------------------------------------ */
/* Tours                                                                 */
/* ------------------------------------------------------------------ */

/**
 * The card array for one tour (oomphtravel_card_tour). The destination
 * shown is the one passed in, else the first term the tour is tagged with.
 *
 * @return array<string,mixed>
 */
function oomphtravel_tour_card( int $id, string $destination = '' ): array {
	$operator = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( $id ) : (int) get_post_meta( $id, 'operator', true );
	$price    = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::from_price( $id ) : null;
	$thumb    = (int) get_post_thumbnail_id( $id );

	if ( '' === $destination ) {
		$places      = get_the_terms( $id, 'oomph_place' );
		$destination = ( is_array( $places ) && $places ) ? (string) $places[0]->name : '';
	}

	return array(
		'id'          => $id,
		'operator_id' => $operator,
		'operator'    => $operator ? (string) get_the_title( $operator ) : '',
		'nights'      => (int) oomphtravel_field( $id, 'nights' ),
		'destination' => $destination,
		'title'       => (string) get_the_title( $id ),
		'blurb'       => oomphtravel_field( $id, 'blurb' ),
		'price'       => null === $price ? '' : '$' . number_format( $price ),
		'url'         => (string) get_permalink( $id ),
		'slug'        => (string) get_post_field( 'post_name', $id ),
		'image_id'    => $thumb,
		'image_alt'   => $thumb ? (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) : '',
	);
}

/**
 * Everything the tour template needs, in one array.
 *
 * @return array<string,mixed>
 */
function oomphtravel_tour_data( int $id ): array {
	$card     = oomphtravel_tour_card( $id );
	$operator = (int) $card['operator_id'];
	$price    = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::from_price( $id ) : null;
	$places   = get_the_terms( $id, 'oomph_place' );
	$places   = is_array( $places ) ? $places : array();

	$destinations = array();
	foreach ( $places as $term ) {
		$post_id = (int) get_term_meta( $term->term_id, 'oomph_destination_post', true );
		$url     = ( $post_id && 'publish' === get_post_status( $post_id ) ) ? (string) get_permalink( $post_id ) : '';
		$destinations[] = array( 'name' => (string) $term->name, 'url' => $url );
	}

	return $card + array(
		'operator_url'   => ( $operator && 'publish' === get_post_status( $operator ) ) ? (string) get_permalink( $operator ) : '',
		'operator_slug'  => $operator ? (string) get_post_field( 'post_name', $operator ) : '',
		'destinations'   => $destinations,
		'from_price'     => $price,
		'price_note'     => oomphtravel_field( $id, 'price_note' ),
		'start_city'     => oomphtravel_field( $id, 'start_city' ),
		'end_city'       => oomphtravel_field( $id, 'end_city' ),
		'group_size'     => oomphtravel_field( $id, 'group_size' ),
		'pace'           => oomphtravel_field( $id, 'pace' ),
		'inclusions'     => oomphtravel_field_lines( $id, 'inclusions' ),
		'itinerary'      => oomphtravel_field_rows( $id, 'itinerary', array( 'day', 'title', 'overnight', 'text' ) ),
		'erics_note'     => oomphtravel_field( $id, 'erics_note' ),
		'brochure_link'  => oomphtravel_field( $id, 'brochure_link' ),
		'months'         => array_map( 'intval', oomphtravel_field_choices( $id, 'months' ) ),
		'ask_url'        => add_query_arg( 'tour', $card['slug'], home_url( '/start-planning/' ) ),
	);
}

/**
 * Every published tour's ID, newest first, once per request.
 *
 * @return int[]
 */
function oomphtravel_tour_ids(): array {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids = array();
	if ( ! post_type_exists( 'oomph_tour' ) ) {
		return $ids;
	}
	$ids = array_map(
		'intval',
		get_posts(
			array(
				'post_type'        => 'oomph_tour',
				'post_status'      => 'publish',
				'numberposts'      => -1,
				'orderby'          => 'menu_order title',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'suppress_filters' => false,
			)
		)
	);
	return $ids;
}

/**
 * Published tours of one operator, as card arrays.
 *
 * @return array<int,array<string,mixed>>
 */
function oomphtravel_operator_tours( int $operator_id, int $limit = -1 ): array {
	$out = array();
	foreach ( oomphtravel_tour_ids() as $id ) {
		$op = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( $id ) : (int) get_post_meta( $id, 'operator', true );
		if ( $op !== $operator_id ) {
			continue;
		}
		$out[] = oomphtravel_tour_card( $id );
		if ( $limit > 0 && count( $out ) >= $limit ) {
			break;
		}
	}
	return $out;
}

/**
 * Related tours for a tour page: the same destination first, then the
 * same operator, never itself, three at most.
 *
 * @return array<int,array<string,mixed>>
 */
function oomphtravel_related_tours( int $id, int $limit = 3 ): array {
	$operator = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( $id ) : (int) get_post_meta( $id, 'operator', true );
	$places   = get_the_terms( $id, 'oomph_place' );
	$term_ids = is_array( $places ) ? wp_list_pluck( $places, 'term_id' ) : array();

	$same_place = array();
	$same_op    = array();
	foreach ( oomphtravel_tour_ids() as $other ) {
		if ( $other === $id ) {
			continue;
		}
		$other_places = get_the_terms( $other, 'oomph_place' );
		$other_terms  = is_array( $other_places ) ? wp_list_pluck( $other_places, 'term_id' ) : array();
		if ( $term_ids && array_intersect( $term_ids, $other_terms ) ) {
			$same_place[] = $other;
			continue;
		}
		$other_op = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( $other ) : (int) get_post_meta( $other, 'operator', true );
		if ( $operator && $other_op === $operator ) {
			$same_op[] = $other;
		}
	}

	$picked = array_slice( array_merge( $same_place, $same_op ), 0, $limit );
	return array_map( 'oomphtravel_tour_card', $picked );
}

/* ------------------------------------------------------------------ */
/* The index: filters in the URL                                         */
/* ------------------------------------------------------------------ */

/**
 * The length buckets on the index filter, by nights.
 *
 * @return array<string,array{label:string,min:int,max:int}>
 */
function oomphtravel_tour_lengths(): array {
	return array(
		'short'  => array( 'label' => __( 'Up to 7 nights', 'oomphtravel' ), 'min' => 0, 'max' => 7 ),
		'medium' => array( 'label' => __( '8 to 10 nights', 'oomphtravel' ), 'min' => 8, 'max' => 10 ),
		'long'   => array( 'label' => __( '11 to 14 nights', 'oomphtravel' ), 'min' => 11, 'max' => 14 ),
		'xlong'  => array( 'label' => __( '15 nights or more', 'oomphtravel' ), 'min' => 15, 'max' => 999 ),
	);
}

/** Month names, 1 to 12. */
function oomphtravel_month_names(): array {
	return array(
		1  => __( 'January', 'oomphtravel' ),
		2  => __( 'February', 'oomphtravel' ),
		3  => __( 'March', 'oomphtravel' ),
		4  => __( 'April', 'oomphtravel' ),
		5  => __( 'May', 'oomphtravel' ),
		6  => __( 'June', 'oomphtravel' ),
		7  => __( 'July', 'oomphtravel' ),
		8  => __( 'August', 'oomphtravel' ),
		9  => __( 'September', 'oomphtravel' ),
		10 => __( 'October', 'oomphtravel' ),
		11 => __( 'November', 'oomphtravel' ),
		12 => __( 'December', 'oomphtravel' ),
	);
}

/**
 * The filters in the current URL, validated: destination (term slug),
 * operator (post slug), month (1–12), length (bucket key). Anything not in
 * the options is dropped, so a hand-typed URL can only ever narrow.
 *
 * @return array{destination:string,operator:string,month:int,length:string}
 */
function oomphtravel_tours_filters(): array {
	static $filters = null;
	if ( null !== $filters ) {
		return $filters;
	}
	$options = oomphtravel_tours_filter_options();
	$get     = static function ( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter state, nothing is written.
		return isset( $_GET[ $key ] ) ? sanitize_key( wp_unslash( (string) $_GET[ $key ] ) ) : '';
	};

	$destination = $get( 'destination' );
	$operator    = $get( 'operator' );
	$month       = (int) $get( 'month' );
	$length      = $get( 'length' );

	$filters = array(
		'destination' => isset( $options['destinations'][ $destination ] ) ? $destination : '',
		'operator'    => isset( $options['operators'][ $operator ] ) ? $operator : '',
		'month'       => isset( $options['months'][ $month ] ) ? $month : 0,
		'length'      => isset( $options['lengths'][ $length ] ) ? $length : '',
	);
	return $filters;
}

/** True when any filter is set: the view is then noindex. */
function oomphtravel_tours_filtered(): bool {
	return (bool) array_filter( oomphtravel_tours_filters() );
}

/**
 * The options each filter offers, built from the published tours only, so
 * no choice leads to an empty grid on an unfiltered page.
 *
 * @return array{destinations:array<string,string>,operators:array<string,string>,months:array<int,string>,lengths:array<string,string>}
 */
function oomphtravel_tours_filter_options(): array {
	static $options = null;
	if ( null !== $options ) {
		return $options;
	}
	$destinations = array();
	$operators    = array();
	$months       = array();
	$lengths      = array();
	$names        = oomphtravel_month_names();
	$buckets      = oomphtravel_tour_lengths();

	foreach ( oomphtravel_tour_ids() as $id ) {
		$places = get_the_terms( $id, 'oomph_place' );
		if ( is_array( $places ) ) {
			foreach ( $places as $term ) {
				$destinations[ $term->slug ] = (string) $term->name;
			}
		}
		$op = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( $id ) : (int) get_post_meta( $id, 'operator', true );
		if ( $op && 'publish' === get_post_status( $op ) ) {
			$operators[ (string) get_post_field( 'post_name', $op ) ] = (string) get_the_title( $op );
		}
		foreach ( oomphtravel_field_choices( $id, 'months' ) as $m ) {
			if ( isset( $names[ (int) $m ] ) ) {
				$months[ (int) $m ] = $names[ (int) $m ];
			}
		}
		$nights = (int) oomphtravel_field( $id, 'nights' );
		foreach ( $buckets as $key => $bucket ) {
			if ( $nights >= $bucket['min'] && $nights <= $bucket['max'] ) {
				$lengths[ $key ] = $bucket['label'];
			}
		}
	}

	asort( $destinations );
	asort( $operators );
	ksort( $months );
	$ordered = array();
	foreach ( $buckets as $key => $bucket ) {
		if ( isset( $lengths[ $key ] ) ) {
			$ordered[ $key ] = $bucket['label'];
		}
	}
	$lengths = $ordered;

	$options = array(
		'destinations' => $destinations,
		'operators'    => $operators,
		'months'       => $months,
		'lengths'      => $lengths,
	);
	return $options;
}

/**
 * The tours the current filters leave, as card arrays.
 *
 * @return array<int,array<string,mixed>>
 */
function oomphtravel_tours_index_cards(): array {
	$f       = oomphtravel_tours_filters();
	$buckets = oomphtravel_tour_lengths();
	$out     = array();

	foreach ( oomphtravel_tour_ids() as $id ) {
		if ( '' !== $f['destination'] ) {
			$places = get_the_terms( $id, 'oomph_place' );
			$slugs  = is_array( $places ) ? wp_list_pluck( $places, 'slug' ) : array();
			if ( ! in_array( $f['destination'], $slugs, true ) ) {
				continue;
			}
		}
		if ( '' !== $f['operator'] ) {
			$op = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( $id ) : (int) get_post_meta( $id, 'operator', true );
			if ( ! $op || (string) get_post_field( 'post_name', $op ) !== $f['operator'] ) {
				continue;
			}
		}
		if ( $f['month'] && ! in_array( $f['month'], array_map( 'intval', oomphtravel_field_choices( $id, 'months' ) ), true ) ) {
			continue;
		}
		if ( '' !== $f['length'] ) {
			$nights = (int) oomphtravel_field( $id, 'nights' );
			$bucket = $buckets[ $f['length'] ];
			if ( $nights < $bucket['min'] || $nights > $bucket['max'] ) {
				continue;
			}
		}
		$out[] = oomphtravel_tour_card( $id );
	}
	return $out;
}

/**
 * The escorted operators for the index row (D25), published only, with
 * their tour counts. The NatGeo card carries the CruiseOomph line (D32).
 *
 * @return array<int,array<string,mixed>>
 */
function oomphtravel_escorted_operator_cards(): array {
	if ( ! post_type_exists( 'oomph_operator' ) ) {
		return array();
	}
	$posts = get_posts(
		array(
			'post_type'        => 'oomph_operator',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'menu_order title',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);
	$cards = array();
	foreach ( $posts as $post ) {
		$id = (int) $post->ID;
		if ( 'escorted' !== oomphtravel_operator_kind( $id ) ) {
			continue;
		}
		$card = array(
			'kind'        => 'escorted',
			'name'        => (string) get_the_title( $post ),
			'fit'         => oomphtravel_field( $id, 'fit_line' ),
			'url'         => (string) get_permalink( $post ),
			'tour_count'  => count( oomphtravel_operator_tours( $id ) ),
			'cruiseoomph' => '1' === oomphtravel_field( $id, 'cruiseoomph_line' ),
		);
		$logo = (int) oomphtravel_field( $id, 'logo' );
		if ( $logo ) {
			$card['logo_id'] = $logo;
		}
		$cards[] = $card;
	}
	return $cards;
}

/* ------------------------------------------------------------------ */
/* Operators                                                             */
/* ------------------------------------------------------------------ */

/** 'escorted' or 'fit'. */
function oomphtravel_operator_kind( int $id ): string {
	if ( class_exists( '\OomphTravel\Core\CPT_Operator' ) ) {
		return \OomphTravel\Core\CPT_Operator::kind( $id );
	}
	return 'fit' === get_post_meta( $id, 'kind', true ) ? 'fit' : 'escorted';
}

/**
 * Everything the operator template needs, in one array.
 *
 * @return array<string,mixed>
 */
function oomphtravel_operator_data( int $id ): array {
	$kind = oomphtravel_operator_kind( $id );
	$slug = (string) get_post_field( 'post_name', $id );
	$logo = (int) oomphtravel_field( $id, 'logo' );

	$ways     = oomphtravel_field_choices( $id, 'ways' );
	$way_list = array();
	if ( in_array( 'custom', $ways, true ) ) {
		$way_list[] = array( 'name' => __( 'Custom journeys', 'oomphtravel' ), 'url' => home_url( '/custom-journeys/' ) );
	}
	if ( in_array( 'resorts', $ways, true ) ) {
		$way_list[] = array( 'name' => __( 'Resorts & villas', 'oomphtravel' ), 'url' => home_url( '/resorts-and-villas/' ) );
	}

	return array(
		'id'               => $id,
		'slug'             => $slug,
		'name'             => (string) get_the_title( $id ),
		'kind'             => $kind,
		'logo_id'          => $logo,
		'fit_line'         => oomphtravel_field( $id, 'fit_line' ),
		'fit_note'         => oomphtravel_field( $id, 'fit_note' ),
		'group_size'       => oomphtravel_field( $id, 'group_size' ),
		'price_band'       => oomphtravel_field( $id, 'price_band' ),
		'inclusions'       => oomphtravel_field_lines( $id, 'inclusions' ),
		'ways'             => $way_list,
		'cruiseoomph_line' => '1' === oomphtravel_field( $id, 'cruiseoomph_line' ),
		'tours'            => 'escorted' === $kind ? oomphtravel_operator_tours( $id ) : array(),
		'destinations'     => oomphtravel_operator_destinations( $id ),
		'plan_url'         => add_query_arg( 'operator', $slug, home_url( '/start-planning/' ) ),
	);
}

/**
 * The published destinations that name this operator in related_operators
 * (FIT suppliers list these instead of tours), plus, for an escorted
 * operator, the destinations its tours are tagged with.
 *
 * @return array<int,array{name:string,url:string}>
 */
function oomphtravel_operator_destinations( int $id ): array {
	$out = array();
	if ( ! post_type_exists( 'oomph_destination' ) ) {
		return $out;
	}
	$posts = get_posts(
		array(
			'post_type'        => 'oomph_destination',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'menu_order title',
			'order'            => 'ASC',
			'suppress_filters' => false,
			'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'related_operators',
					'value'   => '"' . $id . '"',
					'compare' => 'LIKE',
				),
			),
		)
	);
	$seen = array();
	foreach ( $posts as $post ) {
		$seen[ (int) $post->ID ] = true;
		$out[]                   = array( 'name' => (string) get_the_title( $post ), 'url' => (string) get_permalink( $post ) );
	}
	foreach ( oomphtravel_operator_tours( $id ) as $tour ) {
		$places = get_the_terms( (int) $tour['id'], 'oomph_place' );
		if ( ! is_array( $places ) ) {
			continue;
		}
		foreach ( $places as $term ) {
			$post_id = (int) get_term_meta( $term->term_id, 'oomph_destination_post', true );
			if ( ! $post_id || isset( $seen[ $post_id ] ) || 'publish' !== get_post_status( $post_id ) ) {
				continue;
			}
			$seen[ $post_id ] = true;
			$out[]            = array( 'name' => (string) get_the_title( $post_id ), 'url' => (string) get_permalink( $post_id ) );
		}
	}
	return $out;
}

/* ------------------------------------------------------------------ */
/* Heroes                                                                */
/* ------------------------------------------------------------------ */

/**
 * Hero sources for a tour: its featured image, or nothing (navy band).
 *
 * @return array<string,mixed>|null
 */
function oomphtravel_tour_hero_sources( int $id ): ?array {
	$thumb = (int) get_post_thumbnail_id( $id );
	if ( ! $thumb ) {
		return null;
	}
	$meta = wp_get_attachment_metadata( $thumb );
	$src  = wp_get_attachment_image_src( $thumb, 'full' );
	if ( ! $src ) {
		return null;
	}
	$srcset = (string) wp_get_attachment_image_srcset( $thumb, 'full' );
	return array(
		'alt'    => (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ),
		'width'  => (int) ( $meta['width'] ?? $src[1] ),
		'height' => (int) ( $meta['height'] ?? $src[2] ),
		'wide'   => array(
			'media'  => '(min-width: 0px)',
			'src'    => (string) $src[0],
			'srcset' => '' !== $srcset ? $srcset : (string) $src[0] . ' ' . (int) $src[1] . 'w',
			'sizes'  => '100vw',
		),
	);
}

/**
 * The index hero: the theme's own renditions (assets/img/tours-hero-*).
 *
 * @return array<string,mixed>
 */
function oomphtravel_tours_hero_sources(): array {
	$img = OOMPHTRAVEL_THEME_URI . 'assets/img/tours-hero';
	return array(
		'alt'    => __( 'Dubrovnik’s old town from above: the walls, the harbour and the red roofs against the Adriatic.', 'oomphtravel' ),
		'width'  => 1280,
		'height' => 720,
		'tall'   => array(
			'media'  => '(max-width: 767px)',
			'src'    => $img . '-tall-720.webp',
			'srcset' => $img . '-tall-480.webp 480w, ' . $img . '-tall-720.webp 720w, ' . $img . '-tall-900.webp 900w',
			'sizes'  => '100vw',
		),
		'wide'   => array(
			'media'  => '(min-width: 768px)',
			'src'    => $img . '-1280.webp',
			'srcset' => $img . '-640.webp 640w, ' . $img . '-960.webp 960w, ' . $img . '-1280.webp 1280w',
			'sizes'  => '100vw',
		),
	);
}

/* ------------------------------------------------------------------ */
/* Wiring                                                                */
/* ------------------------------------------------------------------ */

/** True on any of the three escorted-tours pages. */
function oomphtravel_is_tours_page(): bool {
	return is_post_type_archive( 'oomph_tour' ) || is_singular( array( 'oomph_tour', 'oomph_operator' ) );
}

/**
 * Stylesheets on the tours pages only: destination.css for the hero, prose
 * and accordion primitives, then tours.css for the rest. The filter script
 * loads on the index alone.
 */
function oomphtravel_enqueue_tours_assets(): void {
	if ( ! oomphtravel_is_tours_page() ) {
		return;
	}
	wp_enqueue_style( 'oomphtravel-destination', OOMPHTRAVEL_THEME_URI . 'assets/css/destination.css', array( 'oomphtravel-components' ), OOMPHTRAVEL_THEME_VERSION );
	wp_enqueue_style( 'oomphtravel-tours', OOMPHTRAVEL_THEME_URI . 'assets/css/tours.css', array( 'oomphtravel-destination' ), OOMPHTRAVEL_THEME_VERSION );
	if ( is_post_type_archive( 'oomph_tour' ) ) {
		wp_enqueue_script( 'oomphtravel-tours', OOMPHTRAVEL_THEME_URI . 'assets/js/tours.js', array(), OOMPHTRAVEL_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_tours_assets', 20 );

/**
 * Preload the hero (R3), one hint per source: the index photo, or a tour's
 * featured image. Operator heroes are type on Mist, nothing to preload.
 */
function oomphtravel_preload_tours_hero(): void {
	if ( is_post_type_archive( 'oomph_tour' ) ) {
		$sources = oomphtravel_tours_hero_sources();
	} elseif ( is_singular( 'oomph_tour' ) ) {
		$sources = oomphtravel_tour_hero_sources( (int) get_queried_object_id() );
	} else {
		return;
	}
	if ( ! $sources ) {
		return;
	}
	foreach ( array( 'tall', 'wide' ) as $k ) {
		if ( empty( $sources[ $k ] ) ) {
			continue;
		}
		printf(
			'<link rel="preload" as="image" media="%s" href="%s" imagesrcset="%s" imagesizes="%s" fetchpriority="high">' . "\n",
			esc_attr( $sources[ $k ]['media'] ),
			esc_url( $sources[ $k ]['src'] ),
			esc_attr( $sources[ $k ]['srcset'] ),
			esc_attr( $sources[ $k ]['sizes'] )
		);
	}
}
add_action( 'wp_head', 'oomphtravel_preload_tours_hero', 2 );

/**
 * The archive's own query is not used by the pattern; keep it to one page
 * so /escorted-tours/page/2/ never exists.
 */
function oomphtravel_tours_archive_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'oomph_tour' ) ) {
		return;
	}
	$query->set( 'posts_per_page', -1 );
	$query->set( 'orderby', 'menu_order title' );
	$query->set( 'order', 'ASC' );
}
add_action( 'pre_get_posts', 'oomphtravel_tours_archive_query' );

/** The archive's document title: "Escorted tours", not the post type label. */
function oomphtravel_tours_archive_title( string $title ): string {
	return is_post_type_archive( 'oomph_tour' ) ? __( 'Escorted tours', 'oomphtravel' ) : $title;
}
add_filter( 'post_type_archive_title', 'oomphtravel_tours_archive_title' );

/**
 * A filtered view is shareable but not indexable (plan §6.5). Core's robots
 * meta and Rank Math's both get the directive, whichever one prints.
 */
function oomphtravel_tours_filtered_robots( array $robots ): array {
	if ( is_post_type_archive( 'oomph_tour' ) && oomphtravel_tours_filtered() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'oomphtravel_tours_filtered_robots' );
add_filter( 'rank_math/frontend/robots', 'oomphtravel_tours_filtered_robots' );

/**
 * Rank Math titles the archive "Tours Archive" from the post type label;
 * the page is Escorted tours (R5), and it gets a description (R6). Core's
 * own title goes through post_type_archive_title above.
 */
function oomphtravel_tours_archive_seo_title( string $title ): string {
	return is_post_type_archive( 'oomph_tour' ) ? str_replace( 'Tours Archive', __( 'Escorted tours', 'oomphtravel' ), $title ) : $title;
}
add_filter( 'rank_math/frontend/title', 'oomphtravel_tours_archive_seo_title' );

function oomphtravel_tours_archive_seo_description( string $description ): string {
	if ( ! is_post_type_archive( 'oomph_tour' ) || '' !== trim( $description ) ) {
		return $description;
	}
	return __( 'Escorted tours from Globus, Tauck, Insight Vacations, Abercrombie & Kent and National Geographic, chosen for the way you travel. Same price as booking direct.', 'oomphtravel' );
}
add_filter( 'rank_math/frontend/description', 'oomphtravel_tours_archive_seo_description' );
