<?php
/**
 * CI fixture — prepares the container so the theme can be exercised.
 *
 * Run through `wp eval` from .github/workflows/ci.yml, base64-encoded so no
 * quoting survives the trip through wp-env. Nothing here ships; it exists so
 * the suite has something deterministic to look at.
 *
 * 1. Publishes the seeded Italy destination, leaving the other ten as drafts,
 *    so the CPT rewrite can be tested and draft privacy can be tested at the
 *    same time.
 * 2. Creates one page per pattern we want to guard, with the pattern block as
 *    its only content. The tour card's fixed caption (D34) is a contractual
 *    rule, so it gets a test rather than a promise.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

// 1. Publish Italy only.
$italy = get_page_by_path( 'italy', OBJECT, 'oomph_destination' );
if ( $italy instanceof WP_Post ) {
	wp_update_post(
		array(
			'ID'          => $italy->ID,
			'post_status' => 'publish',
		)
	);
	WP_CLI::log( 'ci-seed: published destination italy (#' . $italy->ID . ')' );
} else {
	WP_CLI::error( 'ci-seed: the italy destination is missing — did the seed command run?' );
}

// 2. One page per pattern under test.
$pattern_pages = array(
	'pattern-card-tour'   => 'oomphtravel/card-tour',
	'pattern-card-way'    => 'oomphtravel/card-way-to-travel',
	'pattern-band-ticker' => 'oomphtravel/band-ticker',
);

foreach ( $pattern_pages as $slug => $pattern ) {
	if ( get_page_by_path( $slug, OBJECT, 'page' ) instanceof WP_Post ) {
		continue;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Pattern check: ' . $pattern,
			'post_name'    => $slug,
			'post_content' => '<!-- wp:pattern {"slug":"' . $pattern . '"} /-->',
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( 'ci-seed: could not create ' . $slug . ' — ' . $id->get_error_message() );
	}
	WP_CLI::log( 'ci-seed: created /' . $slug . '/ for ' . $pattern );
}

WP_CLI::success( 'ci-seed: done' );
