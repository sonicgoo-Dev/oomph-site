<?php
/**
 * Title: Client stories
 * Slug: oomphtravel/client-stories
 * Inserter: no
 *
 * Client stories (plan §6.12; D37, D38, D39), rendered by
 * templates/page-client-stories.html at /client-stories/. A short hero
 * band, then each of the four verified reviews as a full-width quotation
 * with the trip type and destination as its eyebrow, word for word. All
 * four are about cruises, so each carries the one-line hand-off to
 * CruiseOomph. The plugin emits Review and AggregateRating from the same
 * rows. The only primary button is the closing band's.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_stories = oomphtravel_client_stories();
?>
<div class="ot-stories-page">

	<?php /* 1. Short hero band. */ ?>
	<section class="ot-dest-hero ot-dest-hero--bare ot-stories-hero" aria-labelledby="ot-stories-title">
		<div class="ot-container ot-dest-hero__inner">
			<div class="ot-dest-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Client stories', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-dest-hero__title" id="ot-stories-title"><?php esc_html_e( 'In their words.', 'oomphtravel' ); ?></h1>
				<p class="ot-stories-hero__lead"><?php esc_html_e( 'Four reviews from clients I have planned for, posted publicly in their own words. I have changed nothing: the names, the ships and the trips are theirs.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 2. The stories. */ ?>
	<section class="ot-band ot-stories-list" aria-label="<?php esc_attr_e( 'Client stories', 'oomphtravel' ); ?>">
		<div class="ot-container">
			<?php foreach ( $ot_stories as $ot_i => $ot_story ) : ?>
				<article class="ot-story">
					<?php echo oomphtravel_eyebrow( (string) $ot_story['trip'], true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<h2 class="ot-story__title"><?php echo esc_html( (string) $ot_story['title'] ); ?></h2>
					<?php echo oomphtravel_quotation( (string) $ot_story['body'], oomphtravel_story_attribution( $ot_story ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<?php if ( ! empty( $ot_story['cruise'] ) ) : ?>
						<p class="ot-story__cruise"><?php echo oomphtravel_link( __( 'Read about cruising on CruiseOomph', 'oomphtravel' ), oomphtravel_cruiseoomph_url( '/', 'client-stories' ), true, array( 'rel' => 'noopener' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* 3. Where these come from. */ ?>
	<section class="ot-band ot-band--mist ot-stories-note">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'About these', 'oomphtravel' ), __( 'Why only four.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-prose ot-stories-prose">
				<p><?php esc_html_e( 'These are the reviews clients have chosen to post, and I only show the ones I can stand behind by name.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'I would rather show four real ones than forty I wrote myself. If you have travelled with me and want to add yours, tell me where you would like it to appear and I will send the link.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 4. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( home_url( '/start-planning/' ), __( 'Ready to add a story of your own?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
