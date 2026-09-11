<?php
/**
 * Title: All destinations
 * Slug: oomphtravel/destinations-index
 * Inserter: no
 *
 * The "where" door, plan §6.2, rendered by
 * templates/archive-oomph_destination.html. A short photo band with the H1,
 * then the published destination records grouped: Europe, Sun and sea, and
 * (D33) Africa under its own guided heading. Drafts are not listed, so a
 * group appears as its records go live.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_groups = oomphtravel_destination_groups();
$ot_img    = OOMPHTRAVEL_THEME_URI . 'assets/img/';
?>
<div class="ot-dest-index">

	<?php /* 1. Short hero band, 420px, H1 "Where to". */ ?>
	<section class="ot-dest-hero ot-dest-hero--short">
		<picture class="ot-dest-hero__picture">
			<source media="(max-width: 767px)" srcset="<?php echo esc_attr( $ot_img . 'hero-varenna-tall-480.webp 480w, ' . $ot_img . 'hero-varenna-tall-720.webp 720w' ); ?>" sizes="100vw">
			<img
				src="<?php echo esc_url( $ot_img . 'hero-varenna-1280.webp' ); ?>"
				srcset="<?php echo esc_attr( $ot_img . 'hero-varenna-640.webp 640w, ' . $ot_img . 'hero-varenna-960.webp 960w, ' . $ot_img . 'hero-varenna-1280.webp 1280w' ); ?>"
				sizes="100vw" width="1280" height="853" fetchpriority="high" decoding="async"
				alt="<?php esc_attr_e( 'Varenna at dusk from above, its houses lit along the point, with Lake Como and the mountains beyond.', 'oomphtravel' ); ?>">
		</picture>
		<div class="ot-container ot-dest-hero__inner">
			<div class="ot-dest-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'All destinations', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-dest-hero__title"><?php esc_html_e( 'Where to', 'oomphtravel' ); ?></h1>
			</div>
		</div>
	</section>

	<?php /* 2–3. The groups. */ ?>
	<?php foreach ( $ot_groups as $ot_group ) : ?>
	<section class="ot-band ot-dest-index__group">
		<div class="ot-container">
			<h2 class="ot-dest-index__heading"><?php echo esc_html( $ot_group['heading'] ); ?></h2>
			<div class="ot-grid ot-grid--4 ot-grid--scroll">
				<?php foreach ( $ot_group['cards'] as $ot_i => $ot_card ) : ?>
					<?php echo oomphtravel_card_destination( $ot_card, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endforeach; ?>

	<?php if ( ! $ot_groups ) : ?>
	<section class="ot-band">
		<div class="ot-container">
			<p class="ot-dest-index__empty"><?php esc_html_e( 'Destination pages are on their way. In the meantime, tell me where you have in mind.', 'oomphtravel' ); ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 4. Statement band: elsewhere too. */ ?>
	<section class="ot-band ot-band--mist ot-statement">
		<div class="ot-container ot-statement__inner">
			<h2 class="ot-statement__heading"><?php esc_html_e( 'Don’t see your destination? I plan elsewhere too.', 'oomphtravel' ); ?></h2>
			<p class="ot-statement__body"><?php esc_html_e( 'These are the places I know best and go back to. If yours isn’t on the list, say where, and I’ll tell you honestly whether I’m the right person to plan it.', 'oomphtravel' ); ?></p>
			<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), home_url( '/start-planning/' ), 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<?php /* 5. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
