<?php
/**
 * Template helpers the Stage 3 patterns share.
 *
 * Each helper renders one component from docs/02-components.md with its
 * classes bound to the Stage 1 tokens. The card helpers take plain arrays so
 * later stages can feed them from Tour / Operator records without reshaping
 * the markup; the tour caption (D34) lives inside the helper on purpose so no
 * caller can leave it out.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Foundations                                                         */
/* ------------------------------------------------------------------ */

/**
 * The luggage symbol (Logo / Symbol, D22), inlined so its fills bind to the
 * logo/* tokens. Aspect 487:518 — scale by width, height follows.
 *
 * @param int    $width Rendered width in CSS px.
 * @param string $title Accessible name; '' renders decorative (aria-hidden).
 * @param string $class Extra classes.
 * @return string
 */
function oomphtravel_symbol( int $width = 40, string $title = '', string $class = '' ): string {
	static $svg = null;
	if ( null === $svg ) {
		$file = OOMPHTRAVEL_THEME_DIR . 'assets/img/symbol.svg';
		$svg  = file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$svg  = (string) preg_replace( '/<!--.*?-->\s*/s', '', $svg );
	}
	if ( '' === $svg ) {
		return '';
	}
	$height = (int) round( $width * 518 / 487 );
	$attrs  = sprintf( ' class="ot-symbol %s" width="%d" height="%d"', esc_attr( $class ), $width, $height );
	$attrs .= '' === $title
		? ' aria-hidden="true"'
		: ' aria-label="' . esc_attr( $title ) . '"';

	return (string) preg_replace( '/<svg\b/', '<svg' . $attrs, $svg, 1 );
}

/**
 * Symbol + Fraunces wordmark, linked home. Used by the header (both sizes)
 * and, on navy, by the footer.
 *
 * @param int    $symbol Symbol width in px.
 * @param string $class  Extra classes on the link.
 * @return string
 */
function oomphtravel_lockup( int $symbol = 40, string $class = '' ): string {
	$name = get_bloginfo( 'name' ) ?: 'Oomph Travel';

	return sprintf(
		'<a class="ot-lockup %s" href="%s" rel="home">%s<span class="ot-lockup__wordmark">%s</span></a>',
		esc_attr( $class ),
		esc_url( home_url( '/' ) ),
		oomphtravel_symbol( $symbol ),
		esc_html( $name )
	);
}

/**
 * Arrow glyph in Inter. Fraunces has no arrow glyphs; a bare arrow inside a
 * Fraunces element falls back to emoji presentation (docs/02-components.md).
 *
 * @param string $glyph One of → ← ↗.
 * @return string
 */
function oomphtravel_arrow( string $glyph = '→' ): string {
	return '<span class="ot-arrow" aria-hidden="true">' . esc_html( $glyph ) . '</span>';
}

/**
 * Button (Style=Primary|Ghost × Ground=Light|Navy). Text property: Label.
 *
 * @param string $label   Button label.
 * @param string $url     Destination.
 * @param string $style   'primary' | 'ghost'.
 * @param bool   $on_navy True inside a navy band.
 * @param bool   $arrow   Append the Inter arrow.
 * @param array  $attr    Extra attributes (class, target, rel, data-*).
 * @return string
 */
function oomphtravel_button( string $label, string $url, string $style = 'primary', bool $on_navy = false, bool $arrow = true, array $attr = array() ): string {
	$classes = array( 'ot-btn', 'ot-btn--' . ( 'ghost' === $style ? 'ghost' : 'primary' ) );
	if ( $on_navy ) {
		$classes[] = 'ot-btn--on-navy';
	}
	if ( ! empty( $attr['class'] ) ) {
		$classes[] = (string) $attr['class'];
	}
	unset( $attr['class'] );

	$extra = '';
	foreach ( $attr as $k => $v ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( (string) $k ), esc_attr( (string) $v ) );
	}

	return sprintf(
		'<a class="%s" href="%s"%s><span class="ot-btn__label">%s</span>%s</a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $url ),
		$extra,
		esc_html( $label ),
		$arrow ? oomphtravel_arrow() : ''
	);
}

