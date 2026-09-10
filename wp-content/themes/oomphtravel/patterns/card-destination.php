<?php
/**
 * Title: Card / Destination row
 * Slug: oomphtravel/card-destination
 * Categories: oomphtravel
 * Description: Six tall 4:5 destination cards with "All destinations →". Lifts 6px and the photo eases to 105% on hover.
 *
 * The six from plan §6.1.4. Pictures arrive with the destination records
 * (Stage 4); until then each card keeps its box with the grey placeholder.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_cards = array(
	array( 'name' => __( 'Italy', 'oomphtravel' ), 'url' => home_url( '/destinations/italy/' ) ),
	array( 'name' => __( 'UK & Ireland', 'oomphtravel' ), 'url' => home_url( '/destinations/uk-ireland/' ) ),
	array( 'name' => __( 'France', 'oomphtravel' ), 'url' => home_url( '/destinations/france/' ) ),
	array( 'name' => __( 'Greece', 'oomphtravel' ), 'url' => home_url( '/destinations/greece/' ) ),
	array( 'name' => __( 'Croatia & the Adriatic', 'oomphtravel' ), 'url' => home_url( '/destinations/croatia/' ) ),
	array( 'name' => __( 'Hawaii', 'oomphtravel' ), 'url' => home_url( '/destinations/hawaii/' ) ),
);
?>
<div class="ot-container">
	<div class="ot-grid ot-grid--3 ot-grid--scroll">
		<?php foreach ( $ot_cards as $ot_i => $ot_card ) : ?>
			<?php echo oomphtravel_card_destination( $ot_card, 0 === $ot_i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endforeach; ?>
	</div>
	<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
</div>
