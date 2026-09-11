<?php
/**
 * Title: Resorts & villas
 * Slug: oomphtravel/way-resorts-villas
 * Inserter: no
 *
 * Resorts & villas, plan §6.8, rendered by templates/page-resorts-and-villas.html
 * at /resorts-and-villas/. Hero; the three kinds of stay; what booking
 * through me adds, in two separate blocks because the hotel perks are
 * conditional (D40) and a private house has none; the five destinations;
 * how I choose a property; a client story (D37, D38); questions; the
 * invitation. No booking engine and no property logos, by decision.
 *
 * The two primary buttons are the hero's and the closing band's; both open
 * Start planning with the type pre-set to resort.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_hero     = oomphtravel_way_hero_sources( 'resorts' );
$ot_plan_url = oomphtravel_way_plan_url( 'resort' );
$ot_cards    = oomphtravel_destination_cards( array( 'hawaii', 'mexico', 'caribbean', 'italy', 'france' ) );
$ot_quotes   = oomphtravel_way_quotes( 'resorts' );
$ot_faq      = oomphtravel_way_faqs( 'resorts' );

$ot_kinds = array(
	array(
		'title' => __( 'Resort vacations', 'oomphtravel' ),
		'body'  => __( 'Hawaii, Mexico and the Caribbean, through Classic Vacations. Beachfront, family, adults-only, all-inclusive or not. I know which properties deliver on the second visit as well as the first.', 'oomphtravel' ),
	),
	array(
		'title' => __( 'Villas and private homes', 'oomphtravel' ),
		'body'  => __( 'Italy, France and Croatia, through a network of professionally managed homes. A house with a pool, a cook if you want one, and somebody local who is accountable for it.', 'oomphtravel' ),
	),
	array(
		'title' => __( 'The hotels inside your journey', 'oomphtravel' ),
		'body'  => __( 'The city and country hotels along a custom journey, chosen from the collection I have access to. Depending on the property and the rate, I can usually add something at check-in.', 'oomphtravel' ),
	),
);

$ot_criteria = array(
	array( __( 'Who runs it', 'oomphtravel' ), __( 'A property is its general manager and how long the staff stay. I ask who has been there five years.', 'oomphtravel' ) ),
	array( __( 'The room you will actually get', 'oomphtravel' ), __( 'Not the one in the photographs. Category, floor, which wing, and what the view is at the price you are paying.', 'oomphtravel' ) ),
	array( __( 'The walk to everything', 'oomphtravel' ), __( 'Beach, breakfast, the town if there is one. A fine resort in the wrong spot is a taxi bill.', 'oomphtravel' ) ),
	array( __( 'How it handles a bad day', 'oomphtravel' ), __( 'Rain, an illness, a cancelled flight. I want the property that has a plan, because sooner or later you will need it.', 'oomphtravel' ) ),
);
?>
<div class="ot-way ot-way--resorts">

	<?php /* 1. Hero: photo, place-first, with the primary button. */ ?>
	<?php echo oomphtravel_way_hero( $ot_hero, __( 'Resorts & villas', 'oomphtravel' ), __( 'Somewhere to stay put.', 'oomphtravel' ), __( 'One property, one week, unpacked once. The trip where the place does the work.', 'oomphtravel' ), __( 'Start planning', 'oomphtravel' ), $ot_plan_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

	<?php /* 2. Statement. */ ?>
	<section class="ot-band ot-band--mist ot-way-statement">
		<div class="ot-container">
			<div class="ot-prose">
				<p><?php esc_html_e( 'Not every trip needs a route. Some of the best weeks I plan are one property, unpacked once: a resort on Maui with the children, a house in the Tuscan hills with three generations, an all-inclusive on the Riviera Maya for the honeymoon that kept getting postponed.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'Staying put is a choice, and choosing the right place to stay put is most of the work. The strongest claim I will make is this: I have relationships with the suppliers, I can speak to the property before you arrive, and when a booking goes wrong someone answers the phone.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 3. Three kinds of stay. */ ?>
	<section class="ot-band ot-way-kinds">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Three kinds of stay', 'oomphtravel' ), __( 'What I book, and where.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-dest-ways__cols">
				<?php foreach ( $ot_kinds as $ot_i => $ot_kind ) : ?>
					<li class="ot-dest-way<?php echo 0 === $ot_i ? ' ot-dest-way--lead' : ''; ?>">
						<h3 class="ot-dest-way__title"><?php echo esc_html( $ot_kind['title'] ); ?></h3>
						<p class="ot-dest-way__body"><?php echo esc_html( $ot_kind['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php /* 4. What booking through me adds: hotels and villas kept apart (D40). */ ?>
	<section class="ot-band ot-band--mist ot-way-adds">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Booking through me', 'oomphtravel' ), __( 'What booking through me adds.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-way-adds__cols">
				<div class="ot-way-adds__block ot-way-adds__block--hotels">
					<h3 class="ot-way-adds__title"><?php esc_html_e( 'At the hotels', 'oomphtravel' ); ?></h3>
					<p><?php esc_html_e( 'At the hotels in my collection, I can usually add breakfast for two, an upgrade at check-in, and a credit to spend at the property, depending on the hotel, the rate, and what is free on the day.', 'oomphtravel' ); ?></p>
					<p><?php esc_html_e( 'I confirm what applies to your booking before you pay, so nothing on this page is a promise until it is in writing.', 'oomphtravel' ); ?></p>
				</div>
				<div class="ot-way-adds__block ot-way-adds__block--villas">
					<h3 class="ot-way-adds__title"><?php esc_html_e( 'At the villas', 'oomphtravel' ); ?></h3>
					<p><?php esc_html_e( 'Villas come through a network of professionally managed homes, so somebody is accountable for the property before you arrive and while you are there. That is not the same as a listing site, and it is the reason I use it.', 'oomphtravel' ); ?></p>
					<p><?php esc_html_e( 'Breakfast, room upgrades and property credits do not apply to a private house; there is no front desk to give them. What you get instead is a home that has been inspected, a named person to call, and a plan for the day the pool pump fails.', 'oomphtravel' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<?php /* 5. Where these stays are. */ ?>
	<?php if ( $ot_cards ) : ?>
	<section class="ot-band ot-way-destinations">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Destinations', 'oomphtravel' ), __( 'Where I book these stays.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--3">
				<?php foreach ( $ot_cards as $ot_card ) : ?>
					<?php echo oomphtravel_card_destination( $ot_card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 6. How I choose a property. */ ?>
	<section class="ot-band ot-band--mist ot-way-criteria">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Choosing', 'oomphtravel' ), __( 'How I choose a property.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-way-items ot-way-items--4">
				<?php foreach ( $ot_criteria as $ot_item ) : ?>
					<li class="ot-way-item">
						<h3 class="ot-way-item__title"><?php echo esc_html( $ot_item[0] ); ?></h3>
						<p class="ot-way-item__body"><?php echo esc_html( $ot_item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php /* 7. Client story (D37, D38). */ ?>
	<?php if ( $ot_quotes ) : ?>
	<section class="ot-band ot-dest-quote">
		<div class="ot-container ot-dest-quote__inner">
			<?php echo oomphtravel_quotation( (string) $ot_quotes[0][0], (string) ( $ot_quotes[0][1] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 8. Questions. */ ?>
	<section class="ot-band ot-band--mist ot-way-faq">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Questions', 'oomphtravel' ), __( 'What people ask before they book a stay.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<?php echo oomphtravel_way_faq_list( $ot_faq ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<?php /* 9. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( $ot_plan_url, __( 'Know where you want to stay put?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
