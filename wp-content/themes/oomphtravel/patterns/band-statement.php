<?php
/**
 * Title: Band / Statement
 * Slug: oomphtravel/band-statement
 * Categories: oomphtravel
 * Description: Mist ground, centred, one idea. Heading measure 820, body 720. Ghost button, never primary.
 *
 * The body copy is Eric's (plan §6.1.3: three sentences on how he works) and
 * is not in the handoff, so it carries the bracketed placeholder marker from
 * docs/03-rules-and-readiness.md until he supplies it.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );
?>
<section class="ot-band ot-band--mist ot-statement">
	<div class="ot-container ot-statement__inner">
		<h2 class="ot-statement__heading"><?php esc_html_e( 'One named advisor, first call to last flight home.', 'oomphtravel' ); ?></h2>
		<p class="ot-statement__body">[Three sentences on how Eric works.]</p>
		<?php echo oomphtravel_button( __( 'How it works', 'oomphtravel' ), '#ot-how-it-works', 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
	</div>
</section>
