/**
 * CI fixture — prepares the container so the theme can be exercised.
 *
 * Run through `wp eval` from .github/workflows/ci.yml, base64-encoded so no
 * quoting survives the trip through wp-env. **No opening PHP tag and no
 * declare()**: eval() supplies the PHP context itself, and a strict_types
 * declaration is only legal as the first statement of a real file.
 *
 * Nothing here ships. It exists so the suite has something deterministic to
 * look at.
 *
 * 1. Publishes the seeded Italy destination and leaves the other ten as
 *    drafts, so the CPT rewrite and draft privacy are both testable.
 * 2. Creates one page per pattern worth guarding, with the pattern block as
 *    its only content. The tour card's fixed caption (D34) is a contractual
 *    rule, so it gets a test rather than a promise.
 *
 * Idempotent: re-running changes nothing.
 */

// 1. Publish Italy only.
$italy = get_page_by_path( 'italy', OBJECT, 'oomph_destination' );

if ( ! $italy instanceof WP_Post ) {
	WP_CLI::error( 'ci-seed: the italy destination is missing — did the seed command run?' );
}

if ( 'publish' !== $italy->post_status ) {
	wp_update_post(
		array(
			'ID'          => $italy->ID,
			'post_status' => 'publish',
		)
	);
	WP_CLI::log( 'ci-seed: published destination italy (#' . $italy->ID . ')' );
}

// 1b. Publish every operator and every tour but one, so the escorted tours
//     index, an operator page and a tour page all have records behind them,
//     and one draft tour proves drafts stay private.
foreach ( array( 'oomph_operator', 'oomph_tour' ) as $ci_type ) {
	$ci_records = get_posts(
		array(
			'post_type'   => $ci_type,
			'post_status' => 'draft',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	foreach ( $ci_records as $ci_id ) {
		if ( 'natgeo-andalusia' === get_post_field( 'post_name', $ci_id ) ) {
			continue; // Stays a draft: tests/ci/tours.spec.ts expects a 404.
		}
		wp_update_post( array( 'ID' => $ci_id, 'post_status' => 'publish' ) );
		WP_CLI::log( 'ci-seed: published ' . $ci_type . ' ' . get_post_field( 'post_name', $ci_id ) . ' (#' . $ci_id . ')' );
	}
}

// 2. One page per pattern under test.
$ci_pattern_pages = array(
	'pattern-card-tour'   => 'oomphtravel/card-tour',
	'pattern-card-way'    => 'oomphtravel/card-way-to-travel',
	'pattern-band-ticker' => 'oomphtravel/band-ticker',
);

foreach ( $ci_pattern_pages as $ci_slug => $ci_pattern ) {
	if ( get_page_by_path( $ci_slug, OBJECT, 'page' ) instanceof WP_Post ) {
		continue;
	}

	$ci_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Pattern check: ' . $ci_pattern,
			'post_name'    => $ci_slug,
			'post_content' => '<!-- wp:pattern {"slug":"' . $ci_pattern . '"} /-->',
		),
		true
	);

	if ( is_wp_error( $ci_id ) ) {
		WP_CLI::error( 'ci-seed: could not create ' . $ci_slug . ' — ' . $ci_id->get_error_message() );
	}

	WP_CLI::log( 'ci-seed: created /' . $ci_slug . '/ for ' . $ci_pattern );
}

WP_CLI::success( 'ci-seed: done' );
