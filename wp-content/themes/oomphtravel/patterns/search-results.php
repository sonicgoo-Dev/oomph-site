<?php
/**
 * Title: Search results
 * Slug: oomphtravel/search-results
 * Inserter: no
 *
 * Where the 404 page's search field leads (plan §6.16), rendered by
 * templates/search.html. A short navy band with the query and the field
 * again, then each match as a plain row: what it is, its title, one line.
 * Pages, posts, destinations, tours and operators are all searchable; the
 * private Inquiry records are excluded by their post type. WordPress marks
 * search results noindex on its own.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_query = get_search_query();
$ot_count = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
?>
<div class="ot-search-page">

	<section class="ot-dest-hero ot-dest-hero--bare ot-search-hero" aria-labelledby="ot-search-title">
		<div class="ot-container ot-dest-hero__inner">
			<div class="ot-dest-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'Search', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-dest-hero__title" id="ot-search-title">
					<?php
					if ( '' === $ot_query ) {
						esc_html_e( 'What are you looking for?', 'oomphtravel' );
					} else {
						/* translators: %s: what was searched for */
						echo esc_html( sprintf( __( 'Results for “%s”', 'oomphtravel' ), $ot_query ) );
					}
					?>
				</h1>
				<?php if ( '' !== $ot_query ) : ?>
					<p class="ot-search-hero__count">
						<?php
						/* translators: %d: how many results */
						echo esc_html( sprintf( _n( '%d match.', '%d matches.', $ot_count, 'oomphtravel' ), $ot_count ) );
						?>
					</p>
				<?php endif; ?>
				<?php echo oomphtravel_search_form( 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		</div>
	</section>

	<section class="ot-band ot-search-results" aria-label="<?php esc_attr_e( 'Search results', 'oomphtravel' ); ?>">
		<div class="ot-container">
			<?php if ( have_posts() ) : ?>
				<ol class="ot-search-list">
					<?php
					while ( have_posts() ) :
						the_post();
						$ot_post    = get_post();
						$ot_excerpt = $ot_post instanceof WP_Post ? wp_trim_words( wp_strip_all_tags( (string) get_the_excerpt( $ot_post ) ), 28, '…' ) : '';
						?>
						<li class="ot-search-result">
							<?php echo oomphtravel_eyebrow( $ot_post instanceof WP_Post ? oomphtravel_search_result_kind( $ot_post ) : '', true, 'span' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
							<h2 class="ot-search-result__title"><a href="<?php echo esc_url( (string) get_permalink() ); ?>"><?php echo esc_html( (string) get_the_title() ); ?></a></h2>
							<?php if ( '' !== $ot_excerpt ) : ?>
								<p class="ot-search-result__excerpt"><?php echo esc_html( $ot_excerpt ); ?></p>
							<?php endif; ?>
						</li>
					<?php endwhile; ?>
				</ol>
				<?php
				$ot_pagination = get_the_posts_pagination(
					array(
						'mid_size'           => 1,
						'prev_text'          => __( 'Newer', 'oomphtravel' ),
						'next_text'          => __( 'Older', 'oomphtravel' ),
						'screen_reader_text' => __( 'More results', 'oomphtravel' ),
						'class'              => 'ot-search-pagination',
					)
				);
				echo wp_kses_post( $ot_pagination );
				?>
			<?php else : ?>
				<div class="ot-search-empty">
					<h2 class="ot-search-empty__title"><?php esc_html_e( 'Nothing matched.', 'oomphtravel' ); ?></h2>
					<p class="ot-search-empty__lead"><?php esc_html_e( 'Try a place name, or start from one of the three doors.', 'oomphtravel' ); ?></p>
					<?php echo oomphtravel_doors_list( 'h3' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>
