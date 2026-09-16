<?php
/**
 * Title: Journal article
 * Slug: oomphtravel/journal-article
 * Inserter: no
 *
 * A journal post (plan §6.13), rendered by templates/single.html. Laid out
 * like CruiseOomph's story page (Eric, 2026-09-16): the kicker, title,
 * introduction (the excerpt; R14) and byline beside the featured image on
 * a mist band; the byline from the author's WordPress profile (Eric or
 * Amy; R13) with the reading time and the month it was last updated
 * (R15); the body at 720 px; the author box (name and bio, no portrait:
 * the invitation below carries it); the destination the post is
 * about as a card when a tag or category names one; three related posts;
 * and the advisor's invitation. BlogPosting schema comes from the plugin,
 * its author pointing at whichever advisor wrote it.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_post = get_post();
if ( ! $ot_post instanceof WP_Post ) {
	return;
}

$ot_byline  = oomphtravel_post_byline( $ot_post );
$ot_topic   = oomphtravel_post_topic( $ot_post );
$ot_thumb   = (int) get_post_thumbnail_id( $ot_post );
$ot_dek     = has_excerpt( $ot_post ) ? (string) wp_strip_all_tags( get_the_excerpt( $ot_post ) ) : '';
$ot_minutes = oomphtravel_read_minutes( $ot_post );
$ot_updated = get_the_modified_date( 'Y-m-d', $ot_post ) !== get_the_date( 'Y-m-d', $ot_post );
$ot_author  = oomphtravel_post_author_box( $ot_post );
$ot_dest    = oomphtravel_post_destination_card( $ot_post );
$ot_related = oomphtravel_related_posts( $ot_post );

$ot_hero_img = '';
$ot_caption  = '';
if ( $ot_thumb ) {
	$ot_alt      = trim( (string) get_post_meta( $ot_thumb, '_wp_attachment_image_alt', true ) );
	$ot_hero_img = oomphtravel_card_image( $ot_thumb, '' !== $ot_alt ? $ot_alt : (string) get_the_title( $ot_post ), '(max-width: 767px) calc(100vw - 48px), (max-width: 1023px) calc(100vw - 64px), 600px', true, array( 'class' => 'ot-article__feature-img' ) );
	$ot_caption  = trim( wp_strip_all_tags( (string) wp_get_attachment_caption( $ot_thumb ) ) );
}
?>
<article <?php post_class( 'ot-article' ); ?>>

	<?php /* 1. The story hero: kicker, title, introduction and byline beside the photograph. */ ?>
	<header class="ot-band ot-band--mist ot-article__hero<?php echo '' === $ot_hero_img ? ' ot-article__hero--text' : ''; ?>">
		<div class="ot-container ot-article__hero-inner">
			<div class="ot-article__hero-copy">
				<p class="ot-eyebrow ot-eyebrow--muted ot-article__kicker">
					<a href="<?php echo esc_url( home_url( '/journal/' ) ); ?>"><?php esc_html_e( 'Journal', 'oomphtravel' ); ?></a>
					<?php if ( '' !== $ot_topic['name'] && __( 'Journal', 'oomphtravel' ) !== $ot_topic['name'] ) : ?>
						<span class="ot-article__kicker-sep" aria-hidden="true">/</span>
						<?php if ( '' !== $ot_topic['url'] ) : ?>
							<a href="<?php echo esc_url( $ot_topic['url'] ); ?>"><?php echo esc_html( $ot_topic['name'] ); ?></a>
						<?php else : ?>
							<span><?php echo esc_html( $ot_topic['name'] ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
				</p>
				<h1 class="ot-article__title"><?php echo esc_html( get_the_title( $ot_post ) ); ?></h1>
				<?php if ( '' !== $ot_dek ) : ?>
					<p class="ot-article__dek"><?php echo esc_html( $ot_dek ); ?></p>
				<?php endif; ?>
				<p class="ot-article__byline">
					<?php esc_html_e( 'By', 'oomphtravel' ); ?>
					<a class="ot-article__author" href="<?php echo esc_url( $ot_byline['url'] ); ?>" rel="author"><?php echo esc_html( $ot_byline['name'] ); ?></a>
					<span aria-hidden="true">·</span>
					<?php echo esc_html( sprintf( /* translators: %d: minutes */ __( '%d minute read', 'oomphtravel' ), $ot_minutes ) ); ?>
					<span aria-hidden="true">·</span>
					<?php if ( $ot_updated ) : ?>
						<?php esc_html_e( 'Updated', 'oomphtravel' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $ot_post ) ); ?>"><?php echo esc_html( get_the_modified_date( 'F Y', $ot_post ) ); ?></time>
					<?php else : ?>
						<time datetime="<?php echo esc_attr( get_the_date( 'c', $ot_post ) ); ?>"><?php echo esc_html( get_the_date( 'F Y', $ot_post ) ); ?></time>
					<?php endif; ?>
				</p>
			</div>
			<?php if ( '' !== $ot_hero_img ) : ?>
				<figure class="ot-article__feature">
					<?php echo $ot_hero_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<?php if ( '' !== $ot_caption ) : ?>
						<figcaption class="ot-article__caption"><?php echo esc_html( $ot_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</div>
	</header>

	<?php /* 2. The body. */ ?>
	<div class="ot-band ot-article__body-band">
		<div class="ot-container">
			<div class="ot-article__measure">
				<div class="ot-article__body">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</div>

	<?php /* 3. Who wrote it. */ ?>
	<section class="ot-band ot-article__author-band" aria-labelledby="ot-article-author-name">
		<div class="ot-container">
			<div class="ot-article__author-box">
				<div class="ot-article__author-copy">
					<?php echo oomphtravel_eyebrow( __( 'Written by', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<h2 class="ot-article__author-name" id="ot-article-author-name"><a href="<?php echo esc_url( $ot_author['url'] ); ?>"><?php echo esc_html( $ot_author['name'] ); ?></a></h2>
					<?php if ( '' !== $ot_author['bio'] ) : ?>
						<p class="ot-article__author-bio"><?php echo esc_html( $ot_author['bio'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

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

	<?php /* 5. Related. */ ?>
	<?php if ( $ot_related ) : ?>
	<section class="ot-band ot-article__related">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Keep reading', 'oomphtravel' ), __( 'More from the Journal.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-grid ot-grid--3 ot-journal-cards">
				<?php foreach ( $ot_related as $ot_r ) : ?>
					<?php echo oomphtravel_card_journal( $ot_r ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php endforeach; ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'All journal posts', 'oomphtravel' ), home_url( '/journal/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 6. Start planning. */ ?>
	<?php
	echo oomphtravel_journal_invitation( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
		__( 'One advisor', 'oomphtravel' ),
		__( 'Want to go?', 'oomphtravel' ),
		__( 'Tell me what appealed to you and when you could travel. I will compare the realistic options and say which deserves a closer look.', 'oomphtravel' )
	);
	?>

</article>
