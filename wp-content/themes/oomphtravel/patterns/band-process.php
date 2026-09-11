<?php
/**
 * Title: Band / Process
 * Slug: oomphtravel/band-process
 * Categories: oomphtravel
 * Description: Discover · Design · Depart on Mist-deep. Numerals are Fraunces Light in teal — the only numeral that carries the accent.
 *
 * Each step has three named nodes — Number, Title, Body — addressed by class,
 * never by sibling index (docs/02-components.md). The one-line bodies are
 * Eric's (plan §6.1.7); these are drafts adapted from the old homepage's
 * "How it works" block, for him to change word for word.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_steps = array(
	array( '01', __( 'Discover', 'oomphtravel' ), __( 'A free thirty-minute call about where, when, who, and what the trip needs to do.', 'oomphtravel' ) ),
	array( '02', __( 'Design', 'oomphtravel' ), __( 'One proposal, not five, with the reasoning written in.', 'oomphtravel' ) ),
	array( '03', __( 'Depart', 'oomphtravel' ), __( 'Eyes on it the whole time, from the first flight to the last.', 'oomphtravel' ) ),
);
?>
<section class="ot-band ot-band--mist-deep ot-process" id="ot-how-it-works">
	<div class="ot-container">
		<?php echo oomphtravel_section_heading( __( 'How it works', 'oomphtravel' ), __( 'Three steps, one advisor.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<ol class="ot-process__steps">
			<?php foreach ( $ot_steps as $ot_step ) : ?>
				<li class="ot-step">
					<p class="ot-step__number" aria-hidden="true"><?php echo esc_html( $ot_step[0] ); ?></p>
					<h3 class="ot-step__title"><?php echo esc_html( $ot_step[1] ); ?></h3>
					<p class="ot-step__body"><?php echo esc_html( $ot_step[2] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<div class="ot-process__action">
			<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), home_url( '/start-planning/' ), 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</div>
</section>
