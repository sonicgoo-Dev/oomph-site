<?php
/**
 * Title: Cruise planning
 * Slug: oomphtravel/way-cruise-planning
 * Inserter: no
 *
 * Cruise planning, plan §6.10, rendered by templates/page-cruise-planning.html
 * at /cruise-planning/: the hand-off page. Cruises sell on CruiseOomph
 * (D01, D02); what stays here is the land around the ship. Hero on the one
 * ship image; three link cards into CruiseOomph; before and after the ship;
 * how I plan cruises; a client story (D37, D38); questions; the credential
 * strip (CLIA and Nexion only); the closing band with the CruiseOomph
 * lockup. Every outbound link carries the UTM tag.
 *
 * Three primary buttons, one per section: the hero's and the closing band's
 * both go to CruiseOomph's cruise finder; the land section's opens Start
 * planning with the type pre-set to cruise-land.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_hero     = oomphtravel_way_hero_sources( 'cruise' );
$ot_plan_url = oomphtravel_way_plan_url( 'cruise-land' );
$ot_quotes   = oomphtravel_way_quotes( 'cruise' );
$ot_faq      = oomphtravel_way_faqs( 'cruise' );
$ot_cruises  = oomphtravel_cruiseoomph_url( '/cruises/' );

$ot_links = array(
	array(
		'title' => __( 'Find a cruise', 'oomphtravel' ),
		'body'  => __( 'Every sailing I sell, by region, line and month.', 'oomphtravel' ),
		'label' => __( 'Browse sailings', 'oomphtravel' ),
		'url'   => $ot_cruises,
	),
	array(
		'title' => __( 'Cruise styles', 'oomphtravel' ),
		'body'  => __( 'Expedition, river, ultra-luxury, family: which kind of ship suits how you travel.', 'oomphtravel' ),
		'label' => __( 'Compare styles', 'oomphtravel' ),
		'url'   => oomphtravel_cruiseoomph_url( '/cruise-styles/' ),
	),
	array(
		'title' => __( 'Plan with Eric', 'oomphtravel' ),
		'body'  => __( 'Start a cruise conversation on the site built for it. Same advisor, same phone number.', 'oomphtravel' ),
		'label' => __( 'Start on CruiseOomph', 'oomphtravel' ),
		'url'   => oomphtravel_cruiseoomph_url( '/plan/' ),
	),
);

$ot_land = array(
	array( __( 'The Rule of Two', 'oomphtravel' ), __( 'Two nights in the port before you sail. The first covers a delayed flight; the second lets you reach the ship rested rather than relieved.', 'oomphtravel' ) ),
	array( __( 'After the ship', 'oomphtravel' ), __( 'A cruise ends at seven in the morning in a city worth more than a taxi to the airport. A Tuscan week after Civitavecchia, Vancouver after Alaska.', 'oomphtravel' ) ),
	array( __( 'Private transfers', 'oomphtravel' ), __( 'Airport to hotel, hotel to pier, pier to wherever is next. Booked to the ship’s actual times, not the brochure’s.', 'oomphtravel' ) ),
	array( __( 'Hotels at both ends', 'oomphtravel' ), __( 'The right hotel near the port, at a rate I can stand behind. Depending on the hotel and the rate, I can usually add breakfast or an upgrade.', 'oomphtravel' ) ),
);
?>
<div class="ot-way ot-way--cruise">

	<?php /* 1. Hero: the one ship image, primary to CruiseOomph. */ ?>
	<?php echo oomphtravel_way_hero( $ot_hero, __( 'Cruise planning', 'oomphtravel' ), __( 'Cruises live next door now.', 'oomphtravel' ), __( 'Every cruise I sell is on CruiseOomph, my sister site. What stays here is the land around the ship: the nights before, the days after, and the way the two join.', 'oomphtravel' ), __( 'Explore cruises', 'oomphtravel' ), $ot_cruises ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

	<?php /* 2. Three doors into CruiseOomph. */ ?>
	<section class="ot-band ot-band--mist ot-way-doors">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'CruiseOomph', 'oomphtravel' ), __( 'Three places to start next door.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-way-links">
				<?php foreach ( $ot_links as $ot_l ) : ?>
					<li class="ot-way-link">
						<h3 class="ot-way-link__title"><?php echo esc_html( $ot_l['title'] ); ?></h3>
						<p class="ot-way-link__body"><?php echo esc_html( $ot_l['body'] ); ?></p>
						<?php echo oomphtravel_link( $ot_l['label'], $ot_l['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php /* 3. Before and after the ship: the part that stays here. */ ?>
	<section class="ot-band ot-way-land">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Before and after the ship', 'oomphtravel' ), __( 'The cruise is the middle of the trip, not the whole trip.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-way-items ot-way-items--4">
				<?php foreach ( $ot_land as $ot_item ) : ?>
					<li class="ot-way-item">
						<h3 class="ot-way-item__title"><?php echo esc_html( $ot_item[0] ); ?></h3>
						<p class="ot-way-item__body"><?php echo esc_html( $ot_item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="ot-way-land__action"><?php echo oomphtravel_button( __( 'Plan the land around your cruise', 'oomphtravel' ), $ot_plan_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>

	<?php /* 4. Client story (D37, D38). */ ?>
	<?php if ( $ot_quotes ) : ?>
	<section class="ot-band ot-band--mist ot-dest-quote">
		<div class="ot-container ot-dest-quote__inner">
			<?php echo oomphtravel_quotation( (string) $ot_quotes[0][0], (string) ( $ot_quotes[0][1] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 5. How I plan cruises. */ ?>
	<section class="ot-band ot-way-how">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'The work', 'oomphtravel' ), __( 'How I plan cruises.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-prose ot-way-prose">
				<p><?php esc_html_e( 'Cabin selection is where a cruise is won or lost, and it is the part a booking engine cannot do. The same category on the same deck can mean a quiet veranda or a cabin under the pool deck’s six o’clock chairs. I read the deck plan before I read the price.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'I look at what is above, what is below and what is next door; whether the veranda is full depth or clipped by the ship’s curve; how far the walk is to the restaurant you will use twice a day; and whether a suite category costs more than the difference it makes. On some ships the second-highest category is the better buy.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'Then the rest: dining times, the shore days worth a private guide and the ports where the ship’s tour is fine, and the paperwork that trips people up. The booking itself happens on CruiseOomph. The advice is the same person, either side of the fence.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 6. Questions. */ ?>
	<section class="ot-band ot-band--mist ot-way-faq">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Questions', 'oomphtravel' ), __( 'What people ask before they book a cruise.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<?php echo oomphtravel_way_faq_list( $ot_faq ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<?php /* 7. Credentials: CLIA and Nexion only (plan §6.1.2). */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/credential-strip"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output. ?>

	<?php /* 8. Closing band: the CruiseOomph lockup and one primary button. */ ?>
	<section class="ot-band ot-band--navy ot-closing ot-way-closing">
		<div class="ot-container ot-closing__inner">
			<a class="ot-cruiseoomph__lockup ot-way-closing__lockup" href="<?php echo esc_url( oomphtravel_cruiseoomph_url( '/' ) ); ?>">
				<span class="ot-cruiseoomph__name"><?php esc_html_e( 'CruiseOomph', 'oomphtravel' ); ?></span>
				<span class="ot-eyebrow ot-eyebrow--muted ot-cruiseoomph__by"><?php esc_html_e( 'By Oomph Travel', 'oomphtravel' ); ?></span>
			</a>
			<h2 class="ot-closing__heading"><?php esc_html_e( 'Ready to look at sailings?', 'oomphtravel' ); ?></h2>
			<?php echo oomphtravel_button( __( 'Explore cruises', 'oomphtravel' ), $ot_cruises, 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<p class="ot-closing__note"><?php esc_html_e( 'Same advisor, same phone number, a different front door.', 'oomphtravel' ); ?></p>
		</div>
	</section>

</div>
