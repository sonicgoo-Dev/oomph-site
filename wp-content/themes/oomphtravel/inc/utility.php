<?php
/**
 * The utility pages (plan §6.16): the link-in-bio page at /links/, the 404
 * page, and the search results the 404's search field leads to.
 *
 * /links/ is the one address the Instagram profile points at. Instagram
 * allows a single clickable link, so this page stands in for "whatever I
 * posted about today" and never has to change: the featured card follows the
 * newest Journal post on its own. Eleven links, the same count and shape as
 * the page it replaces, two of them to CruiseOomph (plan §4.3). It is
 * noindex, follow: it duplicates the navigation and exists for one referrer.
 *
 * The 404 is where every deleted cruise address lands (plan §8.4), so it has
 * to be a page with somewhere to go, not a shrug: the symbol, one line, a
 * search field and the three doors.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Which page                                                           */
/* ------------------------------------------------------------------ */

/**
 * 'links', '404', 'search', or '' when the request is none of these.
 */
function oomphtravel_utility_key(): string {
	if ( is_404() ) {
		return '404';
	}
	if ( is_search() ) {
		return 'search';
	}
	if ( is_page() && 'links' === (string) get_post_field( 'post_name', get_queried_object_id() ) ) {
		return 'links';
	}
	return '';
}

/* ------------------------------------------------------------------ */
/* /links/                                                              */
/* ------------------------------------------------------------------ */

/**
 * The featured Journal post: the newest published one, or the post pinned
 * through `oomph_links_featured_post_id` (the same filter the old page had,
 * so anything Eric set up keeps working). Null when there is nothing to show.
 */