/**
 * Eyebrow. Teal by default; `muted` for a Journal category (a category is
 * not an action); on navy the colour follows the band automatically.
 *
 * @param string $text  Text property.
 * @param bool   $muted Muted-slate variant.
 * @param string $tag   Wrapping element.
 * @return string
 */
function oomphtravel_eyebrow( string $text, bool $muted = false, string $tag = 'p' ): string {
	$tag = preg_replace( '/[^a-z0-9]/', '', strtolower( $tag ) ) ?: 'p';

	return sprintf(
		'<%1$s class="ot-eyebrow%2$s">%3$s</%1$s>',
		$tag,
		$muted ? ' ot-eyebrow--muted' : '',
		esc_html( $text )
	);
}

/**
 * The static contrail mark (34 × 12, deep teal) that sits beside a section
 * label — the still form of the motion motif (D23). `draw` is the animated
 * form for under an H1: once per page, 2.4 s, off under reduced motion.
 *
 * @param bool $draw Animated variant.
 * @return string
 */
function oomphtravel_contrail( bool $draw = false ): string {
	return sprintf(
		'<svg class="ot-contrail%s" viewBox="0 0 34 12" width="34" height="12" aria-hidden="true" focusable="false"><path d="M1 9.5C7 9.5 9 2.5 15 2.5s8 7 14 7c1.6 0 2.8-.4 4-1" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
		$draw ? ' ot-contrail--draw' : ''
	);
}

/**
 * Section heading: eyebrow with its contrail, then the Fraunces H2.
 *
 * @param string $eyebrow Eyebrow text.
 * @param string $heading Heading text.
 * @param string $level   Heading level, 'h2' by default.
 * @param string $class   Extra classes on the wrapper.
 * @return string
 */
function oomphtravel_section_heading( string $eyebrow, string $heading, string $level = 'h2', string $class = '' ): string {
	$level = in_array( $level, array( 'h1', 'h2', 'h3' ), true ) ? $level : 'h2';

	return sprintf(
		'<div class="ot-section-heading %1$s"><p class="ot-eyebrow ot-section-heading__eyebrow">%2$s<span>%3$s</span></p><%4$s class="ot-section-heading__title">%5$s</%4$s></div>',
		esc_attr( $class ),
		oomphtravel_contrail(),
		esc_html( $eyebrow ),
		$level,
		esc_html( $heading )
	);
}

/**
 * Link / Underlined — the tertiary action.
 *
 * @param string $text  Link text.
 * @param string $url   Destination.
 * @param bool   $arrow Append the Inter arrow.
 * @param array  $attr  Extra attributes.
 * @return string
 */
function oomphtravel_link( string $text, string $url, bool $arrow = true, array $attr = array() ): string {
	$extra = '';
	foreach ( $attr as $k => $v ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( (string) $k ), esc_attr( (string) $v ) );
	}

	return sprintf(
		'<a class="ot-link" href="%s"%s>%s%s</a>',
		esc_url( $url ),
		$extra,
		esc_html( $text ),
		$arrow ? ' ' . oomphtravel_arrow() : ''
	);
}

/* ------------------------------------------------------------------ */
/* CruiseOomph hand-off                                                */
/* ------------------------------------------------------------------ */

/**
 * The campaign name for a UTM tag: the page the link sits on.
 *
 * @return string
 */
function oomphtravel_utm_campaign(): string {
	if ( is_front_page() ) {
		$slug = 'home';
	} elseif ( is_singular() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	} elseif ( is_home() ) {
		$slug = 'journal';
	} elseif ( is_archive() ) {
		$slug = (string) get_query_var( 'post_type' );
	} elseif ( is_404() ) {
		$slug = '404';
	} else {
		$slug = '';
	}
	$slug = sanitize_title( $slug ) ?: 'site';

	/**
	 * Filters the utm_campaign value for outbound CruiseOomph links.
	 *
	 * @param string $slug Campaign slug.
	 */
	return (string) apply_filters( 'oomphtravel_utm_campaign', $slug );
}

