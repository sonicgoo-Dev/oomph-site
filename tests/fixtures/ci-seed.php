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

// 3. The Journal (plan §6.13): a second advisor user and two posts, one by
//    each of them, in the Destinations category and tagged with Italy so the
//    article's byline, its author node and its destination card all have
//    something to show. The excerpt feeds the "At a glance" box.
$ci_amy = get_user_by( 'login', 'amy' );
if ( ! $ci_amy instanceof WP_User ) {
	$ci_amy_id = wp_insert_user(
		array(
			'user_login'   => 'amy',
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => 'amy@example.com',
			'display_name' => 'Amy Hempel',
			'first_name'   => 'Amy',
			'last_name'    => 'Hempel',
			'role'         => 'author',
		)
	);
	if ( is_wp_error( $ci_amy_id ) ) {
		WP_CLI::error( 'ci-seed: could not create the amy user — ' . $ci_amy_id->get_error_message() );
	}
	WP_CLI::log( 'ci-seed: created user amy (#' . $ci_amy_id . ')' );
} else {
	$ci_amy_id = (int) $ci_amy->ID;
}

$ci_cat = get_category_by_slug( 'destinations' );
$ci_cat_id = $ci_cat instanceof WP_Term ? (int) $ci_cat->term_id : (int) wp_create_category( 'Destinations' );

$ci_admins   = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ) );
$ci_admin_id = $ci_admins ? (int) $ci_admins[0] : 1;

$ci_posts = array(
	array(
		'post_name'    => 'ci-puglia-in-may',
		'post_title'   => 'Puglia in May: the week that works',
		'post_author'  => $ci_admin_id,
		'post_excerpt' => 'Three nights in Lecce, three in the Itria valley, one on the coast. Go in May, before the heat and after the rain.',
		'post_content' => '<p>When I drove the coast road south of Polignano a Mare in May 2025, the beach clubs were still putting out their chairs and the water was already warm enough.</p><h2>Where to base</h2><p>Lecce for the first three nights, then the Itria valley. Skip the second coast hotel.</p>',
	),
	array(
		'post_name'    => 'ci-what-to-pack-for-a-cruise-week',
		'post_title'   => 'What I pack for a week on a ship',
		'post_author'  => $ci_amy_id,
		'post_excerpt' => 'One carry-on, one checked bag, and the four things that are never in either of them.',
		'post_content' => '<p>Eric packs the night before. I pack the week before, and I have never once needed the shop on deck five.</p>',
	),
);
foreach ( $ci_posts as $ci_post ) {
	if ( get_page_by_path( $ci_post['post_name'], OBJECT, 'post' ) instanceof WP_Post ) {
		continue;
	}
	$ci_id = wp_insert_post(
		$ci_post + array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_category' => array( $ci_cat_id ),
			'tags_input'    => array( 'italy' ),
		),
		true
	);
	if ( is_wp_error( $ci_id ) ) {
		WP_CLI::error( 'ci-seed: could not create ' . $ci_post['post_name'] . ' — ' . $ci_id->get_error_message() );
	}
	WP_CLI::log( 'ci-seed: created post ' . $ci_post['post_name'] . ' (#' . $ci_id . ')' );
}

WP_CLI::success( 'ci-seed: done' );
