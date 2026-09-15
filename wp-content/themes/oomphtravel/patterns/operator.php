<?php
/**
 * Title: Operator page
 * Slug: oomphtravel/operator
 * Inserter: no
 *
 * The operator template, plan §6.6, rendered by
 * templates/single-oomph_operator.html for every Operator record. One
 * template, two kinds: an escorted tour operator shows its tour cards; a
 * FIT supplier shows the destinations and trip types it supports instead,
 * and links on to Custom journeys or Resorts & villas. National Geographic
 * Expeditions adds the CruiseOomph hand-off for its voyages (D32).
 * Abercrombie & Kent is escorted only here (D26).
 *
 * The logo slot is empty until each operator's brand guidelines are
 * checked (docs/03-rules-and-readiness.md); the hero is type on Mist until
 * then. Every section reads a field and hides itself when it is empty.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_id = (int) get_queried_object_id();
$ot_o  = oomphtravel_operator_data( $ot_id );

/**
 * Filters the client story shown on an operator page: [quote, attribution]
 * from one of the four verified reviews (D37, D38), or null to show none.
 *
 * @param array|null $quote Quote and attribution.
 * @param int        $post_id Operator ID.
 */
$ot_quote = apply_filters( 'oomphtravel_operator_quote', null, $ot_id );

