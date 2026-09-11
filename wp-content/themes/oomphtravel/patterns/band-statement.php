<?php
/**
 * Title: Band / Statement
 * Slug: oomphtravel/band-statement
 * Categories: oomphtravel
 * Description: Mist ground, centred, one idea. Heading measure 820, body 720. Ghost button, never primary.
 *
 * The body copy is Eric's (plan §6.1.3: three sentences on how he works).
 * The handoff does not carry it, so this is a draft in his voice, adapted
 * from the homepage he wrote for the old site; he can change it word for word.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );
?>
<section class="ot-band ot-band--mist ot-statement">
	<div class="ot-container ot-statement__inner">
		<h2 class="ot-statement__heading"><?php esc_html_e( 'One named advisor, first call to last flight home.', 'oomphtravel' ); ?></h2>
		<p class="ot-statement__body"><?php esc_html_e( 'I take on a handful of trips at a time and plan each one myself. You get one person who knows the whole itinerary, the flights, the rooms and the days in between, and who answers the phone when something changes. When you land back home, I’m still the one you call.', 'oomphtravel' ); ?></p>
		<?php echo oomphtravel_button( __( 'How it works', 'oomphtravel' ), '#ot-how-it-works', 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
	</div>
</section>
