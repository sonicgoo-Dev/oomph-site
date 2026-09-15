<?php
/**
 * Title: Links
 * Slug: oomphtravel/links
 * Inserter: no
 *
 * The link-in-bio page (plan §6.16, §4.3), rendered by
 * templates/page-links.html at /links/. The same eleven-link shape as the
 * page it replaces, on a deep navy ground because it is read on a phone in a
 * feed, often outdoors: who this is, the newest Journal post as a card, the
 * one primary button, four quiet rows, five text links, the credentials.
 *
 * Eleven links: the card (1), Start planning (2), the rows (3–6, two of
 * them to CruiseOomph) and the text links (7–11). Every CruiseOomph link
 * carries the UTM tag through oomphtravel_cruiseoomph_url().
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_featured = oomphtravel_links_featured_post();
$ot_rows     = oomphtravel_links_rows();
$ot_more     = oomphtravel_links_more();
$ot_new_tab  = '<span class="ot-visually-hidden"> ' . esc_html__( '(opens in a new tab)', 'oomphtravel' ) . '</span>';
?>
<div class="ot-links ot-band--navy-deep">
	<div class="ot-links__inner">

		<?php /* 1. Who. */ ?>
		<header class="ot-links__intro">
			<?php echo oomphtravel_eyebrow( __( 'Oomph Travel · Port Angeles, WA', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-links__title"><?php esc_html_e( 'Everything, in one place.', 'oomphtravel' ); ?></h1>
			<p class="ot-links__standfirst"><?php esc_html_e( 'I’m Eric Hempel. I plan custom journeys, escorted tours and resort stays for people who would rather not spend their evenings comparing hotels. Start anywhere below.', 'oomphtravel' ); ?></p>
		</header>

		<?php /* 2. The newest Journal post. Publish a post and this changes itself. */ ?>
		<?php if ( $ot_featured ) : ?>
			<section class="ot-links__feature" aria-labelledby="ot-links-feature-title">
				<h2 class="ot-links__feature-heading" id="ot-links-feature-title"><?php esc_html_e( 'Newest in the Journal', 'oomphtravel' ); ?></h2>
				<?php echo oomphtravel_card_journal( $ot_featured, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</section>
		<?php endif; ?>

		<?php /* 3. The one primary button. */ ?>
		<div class="ot-links__cta">
			<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), home_url( '/start-planning/' ), 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<p class="ot-links__cta-note"><?php esc_html_e( 'Email, text or a quick call, whatever’s easiest.', 'oomphtravel' ); ?></p>
		</div>

		<?php /* 4. Four quiet rows. */ ?>
		<nav class="ot-links__rows" aria-label="<?php esc_attr_e( 'More from Oomph Travel', 'oomphtravel' ); ?>">
			<?php foreach ( $ot_rows as $ot_row ) : ?>
				<a class="ot-links__row" href="<?php echo esc_url( $ot_row['url'] ); ?>"<?php echo $ot_row['external'] ? ' target="_blank" rel="noopener"' : ''; ?>>
					<span class="ot-links__row-text">
						<span class="ot-links__row-label"><?php echo esc_html( $ot_row['label'] ); ?><?php echo $ot_row['external'] ? $ot_new_tab : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></span>
						<span class="ot-links__row-meta"><?php echo esc_html( $ot_row['meta'] ); ?></span>
					</span>
					<?php echo oomphtravel_arrow( $ot_row['external'] ? '↗' : '→' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php /* 5. The rest of the site. Gap, not separators: the row wraps on a phone. */ ?>
		<nav class="ot-links__more" aria-label="<?php esc_attr_e( 'Elsewhere on the site', 'oomphtravel' ); ?>">
			<?php foreach ( $ot_more as $ot_label => $ot_url ) : ?>
				<a href="<?php echo esc_url( $ot_url ); ?>"><?php echo esc_html( $ot_label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<p class="ot-links__foot"><?php esc_html_e( 'CLIA Member · Nexion Affiliated', 'oomphtravel' ); ?></p>

	</div>
</div>
