<?php
/**
 * Title: Custom journeys
 * Slug: oomphtravel/way-custom-journeys
 * Inserter: no
 *
 * Custom journeys, plan §6.4, rendered by templates/page-custom-journeys.html
 * at /custom-journeys/. Hero; who this is for and who it isn't; what I
 * actually do; who I book through (D25, the two FIT suppliers; A&K is
 * escorted only and is not named here, D26); every published destination;
 * how it works, with the honest note on lead times; what it costs (D20, no
 * planning fee); two client stories (D37, D38); questions; the invitation.
 *
 * The two primary buttons are the hero's and the closing band's; both open
 * Start planning with the type pre-set to custom.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_hero     = oomphtravel_way_hero_sources( 'custom' );
$ot_plan_url = oomphtravel_way_plan_url( 'custom' );
$ot_cards    = oomphtravel_destination_cards( array( 'italy', 'uk-ireland', 'france', 'spain', 'portugal', 'greece', 'croatia', 'hawaii', 'mexico', 'caribbean' ) );
$ot_quotes   = oomphtravel_way_quotes( 'custom' );
$ot_faq      = oomphtravel_way_faqs( 'custom' );

$ot_for = array(
	__( 'You want an itinerary built around your pace and your interests, not a fixed group tour.', 'oomphtravel' ),
	__( 'You are marking something: an anniversary, a milestone birthday, a trip you have promised each other for years.', 'oomphtravel' ),
	__( 'You would rather have one advisor who has been to the place than a call centre.', 'oomphtravel' ),
);
$ot_not = array(
	__( 'You want the cheapest package and will book whatever is on sale.', 'oomphtravel' ),
	__( 'You have already built the whole trip and only need someone to click book.', 'oomphtravel' ),
	__( 'You want five countries in ten days. I plan slower trips than that.', 'oomphtravel' ),
);

$ot_do = array(
	array( __( 'Itinerary design', 'oomphtravel' ), __( 'One region at a time: the town worth three nights, the one worth an afternoon, and the drive between them done in daylight.', 'oomphtravel' ) ),
	array( __( 'Stays I have vetted', 'oomphtravel' ), __( 'Family-run hotels, agriturismi, city hotels on the right street. I steer you away from the ones that photograph better than they live.', 'oomphtravel' ) ),
	array( __( 'Drivers and guides', 'oomphtravel' ), __( 'The guide who makes a hill town make sense, and the driver who knows the back road when the coast road is jammed.', 'oomphtravel' ) ),
	array( __( 'Reservations', 'oomphtravel' ), __( 'The restaurants worth planning a day around, the museum entries, the train seats facing forward. Booked before you land.', 'oomphtravel' ) ),
	array( __( 'Pacing', 'oomphtravel' ), __( 'Mornings you will actually use, afternoons that are not a forced march. The plan bends to your energy, not the other way round.', 'oomphtravel' ) ),
	array( __( 'A plan B', 'oomphtravel' ), __( 'A strike, a festival, a closure. Every trip has one. Mine come with the second option already written down.', 'oomphtravel' ) ),
);

$ot_steps = array(
	array(
		'title' => __( 'A thirty-minute call', 'oomphtravel' ),
		'body'  => __( 'Where, when, who is coming, and what a good day looks like to you. I ask more questions than you expect.', 'oomphtravel' ),
	),
	array(
		'title' => __( 'A first draft, then a better one', 'oomphtravel' ),
		'body'  => __( 'A day-by-day outline with the stays named and priced. We go back and forth until the shape is right. Two rounds is normal.', 'oomphtravel' ),
	),
	array(
		'title' => __( 'I book it and stay on', 'oomphtravel' ),
		'body'  => __( 'Every reservation on one file, the documents in one place, and my number in your phone for the whole trip.', 'oomphtravel' ),
	),
);
?>
<div class="ot-way ot-way--custom">

	<?php /* 1. Hero: photo, place-first, with the primary button. */ ?>
	<?php echo oomphtravel_way_hero( $ot_hero, __( 'Custom journeys', 'oomphtravel' ), __( 'Independent travel, planned to the hour.', 'oomphtravel' ), __( 'Your own driver, your own guide, your own pace, and one person who has walked the plan before you.', 'oomphtravel' ), __( 'Start planning', 'oomphtravel' ), $ot_plan_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

	<?php /* 2. Who this is for, and who it isn't. */ ?>
	<section class="ot-band ot-band--mist ot-way-who">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Fit', 'oomphtravel' ), __( 'Who this is for, and who it isn’t.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-way-who__cols">
				<div class="ot-way-who__col ot-way-who__col--for">
					<h3 class="ot-way-who__title"><?php esc_html_e( 'This is for you if', 'oomphtravel' ); ?></h3>
					<ul class="ot-way-who__list">
						<?php foreach ( $ot_for as $ot_line ) : ?>
							<li><?php echo esc_html( $ot_line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="ot-way-who__col ot-way-who__col--not">
					<h3 class="ot-way-who__title"><?php esc_html_e( 'Probably not, if', 'oomphtravel' ); ?></h3>
					<ul class="ot-way-who__list">
						<?php foreach ( $ot_not as $ot_line ) : ?>
							<li><?php echo esc_html( $ot_line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<?php /* 3. What I actually do. */ ?>
	<section class="ot-band ot-way-do">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'The work', 'oomphtravel' ), __( 'What I actually do.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-way-items">
				<?php foreach ( $ot_do as $ot_item ) : ?>
					<li class="ot-way-item">
						<h3 class="ot-way-item__title"><?php echo esc_html( $ot_item[0] ); ?></h3>
						<p class="ot-way-item__body"><?php echo esc_html( $ot_item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="ot-way-note"><?php esc_html_e( 'And while you are away: one phone number for the whole trip. If something needs changing at nine at night, I am the one who changes it.', 'oomphtravel' ); ?></p>
		</div>
	</section>

	<?php /* 4. Who I book through (D25). */ ?>
	<section class="ot-band ot-band--mist ot-way-suppliers">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Suppliers', 'oomphtravel' ), __( 'Who I book through.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-prose ot-way-prose">
				<p>
					<?php
					echo wp_kses_post( sprintf(
						/* translators: 1: Avanti Destinations, linked when its page is published; 2: Classic Vacations, likewise */
						__( 'Most of what goes into a custom journey is booked through two suppliers I have worked with for years. %1$s covers Europe, Latin America and Asia: hotels, rail, transfers and private guiding, all on one file. %2$s handles the resort side and the hotels inside a journey, with the property relationships a booking site does not have.', 'oomphtravel' ),
						oomphtravel_operator_name_link( 'avanti-destinations', 'Avanti Destinations' ),
						oomphtravel_operator_name_link( 'classic-vacations', 'Classic Vacations' )
					) );
					?>
				</p>
				<p><?php esc_html_e( 'Why that beats a booking site: when a hotel overbooks or a driver does not turn up, a supplier answers my call within the hour and someone is accountable. A booking site gives you a chat window.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 5. Where these trips go: every published destination. */ ?>
	<?php if ( $ot_cards ) : ?>
	<section class="ot-band ot-way-destinations">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Destinations', 'oomphtravel' ), __( 'Where I plan these trips.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--4">
				<?php foreach ( $ot_cards as $ot_card ) : ?>
					<?php echo oomphtravel_card_destination( $ot_card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 6. How it works: three steps and the honest note on lead times. */ ?>
	<section class="ot-band ot-band--mist ot-way-steps">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'How it works', 'oomphtravel' ), __( 'From a first call to the last flight home.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-steps">
				<?php foreach ( $ot_steps as $ot_i => $ot_step ) : ?>
					<li class="ot-steps__step">
						<span class="ot-steps__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $ot_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3 class="ot-steps__title"><?php echo esc_html( $ot_step['title'] ); ?></h3>
						<p class="ot-steps__body"><?php echo esc_html( $ot_step['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ot-way-note"><?php esc_html_e( 'One honest note on timing. Peak seasons, which means spring and autumn in Italy and France and summer in Greece and Croatia, need six to nine months. The best guides and the smaller hotels are taken by the people who started early.', 'oomphtravel' ); ?></p>
		</div>
	</section>

	<?php /* 7. What it costs (D20). */ ?>
	<section class="ot-band ot-way-fees">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Fees', 'oomphtravel' ), __( 'What it costs.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-prose ot-way-prose">
				<p><?php esc_html_e( 'There is no planning fee. The hotels, suppliers and operators I book pay me a commission from their side, and their price is the price you pay. What you add is the planning, the vetting and the person on the phone, at no added cost.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'If I ever recommend something that pays me nothing, which happens, I will still recommend it.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 8. Client stories (D37, D38). */ ?>
	<?php if ( $ot_quotes ) : ?>
	<section class="ot-band ot-band--mist ot-way-stories">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Client stories', 'oomphtravel' ), __( 'In their words.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-way-items ot-way-items--2 ot-way-stories__quotes">
				<?php foreach ( $ot_quotes as $ot_q ) : ?>
					<?php echo oomphtravel_quotation( (string) $ot_q[0], (string) ( $ot_q[1] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'More client stories', 'oomphtravel' ), home_url( '/client-stories/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 9. Questions. */ ?>
	<section class="ot-band ot-way-faq">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Questions', 'oomphtravel' ), __( 'What people ask before we start.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<?php echo oomphtravel_way_faq_list( $ot_faq ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<?php /* 10. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( $ot_plan_url, __( 'Ready to plan something of your own?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
