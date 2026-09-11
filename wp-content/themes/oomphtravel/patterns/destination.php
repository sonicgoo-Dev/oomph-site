<?php
/**
 * Title: Destination page
 * Slug: oomphtravel/destination
 * Inserter: no
 *
 * The destination template, plan §6.3, rendered by
 * templates/single-oomph_destination.html for every Destination record. One
 * template, three variants (custom-first, resort-first, guided-first) that
 * change the order of the "Ways to see it" columns and the states of the
 * when-to-go strip; see docs/03-rules-and-readiness.md.
 *
 * Every section reads a field and hides itself when the field is empty.
 * Nothing prints a placeholder (tests/ci/records.spec.ts). The two primary
 * buttons are the hero and the closing band; the closing band's Start
 * planning link carries the destination so the form arrives pre-set.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_id   = (int) get_queried_object_id();
$ot_d    = oomphtravel_destination_data( $ot_id );
$ot_hero = oomphtravel_destination_hero_sources( $ot_id );
$ot_plan = add_query_arg( 'destination', $ot_d['slug'], home_url( '/start-planning/' ) );

/*
 * Ways to see it (§6.3.4). Column order follows the variant: resort-first
 * leads with the resort column, guided-first with the escorted column.
 */
$ot_ways = array(
	'custom'   => array(
		'title' => __( 'As a custom journey', 'oomphtravel' ),
		/* translators: %s: destination name */
		'body'  => sprintf( __( 'Independent and hand-planned: the regions of %s you want, at your pace, with drivers, guides and stays chosen for you.', 'oomphtravel' ), $ot_d['title'] ),
		'link'  => __( 'How custom journeys work', 'oomphtravel' ),
		'url'   => home_url( '/custom-journeys/' ),
	),
	'escorted' => array(
		'title' => __( 'On an escorted tour', 'oomphtravel' ),
		'body'  => __( 'An operator’s departure with a tour director, coach and hotels arranged, and my fit notes on which one suits you.', 'oomphtravel' ),
		'link'  => __( 'See escorted tours', 'oomphtravel' ),
		'url'   => home_url( '/escorted-tours/' ),
	),
	'resort'   => array(
		'title' => __( 'From a resort or villa', 'oomphtravel' ),
		'body'  => __( 'One base, well chosen: a resort, a villa or a private home, with the days planned out from there.', 'oomphtravel' ),
		'link'  => __( 'Resorts and villas', 'oomphtravel' ),
		'url'   => home_url( '/resorts-and-villas/' ),
	),
);
$ot_order = array( 'custom', 'escorted', 'resort' );
if ( 'resort' === $ot_d['variant'] ) {
	$ot_order = array( 'resort', 'custom', 'escorted' );
} elseif ( 'guided' === $ot_d['variant'] ) {
	$ot_order = array( 'escorted', 'custom', 'resort' );
}

$ot_months = array(
	1  => __( 'January', 'oomphtravel' ),
	2  => __( 'February', 'oomphtravel' ),
	3  => __( 'March', 'oomphtravel' ),
	4  => __( 'April', 'oomphtravel' ),
	5  => __( 'May', 'oomphtravel' ),
	6  => __( 'June', 'oomphtravel' ),
	7  => __( 'July', 'oomphtravel' ),
	8  => __( 'August', 'oomphtravel' ),
	9  => __( 'September', 'oomphtravel' ),
	10 => __( 'October', 'oomphtravel' ),
	11 => __( 'November', 'oomphtravel' ),
	12 => __( 'December', 'oomphtravel' ),
);

/**
 * Filters the client story shown on a destination page: [quote, attribution]
 * from one of the four verified reviews (D37, D38), or null to show none.
 *
 * @param array|null $quote Quote and attribution.
 * @param int        $post_id Destination ID.
 */
$ot_quote = apply_filters( 'oomphtravel_destination_quote', null, $ot_id );

