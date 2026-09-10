<?php
/**
 * Title: Band / Closing invitation
 * Slug: oomphtravel/band-closing
 * Categories: oomphtravel
 * Description: Every page ends here. Marine navy, one question, one button. The only other primary button on a page is in the hero.
 *
 * Copy from plan §6.1.12.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );
?>
<section class="ot-band ot-band--navy ot-closing">
	<div class="ot-container ot-closing__inner">
		<h2 class="ot-closing__heading"><?php esc_html_e( 'Worth a thirty-minute conversation?', 'oomphtravel' ); ?></h2>
		<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), home_url( '/start-planning/' ), 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<p class="ot-closing__note"><?php esc_html_e( 'Email, text or a quick call, whatever’s easiest.', 'oomphtravel' ); ?></p>
	</div>
</section>
