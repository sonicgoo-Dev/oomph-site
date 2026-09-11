<?php
/**
 * Title: Escorted tours
 * Slug: oomphtravel/tours-index
 * Inserter: no
 *
 * The escorted tours index, plan §6.5, rendered by
 * templates/archive-oomph_tour.html at /escorted-tours/. Hero, what an
 * escorted tour is and isn't, the operators (D25), every published tour
 * behind four filters that live in the URL (a filtered view is noindex),
 * how Eric matches a traveller to a tour, questions, and the invitation.
 *
 * The one primary button is the closing band's. The tour cards carry the
 * fixed availability caption (D34); no tour page carries dates (D35).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_hero      = oomphtravel_tours_hero_sources();
$ot_operators = oomphtravel_escorted_operator_cards();
$ot_options   = oomphtravel_tours_filter_options();
$ot_filters   = oomphtravel_tours_filters();
$ot_cards     = oomphtravel_tours_index_cards();
$ot_all       = count( oomphtravel_tour_ids() );
$ot_index     = get_post_type_archive_link( 'oomph_tour' );
$ot_filtered  = oomphtravel_tours_filtered();

$ot_steps = array(
	array(
		'title' => __( 'Tell me how you like to travel', 'oomphtravel' ),
		'body'  => __( 'Pace, group size, what included means to you, and what you would rather pay for yourself. Ten minutes on the phone is enough.', 'oomphtravel' ),
	),
	array(
		'title' => __( 'I narrow it to two', 'oomphtravel' ),
		'body'  => __( 'Not a list of forty. Two tours from two operators, with an honest note on what each one does better and what it skips.', 'oomphtravel' ),
	),
	array(
		'title' => __( 'I book it and stay on', 'oomphtravel' ),
		'body'  => __( 'Same price as booking direct. I hold the reservation, watch the details, and I am the person you call if anything changes.', 'oomphtravel' ),
	),
);

$ot_faq = array(
	array(
		'q' => __( 'Does booking through you cost more than booking direct?', 'oomphtravel' ),
		'a' => __( 'No. The operator pays me from its side, and its published price is the price you pay. What changes is who is looking after the booking.', 'oomphtravel' ),
	),
	array(
		'q' => __( 'What is the deposit, and when is the balance due?', 'oomphtravel' ),
		'a' => __( 'It varies by operator. Most take a few hundred dollars per person to hold a departure and the balance two to three months out. I confirm the exact terms before you commit to anything.', 'oomphtravel' ),
	),
	array(
		'q' => __( 'I travel on my own. Is a tour a good idea?', 'oomphtravel' ),
		'a' => __( 'Often the best one. A tour hands you company at dinner and someone driving. Single supplements vary a lot between operators and departures, and some waive them; ask and I will tell you where.', 'oomphtravel' ),
	),
	array(
		'q' => __( 'How much walking, and how early are the mornings?', 'oomphtravel' ),
		'a' => __( 'Every operator rates the pace of each trip, and I note it on the tour page. If mobility is a question, say so on the call and I will steer you to the trips that fit.', 'oomphtravel' ),
	),
	array(
		'q' => __( 'Do I need travel insurance?', 'oomphtravel' ),
		'a' => __( 'I recommend it on every tour, and some operators require proof of it. I will quote a policy alongside the booking so the two are decided together.', 'oomphtravel' ),
	),
);
?>
<div class="ot-tours">

	<?php /* 1. Hero: photo, place-first. */ ?>
	<section class="ot-dest-hero ot-dest-hero--short ot-tours-hero">
		<picture class="ot-dest-hero__picture">
			<source media="<?php echo esc_attr( $ot_hero['tall']['media'] ); ?>" srcset="<?php echo esc_attr( $ot_hero['tall']['srcset'] ); ?>" sizes="<?php echo esc_attr( $ot_hero['tall']['sizes'] ); ?>">
			<img
				src="<?php echo esc_url( $ot_hero['wide']['src'] ); ?>"
				srcset="<?php echo esc_attr( $ot_hero['wide']['srcset'] ); ?>"
				sizes="<?php echo esc_attr( $ot_hero['wide']['sizes'] ); ?>"
				width="<?php echo (int) $ot_hero['width']; ?>" height="<?php echo (int) $ot_hero['height']; ?>"
				fetchpriority="high" decoding="async"
				alt="<?php echo esc_attr( $ot_hero['alt'] ); ?>">
		</picture>
		<div class="ot-container ot-dest-hero__inner">
			<div class="ot-dest-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Escorted tours', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-dest-hero__title"><?php esc_html_e( 'Escorted tours, chosen for the way you travel.', 'oomphtravel' ); ?></h1>
			</div>
		</div>
	</section>

	<?php /* 2. Statement: what an escorted tour is and isn't. */ ?>
	<section class="ot-band ot-band--mist ot-tours-statement">
		<div class="ot-container">
			<div class="ot-prose">
				<p><?php esc_html_e( 'An escorted tour is a departure date, a tour director, a coach and a run of hotels, all arranged by an operator whose name you may already know. You unpack, someone else drives, and the museum queue is not your problem.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'It is not a private trip, and it is not slow. It suits first visits, long distances, places where driving is a chore, and anyone who wants company at dinner. If you want your own driver and afternoons off, a custom journey fits better, and I will say so.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'Booking through me costs the same as booking direct. The operator pays me; the price on its site is the price you pay. What you add is someone who has read the fine print, knows which departures sell out, and answers the phone after you have paid.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 3. Operators I work with (D25); NatGeo hands its voyages to CruiseOomph (D32). */ ?>
	<?php if ( $ot_operators ) : ?>
	<section class="ot-band ot-tours-operators">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Operators', 'oomphtravel' ), __( 'The operators I sell, and who each one suits.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--3">
				<?php foreach ( $ot_operators as $ot_card ) : ?>
					<?php echo oomphtravel_card_operator( $ot_card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 4. Tours: the grid, filtered by the URL. */ ?>
	<section class="ot-band ot-band--mist ot-tours-grid" id="tours">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Tours', 'oomphtravel' ), __( 'Every tour I have entered, and where it goes.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

			<?php if ( $ot_all > 0 ) : ?>
			<form class="ot-filters" method="get" action="<?php echo esc_url( $ot_index ); ?>#tours" aria-label="<?php esc_attr_e( 'Filter the tours', 'oomphtravel' ); ?>">
				<div class="ot-filters__field">
					<label class="ot-filters__label" for="ot-filter-destination"><?php esc_html_e( 'Destination', 'oomphtravel' ); ?></label>
					<select class="ot-filters__select" id="ot-filter-destination" name="destination">
						<option value=""><?php esc_html_e( 'Anywhere', 'oomphtravel' ); ?></option>
						<?php foreach ( $ot_options['destinations'] as $ot_slug => $ot_name ) : ?>
							<option value="<?php echo esc_attr( $ot_slug ); ?>" <?php selected( $ot_filters['destination'], $ot_slug ); ?>><?php echo esc_html( $ot_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ot-filters__field">
					<label class="ot-filters__label" for="ot-filter-operator"><?php esc_html_e( 'Operator', 'oomphtravel' ); ?></label>
					<select class="ot-filters__select" id="ot-filter-operator" name="operator">
						<option value=""><?php esc_html_e( 'Any operator', 'oomphtravel' ); ?></option>
						<?php foreach ( $ot_options['operators'] as $ot_slug => $ot_name ) : ?>
							<option value="<?php echo esc_attr( $ot_slug ); ?>" <?php selected( $ot_filters['operator'], $ot_slug ); ?>><?php echo esc_html( $ot_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if ( $ot_options['months'] ) : ?>
				<div class="ot-filters__field">
					<label class="ot-filters__label" for="ot-filter-month"><?php esc_html_e( 'Month', 'oomphtravel' ); ?></label>
					<select class="ot-filters__select" id="ot-filter-month" name="month">
						<option value=""><?php esc_html_e( 'Any month', 'oomphtravel' ); ?></option>
						<?php foreach ( $ot_options['months'] as $ot_n => $ot_name ) : ?>
							<option value="<?php echo (int) $ot_n; ?>" <?php selected( $ot_filters['month'], $ot_n ); ?>><?php echo esc_html( $ot_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>
				<div class="ot-filters__field">
					<label class="ot-filters__label" for="ot-filter-length"><?php esc_html_e( 'Length', 'oomphtravel' ); ?></label>
					<select class="ot-filters__select" id="ot-filter-length" name="length">
						<option value=""><?php esc_html_e( 'Any length', 'oomphtravel' ); ?></option>
						<?php foreach ( $ot_options['lengths'] as $ot_key => $ot_label ) : ?>
							<option value="<?php echo esc_attr( $ot_key ); ?>" <?php selected( $ot_filters['length'], $ot_key ); ?>><?php echo esc_html( $ot_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ot-filters__actions">
					<button class="ot-btn ot-btn--ghost ot-filters__submit" type="submit"><?php esc_html_e( 'Show tours', 'oomphtravel' ); ?></button>
					<?php if ( $ot_filtered ) : ?>
						<span class="ot-filters__clear"><?php echo oomphtravel_link( __( 'Clear', 'oomphtravel' ), $ot_index . '#tours', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></span>
					<?php endif; ?>
				</div>
			</form>

			<p class="ot-filters__count" aria-live="polite">
				<?php
				if ( $ot_filtered ) {
					echo esc_html( sprintf(
						/* translators: 1: tours shown, 2: tours in total */
						_n( '%1$d of %2$d tours', '%1$d of %2$d tours', count( $ot_cards ), 'oomphtravel' ),
						count( $ot_cards ),
						$ot_all
					) );
				} else {
					/* translators: %d: number of tours */
					echo esc_html( sprintf( _n( '%d tour', '%d tours', $ot_all, 'oomphtravel' ), $ot_all ) );
				}
				?>
			</p>
			<?php endif; ?>

			<?php if ( $ot_cards ) : ?>
				<div class="ot-grid ot-grid--3 ot-tours-grid__cards">
					<?php foreach ( $ot_cards as $ot_i => $ot_card ) : ?>
						<?php echo oomphtravel_card_tour( $ot_card, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<?php endforeach; ?>
				</div>
			<?php elseif ( $ot_all > 0 ) : ?>
				<p class="ot-tours-grid__empty"><?php esc_html_e( 'Nothing matches all of those at once. Loosen one filter, or ask me; the operators run more departures than I have entered here.', 'oomphtravel' ); ?></p>
			<?php else : ?>
				<p class="ot-tours-grid__empty"><?php esc_html_e( 'Tour pages are on their way. In the meantime, tell me where you have in mind and I will send you the two or three departures I would look at.', 'oomphtravel' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php /* 5. How I match you to a tour: three steps. */ ?>
	<section class="ot-band ot-tours-steps">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'How it works', 'oomphtravel' ), __( 'How I match you to a tour.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-steps">
				<?php foreach ( $ot_steps as $ot_i => $ot_step ) : ?>
					<li class="ot-steps__step">
						<span class="ot-steps__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $ot_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3 class="ot-steps__title"><?php echo esc_html( $ot_step['title'] ); ?></h3>
						<p class="ot-steps__body"><?php echo esc_html( $ot_step['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<?php /* 6. Questions. */ ?>
	<section class="ot-band ot-band--mist ot-tours-faq">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Questions', 'oomphtravel' ), __( 'What people ask before they book a tour.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-accordion">
				<?php foreach ( $ot_faq as $ot_qa ) : ?>
					<details class="ot-accordion__item ot-faq__item">
						<summary class="ot-accordion__summary">
							<span class="ot-accordion__title"><?php echo esc_html( $ot_qa['q'] ); ?></span>
							<span class="ot-accordion__marker" aria-hidden="true"></span>
						</summary>
						<p class="ot-accordion__body"><?php echo esc_html( $ot_qa['a'] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php /* 7. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( '', __( 'Ask Eric about departures.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
