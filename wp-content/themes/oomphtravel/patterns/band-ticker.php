<?php
/**
 * Title: Band / Ticker
 * Slug: oomphtravel/band-ticker
 * Categories: oomphtravel
 * Description: Homepage only. Destination names in light italic Fraunces on the marine navy band, each followed by a round photo "stamp", one loop per 70 seconds, pausing on hover (D23).
 *
 * The places are the destinations Eric plans (D07 + D33); each stamp is the
 * destination record's featured image (its square thumbnail), so changing a
 * destination's photo changes its stamp. A record that is not published is
 * left out, so the band never links to a 404. Navy band with stamps chosen by
 * Eric on 2026-10-04 (option D; photo postcards were tried and read as busy).
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
	$ot_thumb = (int) get_post_thumbnail_id( $ot_post );
	$ot_cards[] = array(
		'label' => (string) $ot_place[0],
		'url'   => (string) get_permalink( $ot_post ),
		/* The 150px square crop: a 60px circle needs no more, even at 2x. */
		'stamp' => $ot_thumb ? oomphtravel_defer_image(
			(string) wp_get_attachment_image(
				$ot_thumb,
				'thumbnail',
				false,
				array(
					'alt'      => '',
					'class'    => 'ot-ticker__img',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			)
		) : '',
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
			'<li><a class="ot-ticker__name" href="%s"%s>%s</a>%s</li>',
			esc_url( $card['url'] ),
			$copy ? ' tabindex="-1"' : '',
			esc_html( $card['label'] ),
			'' === $card['stamp'] ? '' : '<span class="ot-ticker__stamp">' . $card['stamp'] . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
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
