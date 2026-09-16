<?php
/**
 * Title: Journal index
 * Slug: oomphtravel/journal-index
 * Inserter: no
 *
 * The Journal index (plan §6.13; D16), rendered by
 * templates/page-journal.html at /journal/. Laid out like CruiseOomph's
 * Cruise Journal (Eric, 2026-09-16): the navy hero with the newest post's
 * photograph, the topic chips, the featured story, the card grid nine a
 * page with the PlainSend newsletter field after the sixth card,
 * pagination, and the advisor's invitation. A chosen topic is a shareable
 * address that search engines are told not to index.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_query    = oomphtravel_journal_query();
$ot_tabs     = oomphtravel_journal_tabs();
$ot_topic    = oomphtravel_journal_topic();
$ot_label    = '' !== $ot_topic ? (string) ( oomphtravel_journal_topics()[ $ot_topic ] ?? '' ) : '';
$ot_paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$ot_posts    = array_values( array_filter( (array) $ot_query->posts, static fn( $p ): bool => $p instanceof WP_Post ) );
$ot_featured = ( 1 === $ot_paged && '' === $ot_topic && $ot_posts ) ? $ot_posts[0] : null;
$ot_hero_id  = $ot_featured ? (int) get_post_thumbnail_id( $ot_featured ) : 0;
$ot_f_url    = $ot_featured ? (string) get_permalink( $ot_featured ) : '';
$ot_f_topic  = $ot_featured ? oomphtravel_post_topic( $ot_featured ) : array( 'name' => '', 'url' => '' );

/** A post's featured image at the size a slot needs, alt from the library. */
$ot_picture = static function ( WP_Post $p, string $sizes, bool $eager, string $class ): string {
	$id = (int) get_post_thumbnail_id( $p );
	if ( ! $id ) {
		return '';
	}
	$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
	return oomphtravel_card_image( $id, '' !== $alt ? $alt : (string) get_the_title( $p ), $sizes, $eager, array( 'class' => $class ) );
};
?>
<div class="ot-journal">

	<?php /* 1. Hero: the copy beside the newest post's photograph. */ ?>
	<section class="ot-band ot-band--navy ot-journal-hero<?php echo $ot_hero_id ? '' : ' ot-journal-hero--text'; ?>" aria-labelledby="ot-journal-title">
		<div class="ot-container ot-journal-hero__inner">
			<div class="ot-journal-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Journal', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-journal-hero__title" id="ot-journal-title"><?php esc_html_e( 'Notes from the road.', 'oomphtravel' ); ?></h1>
				<p class="ot-journal-hero__lead"><?php esc_html_e( 'Destinations, planning, resorts and villas, and escorted tours, written after going there. What is worth knowing before you book, from the two of us who book it.', 'oomphtravel' ); ?></p>
			</div>
			<?php if ( $ot_hero_id && $ot_featured ) : ?>
				<figure class="ot-journal-hero__media">
					<a href="<?php echo esc_url( $ot_f_url ); ?>" tabindex="-1" aria-hidden="true"><?php echo $ot_picture( $ot_featured, '(max-width: 767px) calc(100vw - 48px), (max-width: 1023px) calc(100vw - 64px), 600px', true, 'ot-journal-hero__img' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></a>
					<figcaption class="ot-journal-hero__caption"><?php echo esc_html( __( 'Featured story', 'oomphtravel' ) . ' · ' . $ot_f_topic['name'] ); ?></figcaption>
				</figure>
			<?php endif; ?>
		</div>
	</section>

	<?php /* 2. Topic chips: All, then each topic that has a post. */ ?>
	<section class="ot-band ot-journal-topics">
		<div class="ot-container">
			<nav class="ot-journal-tabs" aria-label="<?php esc_attr_e( 'Journal topics', 'oomphtravel' ); ?>">
				<p class="ot-journal-tabs__title"><?php esc_html_e( 'Explore the journal', 'oomphtravel' ); ?></p>
				<ul class="ot-journal-tabs__list">
					<?php foreach ( $ot_tabs as $ot_tab ) : ?>
						<li>
							<a class="ot-journal-tabs__tab<?php echo $ot_tab['current'] ? ' is-current' : ''; ?>" href="<?php echo esc_url( $ot_tab['url'] ); ?>"<?php echo $ot_tab['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ot_tab['label'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
	</section>

	<?php /* 3. The featured story: the newest post, once there is more than one. */ ?>
	<?php if ( $ot_featured && count( $ot_posts ) > 1 ) : ?>
		<?php
		$ot_f_img = $ot_picture( $ot_featured, '(max-width: 767px) calc(100vw - 48px), (max-width: 1023px) calc(100vw - 64px), 700px', false, 'ot-journal-featured__img' );
		$ot_f_dek = has_excerpt( $ot_featured ) ? trim( wp_strip_all_tags( (string) $ot_featured->post_excerpt ) ) : '';
		?>
		<section class="ot-band ot-journal-featured" aria-labelledby="ot-journal-featured-title">
			<div class="ot-container">
				<?php echo oomphtravel_eyebrow( __( 'Featured story', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h2 class="ot-journal-featured__title" id="ot-journal-featured-title"><a href="<?php echo esc_url( $ot_f_url ); ?>"><?php echo esc_html( get_the_title( $ot_featured ) ); ?></a></h2>
				<div class="ot-journal-featured__grid<?php echo '' === $ot_f_img ? ' ot-journal-featured__grid--text' : ''; ?>">
					<?php if ( '' !== $ot_f_img ) : ?>
						<a class="ot-journal-featured__media" href="<?php echo esc_url( $ot_f_url ); ?>" tabindex="-1" aria-hidden="true"><?php echo $ot_f_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></a>
					<?php endif; ?>
					<div class="ot-journal-featured__copy">
						<p class="ot-eyebrow ot-eyebrow--muted ot-journal-featured__kicker"><?php echo esc_html( $ot_f_topic['name'] . ' · ' . sprintf( /* translators: %d: minutes */ __( '%d min read', 'oomphtravel' ), oomphtravel_read_minutes( $ot_featured ) ) ); ?></p>
						<?php if ( '' !== $ot_f_dek ) : ?>
							<p class="ot-journal-featured__dek"><?php echo esc_html( $ot_f_dek ); ?></p>
						<?php endif; ?>
						<p class="ot-journal-featured__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $ot_featured->post_content ) ), 32, '…' ) ); ?></p>
						<?php echo oomphtravel_button( __( 'Read the story', 'oomphtravel' ), $ot_f_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* 4. The grid, the signup after the sixth card, pagination. */ ?>
	<section class="ot-band ot-journal-grid" aria-label="<?php esc_attr_e( 'Journal posts', 'oomphtravel' ); ?>">
		<div class="ot-container">
			<?php if ( $ot_query->have_posts() ) : ?>
				<?php if ( '' !== $ot_label ) : ?>
					<p class="ot-journal-grid__filter"><?php echo esc_html( sprintf( /* translators: %s: topic label */ __( 'Showing %s.', 'oomphtravel' ), $ot_label ) ); ?></p>
				<?php endif; ?>
				<?php
				echo oomphtravel_section_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					'' !== $ot_label ? $ot_label : __( 'Latest from the Journal', 'oomphtravel' ),
					$ot_paged > 1 ? sprintf( /* translators: %d: page number */ __( 'More notes, page %d', 'oomphtravel' ), $ot_paged ) : __( 'The latest notes.', 'oomphtravel' ),
					'h2',
					'ot-journal-grid__heading'
				);
				?>
				<div class="ot-grid ot-grid--3 ot-journal-grid__cards ot-journal-cards">
					<?php
					$ot_i = 0;
					while ( $ot_query->have_posts() ) :
						$ot_query->the_post();
						echo oomphtravel_card_journal( get_post(), 1 === $ot_paged && 0 === $ot_i && ! $ot_hero_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						++$ot_i;
						if ( 6 === $ot_i && $ot_query->post_count > 6 ) :
							?>
							<div class="ot-journal-signup ot-band--navy">
								<div class="ot-journal-signup__copy">
									<?php echo oomphtravel_section_heading( __( 'A few notes a year', 'oomphtravel' ), __( 'New posts, by email.', 'oomphtravel' ), 'h2', 'ot-journal-signup__heading' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
									<p class="ot-journal-signup__note"><?php esc_html_e( 'When something is worth reading, I send it. Unsubscribe in one click, any time.', 'oomphtravel' ); ?></p>
								</div>
								<div>
									<?php oomphtravel_signup_form( 'newsletter', array( 'source' => 'journal', 'id' => 'ot-signup-journal' ) ); ?>
								</div>
							</div>
							<?php
						endif;
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
				$ot_pagination = paginate_links(
					array(
						'total'     => (int) $ot_query->max_num_pages,
						'current'   => $ot_paged,
						'type'      => 'list',
						'add_args'  => '' !== $ot_topic ? array( 'topic' => $ot_topic ) : false,
						'prev_text' => __( 'Newer', 'oomphtravel' ),
						'next_text' => __( 'Older', 'oomphtravel' ),
					)
				);
				if ( $ot_pagination ) :
					?>
					<nav class="ot-journal-pagination" aria-label="<?php esc_attr_e( 'More journal pages', 'oomphtravel' ); ?>"><?php echo wp_kses_post( $ot_pagination ); ?></nav>
				<?php endif; ?>
			<?php else : ?>
				<div class="ot-prose ot-journal-empty">
					<p><?php esc_html_e( 'Nothing here yet.', 'oomphtravel' ); ?></p>
					<p><?php esc_html_e( 'The first notes are being written. In the meantime the destination pages carry the practical part, and I am one message away.', 'oomphtravel' ); ?></p>
					<p><?php echo oomphtravel_link( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* 5. The advisor's invitation. */ ?>
	<?php
	echo oomphtravel_journal_invitation( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
		__( 'Next step', 'oomphtravel' ),
		__( 'Read enough? Let’s plan it.', 'oomphtravel' ),
		__( 'Tell me what caught your eye, the place, the season or the pace, and I will turn it into a plan you can actually book.', 'oomphtravel' )
	);
	?>

</div>
