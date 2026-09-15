<?php
/**
 * Title: Multi-generational trips
 * Slug: oomphtravel/way-multi-generational
 * Inserter: no
 *
 * Multi-generational trips, plan §6.9, rendered by
 * templates/page-multi-generational-travel-planning.html at
 * /multi-generational-travel-planning/ (the existing page, redesigned in
 * place; its old body is not shown). Hero; who this is for; what I plan
 * around; where these trips go, with the ship hand-off to CruiseOomph;
 * questions; the invitation. No client story: none of the four verified
 * reviews (D37) describes a family trip, so the section shows nothing.
 *
 * The two primary buttons are the hero's and the closing band's; both open
 * Start planning with the type pre-set to multi-gen.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_hero     = oomphtravel_way_hero_sources( 'multigen' );
$ot_plan_url = oomphtravel_way_plan_url( 'multi-gen' );
$ot_cards    = oomphtravel_destination_cards( array( 'italy', 'uk-ireland', 'hawaii', 'caribbean' ) );
$ot_quotes   = oomphtravel_way_quotes( 'multigen' );
$ot_faq      = oomphtravel_way_faqs( 'multigen' );

$ot_for = array(
	__( 'You are planning a trip across two or three generations and want it to actually land.', 'oomphtravel' ),
	__( 'Someone in the group has mobility or dietary needs the plan has to respect.', 'oomphtravel' ),
	__( 'You are marking a milestone: a big anniversary, a reunion, the gathering that happens once a decade.', 'oomphtravel' ),
);
$ot_not = array(
	__( 'Everyone travels the same way and a standard package would do.', 'oomphtravel' ),
	__( 'You have already booked the rooms and flights and only need them confirmed.', 'oomphtravel' ),
	__( 'You want the lowest price above all else, even if the trip is a strain to take.', 'oomphtravel' ),
);

$ot_around = array(
	array( __( 'Pace', 'oomphtravel' ), __( 'The slowest walker sets it. One thing in the morning, a proper lunch, and an afternoon that can be skipped without anyone feeling they missed the trip.', 'oomphtravel' ) ),
	array( __( 'Mobility', 'oomphtravel' ), __( 'Step-free routes, ground-floor rooms, transfers that take a wheelchair, and a property briefed before you arrive rather than surprised when you do.', 'oomphtravel' ) ),
	array( __( 'Food', 'oomphtravel' ), __( 'Allergies and restrictions sent to every kitchen in advance, so dinner is not a negotiation each night.', 'oomphtravel' ) ),
	array( __( 'Rooms', 'oomphtravel' ), __( 'Connecting rooms, a suite for the grandparents near the family, a house with enough bathrooms. Held together while there is still inventory to hold.', 'oomphtravel' ) ),
	array( __( 'A plan B', 'oomphtravel' ), __( 'A rest afternoon, a wet-weather option, and a way for the group to split for a few hours without anyone feeling left behind.', 'oomphtravel' ) ),
	array( __( 'The occasion', 'oomphtravel' ), __( 'The anniversary dinner, the surprise, the group photograph at the right hour. The small things that make it the trip they talk about.', 'oomphtravel' ) ),
);
?>
<div class="ot-way ot-way--multigen">

	<?php /* 1. Hero: photo, place-first, with the primary button. */ ?>
	<?php echo oomphtravel_way_hero( $ot_hero, __( 'Multi-generational trips', 'oomphtravel' ), __( 'The trip that works for everyone, planned around the slowest walker.', 'oomphtravel' ), __( 'Grandparents, parents, teenagers and a toddler, one itinerary. A hundred small decisions, made before you leave.', 'oomphtravel' ), __( 'Start planning', 'oomphtravel' ), $ot_plan_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

	<?php /* 2. Statement. */ ?>
	<section class="ot-band ot-band--mist ot-way-statement">
		<div class="ot-container">
			<div class="ot-prose">
				<p><?php esc_html_e( 'Multi-generational travel is a hundred small decisions, not one big one. Pace, mobility, food, rooms that connect, and the day someone needs to rest. I plan around the constraints first, then the highlights, so the week works for the grandparents, the parents, the teenagers and the toddler.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'The details that derail a group are small: a restaurant that cannot seat ten, a transfer that does not fit a wheelchair, a room with stairs nobody mentioned. I find those before you do, and when the day shifts anyway, it is one phone call instead of fifteen people on a street corner deciding what to do.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 3. Who this is for, and who it isn't. */ ?>
	<section class="ot-band ot-way-who">
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

	<?php /* 4. What I plan around. */ ?>
	<section class="ot-band ot-band--mist ot-way-do">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'The work', 'oomphtravel' ), __( 'What I plan around.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-way-items">
				<?php foreach ( $ot_around as $ot_item ) : ?>
					<li class="ot-way-item">
						<h3 class="ot-way-item__title"><?php echo esc_html( $ot_item[0] ); ?></h3>
						<p class="ot-way-item__body"><?php echo esc_html( $ot_item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php /* 5. Where these trips go, and the ship hand-off (D32-style). */ ?>
	<?php if ( $ot_cards ) : ?>
	<section class="ot-band ot-way-destinations">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Destinations', 'oomphtravel' ), __( 'Where these trips go well.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--4">
				<?php foreach ( $ot_cards as $ot_card ) : ?>
					<?php echo oomphtravel_card_destination( $ot_card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<aside class="ot-band ot-band--mist-deep ot-dest-ship">
		<div class="ot-container ot-dest-ship__inner">
			<p class="ot-dest-ship__text">
				<span class="ot-dest-ship__lead"><?php esc_html_e( 'A ship works for a family too: everyone together at dinner, off doing their own thing by day.', 'oomphtravel' ); ?></span>
				<?php echo oomphtravel_link( __( 'Family sailings are on CruiseOomph', 'oomphtravel' ), oomphtravel_cruiseoomph_url( '/cruises/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</p>
		</div>
	</aside>

	<?php /* 6. Client story, or nothing (D37). */ ?>
	<?php if ( $ot_quotes ) : ?>
	<section class="ot-band ot-dest-quote">
		<div class="ot-container ot-dest-quote__inner">
			<?php echo oomphtravel_quotation( (string) $ot_quotes[0][0], (string) ( $ot_quotes[0][1] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 7. Questions. */ ?>
	<section class="ot-band ot-way-faq">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Questions', 'oomphtravel' ), __( 'What families ask before we start.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<?php echo oomphtravel_way_faq_list( $ot_faq ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<?php /* 8. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( $ot_plan_url, __( 'Planning for the whole family?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
