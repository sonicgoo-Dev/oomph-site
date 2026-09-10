<?php
/**
 * Title: Credential strip
 * Slug: oomphtravel/credential-strip
 * Categories: oomphtravel
 * Description: Mist-deep strip, muted-slate caps. Inner pages; the homepage uses the place-name ticker instead (D23).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

// Earned credentials only (docs/voice-guide.md); plan §6.1.2.
$ot_credentials = array(
	__( 'CLIA', 'oomphtravel' ),
	__( 'Silversea Ultra-Luxury Specialist', 'oomphtravel' ),
	__( 'Nexion / Travel Leaders Network', 'oomphtravel' ),
	__( 'BritAgent Pro', 'oomphtravel' ),
	__( 'Port Angeles, WA', 'oomphtravel' ),
);
?>
<div class="ot-credentials">
	<ul class="ot-credentials__list">
		<?php foreach ( $ot_credentials as $ot_c ) : ?>
			<li><?php echo esc_html( $ot_c ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
