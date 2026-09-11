<?php
/**
 * Title: Tour page
 * Slug: oomphtravel/tour
 * Inserter: no
 *
 * The tour template, plan §6.7, rendered by templates/single-oomph_tour.html
 * for every Tour record: the tour card, expanded. Hero with the operator,
 * nights and destination, the from-price where there is one (D36) and the
 * fixed availability caption (D34); at a glance; the day-by-day accordion;
 * Eric's note; related tours; the invitation. No departures table and no
 * availability status, by decision (D35).
 *
 * The two primary buttons are the hero's and the closing band's; both open
 * Start planning with the tour pre-set.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_id      = (int) get_queried_object_id();
$ot_t       = oomphtravel_tour_data( $ot_id );
$ot_hero    = oomphtravel_tour_hero_sources( $ot_id );
$ot_related = oomphtravel_related_tours( $ot_id );
$ot_index   = get_post_type_archive_link( 'oomph_tour' );

$ot_meta = array_filter(
	array(
		$ot_t['operator'],
		/* translators: %d: nights */
		$ot_t['nights'] > 0 ? sprintf( _n( '%d night', '%d nights', $ot_t['nights'], 'oomphtravel' ), $ot_t['nights'] ) : '',
		$ot_t['destination'],
	),
	'strlen'
);

