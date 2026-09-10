<?php
/**
 * Title: Site header
 * Slug: oomphtravel/header
 * Inserter: no
 *
 * Header / Desktop (State=Default|Scrolled) and Header / Mobile from
 * docs/02-components.md, forked from CruiseOomph's header pattern (D41).
 *
 * Desktop: thin white bar, symbol + Fraunces wordmark left, four nav items and
 * one teal button right. Scrolled (shell.js adds `is-scrolled`): 64px,
 * smaller lockup, hairline becomes a soft shadow. No utility bar, no phone.
 * Mobile (< 1024px): symbol and wordmark, a plain "Start planning" text link
 * and the Menu button; the menu is a full-screen navy drawer with a subtitle
 * under every link, and the two dropdowns read as plain lists.
 *
 * Navigation comes from the `primary` menu location when one is assigned
 * (depth 2: a child item becomes a dropdown entry; the item description
 * becomes the drawer subtitle). Without one, the approved structure from plan
 * §4.1 renders as the fallback below.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_start_url = home_url( '/start-planning/' );

/*
 * Fallback structure (plan §4.1). Subtitles are draft copy for the drawer —
 * replace via the menu item description once Eric assigns a Primary menu.
 * Africa (D33) sits after the two groups; the plan's mega-menu predates it.
 */
$ot_nav = array(
	array(
		'label'    => __( 'Destinations', 'oomphtravel' ),
		'url'      => home_url( '/destinations/' ),
		'subtitle' => __( 'Europe, sun & sea, and Africa', 'oomphtravel' ),
		'groups'   => array(
			array(
				'title' => __( 'Europe', 'oomphtravel' ),
				'links' => array(
					array( __( 'Italy', 'oomphtravel' ), home_url( '/destinations/italy/' ) ),
					array( __( 'UK & Ireland', 'oomphtravel' ), home_url( '/destinations/uk-ireland/' ) ),
					array( __( 'France', 'oomphtravel' ), home_url( '/destinations/france/' ) ),
					array( __( 'Spain', 'oomphtravel' ), home_url( '/destinations/spain/' ) ),
					array( __( 'Portugal', 'oomphtravel' ), home_url( '/destinations/portugal/' ) ),
					array( __( 'Greece', 'oomphtravel' ), home_url( '/destinations/greece/' ) ),
					array( __( 'Croatia & the Adriatic', 'oomphtravel' ), home_url( '/destinations/croatia/' ) ),
				),
			),
			array(
				'title' => __( 'Sun & sea', 'oomphtravel' ),
				'links' => array(
					array( __( 'Hawaii', 'oomphtravel' ), home_url( '/destinations/hawaii/' ) ),
					array( __( 'Mexico', 'oomphtravel' ), home_url( '/destinations/mexico/' ) ),
					array( __( 'Caribbean', 'oomphtravel' ), home_url( '/destinations/caribbean/' ) ),
				),
			),
		),
		'foot'     => array(
			array( __( 'Africa', 'oomphtravel' ), home_url( '/destinations/africa/' ) ),
			array( __( 'All destinations', 'oomphtravel' ), home_url( '/destinations/' ), true ),
		),
	),
	array(
		'label'    => __( 'Ways to travel', 'oomphtravel' ),
		'url'      => '',
		'subtitle' => __( 'Custom journeys, escorted tours, resorts & villas', 'oomphtravel' ),
		'groups'   => array(
			array(
				'title' => '',
				'links' => array(
					array( __( 'Custom journeys', 'oomphtravel' ), home_url( '/custom-journeys/' ) ),
					array( __( 'Escorted tours', 'oomphtravel' ), home_url( '/escorted-tours/' ) ),
					array( __( 'Resorts & villas', 'oomphtravel' ), home_url( '/resorts-and-villas/' ) ),
					array( __( 'Multi-generational trips', 'oomphtravel' ), home_url( '/multi-generational-travel-planning/' ) ),
				),
			),
		),
		'foot'     => array(
			// Opens cruiseoomph.com in the same tab, marked with a small arrow (plan §4.1, §4.3).
			array( __( 'Cruises', 'oomphtravel' ), oomphtravel_cruiseoomph_url( '/' ), true, '↗' ),
		),
	),
	array(
		'label'    => __( 'Journal', 'oomphtravel' ),
		'url'      => home_url( '/journal/' ),
		'subtitle' => __( 'Articles by destination', 'oomphtravel' ),
	),
	array(
		'label'    => __( 'About', 'oomphtravel' ),
		'url'      => home_url( '/about/' ),
		'subtitle' => __( 'Eric and Amy', 'oomphtravel' ),
	),
);

