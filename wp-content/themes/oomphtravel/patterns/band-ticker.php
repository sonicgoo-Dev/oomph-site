<?php
/**
 * Title: Band / Ticker
 * Slug: oomphtravel/band-ticker
 * Categories: oomphtravel
 * Description: Homepage only. Place names separated by a small outlined plane, one loop per 70 seconds, pausing on hover (D23).
 *
 * The names are the destinations Eric plans (D07 + D33). The list is printed
 * twice so the loop is seamless; the copy is hidden from assistive tech. The
 * Pause / Play control is there for keyboard users (WCAG 2.2.2); under
 * reduced motion the names simply wrap and nothing moves.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

/**
 * Filters the ticker place names: array of [label, url].
 *
 * @param array $places Places.
 */
$ot_places = (array) apply_filters(
	'oomphtravel_ticker_places',
	array(
		array( __( 'Italy', 'oomphtravel' ), home_url( '/destinations/italy/' ) ),
		array( __( 'UK & Ireland', 'oomphtravel' ), home_url( '/destinations/uk-ireland/' ) ),
		array( __( 'France', 'oomphtravel' ), home_url( '/destinations/france/' ) ),
		array( __( 'Spain', 'oomphtravel' ), home_url( '/destinations/spain/' ) ),
		array( __( 'Portugal', 'oomphtravel' ), home_url( '/destinations/portugal/' ) ),
		array( __( 'Greece', 'oomphtravel' ), home_url( '/destinations/greece/' ) ),
		array( __( 'Croatia & the Adriatic', 'oomphtravel' ), home_url( '/destinations/croatia/' ) ),
		array( __( 'Africa', 'oomphtravel' ), home_url( '/destinations/africa/' ) ),
		array( __( 'Hawaii', 'oomphtravel' ), home_url( '/destinations/hawaii/' ) ),
		array( __( 'Mexico', 'oomphtravel' ), home_url( '/destinations/mexico/' ) ),
		array( __( 'Caribbean', 'oomphtravel' ), home_url( '/destinations/caribbean/' ) ),
	)
);

$ot_plane = '<svg class="ot-ticker__plane" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M14.5 1.5L1.5 7l5 2 2 5 6-12.5z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="M6.5 9l8-7.5" fill="none" stroke="currentColor" stroke-width="1.2"/></svg>';

/**
 * The list once; called twice.
 *
 * @param array  $places Places.
 * @param string $plane  Separator SVG.
 * @param bool   $copy   The hidden second copy.
 */
$ot_print_track = static function ( array $places, string $plane, bool $copy ): void {
	printf( '<ul class="ot-ticker__track%s"%s%s>', $copy ? ' ot-ticker__track--copy' : '', $copy ? ' aria-hidden="true"' : '', $copy ? '' : ' data-ot-ticker' );
	foreach ( $places as $place ) {
		printf(
			'<li><a href="%s"%s>%s</a>%s</li>',
			esc_url( (string) $place[1] ),
			$copy ? ' tabindex="-1"' : '',
			esc_html( (string) $place[0] ),
			$plane // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		);
	}
	echo '</ul>';
};
?>
<div class="ot-ticker" aria-label="<?php esc_attr_e( 'Places Eric plans', 'oomphtravel' ); ?>">
	<?php $ot_print_track( $ot_places, $ot_plane, false ); ?>
	<?php $ot_print_track( $ot_places, $ot_plane, true ); ?>
	<button class="ot-ticker__toggle" type="button" aria-pressed="false" data-ot-ticker-toggle data-label-pause="<?php esc_attr_e( 'Pause', 'oomphtravel' ); ?>" data-label-play="<?php esc_attr_e( 'Play', 'oomphtravel' ); ?>"><?php esc_html_e( 'Pause', 'oomphtravel' ); ?></button>
</div>
