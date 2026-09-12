<?php
/**
 * The redirects the rebuild needs, in code so they deploy with it.
 *
 * Three pages moved (plan §4.2): the old Italy page, the old cruise service
 * page and the discovery call each have a successor, so the old address keeps
 * working. The old /contact address had a Rank Math rule pointing at a broken
 * URL; it now lands on Start planning too. And one courtesy line for the
 * cruise content that is deleted rather than redirected (D02, D43): anything
 * under /group-cruises/ goes to CruiseOomph's cruise search, tagged like every
 * other outbound link (plan §4.3), instead of a dead end.
 *
 * Nothing else that was deleted is redirected. /trip-quiz/, the old
 * /cruise-travel-trends/ page and the nine cruise articles return the theme's
 * 404 page, which is Eric's decision in the plan.
 *
 * The rules run before WordPress decides whether the address is a page, so
 * they hold whether or not the old page record is still in the database. Rank
 * Math's Redirections table must not repeat them.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Redirects {

	/** Where the cruise search lives on CruiseOomph. */
	private const CRUISEOOMPH_CRUISES = 'https://cruiseoomph.com/cruises/';

	public static function init(): void {
		// Priority 1: ahead of canonical redirects, Rank Math and the template.
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 1 );
	}

	/**
	 * Exact paths that moved, old → new, both relative to the site root.
	 *
	 * @return array<string,string>
	 */
	public static function moved(): array {
		return array(
			'/custom-italy-travel/'    => '/destinations/italy/',
			'/luxury-cruise-planning/' => '/cruise-planning/',
			'/discovery-call/'         => '/start-planning/',
			'/contact/'                => '/start-planning/',
		);
	}

	/**
	 * Path prefixes handed to CruiseOomph. Every address underneath goes to
	 * the same place; a sailing that was indexed two years ago cannot map to
	 * a page on the new site, so the cruise search is the honest landing.
	 *
	 * @return string[]
	 */
	public static function handed_off(): array {
		return array( '/group-cruises/' );
	}

	/**
	 * The target for a matched request, or '' when it needs none.
	 *
	 * Split out from the hook so it can be reasoned about without a request:
	 * the path in, the destination out.
	 *
	 * @param string $path  Request path, e.g. '/discovery-call/'.
	 * @param string $query Query string without the '?', kept on internal moves.
	 * @return string
	 */
	public static function target_for( string $path, string $query = '' ): string {
		$path = '/' . trim( $path, '/' );
		$path = '/' === $path ? '/' : $path . '/';
		$path = strtolower( $path );

		foreach ( self::moved() as $from => $to ) {
			if ( $path === $from ) {
				$url = home_url( $to );
				return '' !== $query ? $url . '?' . $query : $url;
			}
		}

		// $path always ends in '/', so '/group-cruises' and '/group-cruises/'
		// both read as the prefix itself, and '/group-cruises-x/' does not.
		foreach ( self::handed_off() as $prefix ) {
			if ( 0 === strpos( $path, $prefix ) ) {
				return self::cruiseoomph_cruises();
			}
		}

		return '';
	}

	/**
	 * The CruiseOomph cruise search, tagged. The theme's helper knows the
	 * staging origin and the tag format; the fallback builds the same tag so
	 * the plugin still does the right thing under another theme.
	 */
	private static function cruiseoomph_cruises(): string {
		if ( function_exists( 'oomphtravel_cruiseoomph_url' ) ) {
			return (string) \oomphtravel_cruiseoomph_url( '/cruises/', 'group-cruises' );
		}
		return add_query_arg(
			array(
				'utm_source'   => 'oomphtravel',
				'utm_medium'   => 'site',
				'utm_campaign' => 'group-cruises',
			),
			self::CRUISEOOMPH_CRUISES
		);
	}

	public static function maybe_redirect(): void {
		if ( is_admin() || is_preview() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read, compared, never echoed.
		if ( '' === $uri ) {
			return;
		}
		$path  = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );

		// A site in a subdirectory carries that prefix on every request.
		$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( '/' !== $base && 0 === strpos( $path, rtrim( $base, '/' ) ) ) {
			$path = substr( $path, strlen( rtrim( $base, '/' ) ) ) ?: '/';
		}

		$target = self::target_for( $path, $query );
		if ( '' === $target ) {
			return;
		}

		wp_redirect( $target, 301, 'oomph-travel-core' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- the only external target is the CruiseOomph origin above.
		exit;
	}
}