/*
 * An assigned Primary menu replaces the fallback: top-level items become nav
 * items, their children a single dropdown list, descriptions the subtitles.
 */
if ( has_nav_menu( 'primary' ) ) {
	$ot_locations = get_nav_menu_locations();
	$ot_items     = wp_get_nav_menu_items( (int) $ot_locations['primary'] );
	if ( is_array( $ot_items ) && $ot_items ) {
		$ot_built = array();
		foreach ( $ot_items as $ot_item ) {
			if ( 0 === (int) $ot_item->menu_item_parent ) {
				$ot_built[ $ot_item->ID ] = array(
					'label'    => (string) $ot_item->title,
					'url'      => (string) $ot_item->url,
					'subtitle' => (string) $ot_item->description,
					'groups'   => array(),
					'foot'     => array(),
				);
			}
		}
		foreach ( $ot_items as $ot_item ) {
			$ot_parent = (int) $ot_item->menu_item_parent;
			if ( $ot_parent && isset( $ot_built[ $ot_parent ] ) ) {
				$ot_built[ $ot_parent ]['groups'][0]['title']   = '';
				$ot_built[ $ot_parent ]['groups'][0]['links'][] = array( (string) $ot_item->title, (string) $ot_item->url );
			}
		}
		$ot_nav = array_values( $ot_built );
	}
}

$ot_chevron = '<svg class="ot-nav__chevron" width="10" height="6" viewBox="0 0 10 6" aria-hidden="true" focusable="false"><path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';

/**
 * One dropdown link row, escaped.
 *
 * @param array $link [label, url, (bool) underlined, (string) arrow].
 * @return string
 */
