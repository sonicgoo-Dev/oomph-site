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
 * Two hooks, because Rank Math's no-image path is conditional. Its
 * set_images() runs the add_additional_images action before it looks at
 * the default image setting; then, only when that setting is empty, it
 * calls add_image() with nothing so the {network}/image filter can
 * supply a URL. On this site the setting still held a deleted cruise-era
 * attachment, so the filter never ran and About, Client stories and the
 * rest kept shipping without an image (seen on staging, 2026-10-08). The
 * action adds the fallback whenever nothing was found on the page, ahead
 * of that setting; the filter stays as a second net.
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
 * @param string $image The URL Rank Math has, '' when none.
 */
function oomphtravel_share_image( $image ): string {
	$image = (string) $image;
	if ( '' !== trim( $image ) ) {
		return $image;
	}
	$url = oomphtravel_share_image_url();
	return '' !== $url ? $url : OOMPHTRAVEL_THEME_URI . 'assets/img/share-varenna-1200x630.jpg';
}
add_filter( 'rank_math/opengraph/facebook/image', 'oomphtravel_share_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'oomphtravel_share_image' );

/**
 * Add the fallback to Rank Math's image set when the page gave it none.
 *
 * @param object $images Rank Math's OpenGraph Image object for the network.
 */
function oomphtravel_share_image_fallback( $images ): void {
	if ( ! is_object( $images ) || ! method_exists( $images, 'has_images' ) || ! method_exists( $images, 'add_image' ) ) {
		return;
	}
	if ( $images->has_images() ) {
		return;
	}
	$images->add_image( oomphtravel_share_image( '' ) );
}
add_action( 'rank_math/opengraph/facebook/add_additional_images', 'oomphtravel_share_image_fallback' );
add_action( 'rank_math/opengraph/twitter/add_additional_images', 'oomphtravel_share_image_fallback' );

/**
 * The net under the nets. On staging (theme 0.10.12) the home page, the
 * ways pages and About still printed no og:image: Rank Math reached
 * neither the add_additional_images action nor the image filter on a page
 * that gave it no image. So the head is buffered while it is written, and
 * when nothing in it names og:image the theme appends its own og:image and
 * twitter:image. A head that already carries one is echoed untouched.
 */
function oomphtravel_share_image_head_start(): void {
	if ( is_admin() || is_feed() ) {
		return;
	}
	$GLOBALS['oomphtravel_share_ob_level'] = ob_get_level();
	ob_start();
}
add_action( 'wp_head', 'oomphtravel_share_image_head_start', 0 );

function oomphtravel_share_image_head_end(): void {
	if ( ! isset( $GLOBALS['oomphtravel_share_ob_level'] ) ) {
		return;
	}
	$level = (int) $GLOBALS['oomphtravel_share_ob_level'];
	unset( $GLOBALS['oomphtravel_share_ob_level'] );
	if ( ob_get_level() !== $level + 1 ) {
		// Something else opened a buffer inside wp_head and left it open;
		// leave every buffer alone and let PHP flush them in order.
		return;
	}
	$html = (string) ob_get_clean();
	if ( false === stripos( $html, 'og:image' ) ) {
		$url   = oomphtravel_share_image( '' );
		$html .= sprintf(
			'<meta property="og:image" content="%1$s">' . "\n" . '<meta name="twitter:image" content="%1$s">' . "\n",
			esc_url( $url )
		);
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the head as the other hooks wrote it, plus two escaped tags.
}
add_action( 'wp_head', 'oomphtravel_share_image_head_end', PHP_INT_MAX );