$ot_kind_label = 'fit' === $ot_o['kind'] ? __( 'Supplier', 'oomphtravel' ) : __( 'Escorted tour operator', 'oomphtravel' );
?>
<article class="ot-op">

	<?php /* 1. Hero on Mist: logo when cleared, H1 "{Operator}, in my words". */ ?>
	<section class="ot-band ot-band--mist ot-op-hero">
		<div class="ot-container ot-op-hero__inner">
			<?php if ( $ot_o['logo_id'] ) : ?>
				<div class="ot-op-hero__logo">
					<?php echo wp_get_attachment_image( $ot_o['logo_id'], 'medium', false, array( 'alt' => $ot_o['name'], 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?>
				</div>
			<?php endif; ?>
			<?php echo oomphtravel_eyebrow( $ot_kind_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-op-hero__title">
				<?php
				/* translators: %s: operator name */
				echo esc_html( sprintf( __( '%s, in my words', 'oomphtravel' ), $ot_o['name'] ) );
				?>
			</h1>
			<?php if ( '' !== $ot_o['fit_line'] ) : ?>
				<p class="ot-op-hero__lead"><?php echo esc_html( $ot_o['fit_line'] ); ?></p>
			<?php endif; ?>
			<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), $ot_o['plan_url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<?php /* 2. Fit note, with the facts beside it. */ ?>
	<?php if ( '' !== trim( wp_strip_all_tags( $ot_o['fit_note'] ) ) || $ot_o['group_size'] || $ot_o['price_band'] || $ot_o['inclusions'] ) : ?>
	<section class="ot-band ot-op-fit">
		<div class="ot-container ot-op-fit__inner">
			<?php if ( '' !== trim( wp_strip_all_tags( $ot_o['fit_note'] ) ) ) : ?>
				<div class="ot-op-fit__note ot-prose">
					<?php echo wp_kses_post( wpautop( $ot_o['fit_note'] ) ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $ot_o['group_size'] || $ot_o['price_band'] || $ot_o['inclusions'] ) : ?>
				<aside class="ot-op-facts" aria-label="<?php esc_attr_e( 'At a glance', 'oomphtravel' ); ?>">
					<dl class="ot-facts">
						<?php if ( '' !== $ot_o['group_size'] ) : ?>
							<div class="ot-facts__row">
								<dt class="ot-facts__term"><?php esc_html_e( 'Group size', 'oomphtravel' ); ?></dt>
								<dd class="ot-facts__value"><?php echo esc_html( $ot_o['group_size'] ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( '' !== $ot_o['price_band'] ) : ?>
							<div class="ot-facts__row">
								<dt class="ot-facts__term"><?php esc_html_e( 'Price band', 'oomphtravel' ); ?></dt>
								<dd class="ot-facts__value"><?php echo esc_html( $ot_o['price_band'] ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( $ot_o['inclusions'] ) : ?>
							<div class="ot-facts__row">
								<dt class="ot-facts__term"><?php esc_html_e( 'Usually included', 'oomphtravel' ); ?></dt>
								<dd class="ot-facts__value">
									<ul class="ot-facts__list">
										<?php foreach ( $ot_o['inclusions'] as $ot_line ) : ?>
											<li><?php echo esc_html( $ot_line ); ?></li>
										<?php endforeach; ?>
									</ul>
								</dd>
							</div>
						<?php endif; ?>
					</dl>
				</aside>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 3a. Escorted: the tours. */ ?>
	<?php if ( 'escorted' === $ot_o['kind'] ) : ?>
	<section class="ot-band ot-band--mist ot-op-tours">
		<div class="ot-container">
			<?php
			echo oomphtravel_section_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				__( 'Tours', 'oomphtravel' ),
				/* translators: %s: operator name */
				sprintf( __( 'The %s tours I have entered.', 'oomphtravel' ), $ot_o['name'] )
			);
			?>
			<?php if ( $ot_o['tours'] ) : ?>
				<div class="ot-grid ot-grid--3">
					<?php foreach ( $ot_o['tours'] as $ot_card ) : ?>
						<?php echo oomphtravel_card_tour( $ot_card, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="ot-tours-grid__empty">
					<?php
					/* translators: %s: operator name */
					echo esc_html( sprintf( __( 'I sell far more %s departures than I list. Tell me where and when, and I will send you the ones I would look at.', 'oomphtravel' ), $ot_o['name'] ) );
					?>
				</p>
			<?php endif; ?>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All escorted tours', 'oomphtravel' ), get_post_type_archive_link( 'oomph_tour' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php else : ?>

	<?php /* 3b. FIT supplier: destinations and trip types instead of tours. */ ?>
	<section class="ot-band ot-band--mist ot-op-ways">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'What I book through them', 'oomphtravel' ), __( 'The trips this supplier sits behind.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-op-ways__cols">
				<?php if ( $ot_o['ways'] ) : ?>
					<div class="ot-op-ways__col">
						<h3 class="ot-op-ways__title"><?php esc_html_e( 'Ways to travel', 'oomphtravel' ); ?></h3>
						<ul class="ot-op-ways__list">
							<?php foreach ( $ot_o['ways'] as $ot_way ) : ?>
								<li><?php echo oomphtravel_link( $ot_way['name'], $ot_way['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
				<?php if ( $ot_o['destinations'] ) : ?>
					<div class="ot-op-ways__col">
						<h3 class="ot-op-ways__title"><?php esc_html_e( 'Destinations', 'oomphtravel' ); ?></h3>
						<ul class="ot-op-ways__list">
							<?php foreach ( $ot_o['destinations'] as $ot_dest ) : ?>
								<li><a href="<?php echo esc_url( $ot_dest['url'] ); ?>"><?php echo esc_html( $ot_dest['name'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 3c. The CruiseOomph hand-off for ship-based voyages (D32). */ ?>
	<?php if ( $ot_o['cruiseoomph_line'] ) : ?>
	<aside class="ot-band ot-band--mist-deep ot-dest-ship">
		<div class="ot-container ot-dest-ship__inner">
			<p class="ot-dest-ship__text">
				<span class="ot-dest-ship__lead">
					<?php
					/* translators: %s: operator name */
					echo esc_html( sprintf( __( '%s also sails.', 'oomphtravel' ), $ot_o['name'] ) );
					?>
				</span>
				<?php echo oomphtravel_link( __( 'Its ship-based voyages are on CruiseOomph', 'oomphtravel' ), oomphtravel_cruiseoomph_url( '/cruises/', 'operator-' . $ot_o['slug'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</p>
		</div>
	</aside>
	<?php endif; ?>

	<?php /* 4. Quotation, or nothing (D37). */ ?>
	<?php if ( is_array( $ot_quote ) && ! empty( $ot_quote[0] ) ) : ?>
	<section class="ot-band ot-dest-quote">
		<div class="ot-container ot-dest-quote__inner">
			<?php echo oomphtravel_quotation( (string) $ot_quote[0], (string) ( $ot_quote[1] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 5. Closing invitation, Start planning pre-set to this operator. */ ?>
	<?php echo oomphtravel_closing_band( $ot_o['plan_url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</article>
