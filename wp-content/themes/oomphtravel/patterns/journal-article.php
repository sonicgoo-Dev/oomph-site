<?php
/**
 * Title: Journal article
 * Slug: oomphtravel/journal-article
 * Inserter: no
 *
 * A journal post (plan §6.13), rendered by templates/single.html. The
 * featured image full width when there is one, the title with the byline
 * from the author's WordPress profile (Eric or Amy; R13), the published and
 * updated dates (R15), an "At a glance" box from the excerpt (R14), the
 * body at 720 px, the destination the post is about as a card when a tag
 * or category names one, the Start planning invitation, and three related
 * posts. BlogPosting schema comes from the plugin, its author pointing at
 * whichever advisor wrote it.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_post = get_post();
if ( ! $ot_post instanceof WP_Post ) {
	return;
}

$ot_byline    = oomphtravel_post_byline( $ot_post );
$ot_cats      = get_the_category( $ot_post->ID );
$ot_eyebrow   = $ot_cats ? (string) $ot_cats[0]->name : __( 'Journal', 'oomphtravel' );
$ot_published = get_the_date( '', $ot_post );
$ot_modified  = get_the_modified_date( '', $ot_post );
$ot_thumb     = (int) get_post_thumbnail_id( $ot_post );
$ot_glance    = has_excerpt( $ot_post ) ? (string) wp_strip_all_tags( get_the_excerpt( $ot_post ) ) : '';
$ot_dest      = oomphtravel_post_destination_card( $ot_post );
$ot_related   = oomphtravel_related_posts( $ot_post );
?>
<article <?php post_class( 'ot-article' ); ?>>

	<?php /* 1. The photograph, full width, first thing loaded. */ ?>
	<?php if ( $ot_thumb ) : ?>
		<figure class="ot-article__feature">
			<?php echo wp_get_attachment_image( $ot_thumb, 'full', false, array( 'class' => 'ot-article__feature-img', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '100vw' ) ); ?>
		</figure>
	<?php endif; ?>

	<?php /* 2. Title and byline. */ ?>
	<header class="ot-band ot-article__header">
		<div class="ot-container ot-article__measure">
			<?php echo oomphtravel_eyebrow( $ot_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-article__title"><?php echo esc_html( get_the_title( $ot_post ) ); ?></h1>
			<p class="ot-article__byline">
				<?php esc_html_e( 'By', 'oomphtravel' ); ?>
				<a class="ot-article__author" href="<?php echo esc_url( $ot_byline['url'] ); ?>" rel="author"><?php echo esc_html( $ot_byline['name'] ); ?></a>
				<span aria-hidden="true">·</span>
				<time datetime="<?php echo esc_attr( get_the_date( 'c', $ot_post ) ); ?>"><?php echo esc_html( $ot_published ); ?></time>
				<?php if ( $ot_modified !== $ot_published ) : ?>
					<span class="ot-article__updated"><span aria-hidden="true">·</span> <?php esc_html_e( 'Updated', 'oomphtravel' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $ot_post ) ); ?>"><?php echo esc_html( $ot_modified ); ?></time></span>
				<?php endif; ?>
			</p>
		</div>
	</header>

	<?php /* 3. At a glance, then the body. */ ?>
	<div class="ot-band ot-article__body-band">
		<div class="ot-container ot-article__measure">
			<?php if ( '' !== $ot_glance ) : ?>
				<aside class="ot-article__glance" aria-labelledby="ot-article-glance">
					<?php echo oomphtravel_eyebrow( __( 'At a glance', 'oomphtravel' ), false, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<p class="ot-article__glance-text" id="ot-article-glance"><?php echo esc_html( $ot_glance ); ?></p>
				</aside>
			<?php endif; ?>
			<div class="ot-article__body">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

	<?php /* 4. The destination this is about. */ ?>
	<?php if ( $ot_dest ) : ?>
	<section class="ot-band ot-band--mist ot-article__destination">
		<div class="ot-container ot-article__destination-inner">
			<div>
				<?php echo oomphtravel_section_heading( __( 'The place', 'oomphtravel' ), sprintf( /* translators: %s: destination name */ __( 'Planning %s?', 'oomphtravel' ), (string) $ot_dest['name'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<p class="ot-article__destination-note"><?php esc_html_e( 'The destination page has the seasons, the regions and the ways I plan it.', 'oomphtravel' ); ?></p>
			</div>
			<div class="ot-article__destination-card">
				<?php echo oomphtravel_card_destination( $ot_dest ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 5. Start planning. */ ?>
	<?php echo oomphtravel_closing_band( home_url( '/start-planning/' ), __( 'Want to go?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

	<?php /* 6. Related. */ ?>
	<?php if ( $ot_related ) : ?>
	<section class="ot-band ot-article__related">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Keep reading', 'oomphtravel' ), __( 'More from the Journal.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--3">
				<?php foreach ( $ot_related as $ot_r ) : ?>
					<?php echo oomphtravel_card_journal( $ot_r ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All journal posts', 'oomphtravel' ), home_url( '/journal/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

</article>