function oomphtravel_links_featured_post(): ?WP_Post {
	$id = (int) apply_filters( 'oomph_links_featured_post_id', 0 );

	if ( ! $id ) {
		$latest = get_posts(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'numberposts'         => 1,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		$id = $latest ? (int) $latest[0]->ID : 0;
	}

	$post = $id ? get_post( $id ) : null;
	return ( $post instanceof WP_Post && 'publish' === $post->post_status ) ? $post : null;
}

/**
 * The four quiet rows: label, one line of meta, destination, and whether it
 * leaves the site. Lowest commitment first; the two CruiseOomph rows last,
 * because they take the visitor somewhere else.
 *
 * @return array<int,array{label:string,meta:string,url:string,external:bool}>
 */
function oomphtravel_links_rows(): array {
	$year = function_exists( 'oomphtravel_trends_year' ) ? oomphtravel_trends_year() : (string) gmdate( 'Y' );

	return array(
		array(
			'label'    => __( 'Destinations', 'oomphtravel' ),
			'meta'     => __( 'Eleven places, and the months that suit them', 'oomphtravel' ),
			'url'      => home_url( '/destinations/' ),
			'external' => false,
		),
		array(
			/* translators: %s: the guide year */
			'label'    => sprintf( __( 'Travel Trends %s', 'oomphtravel' ), $year ),
			'meta'     => __( 'The guide, free by email', 'oomphtravel' ),
			'url'      => home_url( '/travel-trends/' ),
			'external' => false,
		),
		array(
			'label'    => __( 'Cruises, on CruiseOomph', 'oomphtravel' ),
			'meta'     => __( 'Premium and luxury cruises have their own site', 'oomphtravel' ),
			'url'      => oomphtravel_cruiseoomph_url( '/', 'links' ),
			'external' => true,
		),
		array(
			'label'    => __( 'Find my cruise style', 'oomphtravel' ),
			'meta'     => __( 'A short quiz, about two minutes', 'oomphtravel' ),
			'url'      => oomphtravel_cruiseoomph_url( '/find-my-cruise/', 'links' ),
			'external' => true,
		),
	);
}

/**
 * The five text links under the rows: the four ways to travel and Eric.
 *
 * @return array<string,string> label => url
 */
function oomphtravel_links_more(): array {
	return array(
		__( 'Custom journeys', 'oomphtravel' )   => home_url( '/custom-journeys/' ),
		__( 'Escorted tours', 'oomphtravel' )    => home_url( '/escorted-tours/' ),
		__( 'Resorts & villas', 'oomphtravel' )  => home_url( '/resorts-and-villas/' ),
		__( 'Multi-generational', 'oomphtravel' ) => home_url( '/multi-generational-travel-planning/' ),
		__( 'About Eric', 'oomphtravel' )        => home_url( '/about/' ),
	);
}

/**
 * noindex, follow on /links/, on both robots tags (WordPress's and Rank
 * Math's). `oomph_links_noindex` returning false lifts it, as before.
 */
function oomphtravel_links_noindex(): bool {
	return 'links' === oomphtravel_utility_key() && (bool) apply_filters( 'oomph_links_noindex', true );
}
add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( oomphtravel_links_noindex() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'], $robots['max-image-preview'], $robots['max-snippet'], $robots['max-video-preview'] );
		}
		return $robots;
	},
	99
);
add_filter(
	'rank_math/frontend/robots',
	static function ( $robots ) {
		if ( oomphtravel_links_noindex() && is_array( $robots ) ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	},
	99
);

/* ------------------------------------------------------------------ */
/* The three doors (404 and an empty search)                            */
/* ------------------------------------------------------------------ */

/**
 * Custom journeys · Escorted tours · Resorts & villas, with the one-line
 * bodies the homepage card row uses (plan §6.1.4), so the words match.
 *
 * @return array<int,array{title:string,body:string,url:string}>
 */
function oomphtravel_doors(): array {
	return array(
		array(
			'title' => __( 'Custom journeys', 'oomphtravel' ),
			'body'  => __( 'Independent, hand-planned trips by destination.', 'oomphtravel' ),
			'url'   => home_url( '/custom-journeys/' ),
		),
		array(
			'title' => __( 'Escorted tours', 'oomphtravel' ),
			'body'  => __( 'Operator departures I sell, with my fit notes.', 'oomphtravel' ),
			'url'   => home_url( '/escorted-tours/' ),
		),
		array(
			'title' => __( 'Resorts & villas', 'oomphtravel' ),
			'body'  => __( 'Resort stays, villas and private homes.', 'oomphtravel' ),
			'url'   => home_url( '/resorts-and-villas/' ),
		),
	);
}

/**
 * The doors as a list. `$heading_level` is h2 on the 404, where the page has
 * its own H1, and h3 under the search page's "Nothing matched" heading.
 */
function oomphtravel_doors_list( string $heading_level = 'h2' ): string {
	$level = in_array( $heading_level, array( 'h2', 'h3' ), true ) ? $heading_level : 'h2';
	$html  = '<ul class="ot-doors">';
	foreach ( oomphtravel_doors() as $door ) {
		$html .= sprintf(
			'<li class="ot-doors__door"><%1$s class="ot-doors__title"><a href="%2$s">%3$s%4$s</a></%1$s><p class="ot-doors__body">%5$s</p></li>',
			$level,
			esc_url( $door['url'] ),
			esc_html( $door['title'] ),
			' ' . oomphtravel_arrow(),
			esc_html( $door['body'] )
		);
	}
	return $html . '</ul>';
}

/**
 * The search field. One input, one button; the button is the page's primary
 * action on the 404 and a ghost on the results page, where the results are.
 *
 * @param string $style 'primary' | 'ghost' for the submit.
 */
function oomphtravel_search_form( string $style = 'primary' ): string {
	static $n = 0;
	$n++;
	$id    = 'ot-search-' . $n;
	$style = 'ghost' === $style ? 'ghost' : 'primary';

	return sprintf(
		'<form class="ot-search" role="search" method="get" action="%1$s">
			<label class="ot-search__label" for="%2$s">%3$s</label>
			<div class="ot-search__row">
				<input class="ot-search__input" type="search" id="%2$s" name="s" value="%4$s" placeholder="%5$s" autocomplete="off">
				<button class="ot-btn ot-btn--%6$s ot-btn--on-navy ot-search__submit" type="submit"><span class="ot-btn__label">%7$s</span>%8$s</button>
			</div>
		</form>',
		esc_url( home_url( '/' ) ),
		esc_attr( $id ),
		esc_html__( 'Search the site', 'oomphtravel' ),
		esc_attr( get_search_query() ),
		esc_attr__( 'A place, a tour, a question', 'oomphtravel' ),
		$style,
		esc_html__( 'Search', 'oomphtravel' ),
		oomphtravel_arrow()
	);
}

/**
 * What kind of thing a search result is, for its eyebrow.
 */
function oomphtravel_search_result_kind( WP_Post $post ): string {
	switch ( $post->post_type ) {
		case 'post':
			$cats = get_the_category( $post->ID );
			return $cats ? (string) $cats[0]->name : __( 'Journal', 'oomphtravel' );
		case 'oomph_destination':
			return __( 'Destination', 'oomphtravel' );
		case 'oomph_tour':
			return __( 'Escorted tour', 'oomphtravel' );
		case 'oomph_operator':
			return __( 'Tour operator', 'oomphtravel' );
		default:
			return __( 'Page', 'oomphtravel' );
	}
}

/* ------------------------------------------------------------------ */
/* Assets                                                               */
/* ------------------------------------------------------------------ */

/**
 * destination.css for the hero band and prose, then utility.css, on these
 * pages only.
 */
function oomphtravel_enqueue_utility_assets(): void {
	if ( '' === oomphtravel_utility_key() ) {
		return;
	}
	wp_enqueue_style( 'oomphtravel-destination', OOMPHTRAVEL_THEME_URI . 'assets/css/destination.css', array( 'oomphtravel-components' ), OOMPHTRAVEL_THEME_VERSION );
	wp_enqueue_style( 'oomphtravel-utility', OOMPHTRAVEL_THEME_URI . 'assets/css/utility.css', array( 'oomphtravel-destination' ), OOMPHTRAVEL_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_utility_assets', 20 );

/**
 * On /links/ the featured card's picture is the largest thing above the
 * fold, so it gets the preload the hero photographs get elsewhere (R3).
 */
function oomphtravel_preload_links_image(): void {
	if ( 'links' !== oomphtravel_utility_key() ) {
		return;
	}
	$post = oomphtravel_links_featured_post();
	$id   = $post ? (int) get_post_thumbnail_id( $post ) : 0;
	if ( ! $id ) {
		return;
	}
	$src = wp_get_attachment_image_url( $id, 'large' );
	if ( ! $src ) {
		return;
	}
	$srcset = (string) wp_get_attachment_image_srcset( $id, 'large' );
	printf(
		'<link rel="preload" as="image" href="%s"%s imagesizes="%s" fetchpriority="high">' . "\n",
		esc_url( $src ),
		'' !== $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
		esc_attr( '(min-width: 768px) 560px, 100vw' )
	);
}
add_action( 'wp_head', 'oomphtravel_preload_links_image', 2 );

/**
 * A search description for /links/ (R6). The other two are noindex by
 * WordPress itself (search) or have no description to give (404).
 *
 * @param mixed $description
 */
function oomphtravel_links_seo_description( $description ) {
	if ( 'links' !== oomphtravel_utility_key() ) {
		return $description;
	}
	if ( is_string( $description ) && '' !== trim( $description ) && trim( $description ) !== (string) get_the_title( get_queried_object_id() ) ) {
		return $description;
	}
	return __( 'Everything from Oomph Travel in one place: the newest Journal post, the destinations, the Travel Trends guide, the ways to travel, and where to start planning.', 'oomphtravel' );
}
add_filter( 'rank_math/frontend/description', 'oomphtravel_links_seo_description' );
