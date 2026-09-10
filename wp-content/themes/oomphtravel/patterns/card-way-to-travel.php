<?php
/**
 * Title: Card / Way to travel row
 * Slug: oomphtravel/card-way-to-travel
 * Categories: oomphtravel
 * Description: Four way-to-travel cards: photo above, Fraunces title, two lines of body, one underlined link.
 *
 * The four from plan §6.1.4; one-line bodies are the plan's own definitions
 * (D01, D04, D06, §6.1.5).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_cards = array(
	array(
		'title' => __( 'Custom journeys', 'oomphtravel' ),
		'body'  => __( 'Independent, hand-planned trips by destination.', 'oomphtravel' ),
		'url'   => home_url( '/custom-journeys/' ),
	),
	array(
		'title' => __( 'Escorted tours', 'oomphtravel' ),
		'body'  => __( 'Operator departures I sell, with my fit notes.', 'oomphtravel' ),
		'url'   => home_url( '/escorted-tours/' ),
	),
	array(
		'title' => __( 'Resorts & villas', 'oomphtravel' ),
		'body'  => __( 'Resort stays, villas and private homes, and the hotels inside custom trips.', 'oomphtravel' ),
		'url'   => home_url( '/resorts-and-villas/' ),
	),
	array(
		'title' => __( 'Multi-generational trips', 'oomphtravel' ),
		'body'  => __( 'Families across three generations.', 'oomphtravel' ),
		'url'   => home_url( '/multi-generational-travel-planning/' ),
	),
);
?>
<div class="ot-container">
	<div class="ot-grid ot-grid--4">
		<?php foreach ( $ot_cards as $ot_i => $ot_card ) : ?>
			<?php echo oomphtravel_card_way( $ot_card, 0 === $ot_i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endforeach; ?>
	</div>
</div>
