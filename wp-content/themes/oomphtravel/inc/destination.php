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
 * The journal posts about a destination: published posts carrying a tag or
 * category whose slug is the destination's, newest first, three at most
 * (R17: the pillar links down to its cluster; the posts already link up
 * through oomphtravel_post_destination_card()). An empty list when none
 * does, and the page shows no block.
 *
 * @return WP_Post[]
 */
function oomphtravel_destination_posts( int $post_id, int $limit = 3 ): array {
	$slug = (string) get_post_field( 'post_name', $post_id );
	if ( '' === $slug ) {
		return array();
	}
	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => $limit,
			'suppress_filters' => false,
			'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- three posts, one page.
				'relation' => 'OR',
				array( 'taxonomy' => 'post_tag', 'field' => 'slug', 'terms' => array( $slug ) ),
				array( 'taxonomy' => 'category', 'field' => 'slug', 'terms' => array( $slug ) ),
			),
		)
	);
	$posts = array_values( array_filter( $posts, static fn( $p ): bool => $p instanceof WP_Post ) );
	if ( $posts ) {
		return $posts;
	}

	// No post is tagged or categorised with the place (the Journal's
	// categories are topics: destinations, planning, resorts-villas, tours),
	// so fall back to the posts whose title names it or a place in it.
	$names = oomphtravel_destination_place_names()[ $slug ] ?? array();
	if ( ! $names ) {
		return array();
	}
	$pattern = '/(' . implode( '|', array_map( static fn( string $n ): string => preg_quote( $n, '/' ), $names ) ) . ')/iu';
	$all     = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 60, 'suppress_filters' => false ) );
	$found   = array();
	foreach ( $all as $p ) {
		if ( $p instanceof WP_Post && preg_match( $pattern, (string) $p->post_title ) ) {
			$found[] = $p;
			if ( count( $found ) >= $limit ) {
				break;
			}
		}
	}
	return $found;
}

/**
 * The place names a post title can carry for each destination, for the
 * title fallback in oomphtravel_destination_posts(). Filterable.
 *
 * @return array<string,string[]>
 */
