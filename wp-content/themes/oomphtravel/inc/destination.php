<?php
/**
 * Destination pages (plan §6.2 and §6.3): the data behind the template, the
 * hero sources, the tours tagged with a destination, and the assets that
 * load only on those pages.
 *
 * Fields are read through the plugin's Fields class so the page renders the
 * same with ACF Pro, with SCF, or with neither (wp-env in CI). Every section
 * hides itself when its field is empty; nothing prints a placeholder.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * Everything the destination template needs, in one array.
 *
 * @return array<string,mixed>
 */
function oomphtravel_destination_data( int $post_id ): array {
	$fields = class_exists( '\OomphTravel\Core\Fields' );
	$value  = static function ( string $name ) use ( $fields, $post_id ): string {
		return $fields ? \OomphTravel\Core\Fields::value( $post_id, $name ) : (string) get_post_meta( $post_id, $name, true );
	};
	$rows   = static function ( string $name, array $subs ) use ( $fields, $post_id ): array {
		return $fields ? \OomphTravel\Core\Fields::repeater( $post_id, $name, $subs ) : array();
	};
	$choices = static function ( string $name ) use ( $fields, $post_id ): array {
		return $fields ? \OomphTravel\Core\Fields::choices( $post_id, $name ) : array();
	};

	$title    = (string) get_the_title( $post_id );
	$headline = $value( 'headline' );
	$variant  = $value( 'variant' );
	if ( ! in_array( $variant, array( 'custom', 'resort', 'guided' ), true ) ) {
		$variant = 'custom';
	}

	return array(
		'id'                 => $post_id,
		'slug'               => (string) get_post_field( 'post_name', $post_id ),
		'title'              => $title,
		'headline'           => '' !== $headline ? $headline : sprintf(
			/* translators: %s: destination name */
			__( '%s, planned by someone who keeps going back.', 'oomphtravel' ),
			$title
		),
		'intro'              => $value( 'intro' ),
		'variant'            => $variant,
		'regions'            => $rows( 'regions', array( 'name', 'blurb' ) ),
		'itinerary'          => $rows( 'sample_itinerary', array( 'day', 'title', 'text' ) ),
		'stays'              => $rows( 'stays', array( 'name', 'type', 'note', 'perks' ) ),
		'best_months'        => array_map( 'intval', $choices( 'best_months' ) ),
		'shoulder_months'    => array_map( 'intval', $choices( 'shoulder_months' ) ),
		'faq'                => $rows( 'faq', array( 'question', 'answer' ) ),
		'cruiseoomph_region' => $value( 'cruiseoomph_region' ),
		'operators'          => oomphtravel_destination_operators( $post_id ),
		'tours'              => oomphtravel_destination_tours( $post_id ),
	);
}

/**
 * The operators named in the "On an escorted tour" column: the record's
 * related_operators, else the operators of the tours tagged with it.
 *
 * @return array<int,array{name:string,url:string}>
 */
function oomphtravel_destination_operators( int $post_id ): array {
	$ids = get_post_meta( $post_id, 'related_operators', true );
	$ids = is_array( $ids ) ? array_map( 'intval', $ids ) : array();
	if ( ! $ids ) {
		foreach ( oomphtravel_destination_tours( $post_id, 12 ) as $tour ) {
			if ( $tour['operator_id'] ) {
				$ids[] = $tour['operator_id'];
			}
		}
		$ids = array_values( array_unique( $ids ) );
	}

	$out = array();
	foreach ( $ids as $id ) {
		if ( 'publish' !== get_post_status( $id ) ) {
			continue;
		}
		$out[] = array(
			'name' => (string) get_the_title( $id ),
			'url'  => (string) get_permalink( $id ),
		);
	}
	return $out;
}

/**
 * Published tours tagged with this destination's term, newest first.
 *
 * @return array<int,array<string,mixed>>
 */
function oomphtravel_destination_tours( int $post_id, int $limit = 3 ): array {
	static $cache = array();
	$key = $post_id . ':' . $limit;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$cache[ $key ] = array();
	if ( ! class_exists( '\OomphTravel\Core\CPT_Destination' ) || ! post_type_exists( 'oomph_tour' ) ) {
		return $cache[ $key ];
	}
	$term_id = \OomphTravel\Core\CPT_Destination::term_id( $post_id );
	if ( ! $term_id ) {
		return $cache[ $key ];
	}

	$posts = get_posts(
		array(
			'post_type'        => 'oomph_tour',
			'post_status'      => 'publish',
			'numberposts'      => $limit,
			'suppress_filters' => false,
			'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => \OomphTravel\Core\Taxonomies::DESTINATION,
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		)
	);

	foreach ( $posts as $tour ) {
		$cache[ $key ][] = oomphtravel_tour_card( (int) $tour->ID, (string) get_the_title( $post_id ) );
	}
	return $cache[ $key ];
}

/**
 * Hero sources for a destination: the featured image when there is one,
 * else the theme's own renditions for that slug (assets/img/dest-{slug}-*),
 * else nothing and the hero is a navy band.
 *
 * @return array<string,mixed>|null  tall/wide sources plus alt, width, height.
 */
