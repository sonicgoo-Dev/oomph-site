<?php
/**
 * Title: Not found
 * Slug: oomphtravel/404
 * Inserter: no
 *
 * The 404 page (plan §6.16), rendered by templates/404.html. Every cruise
 * address deleted under D02 lands here, so it is built to move the visitor
 * on: the luggage symbol (the 404 mark, D22), one line, a search field, and
 * the three doors. The search button is the page's one primary action.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );
?>
<div class="ot-404">
	<section class="ot-band ot-band--navy ot-404__band" aria-labelledby="ot-404-title">
		<div class="ot-container ot-404__inner">
			<?php echo oomphtravel_symbol( 88, '', 'ot-404__symbol' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG from the theme's own file. ?>
			<?php echo oomphtravel_eyebrow( __( 'Page not found', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-404__title" id="ot-404-title"><?php esc_html_e( 'This page took a wrong turn.', 'oomphtravel' ); ?></h1>
			<p class="ot-404__lead"><?php esc_html_e( 'The address may be old, or a letter out. Search for what you were after, or take one of the three doors.', 'oomphtravel' ); ?></p>
			<?php echo oomphtravel_search_form( 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>

	<section class="ot-band ot-404__doors" aria-label="<?php esc_attr_e( 'Three ways on', 'oomphtravel' ); ?>">
		<div class="ot-container">
			<?php echo oomphtravel_doors_list( 'h2' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
</div>
