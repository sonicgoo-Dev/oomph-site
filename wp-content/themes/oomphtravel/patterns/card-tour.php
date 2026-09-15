<?php
/**
 * Title: Card / Tour row
 * Slug: oomphtravel/card-tour
 * Categories: oomphtravel
 * Description: Three tour cards. Meta is operator · nights · destination; the price is always "from", per person; the availability caption is part of the card (D34).
 *
 * Tour records arrive with the data layer (Stage 5, D26: Insight Vacations,
 * Globus, Tauck). This pattern shows the component with the handoff's own
 * placeholder markers — anything in square brackets and $X,XXX must never
 * reach a published page (docs/03-rules-and-readiness.md).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_cards = array();
foreach ( array( 'insight-vacations', 'globus', 'tauck' ) as $ot_slug ) {
	$ot_cards[] = array(
		'meta'  => '[OPERATOR] · [nights] · [destination]',
		'title' => '[TOUR NAME]',
		'blurb' => '[Two lines on who this tour suits, in Eric’s voice.]',
		'price' => '$X,XXX',
		'url'   => home_url( '/tours/' . $ot_slug . '/' ),
		'slug'  => $ot_slug,
	);
}
?>
<div class="ot-container">
	<div class="ot-grid ot-grid--3">
		<?php foreach ( $ot_cards as $ot_i => $ot_card ) : ?>
			<?php echo oomphtravel_card_tour( $ot_card, 0 === $ot_i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endforeach; ?>
	</div>
	<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'See all escorted tours', 'oomphtravel' ), home_url( '/escorted-tours/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
</div>
