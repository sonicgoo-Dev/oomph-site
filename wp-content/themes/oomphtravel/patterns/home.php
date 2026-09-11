<?php
/**
 * Title: Homepage
 * Slug: oomphtravel/home
 * Inserter: no
 *
 * The homepage, plan §6.1, in its thirteen sections. Rendered by
 * templates/front-page.html. Written as a PHP pattern, like CruiseOomph's
 * (D41), because five of the sections read real records: featured tours,
 * the latest Journal posts, the destination cards, and the two client
 * quotations.
 *
 * Copy rules that shape what is here:
 *  - Speaks first to couples marking a milestone (D08).
 *  - Nothing mentions a specific sailing, a cabin or a ship (plan §6.1).
 *  - One primary button per section; the page's two are the hero and the
 *    closing band. Everything else is ghost, a pill on a photo, or a link.
 *  - Client quotes are never reworded (D38). The two below are contiguous
 *    excerpts of verified reviews, trimmed for length only, chosen because
 *    they do not name a ship.
 *  - No square-bracket placeholder may reach this page (03-rules, and
 *    tests/ci/records.spec.ts checks the rendered homepage for one). Where
 *    the plan leaves copy to Eric, this file carries a draft in his voice,
 *    adapted from the old homepage he wrote, and the pull request lists
 *    every such line for him to change.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_img  = OOMPHTRAVEL_THEME_URI . 'assets/img/';
$ot_hero = oomphtravel_home_hero_sources();

/*
 * Destination cards (§6.1.4, tab 1): the six the plan names. When the
 * destination record exists and is published the card links to it and uses
 * its featured image; otherwise the link still points where the record will
 * live, and the picture slot is the grey rectangle.
 */
$ot_destinations = array();
foreach ( array(
	'italy'      => __( 'Italy', 'oomphtravel' ),
	'uk-ireland' => __( 'UK & Ireland', 'oomphtravel' ),
	'france'     => __( 'France', 'oomphtravel' ),
	'greece'     => __( 'Greece', 'oomphtravel' ),
	'croatia'    => __( 'Croatia & the Adriatic', 'oomphtravel' ),
	'hawaii'     => __( 'Hawaii', 'oomphtravel' ),
) as $ot_slug => $ot_name ) {
	$ot_card = array(
		'name' => $ot_name,
		'url'  => home_url( '/destinations/' . $ot_slug . '/' ),
	);
	$ot_post = post_type_exists( 'oomph_destination' ) ? get_page_by_path( $ot_slug, OBJECT, 'oomph_destination' ) : null;
	if ( $ot_post instanceof WP_Post && 'publish' === $ot_post->post_status ) {
		$ot_card['url'] = (string) get_permalink( $ot_post );
		$ot_thumb       = (int) get_post_thumbnail_id( $ot_post );
		if ( $ot_thumb ) {
			$ot_card['image_id']  = $ot_thumb;
			$ot_card['image_alt'] = (string) get_post_meta( $ot_thumb, '_wp_attachment_image_alt', true );
		}
	}
	$ot_destinations[] = $ot_card;
}

/*
 * Featured tours (§6.1.6): Tour records flagged "featured", newest first.
 * The section is hidden automatically with fewer than three published.
 */
