<?php
/**
 * Technical SEO: llms.txt, and the one archive that must not be indexed.
 *
 * - robots.txt: Rank Math already serves a crawl-friendly one with the XML
 *   sitemap, so nothing here touches it. Non-public environments (staging)
 *   keep WordPress's default disallow.
 * - llms.txt: a plain-text guide for AI crawlers (the emerging /llms.txt
 *   convention), generated from the site's key pages, the destinations, the
 *   tour index and the recent journal posts. Rewritten for the resurfaced
 *   site map (plan §4.2); the old cruise pages are gone from it.
 * - /category/uncategorized/: WordPress's default category, which every post
 *   without a chosen topic falls into. It is a duplicate of the Journal and
 *   the plan asks for it to be noindexed or removed (§4.2). Noindexed here,
 *   so the archive still resolves for anyone holding the link.
 * - XML sitemap: Rank Math lists a post type only when its own "Include in
 *   Sitemap" setting is on, and a type registered after Rank Math was set
 *   up starts with it off. Tours and operators were missing for that
 *   reason, and with them the /escorted-tours/ archive, which Rank Math adds
 *   as the first entry of the tour sitemap. The setting is held on here.
 *   /links/ is noindex, so it is kept out of the page sitemap.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SEO {

	public static function init(): void {
		// Served on init, after the post types register at priority 10 (their
		// permalinks and archive links need that), and long before the
		// canonical trailing-slash redirect at template_redirect would fire
		// on the .txt request.
		add_action( 'init', array( __CLASS__, 'serve_llms' ), 20 );
		add_filter( 'wp_robots', array( __CLASS__, 'uncategorized_robots' ) );
		add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'uncategorized_robots_rank_math' ) );
		add_filter( 'option_rank-math-options-sitemap', array( __CLASS__, 'sitemap_post_types' ) );
		add_filter( 'rank_math/sitemap/entry', array( __CLASS__, 'sitemap_skip_noindex_pages' ), 10, 3 );
		add_action( 'init', array( __CLASS__, 'refresh_sitemap_cache' ), 101 );
	}

	/* ---------------------------------------------------------------- */
	/* XML sitemap                                                        */
	/* ---------------------------------------------------------------- */

	/** Post types whose records are public pages and belong in the sitemap. */
	private const SITEMAP_POST_TYPES = array( 'oomph_destination', 'oomph_tour', 'oomph_operator' );

	/**
	 * Hold Rank Math's "Include in Sitemap" on for the site's own record
	 * types. Rank Math reads this option when it builds the sitemap index.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public static function sitemap_post_types( $options ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}
		foreach ( self::SITEMAP_POST_TYPES as $type ) {
			$options[ 'pt_' . $type . '_sitemap' ] = 'on';
		}
		return $options;
	}

	/**
	 * Leave /links/ out of the page sitemap while it is noindex (the theme
	 * sets the tag; `oomph_links_noindex` returning false lifts both).
	 *
	 * @param mixed  $url
	 * @param string $type
	 * @param mixed  $post
	 * @return mixed
	 */
	public static function sitemap_skip_noindex_pages( $url, $type = '', $post = null ) {
		if (
			'post' === $type
			&& $post instanceof \WP_Post
			&& 'page' === $post->post_type
			&& 'links' === $post->post_name
			&& (bool) apply_filters( 'oomph_links_noindex', true )
		) {
			return false;
		}
		return $url;
	}

	/**
	 * Rank Math caches the sitemap. Clear it once per plugin version, so a
	 * deploy that changes what the sitemap holds shows up without a manual
	 * settings save.
	 */
	public static function refresh_sitemap_cache(): void {
		if ( OOMPH_CORE_VERSION === get_option( 'oomph_core_sitemap_version' ) ) {
			return;
		}
		if ( ! class_exists( '\RankMath\Sitemap\Cache' ) || ! method_exists( '\RankMath\Sitemap\Cache', 'invalidate_storage' ) ) {
			return;
		}
		\RankMath\Sitemap\Cache::invalidate_storage();
		update_option( 'oomph_core_sitemap_version', OOMPH_CORE_VERSION, false );
	}

	/* ---------------------------------------------------------------- */
	/* The stray archive                                                  */
	/* ---------------------------------------------------------------- */

	/** True on /category/uncategorized/ (whatever the default category is called). */
	private static function is_uncategorized_archive(): bool {
		if ( ! is_category() ) {
			return false;
		}
		$default = (int) get_option( 'default_category' );
		return $default > 0 && is_category( $default );
	}

	/**
	 * @param array<string,mixed> $robots
	 * @return array<string,mixed>
	 */
	public static function uncategorized_robots( array $robots ): array {
		if ( self::is_uncategorized_archive() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
		return $robots;
	}

	/**
	 * Rank Math writes its own robots tag; give it the same instruction.
	 *
	 * @param mixed $robots
	 * @return mixed
	 */
	public static function uncategorized_robots_rank_math( $robots ) {
		if ( is_array( $robots ) && self::is_uncategorized_archive() ) {
			$robots['index'] = 'noindex';
		}
		return $robots;
	}

	/* ---------------------------------------------------------------- */
	/* llms.txt                                                           */
	/* ---------------------------------------------------------------- */

	public static function serve_llms(): void {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return; // No request context (e.g. WP-CLI).
		}
		$uri = strtok( (string) $_SERVER['REQUEST_URI'], '?' );
		if ( ! is_string( $uri ) || '/llms.txt' !== rtrim( $uri, '/' ) ) {
			return;
		}
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo self::llms_content(); // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	}

	/**
	 * One published page as a list line, or '' when it is not there to link.
	 *
	 * @param string $slug Page slug.
	 * @param string $desc One sentence after the colon.
	 */
	private static function page_line( string $slug, string $desc ): string {
		$p = get_page_by_path( $slug );
		if ( ! $p || 'publish' !== get_post_status( $p ) ) {
			return '';
		}
		return '- [' . get_the_title( $p ) . '](' . get_permalink( $p ) . '): ' . $desc;
	}

	public static function llms_content(): string {
		$out     = array();
		$advisor = Advisor::name();

		$out[] = '# Oomph Travel';
		$out[] = '';
		$out[] = '> Custom journeys, escorted tours, resort and villa stays and multi-generational trips, planned by one named advisor — ' . $advisor . ', a CLIA member based in Port Angeles, Washington. No planning fee; suppliers pay a commission that does not change your price. Premium and luxury cruises are planned by the same advisor at CruiseOomph (https://cruiseoomph.com).';
		$out[] = '';

		$ways = array_filter(
			array(
				self::page_line( 'custom-journeys', 'Independent, hand-planned trips by destination: the route, the hotels, the drivers and guides, booked and held by one person.' ),
				self::page_line( 'resorts-and-villas', 'Resort stays, villas and private homes, and the hotels inside custom trips, vetted and booked by the advisor.' ),
				self::page_line( 'multi-generational-travel-planning', 'Trips planned across two or three generations, around pace, mobility, dietary needs and room configurations.' ),
				self::page_line( 'cruise-planning', 'How cruise planning fits alongside land travel; the cruises themselves are found and booked at CruiseOomph.' ),
			)
		);
		if ( $ways ) {
			$out[] = '## Ways to travel';
			array_push( $out, ...$ways );
			$out[] = '';
		}

		$archive = get_post_type_archive_link( CPT_Tour::POST_TYPE );
		if ( $archive ) {
			$out[] = '## Escorted tours';
			$out[] = '- [Escorted tours](' . $archive . '): small-group and classic escorted departures from operators the advisor sells, with a fit note on each and the next dates.';
			$out[] = '';
		}

		$destinations = get_posts(
			array(
				'post_type'      => CPT_Destination::POST_TYPE,
				'post_status'    => 'publish',
				'numberposts'    => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		);
		if ( $destinations ) {
			$out[] = '## Destinations';
			$index = get_post_type_archive_link( CPT_Destination::POST_TYPE );
			if ( $index ) {
				$out[] = '- [All destinations](' . $index . ')';
			}
			foreach ( $destinations as $d ) {
				$excerpt = wp_strip_all_tags( (string) get_the_excerpt( $d ) );
				$out[]   = '- [' . get_the_title( $d ) . '](' . get_permalink( $d ) . ')' . ( '' !== $excerpt ? ': ' . $excerpt : '' );
			}
			$out[] = '';
		}

		$start = array_filter(
			array(
				self::page_line( 'start-planning', 'Two short steps to describe the trip; a reply within one business day.' ),
				self::page_line( 'travel-trends', 'The yearly Travel Trends guide, free by email.' ),
			)
		);
		if ( $start ) {
			$out[] = '## Start here';
			array_push( $out, ...$start );
			$out[] = '';
		}

		$posts = get_posts( array( 'numberposts' => 20, 'post_status' => 'publish' ) );
		if ( $posts ) {
			$out[] = '## Journal';
			foreach ( $posts as $post ) {
				$out[] = '- [' . get_the_title( $post ) . '](' . get_permalink( $post ) . '): ' . wp_strip_all_tags( (string) get_the_excerpt( $post ) );
			}
			$out[] = '';
		}

		$about = array_filter(
			array(
				self::page_line( 'about', 'Who plans the trips, and why.' ),
				self::page_line( 'client-stories', 'Four reviews from clients, in their own words.' ),
			)
		);
		if ( $about ) {
			$out[] = '## About';
			array_push( $out, ...$about );
			$out[] = '';
		}

		return implode( "\n", $out );
	}
}