/**
 * A cruiseoomph.com URL carrying the UTM tag every outbound link must have
 * (plan §4.3): ?utm_source=oomphtravel&utm_medium=site&utm_campaign={page}.
 *
 * @param string $path     Path on cruiseoomph.com, e.g. '/find-my-cruise/'.
 * @param string $campaign Override the campaign (defaults to the current page).
 * @param array  $extra    Extra query args, e.g. array( 'region' => 'italy' ).
 * @return string
 */
function oomphtravel_cruiseoomph_url( string $path = '/', string $campaign = '', array $extra = array() ): string {
	/**
	 * Filters the CruiseOomph origin (staging can point at its staging copy).
	 *
	 * @param string $base Origin without a trailing slash.
	 */
	$base = untrailingslashit( (string) apply_filters( 'oomphtravel_cruiseoomph_base', 'https://cruiseoomph.com' ) );
	$args = array_merge(
		$extra,
		array(
			'utm_source'   => 'oomphtravel',
			'utm_medium'   => 'site',
			'utm_campaign' => '' !== $campaign ? sanitize_title( $campaign ) : oomphtravel_utm_campaign(),
		)
	);

	return add_query_arg( array_map( 'rawurlencode', $args ), $base . '/' . ltrim( $path, '/' ) );
}

/* ------------------------------------------------------------------ */
/* Images                                                              */
/* ------------------------------------------------------------------ */

/**
 * Hold a below-the-fold picture back until the page has loaded (carried from
 * CruiseOomph, Phase 10.2). The real src/srcset move to data-ot-src /
 * data-ot-srcset behind a transparent pixel; shell.js puts them back on the
 * load event. Width and height keep the box, so nothing shifts. A <noscript>
 * copy serves readers without JavaScript and crawlers alike.
 *
 * @param string $img A single `<img … loading="lazy">` tag.
 * @return string
 */
function oomphtravel_defer_image( string $img ): string {
	if ( '' === $img || false === stripos( $img, 'loading="lazy"' ) || false !== stripos( $img, 'data-ot-src=' ) ) {
		return $img;
	}
	$held = preg_replace(
		array( '/\ssrcset="/i', '/\ssrc="/i', '/<img\b/i' ),
		array( ' data-ot-srcset="', ' data-ot-src="', '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"' ),
		$img,
		1
	);

	return (string) $held . '<noscript>' . $img . '</noscript>';
}

/**
 * A responsive card image for a local attachment, capped at the 1024 px
 * rendition (cards are at most ~400 px wide). The first card on a page may
 * load eagerly with fetchpriority="high".
 *
 * @param int    $attachment_id Attachment.
 * @param string $alt           Alt text ('' when decorative).
 * @param string $sizes         The `sizes` attribute.
 * @param bool   $eager         True for the page's first card.
 * @param array  $attr          Extra attributes (class, …).
 * @return string `<img>` markup, '' when the attachment is gone.
 */
function oomphtravel_card_image( int $attachment_id, string $alt, string $sizes = '(min-width: 1024px) 400px, 100vw', bool $eager = false, array $attr = array() ): string {
	if ( ! $attachment_id ) {
		return '';
	}
	$cap = static fn(): int => 1024;
	add_filter( 'max_srcset_image_width', $cap, 20 );

	$attr = array_merge(
		array(
			'alt'      => $alt,
			'sizes'    => $sizes,
			'loading'  => $eager ? 'eager' : 'lazy',
			'decoding' => 'async',
		),
		$eager ? array( 'fetchpriority' => 'high' ) : array(),
		$attr
	);
	$html = (string) wp_get_attachment_image( $attachment_id, 'large', false, $attr );

	remove_filter( 'max_srcset_image_width', $cap, 20 );

	return $eager ? $html : oomphtravel_defer_image( $html );
}

/**
 * The picture slot of a card: an attachment, a URL, or nothing — in which
 * case a Mist placeholder keeps the aspect box (the handoff's "grey
 * rectangle" marker for missing imagery).
 *
 * @param array  $card  Card array (image_id | image_url, image_alt).
 * @param string $class Class on the wrapper.
 * @param bool   $eager First card on the page.
 * @return string
 */
