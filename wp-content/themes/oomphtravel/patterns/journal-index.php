<?php
/**
 * Title: Journal index
 * Slug: oomphtravel/journal-index
 * Inserter: no
 *
 * The Journal index (plan §6.13; D16), rendered by
 * templates/page-journal.html at /journal/. A hero band, the topic tabs
 * (All, then each of the four topics that has a post), the card grid nine
 * a page with the PlainSend newsletter field between the second and third
 * rows, pagination, and the closing invitation. A chosen topic is a
 * shareable address that search engines are told not to index.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_query = oomphtravel_journal_query();
$ot_tabs  = oomphtravel_journal_tabs();
$ot_topic = oomphtravel_journal_topic();
$ot_label = '' !== $ot_topic ? (string) ( oomphtravel_journal_topics()[ $ot_topic ] ?? '' ) : '';
$ot_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
?>
<div class="ot-journal">

	<?php /* 1. Hero band. */ ?>
	<section class="ot-dest-hero ot-dest-hero--bare ot-journal-hero" aria-labelledby="ot-journal-title">
		<div class="ot-container ot-dest-hero__inner">
			<div class="ot-dest-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Journal', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-dest-hero__title" id="ot-journal-title"><?php esc_html_e( 'Notes from the road.', 'oomphtravel' ); ?></h1>
				<p class="ot-journal-hero__lead"><?php esc_html_e( 'Destinations, planning, resorts and villas, and escorted tours, written after going there. What is worth knowing before you book, from the two of us who book it.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 2. Topic tabs. */ ?>
	<?php if ( count( $ot_tabs ) > 1 ) : ?>
	<nav class="ot-band ot-journal-tabs" aria-label="<?php esc_attr_e( 'Journal topics', 'oomphtravel' ); ?>">
		<div class="ot-container">
			<ul class="ot-journal-tabs__list">
				<?php foreach ( $ot_tabs as $ot_tab ) : ?>
					<li>
						<a class="ot-journal-tabs__tab<?php echo $ot_tab['current'] ? ' is-current' : ''; ?>" href="<?php echo esc_url( $ot_tab['url'] ); ?>"<?php echo $ot_tab['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ot_tab['label'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</nav>
	<?php endif; ?>

	<?php /* 3. The grid, the signup after the sixth card, pagination. */ ?>
	<section class="ot-band ot-journal-grid" aria-label="<?php esc_attr_e( 'Journal posts', 'oomphtravel' ); ?>">
		<div class="ot-container">
			<?php if ( $ot_query->have_posts() ) : ?>
				<?php if ( '' !== $ot_label ) : ?>
					<p class="ot-journal-grid__filter"><?php echo esc_html( sprintf( /* translators: %s: topic label */ __( 'Showing %s.', 'oomphtravel' ), $ot_label ) ); ?></p>
				<?php endif; ?>
				<div class="ot-grid ot-grid--3 ot-journal-grid__cards">
					<?php
					$ot_i = 0;
					while ( $ot_query->have_posts() ) :
						$ot_query->the_post();
						echo oomphtravel_card_journal( get_post(), 1 === $ot_paged && 0 === $ot_i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
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

	<?php /* 4. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( home_url( '/start-planning/' ), __( 'Read enough? Let’s plan it.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
