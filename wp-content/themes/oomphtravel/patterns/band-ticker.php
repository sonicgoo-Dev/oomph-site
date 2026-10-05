<?php
/**
 * Title: Band / Ticker
 * Slug: oomphtravel/band-ticker
 * Categories: oomphtravel
 * Description: Homepage only. Destination postcards (photo, name over a scrim) scrolling on the marine navy band, one loop per 70 seconds, pausing on hover (D23).
 *
 * The places are the destinations Eric plans (D07 + D33); each postcard is
 * the destination record's featured image, so changing a destination's photo
 * changes its postcard. A record that is not published is left out, so the
 * band never links to a 404. Postcards and navy band chosen by Eric on
 * 2026-10-04 (option A on option D's band).
 *
 * The list is printed twice inside one rail that slides by half its width,
 * so the loop is seamless; the copy is hidden from assistive tech. Pictures
 * are held until the page has loaded (oomphtravel_defer_image) so they never
 * compete with the hero. The Pause / Play control is there for keyboard users
 * (WCAG 2.2.2). The band keeps scrolling under reduced motion, as
 * CruiseOomph's does (Eric, 2026-10-04); Pause and hover are the way to stop it.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

/**
 * Filters the ticker places: array of [label, destination slug].
 *
 * @param array $places Places.
 */
$ot_places = (array) apply_filters(
	'oomphtravel_ticker_places',
	array(
		array( __( 'Italy', 'oomphtravel' ), 'italy' ),
		array( __( 'UK & Ireland', 'oomphtravel' ), 'uk-ireland' ),
		array( __( 'France', 'oomphtravel' ), 'france' ),
		array( __( 'Spain', 'oomphtravel' ), 'spain' ),
		array( __( 'Portugal', 'oomphtravel' ), 'portugal' ),
		array( __( 'Greece', 'oomphtravel' ), 'greece' ),
		array( __( 'Croatia & the Adriatic', 'oomphtravel' ), 'croatia' ),
		array( __( 'Africa', 'oomphtravel' ), 'africa' ),
		array( __( 'Hawaii', 'oomphtravel' ), 'hawaii' ),
		array( __( 'Mexico', 'oomphtravel' ), 'mexico' ),
		array( __( 'Caribbean', 'oomphtravel' ), 'caribbean' ),
	)
);

$ot_cards = array();
foreach ( $ot_places as $ot_place ) {
	$ot_post = oomphtravel_destination_live_post( (string) $ot_place[1] );
	if ( ! $ot_post ) {
		continue;
	}
	$ot_cards[] = array(
		'label' => (string) $ot_place[0],
		'url'   => (string) get_permalink( $ot_post ),
		'image' => oomphtravel_card_image( (int) get_post_thumbnail_id( $ot_post ), '', '(min-width: 768px) 236px, 184px', false, array( 'class' => 'ot-ticker__img' ) ),
	);
}
if ( ! $ot_cards ) {
	return;
}

/**
 * The list once; called twice.
 *
 * @param array $cards Cards.
 * @param bool  $copy  The hidden second copy.
 */
$ot_print_list = static function ( array $cards, bool $copy ): void {
	printf( '<ul class="ot-ticker__list"%s>', $copy ? ' aria-hidden="true"' : '' );
	foreach ( $cards as $card ) {
		printf(
			'<li><a class="ot-ticker__card%s" href="%s"%s>%s<span class="ot-ticker__name">%s</span></a></li>',
			'' === $card['image'] ? ' ot-ticker__card--empty' : '',
			esc_url( $card['url'] ),
			$copy ? ' tabindex="-1"' : '',
			$card['image'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
			esc_html( $card['label'] )
		);
	}
	echo '</ul>';
};
?>
<div class="ot-ticker" aria-label="<?php esc_attr_e( 'Places Eric plans', 'oomphtravel' ); ?>">
	<div class="ot-ticker__rail" data-ot-ticker>
		<?php $ot_print_list( $ot_cards, false ); ?>
		<?php $ot_print_list( $ot_cards, true ); ?>
	</div>
	<button class="ot-ticker__toggle" type="button" aria-pressed="false" data-ot-ticker-toggle data-label-pause="<?php esc_attr_e( 'Pause', 'oomphtravel' ); ?>" data-label-play="<?php esc_attr_e( 'Play', 'oomphtravel' ); ?>"><?php esc_html_e( 'Pause', 'oomphtravel' ); ?></button>
</div>
