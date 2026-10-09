<?php
/**
 * The image a shared link shows (og:image, twitter:image).
 *
 * Rank Math takes the share image from the record's own field, then the
 * featured image, then the first image in the content, then its default
 * image setting. The pages built in code have none of those: the home
 * page, the four ways to travel, About, Client stories, the Journal index,
 * Start planning, Travel trends and the operator pages all went out with
 * no image at all (SEO audit 2026-10-08, B2), so a link pasted into
 * Facebook, LinkedIn or Messages showed as bare text.
 *
 * This fills the gap from what the page already shows: the hero photograph
 * on the home page, the ways and a destination without a featured image,
 * the newest post's picture on the Journal index, and the Varenna
 * photograph everywhere else. An image Rank Math found on the page (the
 * record's field, the featured image, an image in the content) is kept.
 *
 * Rank Math reaches its image filter only when it adds an image; on a page
 * that gives it none it adds nothing and calls nothing (staging, theme
 * 0.10.12). So the filter records whether an image was added, and a late
 * wp_head hook prints the theme's own tags when none was. Without Rank
 * Math (CI) the same hook prints them on every page.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * The share image for the current request, or '' to leave Rank Math's.
 */
function oomphtravel_share_image_url(): string {
	if ( is_front_page() && function_exists( 'oomphtravel_home_hero_sources' ) ) {
		return (string) oomphtravel_home_hero_sources()['wide']['src'];
	}
	if ( function_exists( 'oomphtravel_way_key' ) && '' !== oomphtravel_way_key() ) {
		return (string) oomphtravel_way_hero_sources( oomphtravel_way_key() )['wide']['src'];
	}
	if ( is_singular( 'oomph_destination' ) && function_exists( 'oomphtravel_destination_hero_sources' ) ) {
		$hero = oomphtravel_destination_hero_sources( (int) get_queried_object_id() );
		return $hero ? (string) $hero['wide']['src'] : '';
	}
	if ( is_page( 'journal' ) ) {
		$latest = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish', 'suppress_filters' => false ) );
		$url    = $latest ? (string) get_the_post_thumbnail_url( $latest[0], 'large' ) : '';
		if ( '' !== $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Rank Math's share image when it found none. Photographs on this site are
 * WebP, which Facebook, X and Messages render; LinkedIn does not, so the
 * site-wide fallback is a JPEG cut of the Varenna photograph at the
 * 1200×630 the networks ask for.
 *
 * Rank Math runs this filter once for every image it adds, so it doubles
 * as the record of whether it added any: oomphtravel_share_image_tags()
 * prints the theme's own tags at the end of the head when it did not.
 *
 * @param string $image The URL Rank Math has, '' when none.
 */
function oomphtravel_share_image( $image ): string {
	$image = (string) $image;
	if ( '' !== trim( $image ) ) {
		oomphtravel_share_image_seen( true );
		return $image;
	}
	$url = oomphtravel_share_image_url();
	$url = '' !== $url ? $url : OOMPHTRAVEL_THEME_URI . 'assets/img/share-varenna-1200x630.jpg';
	oomphtravel_share_image_seen( true );
	return $url;
}
add_filter( 'rank_math/opengraph/facebook/image', 'oomphtravel_share_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'oomphtravel_share_image' );

/** Whether Rank Math has added a share image on this request. */
function oomphtravel_share_image_seen( ?bool $set = null ): bool {
	static $seen = false;
	if ( null !== $set ) {
		$seen = $set;
	}
	return $seen;
}

/**
 * The theme's own og:image and twitter:image, printed at the end of the
 * head when nothing else added one.
 *
 * On a page that gives Rank Math no image it reaches neither its image
 * filter nor its add_additional_images action (staging, theme 0.10.12: the
 * home page, the ways pages and About all went out without one), and
 * without Rank Math (CI) nobody prints the tags at all. So the filter above
 * records whether an image was added, and this prints the fallback when
 * none was.
 */
function oomphtravel_share_image_tags(): void {
	if ( is_admin() || is_feed() || oomphtravel_share_image_seen() ) {
		return;
	}
	$url = oomphtravel_share_image_url();
	$url = '' !== $url ? $url : OOMPHTRAVEL_THEME_URI . 'assets/img/share-varenna-1200x630.jpg';
	printf(
		'<meta property="og:image" content="%1$s">' . "\n" . '<meta name="twitter:image" content="%1$s">' . "\n",
		esc_url( $url )
	);
}
add_action( 'wp_head', 'oomphtravel_share_image_tags', 999 );