$ot_stay_types = array(
	'hotel'        => __( 'Hotel', 'oomphtravel' ),
	'resort'       => __( 'Resort', 'oomphtravel' ),
	'villa'        => __( 'Villa', 'oomphtravel' ),
	'private-home' => __( 'Private home', 'oomphtravel' ),
);
?>
<article class="ot-dest">

	<?php /* 1. Hero: full-width photo, the place is the subject (D11). */ ?>
	<section class="ot-dest-hero<?php echo $ot_hero ? '' : ' ot-dest-hero--bare'; ?>">
		<?php if ( $ot_hero ) : ?>
			<picture class="ot-dest-hero__picture">
				<?php if ( ! empty( $ot_hero['tall'] ) ) : ?>
					<source media="<?php echo esc_attr( $ot_hero['tall']['media'] ); ?>" srcset="<?php echo esc_attr( $ot_hero['tall']['srcset'] ); ?>" sizes="<?php echo esc_attr( $ot_hero['tall']['sizes'] ); ?>">
				<?php endif; ?>
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
			<div class="ot-dest-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Destination', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-dest-hero__title"><?php echo esc_html( $ot_d['headline'] ); ?></h1>
				<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), $ot_plan, 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		</div>
	</section>

	<?php /* 2. Intro: three paragraphs at the 720 measure. */ ?>
	<?php if ( '' !== trim( wp_strip_all_tags( $ot_d['intro'] ) ) ) : ?>
	<section class="ot-band ot-dest-intro">
		<div class="ot-container">
			<div class="ot-dest-intro__body ot-prose">
				<?php echo wp_kses_post( wpautop( $ot_d['intro'] ) ); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 3. Regions: heading + two sentences each, no separate pages yet. */ ?>
	<?php if ( $ot_d['regions'] ) : ?>
	<section class="ot-band ot-band--mist ot-dest-regions">
		<div class="ot-container">
			<?php
			echo oomphtravel_section_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				__( 'Regions', 'oomphtravel' ),
				/* translators: %s: destination name */
				sprintf( __( 'The %s I plan, region by region.', 'oomphtravel' ), $ot_d['title'] )
			);
			?>
			<div class="ot-dest-regions__grid">
				<?php foreach ( $ot_d['regions'] as $ot_region ) : ?>
					<div class="ot-dest-region">
						<h3 class="ot-dest-region__name"><?php echo esc_html( $ot_region['name'] ); ?></h3>
						<p class="ot-dest-region__blurb"><?php echo esc_html( $ot_region['blurb'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 4. Ways to see it: three columns in variant order. */ ?>
	<section class="ot-band ot-dest-ways">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Ways to see it', 'oomphtravel' ), __( 'Three ways in. I’ll tell you which fits.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-dest-ways__cols">
				<?php foreach ( $ot_order as $ot_key ) : $ot_way = $ot_ways[ $ot_key ]; ?>
					<div class="ot-dest-way<?php echo $ot_key === $ot_order[0] ? ' ot-dest-way--lead' : ''; ?>">
						<h3 class="ot-dest-way__title"><?php echo esc_html( $ot_way['title'] ); ?></h3>
						<p class="ot-dest-way__body"><?php echo esc_html( $ot_way['body'] ); ?></p>
						<?php if ( 'escorted' === $ot_key && $ot_d['operators'] ) : ?>
							<p class="ot-dest-way__operators">
								<span class="ot-dest-way__operators-label"><?php esc_html_e( 'Operators I sell here:', 'oomphtravel' ); ?></span>
								<?php
								$ot_names = array();
								foreach ( $ot_d['operators'] as $ot_op ) {
									$ot_names[] = '<a href="' . esc_url( $ot_op['url'] ) . '">' . esc_html( $ot_op['name'] ) . '</a>';
								}
								echo implode( ', ', $ot_names ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
								?>
							</p>
						<?php endif; ?>
						<?php echo oomphtravel_link( $ot_way['link'], $ot_way['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( $ot_d['tours'] ) : ?>
				<div class="ot-grid ot-grid--3 ot-dest-ways__tours">
					<?php foreach ( $ot_d['tours'] as $ot_tour ) : ?>
						<?php echo oomphtravel_card_tour( $ot_tour, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* 5. Sample itinerary: an accordion, illustrative, no prices. */ ?>
	<?php if ( $ot_d['itinerary'] ) : ?>
	<section class="ot-band ot-band--mist ot-dest-itinerary">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Sample itinerary', 'oomphtravel' ), __( 'Ten days, one way to do it.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-accordion ot-dest-itinerary__days">
				<?php foreach ( $ot_d['itinerary'] as $ot_day ) : ?>
					<li>
						<details class="ot-accordion__item">
							<summary class="ot-accordion__summary">
								<span class="ot-accordion__label"><?php echo esc_html( sprintf( /* translators: %s: day number */ __( 'Day %s', 'oomphtravel' ), $ot_day['day'] ) ); ?></span>
								<span class="ot-accordion__title"><?php echo esc_html( $ot_day['title'] ); ?></span>
								<span class="ot-accordion__marker" aria-hidden="true"></span>
							</summary>
							<p class="ot-accordion__body"><?php echo esc_html( $ot_day['text'] ); ?></p>
						</details>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ot-dest-itinerary__note"><?php esc_html_e( 'Illustrative. Your trip starts from a blank page, not from this one.', 'oomphtravel' ); ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 6. Stays: where I'd put you. No booking links. */ ?>
	<?php if ( $ot_d['stays'] ) : ?>
	<section class="ot-band ot-dest-stays">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Stays', 'oomphtravel' ), __( 'Where I’d put you.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-dest-stays__list">
				<?php foreach ( $ot_d['stays'] as $ot_stay ) : ?>
					<li class="ot-dest-stay">
						<?php if ( isset( $ot_stay_types[ $ot_stay['type'] ] ) ) : ?>
							<?php echo oomphtravel_eyebrow( $ot_stay_types[ $ot_stay['type'] ], true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						<?php endif; ?>
						<h3 class="ot-dest-stay__name"><?php echo esc_html( $ot_stay['name'] ); ?></h3>
						<?php if ( '' !== $ot_stay['note'] ) : ?>
							<p class="ot-dest-stay__note"><?php echo esc_html( $ot_stay['note'] ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $ot_stay['perks'] ) : ?>
							<p class="ot-dest-stay__perks"><?php echo esc_html( $ot_stay['perks'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 7. When to go: twelve months, best highlighted (three states on the guided variant). */ ?>
	<?php if ( $ot_d['best_months'] ) : ?>
	<section class="ot-band ot-band--mist ot-dest-months">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'When to go', 'oomphtravel' ), __( 'The months I’d send you.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-months" aria-label="<?php esc_attr_e( 'Best months to visit', 'oomphtravel' ); ?>">
				<?php foreach ( $ot_months as $ot_n => $ot_name ) : ?>
					<?php
					$ot_state = '';
					if ( in_array( $ot_n, $ot_d['best_months'], true ) ) {
						$ot_state = 'is-best';
					} elseif ( 'guided' === $ot_d['variant'] && in_array( $ot_n, $ot_d['shoulder_months'], true ) ) {
						$ot_state = 'is-shoulder';
					}
					?>
					<li class="ot-months__month <?php echo esc_attr( $ot_state ); ?>">
						<abbr title="<?php echo esc_attr( $ot_name ); ?>"><?php echo esc_html( mb_substr( $ot_name, 0, 1 ) ); ?></abbr>
						<span class="ot-visually-hidden">
							<?php
							if ( 'is-best' === $ot_state ) {
								esc_html_e( ': best', 'oomphtravel' );
							} elseif ( 'is-shoulder' === $ot_state ) {
								esc_html_e( ': possible', 'oomphtravel' );
							}
							?>
						</span>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ot-months__key">
				<span class="ot-months__key-item ot-months__key-item--best"><?php esc_html_e( 'Best', 'oomphtravel' ); ?></span>
				<?php if ( 'guided' === $ot_d['variant'] ) : ?>
					<span class="ot-months__key-item ot-months__key-item--shoulder"><?php esc_html_e( 'Possible', 'oomphtravel' ); ?></span>
				<?php endif; ?>
			</p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 8. Quotation: one verified client story, or nothing (D37, no placeholder on a published page). */ ?>
	<?php if ( is_array( $ot_quote ) && ! empty( $ot_quote[0] ) ) : ?>
	<section class="ot-band ot-dest-quote">
		<div class="ot-container ot-dest-quote__inner">
			<?php echo oomphtravel_quotation( (string) $ot_quote[0], (string) ( $ot_quote[1] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 9. FAQ: four to six questions; FAQPage schema comes from the plugin. */ ?>
	<?php if ( $ot_d['faq'] ) : ?>
	<section class="ot-band ot-dest-faq">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Questions', 'oomphtravel' ), __( 'What people ask before they call.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-accordion ot-dest-faq__list">
				<?php foreach ( $ot_d['faq'] as $ot_qa ) : ?>
					<details class="ot-accordion__item ot-faq__item">
						<summary class="ot-accordion__summary">
							<span class="ot-accordion__title"><?php echo esc_html( $ot_qa['question'] ); ?></span>
							<span class="ot-accordion__marker" aria-hidden="true"></span>
						</summary>
						<p class="ot-accordion__body"><?php echo esc_html( $ot_qa['answer'] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 10. By ship: coastal destinations only, to CruiseOomph with the UTM tag. */ ?>
	<?php if ( '' !== $ot_d['cruiseoomph_region'] ) : ?>
	<aside class="ot-band ot-band--mist-deep ot-dest-ship">
		<div class="ot-container ot-dest-ship__inner">
			<p class="ot-dest-ship__text">
				<span class="ot-dest-ship__lead"><?php esc_html_e( 'Prefer to see it by ship?', 'oomphtravel' ); ?></span>
				<?php
				echo oomphtravel_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					/* translators: %s: cruise region name */
					sprintf( __( '%s cruises are on CruiseOomph', 'oomphtravel' ), $ot_d['cruiseoomph_region'] ),
					oomphtravel_cruiseoomph_url( '/cruises/', '', array( 'region' => $ot_d['cruiseoomph_region'] ) )
				);
				?>
			</p>
		</div>
	</aside>
	<?php endif; ?>

	<?php /* 11. Closing invitation, Start planning pre-set to this destination. */ ?>
	<?php echo oomphtravel_closing_band( $ot_plan ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</article>