$ot_tours = array();
if ( post_type_exists( 'oomph_tour' ) ) {
	$ot_tour_posts = get_posts(
		array(
			'post_type'        => 'oomph_tour',
			'post_status'      => 'publish',
			'numberposts'      => 3,
			'meta_key'         => 'featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);
	foreach ( $ot_tour_posts as $ot_tour ) {
		$ot_tours[] = oomphtravel_tour_card( (int) $ot_tour->ID );
	}
}

/*
 * Who I plan for (§6.1.5). Draft copy, adapted from the "Who I help" block
 * Eric wrote for the old homepage, with the cruise column replaced by the
 * plan's third audience.
 */
$ot_who = array(
	array(
		'title' => __( 'Couples marking a milestone', 'oomphtravel' ),
		'body'  => __( 'Anniversaries, retirements, the trip you’ve talked about for ten years. The kind that has to land, paced so neither of you comes home tired.', 'oomphtravel' ),
		'link'  => __( 'Custom journeys', 'oomphtravel' ),
		'url'   => home_url( '/custom-journeys/' ),
	),
	array(
		'title' => __( 'Families across three generations', 'oomphtravel' ),
		'body'  => __( 'Planned around the slowest walker and the pickiest eater. Rooms, pace, mobility, a plan B for weather. It isn’t one decision; it’s a hundred.', 'oomphtravel' ),
		'link'  => __( 'Multi-generational trips', 'oomphtravel' ),
		'url'   => home_url( '/multi-generational-travel-planning/' ),
	),
	array(
		'title' => __( 'Travelers done with doing it themselves', 'oomphtravel' ),
		'body'  => __( 'You’ve booked your own trips for years and you’d like the next one handled, start to finish, by someone who answers the phone.', 'oomphtravel' ),
		'link'  => __( 'Escorted tours', 'oomphtravel' ),
		'url'   => home_url( '/escorted-tours/' ),
	),
);

/*
 * Client stories (§6.1.9): two of the four verified reviews (D37), word for
 * word (D38). Both are trimmed to whole contiguous sentences that do not
 * name a ship, a cabin or a sailing, which is the homepage's rule.
 */
$ot_quotes = array(
	array(
		'quote'       => 'From the moment we booked, our travel agent made the entire process smooth and stress-free. Communication was excellent — every detail was clearly laid out in our itinerary, and everything went exactly as planned.',
		'attribution' => 'LCMurray · Port Angeles, WA · 2025',
	),
	array(
		'quote'       => 'What stood out most was his outstanding communication; he was always available to answer questions and kept us informed every step of the way. His expertise and personal touch made the entire process easy and stress-free.',
		'attribution' => 'Gary T. · Poulsbo, WA · 2025',
	),
);

$ot_credentials = array(
	__( 'CLIA', 'oomphtravel' ),
	__( 'Nexion / Travel Leaders Network', 'oomphtravel' ),
	__( 'Port Angeles, WA', 'oomphtravel' ),
);
?>
<div class="ot-home">

	<?php /* 1. Hero — full-width photograph, place first (D11). */ ?>
	<section class="ot-hero" aria-labelledby="ot-hero-title">
		<picture class="ot-hero__picture">
			<source media="<?php echo esc_attr( $ot_hero['tall']['media'] ); ?>" srcset="<?php echo esc_attr( $ot_hero['tall']['srcset'] ); ?>" sizes="<?php echo esc_attr( $ot_hero['tall']['sizes'] ); ?>" width="720" height="960">
			<img
				src="<?php echo esc_url( $ot_hero['wide']['src'] ); ?>"
				srcset="<?php echo esc_attr( $ot_hero['wide']['srcset'] ); ?>"
				sizes="<?php echo esc_attr( $ot_hero['wide']['sizes'] ); ?>"
				width="1280" height="853"
				fetchpriority="high" decoding="async"
				alt="<?php esc_attr_e( 'Varenna at dusk from above, its ochre and red houses lit along the point, with Lake Como and the mountains beyond.', 'oomphtravel' ); ?>">
		</picture>
		<div class="ot-container ot-hero__inner">
			<div class="ot-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Custom journeys · Escorted tours · Resorts & villas', 'oomphtravel' ), false, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<div class="ot-hero__headline">
					<h1 class="ot-hero__title ot-type-desktop-display" id="ot-hero-title"><?php esc_html_e( 'The trip you’ve been talking about.', 'oomphtravel' ); ?></h1>
					<?php echo oomphtravel_contrail( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				</div>
				<div class="ot-hero__actions">
					<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), home_url( '/start-planning/' ), 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				</div>
			</div>
		</div>
		<p class="ot-hero__caption"><?php esc_html_e( 'Varenna, Lake Como', 'oomphtravel' ); ?></p>
	</section>

	<?php /* 2. Place-name ticker takes the credential strip's position (D23). */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/band-ticker"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>

	<?php /* 3. Statement band. */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/band-statement"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>

	<?php /* 4. Tabbed card row: Where to / How to travel. */ ?>
	<section class="ot-band ot-home__doors">
		<div class="ot-container">
			<div class="ot-tabs" data-ot-tabs>
				<div class="ot-tabs__head">
					<?php echo oomphtravel_section_heading( __( 'Three doors', 'oomphtravel' ), __( 'Where to, and how to travel.', 'oomphtravel' ), 'h2', 'ot-tabs__heading' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<div class="ot-tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Browse by place or by kind of trip', 'oomphtravel' ); ?>">
						<button class="ot-tabs__tab" type="button" role="tab" id="ot-tab-where" aria-controls="ot-panel-where" aria-selected="true" tabindex="0"><?php esc_html_e( 'Where to', 'oomphtravel' ); ?></button>
						<button class="ot-tabs__tab" type="button" role="tab" id="ot-tab-how" aria-controls="ot-panel-how" aria-selected="false" tabindex="-1"><?php esc_html_e( 'How to travel', 'oomphtravel' ); ?></button>
					</div>
				</div>
				<div class="ot-tabs__panel" role="tabpanel" id="ot-panel-where" aria-labelledby="ot-tab-where" tabindex="0">
					<div class="ot-grid ot-grid--3 ot-grid--scroll">
						<?php foreach ( $ot_destinations as $ot_i => $ot_card ) : ?>
							<?php echo oomphtravel_card_destination( $ot_card, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						<?php endforeach; ?>
					</div>
					<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
				</div>
				<div class="ot-tabs__panel" role="tabpanel" id="ot-panel-how" aria-labelledby="ot-tab-how" tabindex="0">
					<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/card-way-to-travel"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>
				</div>
			</div>
		</div>
	</section>

	<?php /* 5. Who I plan for — three columns, no boxes. */ ?>
	<section class="ot-band ot-band--mist ot-who">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Who I plan for', 'oomphtravel' ), __( 'Three kinds of trip, three kinds of traveler.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-who__cols">
				<?php foreach ( $ot_who as $ot_col ) : ?>
					<div class="ot-who__col">
						<h3 class="ot-who__title"><?php echo esc_html( $ot_col['title'] ); ?></h3>
						<p class="ot-who__body"><?php echo esc_html( $ot_col['body'] ); ?></p>
						<?php echo oomphtravel_link( $ot_col['link'], $ot_col['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php /* 6. Featured tours — hidden with fewer than three published. */ ?>
	<?php if ( count( $ot_tours ) >= 3 ) : ?>
	<section class="ot-band ot-home__tours">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Escorted tours', 'oomphtravel' ), __( 'Three departures I’d put my own name on.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--3">
				<?php foreach ( $ot_tours as $ot_card ) : ?>
					<?php echo oomphtravel_card_tour( $ot_card, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'See all escorted tours', 'oomphtravel' ), home_url( '/escorted-tours/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 7. How it works — Discover · Design · Depart. */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/band-process"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>

	<?php /* 8. Advisor block — the credentials live here on the homepage (D23). */ ?>
	<section class="ot-band ot-advisor">
		<div class="ot-container ot-advisor__inner">
			<div class="ot-advisor__copy">
				<?php echo oomphtravel_section_heading( __( 'One advisor', 'oomphtravel' ), __( 'Hi, I’m Eric.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<p class="ot-advisor__body"><?php esc_html_e( 'I plan custom journeys, escorted tours and resort stays for clients who want one named advisor across the whole trip, from the first call to the last flight home. Based in Port Angeles, Washington.', 'oomphtravel' ); ?></p>
				<p class="ot-advisor__with"><?php esc_html_e( 'with Amy Hempel, advisor', 'oomphtravel' ); ?></p>
				<ul class="ot-advisor__credentials" aria-label="<?php esc_attr_e( 'Credentials', 'oomphtravel' ); ?>">
					<?php foreach ( $ot_credentials as $ot_credential ) : ?>
						<li><?php echo esc_html( $ot_credential ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php echo oomphtravel_link( __( 'Read my story', 'oomphtravel' ), home_url( '/about/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
			<figure class="ot-advisor__portrait">
				<img
					src="<?php echo esc_url( $ot_img . 'advisor-eric-720.webp' ); ?>"
					srcset="<?php echo esc_attr( $ot_img . 'advisor-eric-360.webp 360w, ' . $ot_img . 'advisor-eric-720.webp 720w' ); ?>"
					sizes="(min-width: 1024px) 480px, (min-width: 768px) 40vw, 100vw"
					width="720" height="552" loading="lazy" decoding="async"
					alt="<?php esc_attr_e( 'Eric Hempel, travel advisor at Oomph Travel.', 'oomphtravel' ); ?>">
			</figure>
		</div>
	</section>

	<?php /* 9. Client stories — two quotations side by side. */ ?>
	<section class="ot-band ot-band--mist ot-stories">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Client stories', 'oomphtravel' ), __( 'In their words.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-stories__quotes">
				<?php foreach ( $ot_quotes as $ot_quote ) : ?>
					<?php echo oomphtravel_quotation( $ot_quote['quote'], $ot_quote['attribution'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'More client stories', 'oomphtravel' ), home_url( '/client-stories/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>

	<?php /* 10. Cruises, on their own site. */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/band-cruiseoomph"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>

	<?php /* 11. Journal — the pattern renders nothing below three published posts, and then the band goes too. */ ?>
	<?php $ot_journal = trim( do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/card-journal"} /-->' ) ); ?>
	<?php if ( '' !== $ot_journal ) : ?>
	<section class="ot-band ot-home__journal">
		<?php echo $ot_journal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>
	</section>
	<?php endif; ?>

	<?php /* 12. Closing invitation. 13 is the footer template part. */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/band-closing"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>

</div>
