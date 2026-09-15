<?php
/**
 * Title: Band / CruiseOomph
 * Slug: oomphtravel/band-cruiseoomph
 * Categories: oomphtravel
 * Description: The hand-off to the sister site. Marine navy, two actions and one tertiary link to Cruise planning. Every outbound link carries the UTM tag.
 *
 * Copy and targets from plan §4.3 and §6.1.10. Both actions are ghost on
 * navy: the page's primary buttons belong to the hero and the closing band.
 * The photograph (a ship on a glassy sea at dawn, from Eric's library) sits
 * under a navy scrim and is decorative, so its alt is empty; it is below the
 * fold on every page and loads lazily.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );
?>
<section class="ot-band ot-band--navy ot-cruiseoomph-band">
	<img class="ot-cruiseoomph-band__photo"
		src="<?php echo esc_url( OOMPHTRAVEL_THEME_URI . 'assets/img/cruiseoomph-dawn-1440.webp' ); ?>"
		srcset="<?php echo esc_attr( OOMPHTRAVEL_THEME_URI . 'assets/img/cruiseoomph-dawn-960.webp 960w, ' . OOMPHTRAVEL_THEME_URI . 'assets/img/cruiseoomph-dawn-1440.webp 1440w, ' . OOMPHTRAVEL_THEME_URI . 'assets/img/cruiseoomph-dawn-1920.webp 1920w' ); ?>"
		sizes="100vw" width="1440" height="600" loading="lazy" decoding="async" alt="">
	<div class="ot-container ot-cruiseoomph">
		<div>
			<a class="ot-cruiseoomph__lockup" href="<?php echo esc_url( oomphtravel_cruiseoomph_url( '/' ) ); ?>">
				<span class="ot-cruiseoomph__name"><?php esc_html_e( 'CruiseOomph', 'oomphtravel' ); ?></span>
				<span class="ot-eyebrow ot-eyebrow--muted ot-cruiseoomph__by"><?php esc_html_e( 'By Oomph Travel', 'oomphtravel' ); ?></span>
			</a>
			<h2 class="ot-cruiseoomph__heading"><?php esc_html_e( 'Premium and luxury cruises now live at CruiseOomph.', 'oomphtravel' ); ?></h2>
		</div>
		<div class="ot-cruiseoomph__actions">
			<div class="ot-cruiseoomph__buttons">
				<?php echo oomphtravel_button( __( 'Explore cruises', 'oomphtravel' ), oomphtravel_cruiseoomph_url( '/' ), 'ghost', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php echo oomphtravel_button( __( 'Find my cruise style', 'oomphtravel' ), oomphtravel_cruiseoomph_url( '/find-my-cruise/' ), 'ghost', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
			<?php echo oomphtravel_link( __( 'How I plan cruises and the land around them', 'oomphtravel' ), home_url( '/cruise-planning/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</div>
</section>