$ot_menu_link = static function ( array $link ): string {
	$arrow = isset( $link[3] ) ? ' ' . oomphtravel_arrow( (string) $link[3] ) : '';
	return sprintf(
		'<a%s href="%s">%s%s</a>',
		! empty( $link[2] ) ? ' class="ot-link"' : '',
		esc_url( (string) $link[1] ),
		esc_html( (string) $link[0] ),
		$arrow
	);
};
?>
<div class="ot-header" data-ot-header>
	<div class="ot-header__bar">
		<?php echo oomphtravel_lockup( 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

		<nav class="ot-nav" aria-label="<?php esc_attr_e( 'Primary', 'oomphtravel' ); ?>">
			<ul class="ot-nav__list">
				<?php foreach ( $ot_nav as $ot_i => $ot_entry ) : ?>
					<?php if ( ! empty( $ot_entry['groups'] ) || ! empty( $ot_entry['foot'] ) ) : ?>
						<li class="ot-nav__item ot-nav__item--has-menu">
							<button class="ot-nav__link" type="button" aria-expanded="false" aria-controls="ot-nav-menu-<?php echo (int) $ot_i; ?>" data-ot-dropdown-toggle>
								<?php echo esc_html( $ot_entry['label'] ); ?>
								<?php echo $ot_chevron; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
							</button>
							<div class="ot-nav__menu<?php echo count( $ot_entry['groups'] ) > 1 ? ' ot-nav__menu--wide' : ''; ?>" id="ot-nav-menu-<?php echo (int) $ot_i; ?>">
								<div class="ot-nav__groups" style="<?php echo count( $ot_entry['groups'] ) > 1 ? '' : 'grid-template-columns:1fr'; ?>">
									<?php foreach ( $ot_entry['groups'] as $ot_group ) : ?>
										<div class="ot-nav__group">
											<?php if ( ! empty( $ot_group['title'] ) ) : ?>
												<?php echo oomphtravel_eyebrow( (string) $ot_group['title'], true, 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
											<?php endif; ?>
											<ul>
												<?php foreach ( $ot_group['links'] as $ot_link ) : ?>
													<li><?php echo $ot_menu_link( $ot_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?></li>
												<?php endforeach; ?>
											</ul>
										</div>
									<?php endforeach; ?>
								</div>
								<?php if ( ! empty( $ot_entry['foot'] ) ) : ?>
									<div class="ot-nav__menu-foot ot-nav__menu-foot--stack">
										<?php foreach ( $ot_entry['foot'] as $ot_link ) : ?>
											<?php echo $ot_menu_link( $ot_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</li>
					<?php else : ?>
						<li class="ot-nav__item"><a class="ot-nav__link" href="<?php echo esc_url( (string) $ot_entry['url'] ); ?>"><?php echo esc_html( $ot_entry['label'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), $ot_start_url, 'primary', false, true, array( 'class' => 'ot-header__cta' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</nav>

		<a class="ot-header__text-link" href="<?php echo esc_url( $ot_start_url ); ?>"><?php esc_html_e( 'Start planning', 'oomphtravel' ); ?></a>
		<button class="ot-menu-toggle" type="button" aria-expanded="false" aria-controls="ot-drawer" data-ot-menu-open><?php esc_html_e( 'Menu', 'oomphtravel' ); ?></button>
	</div>

	<nav id="ot-drawer" class="ot-drawer" aria-label="<?php esc_attr_e( 'Menu', 'oomphtravel' ); ?>" data-ot-menu>
		<div class="ot-drawer__bar">
			<?php echo oomphtravel_lockup( 32, 'ot-lockup--on-navy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<button class="ot-menu-close" type="button" data-ot-menu-close><?php esc_html_e( 'Close', 'oomphtravel' ); ?></button>
		</div>
		<ul class="ot-drawer__list">
			<?php foreach ( $ot_nav as $ot_entry ) : ?>
				<li class="ot-drawer__item">
					<?php if ( '' !== (string) $ot_entry['url'] ) : ?>
						<a class="ot-drawer__link" href="<?php echo esc_url( (string) $ot_entry['url'] ); ?>">
							<span class="ot-drawer__title"><?php echo esc_html( $ot_entry['label'] ); ?></span>
							<?php if ( ! empty( $ot_entry['subtitle'] ) ) : ?><span class="ot-drawer__subtitle"><?php echo esc_html( $ot_entry['subtitle'] ); ?></span><?php endif; ?>
						</a>
					<?php else : ?>
						<span class="ot-drawer__link">
							<span class="ot-drawer__title"><?php echo esc_html( $ot_entry['label'] ); ?></span>
							<?php if ( ! empty( $ot_entry['subtitle'] ) ) : ?><span class="ot-drawer__subtitle"><?php echo esc_html( $ot_entry['subtitle'] ); ?></span><?php endif; ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty( $ot_entry['groups'] ) || ! empty( $ot_entry['foot'] ) ) : ?>
						<ul class="ot-drawer__sub">
							<?php foreach ( (array) ( $ot_entry['groups'] ?? array() ) as $ot_group ) : ?>
								<?php foreach ( $ot_group['links'] as $ot_link ) : ?>
									<li><a href="<?php echo esc_url( (string) $ot_link[1] ); ?>"><?php echo esc_html( (string) $ot_link[0] ); ?></a></li>
								<?php endforeach; ?>
							<?php endforeach; ?>
							<?php foreach ( (array) ( $ot_entry['foot'] ?? array() ) as $ot_link ) : ?>
								<li><a href="<?php echo esc_url( (string) $ot_link[1] ); ?>"><?php echo esc_html( (string) $ot_link[0] ); ?><?php echo isset( $ot_link[3] ) ? ' ' . oomphtravel_arrow( (string) $ot_link[3] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="ot-drawer__cta">
			<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), $ot_start_url, 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</nav>
</div>