function oomphtravel_card_media( array $card, string $class, bool $eager = false ): string {
	$alt = (string) ( $card['image_alt'] ?? '' );
	$img = '';
	if ( ! empty( $card['image_id'] ) ) {
		$img = oomphtravel_card_image( (int) $card['image_id'], $alt, '(min-width: 1024px) 400px, 100vw', $eager );
	} elseif ( ! empty( $card['image_url'] ) ) {
		$img = sprintf(
			'<img src="%s" alt="%s" loading="%s" decoding="async">',
			esc_url( (string) $card['image_url'] ),
			esc_attr( $alt ),
			$eager ? 'eager' : 'lazy'
		);
	}

	return sprintf(
		'<div class="%s%s">%s</div>',
		esc_attr( $class ),
		'' === $img ? ' ot-media--empty' : '',
		$img
	);
}

/* ------------------------------------------------------------------ */
/* Cards                                                               */
/* ------------------------------------------------------------------ */

/**
 * Card / Destination. Tall 4:5 portrait, name above the pill over a scrim.
 *
 * @param array $card  name, url, image_id|image_url, image_alt, pill (default "Explore").
 * @param bool  $eager First card on the page.
 * @return string
 */
function oomphtravel_card_destination( array $card, bool $eager = false ): string {
	return sprintf(
		'<a class="ot-card ot-card-dest" href="%s">%s<span class="ot-card-dest__copy"><span class="ot-card-dest__name">%s</span><span class="ot-pill">%s</span></span></a>',
		esc_url( (string) ( $card['url'] ?? '#' ) ),
		oomphtravel_card_media( $card, 'ot-card-dest__media', $eager ),
		esc_html( (string) ( $card['name'] ?? '' ) ),
		esc_html( (string) ( $card['pill'] ?? __( 'Explore', 'oomphtravel' ) ) )
	);
}

/**
 * Card / Way to travel. Photo above, Fraunces title, two lines, one link.
 *
 * @param array $card  title, body, url, link_text, image_id|image_url, image_alt.
 * @param bool  $eager First card on the page.
 * @return string
 */
function oomphtravel_card_way( array $card, bool $eager = false ): string {
	$url = (string) ( $card['url'] ?? '#' );

	return sprintf(
		'<article class="ot-card ot-card-way">%s<div class="ot-card-way__body"><h3 class="ot-card-way__title"><a href="%s">%s</a></h3><p class="ot-card-way__text">%s</p>%s</div></article>',
		oomphtravel_card_media( $card, 'ot-card-way__media', $eager ),
		esc_url( $url ),
		esc_html( (string) ( $card['title'] ?? '' ) ),
		esc_html( (string) ( $card['body'] ?? '' ) ),
		oomphtravel_link( (string) ( $card['link_text'] ?? __( 'Find out more', 'oomphtravel' ) ), $url )
	);
}

/**
 * Card / Tour. One Tour record. The availability caption is fixed copy and
 * part of the component (D34): it is printed here, always, and no argument
 * can suppress it. The Copy frame is fixed at 178px in CSS so price rows
 * bottom-align across a row.
 *
 * @param array $card  meta (or operator, nights, destination), title, blurb,
 *                     price (display string, e.g. "$4,995"), url, slug,
 *                     image_id|image_url, image_alt.
 * @param bool  $eager First card on the page.
 * @return string
 */
