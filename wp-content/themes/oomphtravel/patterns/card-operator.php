<?php
/**
 * Title: Card / Operator row
 * Slug: oomphtravel/card-operator
 * Categories: oomphtravel
 * Description: The seven operators at launch (D25): five escorted, two FIT suppliers. Escorted cards link to the operator page; FIT cards to Custom journeys or Resorts & villas.
 *
 * Fit lines are Eric's and bracketed until supplied; vendor logos are
 * deferred (docs/03-rules-and-readiness.md), so the logo slot is the grey
 * rectangle. Tour counts appear once Tour records exist (Stage 5).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_escorted = array(
	array( 'Globus', 'globus' ),
	array( 'Tauck', 'tauck' ),
	array( 'Insight Vacations', 'insight-vacations' ),
	array( 'Abercrombie & Kent', 'abercrombie-kent' ),
	array( 'National Geographic Expeditions', 'national-geographic-expeditions' ),
);

$ot_cards = array();
foreach ( $ot_escorted as $ot_op ) {
	$ot_cards[] = array(
		'kind' => 'escorted',
		'name' => $ot_op[0],
		'fit'  => '[One-line fit note]',
		'url'  => home_url( '/escorted-tours/' . $ot_op[1] . '/' ),
	);
}
$ot_cards[] = array(
	'kind'       => 'fit',
	'name'       => 'Classic Vacations',
	'fit'        => '[One-line fit note]',
	'trip_types' => array( __( 'Custom journeys', 'oomphtravel' ), __( 'Resorts & villas', 'oomphtravel' ) ),
	'url'        => home_url( '/custom-journeys/' ),
	'link_text'  => __( 'Custom journeys', 'oomphtravel' ),
);
$ot_cards[] = array(
	'kind'       => 'fit',
	'name'       => 'Avanti Destinations',
	'fit'        => '[One-line fit note]',
	'trip_types' => array( __( 'Custom journeys', 'oomphtravel' ) ),
	'url'        => home_url( '/custom-journeys/' ),
	'link_text'  => __( 'Custom journeys', 'oomphtravel' ),
);
?>
<div class="ot-container">
	<div class="ot-grid">
		<?php foreach ( $ot_cards as $ot_card ) : ?>
			<?php echo oomphtravel_card_operator( $ot_card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endforeach; ?>
	</div>
</div>