function oomphtravel_destination_place_names(): array {
	$names = array(
		'italy'      => array( 'Italy', 'Italian', 'Rome', 'Florence', 'Venice', 'Tuscany', 'Puglia', 'Amalfi', 'Sicily', 'Lake Como', 'Milan', 'Naples' ),
		'uk-ireland' => array( 'UK', 'United Kingdom', 'Britain', 'England', 'Scotland', 'Wales', 'Ireland', 'London', 'Cotswolds', 'Edinburgh', 'Dublin' ),
		'france'     => array( 'France', 'French', 'Paris', 'Provence', 'Normandy', 'Dordogne', 'Loire', 'Burgundy', 'Bordeaux' ),
		'spain'      => array( 'Spain', 'Spanish', 'Madrid', 'Barcelona', 'Seville', 'Andalusia', 'Basque' ),
		'portugal'   => array( 'Portugal', 'Portuguese', 'Lisbon', 'Porto', 'Douro', 'Algarve' ),
		'greece'     => array( 'Greece', 'Greek', 'Athens', 'Santorini', 'Crete', 'Mykonos', 'Corfu' ),
		'croatia'    => array( 'Croatia', 'Croatian', 'Dubrovnik', 'Split', 'Hvar', 'Dalmatia', 'Adriatic' ),
		'hawaii'     => array( 'Hawaii', 'Hawaiian', 'Maui', 'Kauai', 'Oahu', 'Big Island', 'Honolulu' ),
		'mexico'     => array( 'Mexico', 'Mexican', 'Cabo', 'Los Cabos', 'Riviera Maya', 'Cancun', 'Cancún', 'Puerto Vallarta', 'Oaxaca' ),
		'caribbean'  => array( 'Caribbean', 'Turks and Caicos', 'Turks & Caicos', 'St Lucia', 'St. Lucia', 'Barbados', 'Anguilla', 'Antigua', 'Jamaica', 'Bahamas' ),
		'africa'     => array( 'Africa', 'African', 'safari', 'Kenya', 'Tanzania', 'Botswana', 'South Africa', 'Namibia', 'Rwanda', 'Serengeti' ),
	);
	/**
	 * Filters the place names a post title can carry for each destination.
	 *
	 * @param array<string,string[]> $names Names by destination slug.
	 */
	return (array) apply_filters( 'oomphtravel_destination_place_names', $names );
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
		// Phones stop at 720w; see oomphtravel_way_hero_sources() for why.
		$sources['tall'] = array(
			'media'  => '(max-width: 767px)',
			'src'    => $img . '-tall-720.webp',
			'srcset' => $img . '-tall-480.webp 480w, ' . $img . '-tall-720.webp 720w',
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
	$post = oomphtravel_destination_live_post( $slug );
	if ( ! $post ) {
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

/**
 * The published destination record for a slug, or null. Every list of
 * destination links in the theme (the header drawer, the homepage cards, the
 * index, the "Where" rows on the ways pages) goes through here, so a record
 * that is still a draft, or has been unpublished, never gets a link that
 * 404s (Stage 12 quality gate). Cached per request: the header alone asks
 * eleven times.
 */
function oomphtravel_destination_live_post( string $slug ): ?WP_Post {
	static $cache = array();
	if ( ! array_key_exists( $slug, $cache ) ) {
		$post           = post_type_exists( 'oomph_destination' ) ? get_page_by_path( $slug, OBJECT, 'oomph_destination' ) : null;
		$cache[ $slug ] = $post instanceof WP_Post && 'publish' === $post->post_status ? $post : null;
	}
	return $cache[ $slug ];
}

/**
 * Is this destination published? The header and the homepage ask before
 * printing a link to it.
 */
function oomphtravel_destination_is_live( string $slug ): bool {
	return null !== oomphtravel_destination_live_post( $slug );
}

/**
 * The search title and description for each destination, by slug (R5, R6;
 * SEO audit 2026-10-08, B1): the query the page is written for first, the
 * angle the H1 takes, " | Oomph Travel" last. Used only while Rank Math's
 * own fields on the record are empty, so anything Eric types there wins.
 * Italy's were written in Rank Math on 2026-10-04 and are not repeated here.
 *
 * @return array<string,array{title:string,description:string}>
 */
function oomphtravel_destination_seo_copy(): array {
	return array(
		'uk-ireland' => array(
			'title'       => __( 'Custom trips to Britain & Ireland, by county | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Custom trips to England, Scotland, Wales and Ireland planned a county at a time, with real drive times, country hotels and the trains that beat the car.', 'oomphtravel' ),
		),
		'france'     => array(
			'title'       => __( 'Custom trips to France, beyond Paris | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Custom trips to France planned by one advisor: four nights in Paris, then Provence, the Dordogne, Burgundy or the Loire, with drivers, guides and hotels booked.', 'oomphtravel' ),
		),
		'spain'      => array(
			'title'       => __( 'Custom trips to Spain, city by city | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Custom trips to Spain planned around its late clock: Madrid, Barcelona, Seville and the Basque coast, with trains, hotels and tables booked in the right order.', 'oomphtravel' ),
		),
		'portugal'   => array(
			'title'       => __( 'Custom trips to Portugal, Lisbon to the Douro | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Custom trips to Portugal: Lisbon, Porto, the Douro valley and the Algarve, planned by one advisor who has driven the roads. A ten-day outline and when to go.', 'oomphtravel' ),
		),
		'greece'     => array(
			'title'       => __( 'Custom trips to Greece, Athens to the islands | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Custom trips to Greece planned from Athens outward: which islands, how many, and the ferries and flights between them, with hotels chosen for the view.', 'oomphtravel' ),
		),
		'croatia'    => array(
			'title'       => __( 'Custom trips to Croatia and the Adriatic | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Custom trips along the Dalmatian coast: Split, Hvar, Korčula and Dubrovnik by road and ferry, planned by one advisor, with the islands chosen for your pace.', 'oomphtravel' ),
		),
		'hawaii'     => array(
			'title'       => __( 'Custom Hawaii trips, one island at a time | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Hawaii planned island by island: Oahu, Maui, Kauai or the Big Island, which suits families, couples or three generations, and the resorts worth the rate.', 'oomphtravel' ),
		),
		'mexico'     => array(
			'title'       => __( 'Custom Mexico trips from one good base | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'Mexico planned from one good base: Riviera Maya, Los Cabos, Puerto Vallarta or the colonial towns, with the flights, resorts and days out booked by one advisor.', 'oomphtravel' ),
		),
		'caribbean'  => array(
			'title'       => __( 'Caribbean trips, planned island by island | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'The Caribbean planned island by island: which one fits the group, the flight that gets you there, and resort versus staffed villa, with real costs for each.', 'oomphtravel' ),
		),
		'africa'     => array(
			'title'       => __( 'Safari planning, with the right guide | Oomph Travel', 'oomphtravel' ),
			'description' => __( 'A first safari planned by one advisor: Kenya, Tanzania, Botswana or South Africa, escorted or private, the camps, the light aircraft and the right seasons.', 'oomphtravel' ),
		),
	);
}

/**
 * The search title for a destination page, from oomphtravel_destination_seo_copy()
 * while Rank Math's title field on the record is empty. Without this Rank
 * Math prints the record name and the site name ("France - Oomph Travel"),
 * which says nothing a searcher typed.
 */
function oomphtravel_destination_seo_title( string $title ): string {
	if ( ! is_singular( 'oomph_destination' ) ) {
		return $title;
	}
	$post_id = (int) get_queried_object_id();
	if ( '' !== trim( (string) get_post_meta( $post_id, 'rank_math_title', true ) ) ) {
		return $title;
	}
	$copy = oomphtravel_destination_seo_copy()[ (string) get_post_field( 'post_name', $post_id ) ] ?? null;
	return $copy ? (string) $copy['title'] : $title;
}
add_filter( 'rank_math/frontend/title', 'oomphtravel_destination_seo_title' );

/**
 * Meta description for destination pages (plan 8.6: every page has a unique
 * description). The record has no post content for Rank Math to fall back
 * on, so without this the tag is empty. Nothing is set in Rank Math's own
 * field, so a description Eric writes there still wins.
 *
 * A single destination uses the written line from
 * oomphtravel_destination_seo_copy(), else the first sentence or two of its
 * intro, cut at a word boundary under 155 characters. The index gets a
 * fixed line.
 */
function oomphtravel_destination_seo_description( string $description ): string {
	$current = trim( $description );

	if ( is_post_type_archive( 'oomph_destination' ) ) {
		// Rank Math's archive default is the archive title itself, which is not a description.
		if ( '' !== $current && false === stripos( $current, 'archive' ) ) {
			return $description;
		}
		return __( 'The destinations I plan, from Italy and the UK to Hawaii, Mexico, the Caribbean and Africa: the ways to travel each one, the months that suit it, and where to stay.', 'oomphtravel' );
	}

	if ( ! is_singular( 'oomph_destination' ) || '' !== $current ) {
		return $description;
	}

	$post_id = (int) get_queried_object_id();
	$copy    = oomphtravel_destination_seo_copy()[ (string) get_post_field( 'post_name', $post_id ) ] ?? null;
	if ( $copy && '' !== (string) $copy['description'] ) {
		return (string) $copy['description'];
	}
	$intro   = class_exists( '\OomphTravel\Core\Fields' ) ? \OomphTravel\Core\Fields::value( $post_id, 'intro' ) : (string) get_post_meta( $post_id, 'intro', true );
	$intro   = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $intro ) ) ?? '' );
	if ( '' === $intro ) {
		return $description;
	}
	if ( mb_strlen( $intro ) <= 155 ) {
		return $intro;
	}
	// Whole sentences first; otherwise cut at the last space before the limit.
	$out = '';
	foreach ( preg_split( '/(?<=[.!?])\s+/', $intro ) ?: array() as $sentence ) {
		$candidate = '' === $out ? $sentence : $out . ' ' . $sentence;
		if ( mb_strlen( $candidate ) > 155 ) {
			break;
		}
		$out = $candidate;
	}
	if ( '' === $out ) {
		$out = mb_substr( $intro, 0, 152 );
		$cut = mb_strrpos( $out, ' ' );
		$out = rtrim( mb_substr( $out, 0, $cut ?: 152 ), ' ,;:' ) . '…';
	}
	return $out;
}
add_filter( 'rank_math/frontend/description', 'oomphtravel_destination_seo_description' );