function oomphtravel_card_tour( array $card, bool $eager = false ): string {
	$meta = (string) ( $card['meta'] ?? '' );
	if ( '' === $meta ) {
		$nights = (int) ( $card['nights'] ?? 0 );
		$meta   = implode(
			' · ',
			array_filter(
				array(
					(string) ( $card['operator'] ?? '' ),
					/* translators: %d: nights. */
					$nights > 0 ? sprintf( _n( '%d night', '%d nights', $nights, 'oomphtravel' ), $nights ) : '',
					(string) ( $card['destination'] ?? '' ),
				),
				'strlen'
			)
		);
	}
	$url   = (string) ( $card['url'] ?? '#' );
	$slug  = (string) ( $card['slug'] ?? '' );
	$ask   = add_query_arg( array( 'tour' => rawurlencode( $slug ) ), home_url( '/start-planning/' ) );
	$price = (string) ( $card['price'] ?? '' );

	$html  = '<article class="ot-card ot-card-tour">';
	$html .= oomphtravel_card_media( $card, 'ot-card-tour__media', $eager );
	$html .= '<div class="ot-card-tour__body">';
	$html .= '<div class="ot-card-tour__copy">';
	$html .= '<p class="ot-card-tour__meta">' . esc_html( $meta ) . '</p>';
	$html .= '<h3 class="ot-card-tour__title"><a href="' . esc_url( $url ) . '">' . esc_html( (string) ( $card['title'] ?? '' ) ) . '</a></h3>';
	$html .= '<p class="ot-card-tour__blurb">' . esc_html( (string) ( $card['blurb'] ?? '' ) ) . '</p>';
	$html .= '</div>';
	$html .= '<div class="ot-card-tour__price-row">';
	if ( '' !== $price ) {
		$html .= '<p class="ot-card-tour__price"><span class="ot-card-tour__from">' . esc_html__( 'from', 'oomphtravel' ) . '</span> ' . esc_html( $price ) . ' <span class="ot-card-tour__per">' . esc_html__( 'per person', 'oomphtravel' ) . '</span></p>';
	}
	$html .= oomphtravel_link( __( 'Ask Eric', 'oomphtravel' ), $ask );
	$html .= '</div>';
	// Fixed caption — part of the component, not per-instance copy (D34).
	$html .= '<p class="ot-card-tour__caption">' . esc_html__( 'Dates and availability confirmed on request.', 'oomphtravel' ) . '</p>';
	$html .= '</div></article>';

	return $html;
}

/**
 * Card / Operator (Kind=Escorted|FIT supplier).
 *
 * Escorted: a tour count and a link to the operator page. FIT supplier: the
 * trip types it supports and a link to Custom journeys or Resorts & villas.
 * The logo slot renders the handoff's grey rectangle until vendor logos are
 * cleared (docs/03-rules-and-readiness.md, blocked item 2).
 *
 * @param array $card kind ('escorted'|'fit'), name, fit (one line), url,
 *                    tour_count, trip_types (array), link_text,
 *                    logo_id|logo_url, logo_alt.
 * @return string
 */