function oomphtravel_destination_hero_sources( int $post_id ): ?array {
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		$meta = wp_get_attachment_metadata( $thumb );
		$src  = wp_get_attachment_image_src( $thumb, 'full' );
		if ( $src ) {
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
	}

	$slug = (string) get_post_field( 'post_name', $post_id );
	$dir  = OOMPHTRAVEL_THEME_DIR . 'assets/img/';
	if ( '' === $slug || ! file_exists( $dir . 'dest-' . $slug . '-1280.webp' ) ) {
		return null;
	}
	$img = OOMPHTRAVEL_THEME_URI . 'assets/img/dest-' . $slug;

	$sources = array(
		'alt'    => '',
		'width'  => 1280,
		'height' => 853,
		'wide'   => array(
			'media'  => '(min-width: 768px)',
			'src'    => $img . '-1280.webp',
			'srcset' => $img . '-640.webp 640w, ' . $img . '-960.webp 960w, ' . $img . '-1280.webp 1280w',
			'sizes'  => '100vw',
		),
	);
	if ( file_exists( $dir . 'dest-' . $slug . '-tall-720.webp' ) ) {
		$sources['tall'] = array(
			'media'  => '(max-width: 767px)',
			'src'    => $img . '-tall-720.webp',
			'srcset' => $img . '-tall-480.webp 480w, ' . $img . '-tall-720.webp 720w, ' . $img . '-tall-900.webp 900w',
			'sizes'  => '100vw',
		);
	}

	/**
	 * Filters the alt text of a theme-supplied destination hero.
	 *
	 * @param string $alt  Alt text.
	 * @param string $slug Destination slug.
	 */
	$sources['alt'] = (string) apply_filters( 'oomphtravel_destination_hero_alt', oomphtravel_destination_hero_alt( $slug ), $slug );

	return $sources;
}

/** Alt text for the theme's own destination heroes. */
function oomphtravel_destination_hero_alt( string $slug ): string {
	$alts = array(
		'italy' => __( 'Florence from above at first light: the Duomo’s red dome and Giotto’s tower over a sea of terracotta roofs.', 'oomphtravel' ),
	);
	return $alts[ $slug ] ?? '';
}

/**
 * Destination stylesheet on the destination pages only, after components.
 */
function oomphtravel_enqueue_destination_assets(): void {
	if ( ! is_singular( 'oomph_destination' ) && ! is_post_type_archive( 'oomph_destination' ) ) {
		return;
	}
	wp_enqueue_style( 'oomphtravel-destination', OOMPHTRAVEL_THEME_URI . 'assets/css/destination.css', array( 'oomphtravel-components' ), OOMPHTRAVEL_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_destination_assets', 20 );

/**
 * Preload the destination hero (R3), one hint per source.
 */
function oomphtravel_preload_destination_hero(): void {
	if ( ! is_singular( 'oomph_destination' ) ) {
		return;
	}
	$sources = oomphtravel_destination_hero_sources( (int) get_queried_object_id() );
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
add_action( 'wp_head', 'oomphtravel_preload_destination_hero', 2 );

/**
 * The published destination records, grouped for the index (plan §6.2, D33).
 * Drafts are left out, so the index never links to a page that 404s.
 *
 * @return array<int,array{heading:string,cards:array<int,array<string,mixed>>}>
 */
function oomphtravel_destination_groups(): array {
	$groups = array(
		array(
			'heading' => __( 'Europe, planned region by region', 'oomphtravel' ),
			'slugs'   => array( 'italy', 'uk-ireland', 'france', 'spain', 'portugal', 'greece', 'croatia' ),
		),
		array(
			'heading' => __( 'Sun and sea', 'oomphtravel' ),
			'slugs'   => array( 'hawaii', 'mexico', 'caribbean' ),
		),
		array(
			'heading' => __( 'Guided, through the operators I trust', 'oomphtravel' ),
			'slugs'   => array( 'africa' ),
		),
	);

	$out = array();
	foreach ( $groups as $group ) {
		$cards = array();
		foreach ( $group['slugs'] as $slug ) {
			$card = oomphtravel_destination_card( $slug );
			if ( $card ) {
				$cards[] = $card;
			}
		}
		if ( $cards ) {
			$out[] = array( 'heading' => $group['heading'], 'cards' => $cards );
		}
	}
	return $out;
}

/**
 * The card array (oomphtravel_card_destination) for one destination by
 * slug, or null when the record is missing or not published. The image is
 * the record's featured image, else the theme's own tall rendition.
 *
 * @return array<string,mixed>|null
 */
function oomphtravel_destination_card( string $slug ): ?array {
	if ( ! post_type_exists( 'oomph_destination' ) ) {
		return null;
	}
	$post = get_page_by_path( $slug, OBJECT, 'oomph_destination' );
	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		return null;
	}
	$card  = array(
		'name' => (string) get_the_title( $post ),
		'url'  => (string) get_permalink( $post ),
	);
	$thumb = (int) get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$card['image_id']  = $thumb;
		$card['image_alt'] = (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true );
	} elseif ( file_exists( OOMPHTRAVEL_THEME_DIR . 'assets/img/dest-' . $slug . '-tall-720.webp' ) ) {
		$card['image_url'] = OOMPHTRAVEL_THEME_URI . 'assets/img/dest-' . $slug . '-tall-720.webp';
		$card['image_alt'] = oomphtravel_destination_hero_alt( $slug );
	}
	return $card;
}