$ot_glance = array_filter(
	array(
		__( 'Starts', 'oomphtravel' )     => $ot_t['start_city'],
		__( 'Ends', 'oomphtravel' )       => $ot_t['end_city'],
		__( 'Group size', 'oomphtravel' ) => $ot_t['group_size'],
		__( 'Pace', 'oomphtravel' )       => $ot_t['pace'],
	),
	'strlen'
);
?>
<article class="ot-tour">

	<?php /* 1. Hero: the tour photo when there is one, else a navy band. */ ?>
	<section class="ot-dest-hero ot-tour-hero<?php echo $ot_hero ? '' : ' ot-dest-hero--bare'; ?>">
		<?php if ( $ot_hero ) : ?>
			<picture class="ot-dest-hero__picture">
				<img
					src="<?php echo esc_url( $ot_hero['wide']['src'] ); ?>"
					srcset="<?php echo esc_attr( $ot_hero['wide']['srcset'] ); ?>"
					sizes="<?php echo esc_attr( $ot_hero['wide']['sizes'] ); ?>"
					width="<?php echo (int) $ot_hero['width']; ?>" height="<?php echo (int) $ot_hero['height']; ?>"
					fetchpriority="high" decoding="async"
					alt="<?php echo esc_attr( $ot_hero['alt'] ); ?>">
			</picture>
		<?php endif; ?>
		<div class="ot-container ot-dest-hero__inner">
			<div class="ot-dest-hero__copy ot-tour-hero__copy">
				<?php if ( $ot_meta ) : ?>
					<?php echo oomphtravel_eyebrow( implode( ' · ', $ot_meta ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endif; ?>
				<h1 class="ot-dest-hero__title"><?php echo esc_html( $ot_t['title'] ); ?></h1>
				<?php if ( '' !== $ot_t['blurb'] ) : ?>
					<p class="ot-tour-hero__lead"><?php echo esc_html( $ot_t['blurb'] ); ?></p>
				<?php endif; ?>
				<div class="ot-tour-hero__price">
					<?php if ( null !== $ot_t['from_price'] ) : ?>
						<p class="ot-tour-price">
							<span class="ot-tour-price__from"><?php esc_html_e( 'from', 'oomphtravel' ); ?></span>
							<span class="ot-tour-price__amount"><?php echo esc_html( '$' . number_format( $ot_t['from_price'] ) ); ?></span>
							<span class="ot-tour-price__per"><?php esc_html_e( 'per person, double occupancy', 'oomphtravel' ); ?></span>
						</p>
						<?php if ( '' !== $ot_t['price_note'] ) : ?>
							<p class="ot-tour-price__note"><?php echo esc_html( $ot_t['price_note'] ); ?></p>
						<?php endif; ?>
					<?php endif; ?>
					<?php // Fixed caption, part of the component (D34); never dates (D35). ?>
					<p class="ot-tour-price__caption"><?php esc_html_e( 'Dates and availability confirmed on request.', 'oomphtravel' ); ?></p>
				</div>
				<?php echo oomphtravel_button( __( 'Ask Eric about this tour', 'oomphtravel' ), $ot_t['ask_url'], 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		</div>
	</section>

	<?php /* 2. At a glance. */ ?>
	<?php if ( $ot_glance || $ot_t['inclusions'] || '' !== $ot_t['brochure_link'] ) : ?>
	<section class="ot-band ot-tour-glance">
		<div class="ot-container ot-tour-glance__inner">
			<?php echo oomphtravel_section_heading( __( 'At a glance', 'oomphtravel' ), __( 'The shape of the trip.', 'oomphtravel' ), 'h2', 'ot-tour-glance__heading' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-tour-glance__cols">
				<?php if ( $ot_glance ) : ?>
					<dl class="ot-facts">
						<?php foreach ( $ot_glance as $ot_term => $ot_value ) : ?>
							<div class="ot-facts__row">
								<dt class="ot-facts__term"><?php echo esc_html( $ot_term ); ?></dt>
								<dd class="ot-facts__value"><?php echo esc_html( $ot_value ); ?></dd>
							</div>
						<?php endforeach; ?>
						<?php if ( '' !== $ot_t['operator_url'] ) : ?>
							<div class="ot-facts__row">
								<dt class="ot-facts__term"><?php esc_html_e( 'Operator', 'oomphtravel' ); ?></dt>
								<dd class="ot-facts__value"><a href="<?php echo esc_url( $ot_t['operator_url'] ); ?>"><?php echo esc_html( $ot_t['operator'] ); ?></a></dd>
							</div>
						<?php endif; ?>
					</dl>
				<?php endif; ?>
				<?php if ( $ot_t['inclusions'] ) : ?>
					<div class="ot-tour-glance__included">
						<h3 class="ot-tour-glance__subhead"><?php esc_html_e( 'What is included', 'oomphtravel' ); ?></h3>
						<ul class="ot-facts__list">
							<?php foreach ( $ot_t['inclusions'] as $ot_line ) : ?>
								<li><?php echo esc_html( $ot_line ); ?></li>
							<?php endforeach; ?>
						</ul>
						<?php if ( '' !== $ot_t['brochure_link'] ) : ?>
							<p class="ot-tour-glance__brochure">
								<?php echo oomphtravel_link( __( 'The operator’s own page for this trip', 'oomphtravel' ), $ot_t['brochure_link'], true, array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
							</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 3. Day by day: an accordion, with the overnight stop. */ ?>
	<?php if ( $ot_t['itinerary'] ) : ?>
	<section class="ot-band ot-band--mist ot-dest-itinerary ot-tour-itinerary">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Day by day', 'oomphtravel' ), __( 'Where each day goes.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-accordion ot-tour-itinerary__days">
				<?php foreach ( $ot_t['itinerary'] as $ot_day ) : ?>
					<li>
						<details class="ot-accordion__item">
							<summary class="ot-accordion__summary">
								<span class="ot-accordion__label">
									<?php
									/* translators: %s: day number */
									echo esc_html( '' !== $ot_day['day'] ? sprintf( __( 'Day %s', 'oomphtravel' ), $ot_day['day'] ) : '' );
									?>
								</span>
								<span class="ot-accordion__title"><?php echo esc_html( $ot_day['title'] ); ?></span>
								<span class="ot-accordion__marker" aria-hidden="true"></span>
							</summary>
							<div class="ot-accordion__body">
								<?php if ( '' !== $ot_day['text'] ) : ?>
									<p class="ot-tour-itinerary__text"><?php echo esc_html( $ot_day['text'] ); ?></p>
								<?php endif; ?>
								<?php if ( '' !== $ot_day['overnight'] ) : ?>
									<p class="ot-tour-itinerary__overnight">
										<span class="ot-tour-itinerary__overnight-label"><?php esc_html_e( 'Overnight', 'oomphtravel' ); ?></span>
										<?php echo esc_html( $ot_day['overnight'] ); ?>
									</p>
								<?php endif; ?>
							</div>
						</details>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ot-dest-itinerary__note"><?php esc_html_e( 'As the operator publishes it. Small changes between seasons are normal; I confirm the current version before you book.', 'oomphtravel' ); ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 4. Eric's note: why this tour, who it suits. */ ?>
	<?php if ( '' !== trim( wp_strip_all_tags( $ot_t['erics_note'] ) ) ) : ?>
	<section class="ot-band ot-tour-note">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Eric’s note', 'oomphtravel' ), __( 'Why this one, and who it suits.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-prose ot-tour-note__body">
				<?php echo wp_kses_post( wpautop( $ot_t['erics_note'] ) ); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 5. Related tours: same destination, then same operator. */ ?>
	<?php if ( $ot_related ) : ?>
	<section class="ot-band ot-band--mist ot-tour-related">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Related', 'oomphtravel' ), __( 'Tours in the same direction.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--3">
				<?php foreach ( $ot_related as $ot_card ) : ?>
					<?php echo oomphtravel_card_tour( $ot_card, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All escorted tours', 'oomphtravel' ), $ot_index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 6. Closing invitation, Start planning pre-set to this tour. */ ?>
	<?php echo oomphtravel_closing_band( $ot_t['ask_url'], __( 'Want the current dates for this tour?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</article>