function oomphtravel_card_operator( array $card ): string {
	$kind = 'fit' === ( $card['kind'] ?? 'escorted' ) ? 'fit' : 'escorted';
	$name = (string) ( $card['name'] ?? '' );
	$url  = (string) ( $card['url'] ?? '#' );
	$logo = '';
	if ( ! empty( $card['logo_id'] ) ) {
		$logo = (string) wp_get_attachment_image(
			(int) $card['logo_id'],
			'medium',
			false,
			array(
				'alt'      => (string) ( $card['logo_alt'] ?? $name ),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	} elseif ( ! empty( $card['logo_url'] ) ) {
		$logo = sprintf( '<img src="%s" alt="%s" loading="lazy" decoding="async">', esc_url( (string) $card['logo_url'] ), esc_attr( (string) ( $card['logo_alt'] ?? $name ) ) );
	}

	$html  = '<article class="ot-card ot-card-operator ot-card-operator--' . $kind . '">';
	$html .= '<div class="ot-card-operator__logo' . ( '' === $logo ? ' ot-media--empty' : '' ) . '">' . $logo . '</div>';
	$html .= '<h3 class="ot-card-operator__name"><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></h3>';
	$html .= '<p class="ot-card-operator__fit">' . esc_html( (string) ( $card['fit'] ?? '' ) ) . '</p>';

	if ( 'escorted' === $kind ) {
		if ( isset( $card['tour_count'] ) ) {
			$count = (int) $card['tour_count'];
			/* translators: %d: number of tours. */
			$html .= '<p class="ot-card-operator__meta">' . esc_html( sprintf( _n( '%d tour', '%d tours', $count, 'oomphtravel' ), $count ) ) . '</p>';
		}
		$html .= oomphtravel_link( (string) ( $card['link_text'] ?? __( 'See their tours', 'oomphtravel' ) ), $url );
	} else {
		$types = array_map( 'strval', (array) ( $card['trip_types'] ?? array() ) );
		if ( $types ) {
			$html .= '<p class="ot-card-operator__meta">' . esc_html( implode( ' · ', $types ) ) . '</p>';
		}
		$html .= oomphtravel_link( (string) ( $card['link_text'] ?? __( 'Custom journeys', 'oomphtravel' ) ), $url );
	}
	$html .= '</article>';

	return $html;
}

/**
 * Card / Journal. Category eyebrow in muted slate, not teal.
 *
 * @param int|WP_Post|array $post  A post, or an array (category, title, blurb, url, image_id|image_url, image_alt).
 * @param bool              $eager First card on the page.
 * @return string
 */
function oomphtravel_card_journal( $post, bool $eager = false ): string {
	if ( ! is_array( $post ) ) {
		$p = get_post( $post );
		if ( ! $p instanceof WP_Post ) {
			return '';
		}
		$cats = get_the_category( $p->ID );
		$post = array(
			'category'  => $cats ? (string) $cats[0]->name : '',
			'title'     => (string) get_the_title( $p ),
			'blurb'     => (string) wp_strip_all_tags( get_the_excerpt( $p ) ),
			'url'       => (string) get_permalink( $p ),
			'image_id'  => (int) get_post_thumbnail_id( $p ),
			'image_alt' => '',
		);
	}

	$html  = '<article class="ot-card ot-card-journal">';
	$html .= '<a class="ot-card-journal__link" href="' . esc_url( (string) ( $post['url'] ?? '#' ) ) . '">';
	$html .= oomphtravel_card_media( $post, 'ot-card-journal__media', $eager );
	$html .= '<span class="ot-card-journal__body">';
	if ( ! empty( $post['category'] ) ) {
		$html .= oomphtravel_eyebrow( (string) $post['category'], true, 'span' );
	}
	$html .= '<span class="ot-card-journal__title">' . esc_html( (string) ( $post['title'] ?? '' ) ) . '</span>';
	if ( ! empty( $post['blurb'] ) ) {
		$html .= '<span class="ot-card-journal__blurb">' . esc_html( (string) $post['blurb'] ) . '</span>';
	}
	$html .= '</span></a></article>';

	return $html;
}

/* ------------------------------------------------------------------ */
/* Quotation                                                           */
/* ------------------------------------------------------------------ */

/**
 * Quotation. Fraunces Light Italic on a 2px teal rule; attribution in muted
 * slate. No stars, no photographs (docs/02-components.md). Quotes are never
 * reworded (D38) — this prints exactly what it is given.
 *
 * @param string $quote       Quote text.
 * @param string $attribution Attribution line.
 * @return string
 */
function oomphtravel_quotation( string $quote, string $attribution ): string {
	return sprintf(
		'<figure class="ot-quote"><blockquote class="ot-quote__text"><p>%s</p></blockquote><figcaption class="ot-quote__attribution">%s</figcaption></figure>',
		esc_html( $quote ),
		esc_html( $attribution )
	);
}

/* ------------------------------------------------------------------ */
/* Newsletter (PlainSend)                                              */
/* ------------------------------------------------------------------ */

/**
 * The PlainSend signup row (Footer, D30): email, website honeypot, elapsed_ms
 * timing field, submit. Posts straight to PlainSend via the endpoint the core
 * plugin resolves per environment; renders nothing when the plugin is
 * inactive rather than a form that posts nowhere. Markup keeps the
 * `.oomph-signup` hooks newsletter.js listens for.
 *
 * @param string $key  PlainSend form key ('newsletter', 'trends-guide').
 * @param array  $args label, button, source, id.
 * @return void
 */
function oomphtravel_signup_form( string $key = 'newsletter', array $args = array() ): void {
	$endpoint = class_exists( '\OomphTravel\Core\Plainsend' )
		? \OomphTravel\Core\Plainsend::endpoint( $key )
		: '';
	if ( ! $endpoint ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'label'  => __( 'Email address', 'oomphtravel' ),
			'button' => __( 'Sign me up', 'oomphtravel' ),
			'source' => $key,
			'id'     => 'ot-signup-' . $key,
		)
	);
	$id   = (string) $args['id'];
	?>
	<form class="oomph-signup ot-signup" method="post" action="<?php echo esc_url( $endpoint ); ?>" data-source="<?php echo esc_attr( (string) $args['source'] ); ?>">
		<div class="ot-signup__row">
			<label class="ot-signup__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( (string) $args['label'] ); ?></label>
			<input class="oomph-signup__input ot-signup__input" type="email" id="<?php echo esc_attr( $id ); ?>" name="email" autocomplete="email" inputmode="email" placeholder="you@example.com" required>
			<button class="oomph-signup__submit ot-btn ot-btn--primary ot-signup__submit" type="submit"><span class="ot-btn__label"><?php echo esc_html( (string) $args['button'] ); ?></span><?php echo oomphtravel_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></button>
		</div>
		<?php /* Hidden from people, irresistible to bots: off-screen, not display:none. */ ?>
		<div class="ot-signup__trap" aria-hidden="true">
			<label for="<?php echo esc_attr( $id ); ?>-website">Website</label>
			<input type="text" id="<?php echo esc_attr( $id ); ?>-website" name="website" tabindex="-1" autocomplete="off">
		</div>
		<input type="hidden" name="elapsed_ms" value="">
		<p class="oomph-signup__message ot-signup__message" role="status" aria-live="polite"></p>
	</form>
	<?php
}

/**
 * Homepage hero sources (plan §6.1.1), one entry per orientation.
 *
 * The phone gets a 3:4 crop of the same photograph, the desktop the 3:2
 * frame; both carry their own srcset so the browser fetches one file. The
 * largest desktop rendition stops at 1280 because the 1600 one weighed more
 * than the 250 KB LCP budget in docs/cro-rules.md R3. Used by patterns/home.php
 * for the <picture> and by oomphtravel_preload_hero() for the preload hints,
 * so the two can never disagree.
 *
 * @return array<string, array{media: string, src: string, srcset: string, sizes: string}>
 */
function oomphtravel_home_hero_sources(): array {
	$img = OOMPHTRAVEL_THEME_URI . 'assets/img/';

	return array(
		'tall' => array(
			'media'  => '(max-width: 767px)',
			'src'    => $img . 'hero-varenna-tall-720.webp',
			'srcset' => $img . 'hero-varenna-tall-480.webp 480w, ' . $img . 'hero-varenna-tall-720.webp 720w, ' . $img . 'hero-varenna-tall-900.webp 900w',
			'sizes'  => '100vw',
		),
		'wide' => array(
			'media'  => '(min-width: 768px)',
			'src'    => $img . 'hero-varenna-1280.webp',
			'srcset' => $img . 'hero-varenna-640.webp 640w, ' . $img . 'hero-varenna-960.webp 960w, ' . $img . 'hero-varenna-1280.webp 1280w',
			'sizes'  => '100vw',
		),
	);
}

/**
 * Band / Closing invitation (docs/02-components.md): every page ends here.
 * Marine navy, one question, one primary button. Pages that know where the
 * visitor is heading pass a pre-set Start planning URL (a destination, a
 * way to travel); the pattern oomphtravel/band-closing calls this with none.
 *
 * @param string $url     Start planning URL; default /start-planning/.
 * @param string $heading The question; default "Worth a thirty-minute conversation?".
 * @param string $note    The line under the button.
 */
function oomphtravel_closing_band( string $url = '', string $heading = '', string $note = '' ): string {
	$url     = '' !== $url ? $url : home_url( '/start-planning/' );
	$heading = '' !== $heading ? $heading : __( 'Worth a thirty-minute conversation?', 'oomphtravel' );
	$note    = '' !== $note ? $note : __( 'Email, text or a quick call, whatever’s easiest.', 'oomphtravel' );

	return sprintf(
		'<section class="ot-band ot-band--navy ot-closing"><div class="ot-container ot-closing__inner"><h2 class="ot-closing__heading">%s</h2>%s<p class="ot-closing__note">%s</p></div></section>',
		esc_html( $heading ),
		oomphtravel_button( __( 'Start planning', 'oomphtravel' ), $url, 'primary', true ),
		esc_html( $note )
	);
}
