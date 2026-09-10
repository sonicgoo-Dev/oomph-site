<?php
/**
 * Title: Site footer
 * Slug: oomphtravel/footer
 * Inserter: no
 *
 * Footer from docs/02-components.md, forked from CruiseOomph's footer (D41).
 * Deep navy. The signup row posts straight to PlainSend (D30) — email,
 * honeypot and timing field; double opt-in means a confirmation email
 * follows. Four columns (plan §4.1): Destinations · Ways to travel · About &
 * Journal · Get in touch. Then the OomphTravel / CruiseOomph family lockup
 * and the legal links. The Destinations column includes Africa (D33) between
 * "Croatia & the Adriatic" and "Hawaii · Mexico · Caribbean".
 *
 * The `footer` menu location, when assigned, replaces the About & Journal
 * column; everything else is structural and stays in code so the year is
 * never stale and the CruiseOomph links always carry their UTM tag.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_destinations = array(
	array( __( 'Italy', 'oomphtravel' ), home_url( '/destinations/italy/' ) ),
	array( __( 'UK & Ireland', 'oomphtravel' ), home_url( '/destinations/uk-ireland/' ) ),
	array( __( 'France', 'oomphtravel' ), home_url( '/destinations/france/' ) ),
	array( __( 'Spain', 'oomphtravel' ), home_url( '/destinations/spain/' ) ),
	array( __( 'Portugal', 'oomphtravel' ), home_url( '/destinations/portugal/' ) ),
	array( __( 'Greece', 'oomphtravel' ), home_url( '/destinations/greece/' ) ),
	array( __( 'Croatia & the Adriatic', 'oomphtravel' ), home_url( '/destinations/croatia/' ) ),
	array( __( 'Africa', 'oomphtravel' ), home_url( '/destinations/africa/' ) ),
);

// One row, three links (docs/02-components.md keeps these on a single line).
$ot_sun_sea = array(
	array( __( 'Hawaii', 'oomphtravel' ), home_url( '/destinations/hawaii/' ) ),
	array( __( 'Mexico', 'oomphtravel' ), home_url( '/destinations/mexico/' ) ),
	array( __( 'Caribbean', 'oomphtravel' ), home_url( '/destinations/caribbean/' ) ),
);

$ot_ways = array(
	array( __( 'Custom journeys', 'oomphtravel' ), home_url( '/custom-journeys/' ) ),
	array( __( 'Escorted tours', 'oomphtravel' ), home_url( '/escorted-tours/' ) ),
	array( __( 'Resorts & villas', 'oomphtravel' ), home_url( '/resorts-and-villas/' ) ),
	array( __( 'Multi-generational trips', 'oomphtravel' ), home_url( '/multi-generational-travel-planning/' ) ),
	array( __( 'Cruise planning', 'oomphtravel' ), home_url( '/cruise-planning/' ) ),
);

$ot_about = array(
	array( __( 'About', 'oomphtravel' ), home_url( '/about/' ) ),
	array( __( 'Journal', 'oomphtravel' ), home_url( '/journal/' ) ),
	array( __( 'Client stories', 'oomphtravel' ), home_url( '/client-stories/' ) ),
	array( __( 'Travel trends', 'oomphtravel' ), home_url( '/travel-trends/' ) ),
	array( __( 'Start planning', 'oomphtravel' ), home_url( '/start-planning/' ) ),
);

$ot_footer_menu = has_nav_menu( 'footer' )
	? wp_nav_menu(
		array(
			'theme_location' => 'footer',
			'container'      => false,
			'menu_class'     => 'ot-footer__list',
			'depth'          => 1,
			'echo'           => false,
			'fallback_cb'    => false,
		)
	)
	: '';

// Same person, same profiles as CruiseOomph's footer.
$ot_social = array(
	array(
		'name' => __( 'Facebook', 'oomphtravel' ),
		'url'  => 'https://www.facebook.com/profile.php?id=100087673102528',
		'icon' => '<path d="M13.5 22v-8h2.7l.4-3.2h-3.1V8.8c0-.9.3-1.6 1.6-1.6h1.7V4.4c-.3 0-1.3-.1-2.5-.1-2.5 0-4.1 1.5-4.1 4.2v2.3H7.4V14h2.8v8h3.3z"/>',
	),
	array(
		'name' => __( 'Instagram', 'oomphtravel' ),
		'url'  => 'https://www.instagram.com/oomph_travel/',
		'icon' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3.8" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17" cy="7" r="1.1"/>',
	),
	array(
		'name' => __( 'LinkedIn', 'oomphtravel' ),
		'url'  => 'https://www.linkedin.com/in/erichempeloomphtravel/',
		'icon' => '<path d="M6.9 9.2H4V20h2.9V9.2zM5.4 4.2a1.7 1.7 0 1 0 0 3.4 1.7 1.7 0 0 0 0-3.4zM20 13.4c0-3.2-1.7-4.6-4-4.6-1.8 0-2.7 1-3.1 1.7V9.2H10V20h2.9v-5.8c0-1.5.3-3 2.2-3 1.9 0 1.9 1.8 1.9 3.1V20H20v-6.6z"/>',
	),
);

$ot_new_tab = '<span class="ot-visually-hidden"> ' . esc_html__( '(opens in a new tab)', 'oomphtravel' ) . '</span>';

// Legal links render only for pages that exist, so nothing here 404s.
$ot_legal = array();
foreach ( array( 'privacy-policy' => __( 'Privacy', 'oomphtravel' ), 'accessibility' => __( 'Accessibility', 'oomphtravel' ) ) as $ot_slug => $ot_label ) {
	$ot_page = get_page_by_path( $ot_slug );
	if ( $ot_page instanceof WP_Post && 'publish' === $ot_page->post_status ) {
		$ot_legal[] = sprintf( '<a href="%s">%s</a>', esc_url( (string) get_permalink( $ot_page ) ), esc_html( $ot_label ) );
	}
}
?>
<div class="ot-footer ot-band--navy-deep">
	<div class="ot-footer__inner">

		<div class="ot-footer__signup">
			<div>
				<h2 class="ot-footer__signup-heading"><?php esc_html_e( 'Trip notes, a few times a year', 'oomphtravel' ); ?></h2>
				<p class="ot-footer__signup-copy"><?php esc_html_e( 'First-hand notes from the road. One email to confirm, then now and then.', 'oomphtravel' ); ?></p>
			</div>
			<div>
				<?php
				if ( function_exists( 'oomphtravel_signup_form' ) ) {
					oomphtravel_signup_form( 'newsletter', array( 'source' => 'footer' ) );
				}
				?>
				<p class="ot-footer__signup-note"><?php esc_html_e( 'Unsubscribe in one click, any time.', 'oomphtravel' ); ?></p>
			</div>
		</div>

		<div class="ot-footer__columns">
			<nav class="ot-footer__col" aria-label="<?php esc_attr_e( 'Destinations', 'oomphtravel' ); ?>">
				<?php echo oomphtravel_eyebrow( __( 'Destinations', 'oomphtravel' ), false, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<ul class="ot-footer__list">
					<?php foreach ( $ot_destinations as $ot_link ) : ?>
						<li><a href="<?php echo esc_url( $ot_link[1] ); ?>"><?php echo esc_html( $ot_link[0] ); ?></a></li>
					<?php endforeach; ?>
					<li>
						<?php
						$ot_parts = array();
						foreach ( $ot_sun_sea as $ot_link ) {
							$ot_parts[] = sprintf( '<a href="%s">%s</a>', esc_url( $ot_link[1] ), esc_html( $ot_link[0] ) );
						}
						echo implode( ' <span aria-hidden="true">&middot;</span> ', $ot_parts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
						?>
					</li>
					<li><a href="<?php echo esc_url( home_url( '/destinations/' ) ); ?>"><?php esc_html_e( 'All destinations', 'oomphtravel' ); ?></a></li>
				</ul>
			</nav>

			<nav class="ot-footer__col" aria-label="<?php esc_attr_e( 'Ways to travel', 'oomphtravel' ); ?>">
				<?php echo oomphtravel_eyebrow( __( 'Ways to travel', 'oomphtravel' ), false, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<ul class="ot-footer__list">
					<?php foreach ( $ot_ways as $ot_link ) : ?>
						<li><a href="<?php echo esc_url( $ot_link[1] ); ?>"><?php echo esc_html( $ot_link[0] ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( oomphtravel_cruiseoomph_url( '/' ) ); ?>"><?php esc_html_e( 'CruiseOomph', 'oomphtravel' ); ?> <?php echo oomphtravel_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></a></li>
				</ul>
			</nav>

			<nav class="ot-footer__col" aria-label="<?php esc_attr_e( 'About and Journal', 'oomphtravel' ); ?>">
				<?php echo oomphtravel_eyebrow( __( 'Oomph Travel', 'oomphtravel' ), false, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php if ( is_string( $ot_footer_menu ) && '' !== $ot_footer_menu ) : ?>
					<?php echo $ot_footer_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
				<?php else : ?>
					<ul class="ot-footer__list">
						<?php foreach ( $ot_about as $ot_link ) : ?>
							<li><a href="<?php echo esc_url( $ot_link[1] ); ?>"><?php echo esc_html( $ot_link[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</nav>

			<div class="ot-footer__col">
				<?php echo oomphtravel_eyebrow( __( 'Get in touch', 'oomphtravel' ), false, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<ul class="ot-footer__list ot-footer__contact">
					<li><a href="mailto:eric@oomphtravel.com">eric@oomphtravel.com</a></li>
					<li><a href="tel:+13607754644">(360) 775-4644</a></li>
					<li><?php esc_html_e( 'Port Angeles, WA', 'oomphtravel' ); ?></li>
				</ul>
				<p class="ot-footer__credentials"><?php esc_html_e( 'CLIA · Silversea Ultra-Luxury Specialist · Nexion / Travel Leaders Network · BritAgent Pro', 'oomphtravel' ); ?></p>
				<ul class="ot-footer__social" aria-label="<?php esc_attr_e( 'Oomph Travel on social media', 'oomphtravel' ); ?>">
					<?php foreach ( $ot_social as $ot_s ) : ?>
						<li>
							<a href="<?php echo esc_url( $ot_s['url'] ); ?>" target="_blank" rel="noopener">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><?php echo $ot_s['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup above. ?></svg>
								<span class="ot-visually-hidden"><?php echo esc_html( $ot_s['name'] ); ?><?php echo $ot_new_tab; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<div class="ot-footer__bottom">
			<div class="ot-family">
				<a class="ot-family__name" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'OomphTravel', 'oomphtravel' ); ?></a>
				<span class="ot-family__rule" aria-hidden="true"></span>
				<a class="ot-family__name" href="<?php echo esc_url( oomphtravel_cruiseoomph_url( '/' ) ); ?>"><?php esc_html_e( 'CruiseOomph', 'oomphtravel' ); ?></a>
				<p class="ot-family__note"><?php esc_html_e( 'Two sites, one advisor.', 'oomphtravel' ); ?></p>
			</div>
			<div class="ot-footer__legal">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'Oomph Travel LLC', 'oomphtravel' ); ?></span>
				<?php echo implode( ' ', $ot_legal ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
			</div>
		</div>

	</div>
</div>
