<?php
/**
 * Title: Card / Journal row
 * Slug: oomphtravel/card-journal
 * Categories: oomphtravel
 * Description: The three latest Journal posts as cards. Category eyebrow in muted slate, not teal.
 *
 * Renders nothing with fewer than three published posts: the Journal is
 * hidden from the homepage until three launch articles are up (plan §9).
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_posts = get_posts(
	array(
		'numberposts'      => 3,
		'post_status'      => 'publish',
		'suppress_filters' => false,
	)
);

if ( count( $ot_posts ) < 3 ) {
	return;
}
?>
<div class="ot-container">
	<?php echo oomphtravel_section_heading( __( 'From the Journal', 'oomphtravel' ), __( 'Latest from the road', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
	<div class="ot-grid ot-grid--3">
		<?php foreach ( $ot_posts as $ot_i => $ot_post ) : ?>
			<?php echo oomphtravel_card_journal( $ot_post, 0 === $ot_i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endforeach; ?>
	</div>
	<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All journal posts', 'oomphtravel' ), home_url( '/journal/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
</div>
