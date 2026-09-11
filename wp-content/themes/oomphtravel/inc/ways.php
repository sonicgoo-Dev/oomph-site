<?php
/**
 * Ways to travel (plan §6.4 Custom journeys, §6.8 Resorts & villas, §6.9
 * Multi-generational trips, §6.10 Cruise planning): the four service pages
 * that sit between the destinations and Start planning. Each is a page
 * record with an empty body; the copy lives in its pattern and the template
 * templates/page-{slug}.html mounts it.
 *
 * This file holds what the four patterns share: the slugs, the hero
 * renditions, the Start planning links (each page pre-sets a `type` that
 * Start planning reads, plan §6.15), the questions (shared by the page and
 * the FAQPage schema so the two never drift), the client story each page
 * shows (D37, D38), the stylesheets that load on these pages only, and the
 * search description each one gets when Eric has not typed his own.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* The four pages                                                        */
/* ------------------------------------------------------------------ */

/**
 * The page slugs, keyed by the short name the assets and filters use.
 *
 * @return array<string,string> key => slug
 */
function oomphtravel_way_slugs(): array {
	return array(
		'custom'   => 'custom-journeys',
		'resorts'  => 'resorts-and-villas',
		'multigen' => 'multi-generational-travel-planning',
		'cruise'   => 'cruise-planning',
	);
}

/** The short key of the current page ('custom', 'resorts', 'multigen', 'cruise'), or ''. */
function oomphtravel_way_key(): string {
	if ( ! is_page() ) {
		return '';
	}
	$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	$key  = array_search( $slug, oomphtravel_way_slugs(), true );
	return false === $key ? '' : (string) $key;
}

/** True on any of the four ways-to-travel pages. */
function oomphtravel_is_way_page(): bool {
	return '' !== oomphtravel_way_key();
}

/**
 * The Start planning link for a way, with the type pre-set (plan §6.15).
 *
 * @param string $type 'custom', 'resort', 'multi-gen' or 'cruise-land'.
 */
function oomphtravel_way_plan_url( string $type ): string {
	return add_query_arg( 'type', $type, home_url( '/start-planning/' ) );
}

/* ------------------------------------------------------------------ */
/* Heroes                                                                */
/* ------------------------------------------------------------------ */

/**
 * The hero for one way: the theme's own renditions (assets/img/{key}-hero-*),
 * a 3:2 wide set and a 3:4 tall set for phones. Place-first photographs:
 * Florence from the air, a pool terrace above the bay in Old San Juan,
 * Sitges on the Catalan coast, and the one ship image the site uses (the
 * CruiseOomph band's dawn photograph, plan §6.10).
 *
 * @return array<string,mixed>
 */
function oomphtravel_way_hero_sources( string $key ): array {
	$alts = array(
		'custom'   => __( 'Florence at sunrise, from the air: the Duomo above the rooftops and the hills beyond.', 'oomphtravel' ),
		'resorts'  => __( 'A hotel terrace pool above the bay in Old San Juan, Puerto Rico.', 'oomphtravel' ),
		'multigen' => __( 'Sitges, on the Catalan coast: the church of Sant Bartomeu above the beach in late light.', 'oomphtravel' ),
		'cruise'   => __( 'A ship on a glassy sea at dawn.', 'oomphtravel' ),
	);
	$img = OOMPHTRAVEL_THEME_URI . 'assets/img/' . $key . '-hero';
	return array(
		'alt'    => $alts[ $key ] ?? '',
		'width'  => 1280,
		'height' => 853,
		'tall'   => array(
			'media'  => '(max-width: 767px)',
			'src'    => $img . '-tall-720.webp',
			'srcset' => $img . '-tall-480.webp 480w, ' . $img . '-tall-720.webp 720w, ' . $img . '-tall-900.webp 900w',
			'sizes'  => '100vw',
		),
		'wide'   => array(
			'media'  => '(min-width: 768px)',
			'src'    => $img . '-1280.webp',
			'srcset' => $img . '-640.webp 640w, ' . $img . '-960.webp 960w, ' . $img . '-1280.webp 1280w',
			'sizes'  => '100vw',
		),
	);
}

/**
 * The hero markup the four patterns share: picture, eyebrow, H1, an
 * optional lead and an optional primary button.
 *
 * @param array<string,mixed> $hero From oomphtravel_way_hero_sources().
 */
function oomphtravel_way_hero( array $hero, string $eyebrow, string $title, string $lead = '', string $button_label = '', string $button_url = '', string $class = '' ): string {
	$out  = '<section class="ot-dest-hero ot-dest-hero--short ot-way-hero' . ( '' !== $class ? ' ' . esc_attr( $class ) : '' ) . '">';
	$out .= '<picture class="ot-dest-hero__picture">';
	$out .= sprintf(
		'<source media="%s" srcset="%s" sizes="%s">',
		esc_attr( $hero['tall']['media'] ),
		esc_attr( $hero['tall']['srcset'] ),
		esc_attr( $hero['tall']['sizes'] )
	);
	$out .= sprintf(
		'<img src="%s" srcset="%s" sizes="%s" width="%d" height="%d" fetchpriority="high" decoding="async" alt="%s">',
		esc_url( $hero['wide']['src'] ),
		esc_attr( $hero['wide']['srcset'] ),
		esc_attr( $hero['wide']['sizes'] ),
		(int) $hero['width'],
		(int) $hero['height'],
		esc_attr( $hero['alt'] )
	);
	$out .= '</picture>';
	$out .= '<div class="ot-container ot-dest-hero__inner"><div class="ot-dest-hero__copy ot-way-hero__copy">';
	$out .= oomphtravel_eyebrow( $eyebrow );
	$out .= '<h1 class="ot-dest-hero__title">' . esc_html( $title ) . '</h1>';
	if ( '' !== $lead ) {
		$out .= '<p class="ot-way-hero__lead">' . esc_html( $lead ) . '</p>';
	}
	if ( '' !== $button_label && '' !== $button_url ) {
		$out .= oomphtravel_button( $button_label, $button_url, 'primary', true );
	}
	$out .= '</div></div></section>';
	return $out;
}

/* ------------------------------------------------------------------ */
/* Shared pieces                                                        */
/* ------------------------------------------------------------------ */

/**
 * Destination cards for a list of slugs, published records only, in the
 * order given (the same builder the destinations index uses).
 *
 * @param string[] $slugs
 * @return array<int,array<string,mixed>>
 */
function oomphtravel_destination_cards( array $slugs ): array {
	$cards = array();
	foreach ( $slugs as $slug ) {
		$card = oomphtravel_destination_card( $slug );
		if ( $card ) {
			$cards[] = $card;
		}
	}
	return $cards;
}

/**
 * An operator's name as a link to its page when the record is published,
 * else the bare name (D25: named either way, linked only when the page is
 * there).
 */
function oomphtravel_operator_name_link( string $slug, string $name ): string {
	$post = post_type_exists( 'oomph_operator' ) ? get_page_by_path( $slug, OBJECT, 'oomph_operator' ) : null;
	if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
		return '<a href="' . esc_url( (string) get_permalink( $post ) ) . '">' . esc_html( $name ) . '</a>';
	}
	return esc_html( $name );
}

/**
 * The questions on each page. One source for the accordion and for the
 * FAQPage node, so the schema never claims a question the page does not
 * show.
 *
 * @return array<int,array{q:string,a:string}>
 */
function oomphtravel_way_faqs( string $key ): array {
	switch ( $key ) {
		case 'custom':
			return array(
				array(
					'q' => __( 'Do you charge a planning fee?', 'oomphtravel' ),
					'a' => __( 'No. The hotels and suppliers I book pay me a commission from their side, and that does not change your price. You get the itinerary, the vetted stays and guides, and the logistics handled, at no added cost.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Can you take over a trip I have already started planning?', 'oomphtravel' ),
					'a' => __( 'Often, yes. The earlier I am involved the more I can shape, but if you have already booked a hotel or two I can build the rest of the trip around them. Bring what you have to the call and I will tell you honestly where I add something.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'How far ahead should I start?', 'oomphtravel' ),
					'a' => __( 'For spring and autumn in the popular regions, six to nine months is comfortable; the best small hotels and guides book early. I have turned around shorter timelines, but the runway buys you the good options.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Is a custom journey more expensive than an escorted tour?', 'oomphtravel' ),
					'a' => __( 'Night for night, usually yes: a private driver and guide cost more than a seat on a coach. It is also a different trip. If a tour would serve you better, I will say so and point you to one.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Do you book flights?', 'oomphtravel' ),
					'a' => __( 'I advise on routing and timing and fit the flights to the rest of the trip. I will tell you when it is worth using miles and when it is not.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Can you plan a trip for three generations?', 'oomphtravel' ),
					'a' => __( 'Yes, and it is one of the things I plan most. Pace, mobility and dietary needs change the plan; there is a page on multi-generational trips, or just mention it on the call.', 'oomphtravel' ),
				),
			);
		case 'resorts':
			return array(
				array(
					'q' => __( 'All-inclusive, or not?', 'oomphtravel' ),
					'a' => __( 'It depends on how you like to eat. All-inclusive suits families, groups and anyone who wants the bill settled before they leave home. If you would rather eat in town three nights out of five, a room-only rate at a better property often costs less. I will run both.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Do resorts work with children?', 'oomphtravel' ),
					'a' => __( 'The right ones do, and the wrong ones make for a long week. I know which properties have a real kids’ club, which have a pool the teenagers will actually use, and which say family-friendly and mean it less.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Do you book adults-only properties?', 'oomphtravel' ),
					'a' => __( 'Yes. Mexico and the Caribbean have some of the best adults-only resorts anywhere, and I keep a short list I would send my own friends to.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Are the villas staffed?', 'oomphtravel' ),
					'a' => __( 'Some are, some are not, and it is the first thing I ask. A cook, a housekeeper or a full team can be arranged at most of the homes I book; I price it alongside the house so there are no surprises.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Can a villa be part of a longer trip?', 'oomphtravel' ),
					'a' => __( 'Often the best version of one: a week of moving around, then a week in one house. I plan the two together so the transfers land on the right day.', 'oomphtravel' ),
				),
			);
		case 'multigen':
			return array(
				array(
					'q' => __( 'What makes a multi-generational trip different to plan?', 'oomphtravel' ),
					'a' => __( 'It is a hundred small decisions, not one. Pace for the slowest walker, food for the pickiest eater, rooms that actually work, and a plan B for the day someone needs to rest. I plan around the constraints first, then the highlights.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Do you charge a planning fee?', 'oomphtravel' ),
					'a' => __( 'No. Suppliers pay me a commission on what you book, and that does not change your price. You get the planning a multi-generational trip genuinely takes at no added cost.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'How many people can you plan for?', 'oomphtravel' ),
					'a' => __( 'From a trip with the grandparents to a reunion of fifteen or more across several rooms. The larger the group, the earlier we should start.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Can you handle mobility and dietary needs?', 'oomphtravel' ),
					'a' => __( 'Yes; that is central to how I plan, not an afterthought. Step-free routes, accessible rooms, transfers that fit a wheelchair, kitchens briefed on allergies. Tell me what you are working with and I will build around it.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Cruise or land for a family trip?', 'oomphtravel' ),
					'a' => __( 'Both work; it depends on the group. A ship solves the different-interests, one-base problem; a villa solves the we-just-want-to-be-together one. Cruises are planned on CruiseOomph, my sister site, and I will talk you through the trade-off on the call.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'How far ahead should we book?', 'oomphtravel' ),
					'a' => __( 'For groups, sooner is better; connecting rooms and large tables go first. Six to twelve months out for peak season.', 'oomphtravel' ),
				),
			);
		case 'cruise':
			return array(
				array(
					'q' => __( 'Do you charge a planning fee for a cruise?', 'oomphtravel' ),
					'a' => __( 'No. Cruise lines pay travel advisors a commission on the booked fare, and that does not change your price. Cabin selection, dining and the land at both ends come with the booking, at no added cost.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'I have already booked directly with a cruise line. Can you still help?', 'oomphtravel' ),
					'a' => __( 'Sometimes. Lines do not always allow an advisor to take over an existing booking, and the rules differ by line and fare. The earlier I am involved, the more I can do. If the deposit is paid, call me before you book the next one.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'Can you hold a cabin before I commit?', 'oomphtravel' ),
					'a' => __( 'Most lines let an advisor hold a cabin for 24 to 72 hours without payment. I use it to lock in the right cabin while you check calendars or sleep on it. Holds are not always available on promotional fares.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'What if I want to extend before or after the sailing?', 'oomphtravel' ),
					'a' => __( 'That is the part that stays on this site. Hotels in the embarkation and disembarkation ports, transfers, day tours, and the rail or flight connections are priced separately from the cruise, and it is where most of my planning time goes.', 'oomphtravel' ),
				),
				array(
					'q' => __( 'How do you handle solo supplements?', 'oomphtravel' ),
					'a' => __( 'A solo supplement can nearly double the price of a cruise. I know which sailings waive it, which suite categories are friendliest to solo bookings, and which lines run solo promotions. It is often a question of timing as much as ship.', 'oomphtravel' ),
				),
			);
	}
	return array();
}

/**
 * The client story on a page: [quote, attribution] from one of the four
 * verified reviews, word for word (D37, D38), or null for none. Custom
 * journeys shows the two the homepage uses; the multi-generational page has
 * no review that fits and shows none.
 *
 * @return array<int,array{0:string,1:string}>
 */
function oomphtravel_way_quotes( string $key ): array {
	$defaults = array(
		'custom'   => array(
			array(
				'From the moment we booked, our travel agent made the entire process smooth and stress-free. Communication was excellent — every detail was clearly laid out in our itinerary, and everything went exactly as planned.',
				'LCMurray · Port Angeles, WA · 2025',
			),
			array(
				'What stood out most was his outstanding communication; he was always available to answer questions and kept us informed every step of the way. His expertise and personal touch made the entire process easy and stress-free.',
				'Gary T. · Poulsbo, WA · 2025',
			),
		),
		'resorts'  => array(
			array(
				'Eric arranged for us to fly into Miami and stay at a charming boutique hotel, perfectly located in the heart of vibrant Miami Beach. From evening strolls to dining out and soaking in the local atmosphere, everything felt effortless and exciting.',
				'travellover52 · Port Angeles, WA · 2025',
			),
		),
		'multigen' => array(),
		'cruise'   => array(
			array(
				'While the cruise itself was a blast, the highlights of our trip were actually the moments he planned before we set sail and after we returned.',
				'travellover52 · Port Angeles, WA · 2025',
			),
		),
	);

	/**
	 * Filters the client stories shown on a ways-to-travel page: a list of
	 * [quote, attribution] pairs from the four verified reviews (D37, D38).
	 *
	 * @param array  $quotes The pairs.
	 * @param string $key    'custom', 'resorts', 'multigen' or 'cruise'.
	 */
	$quotes = apply_filters( 'oomphtravel_way_quotes', $defaults[ $key ] ?? array(), $key );
	return is_array( $quotes ) ? array_values( array_filter( $quotes, static fn( $q ): bool => is_array( $q ) && ! empty( $q[0] ) ) ) : array();
}

/**
 * The FAQ accordion the four patterns share.
 *
 * @param array<int,array{q:string,a:string}> $faqs
 */
function oomphtravel_way_faq_list( array $faqs ): string {
	$out = '<div class="ot-accordion">';
	foreach ( $faqs as $qa ) {
		$out .= '<details class="ot-accordion__item ot-faq__item"><summary class="ot-accordion__summary">';
		$out .= '<span class="ot-accordion__title">' . esc_html( $qa['q'] ) . '</span>';
		$out .= '<span class="ot-accordion__marker" aria-hidden="true"></span></summary>';
		$out .= '<p class="ot-accordion__body">' . esc_html( $qa['a'] ) . '</p></details>';
	}
	return $out . '</div>';
}

/* ------------------------------------------------------------------ */
/* Wiring                                                                */
/* ------------------------------------------------------------------ */

/**
 * Stylesheets on the four pages only: destination.css for the hero, prose,
 * columns and accordion; tours.css for the numbered steps; then ways.css.
 */
function oomphtravel_enqueue_ways_assets(): void {
	if ( ! oomphtravel_is_way_page() ) {
		return;
	}
	wp_enqueue_style( 'oomphtravel-destination', OOMPHTRAVEL_THEME_URI . 'assets/css/destination.css', array( 'oomphtravel-components' ), OOMPHTRAVEL_THEME_VERSION );
	wp_enqueue_style( 'oomphtravel-tours', OOMPHTRAVEL_THEME_URI . 'assets/css/tours.css', array( 'oomphtravel-destination' ), OOMPHTRAVEL_THEME_VERSION );
	wp_enqueue_style( 'oomphtravel-ways', OOMPHTRAVEL_THEME_URI . 'assets/css/ways.css', array( 'oomphtravel-tours' ), OOMPHTRAVEL_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_ways_assets', 20 );

/** Preload the hero (R3), one hint per source. */
function oomphtravel_preload_way_hero(): void {
	$key = oomphtravel_way_key();
	if ( '' === $key ) {
		return;
	}
	$sources = oomphtravel_way_hero_sources( $key );
	foreach ( array( 'tall', 'wide' ) as $k ) {
		printf(
			'<link rel="preload" as="image" media="%s" href="%s" imagesrcset="%s" imagesizes="%s" fetchpriority="high">' . "\n",
			esc_attr( $sources[ $k ]['media'] ),
			esc_url( $sources[ $k ]['src'] ),
			esc_attr( $sources[ $k ]['srcset'] ),
			esc_attr( $sources[ $k ]['sizes'] )
		);
	}
}
add_action( 'wp_head', 'oomphtravel_preload_way_hero', 2 );

/**
 * A search description for each page (R6) when Eric has not typed one in
 * Rank Math's panel. The page bodies are empty, so without this Rank Math
 * would have nothing to fall back on.
 */
function oomphtravel_way_seo_description( string $description ): string {
	$key = oomphtravel_way_key();
	if ( '' === $key ) {
		return $description;
	}
	$title = (string) get_the_title( get_queried_object_id() );
	if ( '' !== trim( $description ) && trim( $description ) !== $title ) {
		return $description;
	}
	$defaults = array(
		'custom'   => __( 'Custom journeys planned to the hour: itinerary, vetted stays, private drivers and guides, and one advisor on call for the whole trip. No planning fee.', 'oomphtravel' ),
		'resorts'  => __( 'Resort vacations in Hawaii, Mexico and the Caribbean, and villas in Italy, France and Croatia, booked through suppliers who answer the phone. No planning fee.', 'oomphtravel' ),
		'multigen' => __( 'Multi-generational trips planned around pace, mobility, food and rooms that connect, so the week works for grandparents, parents and children alike.', 'oomphtravel' ),
		'cruise'   => __( 'Cruises are sold on CruiseOomph, the sister site. Here: the nights before, the days after, the transfers and hotels at both ends, planned by the same advisor.', 'oomphtravel' ),
	);
	return $defaults[ $key ];
}
add_filter( 'rank_math/frontend/description', 'oomphtravel_way_seo_description' );

/**
 * The plugin's schema treats these four as service pages (Service +
 * FAQPage + breadcrumb, class-schema.php), through the filters it already
 * exposes. The questions come from the same list the page shows.
 */
function oomphtravel_way_service_slugs( array $slugs ): array {
	return array_values( array_unique( array_merge( $slugs, array_values( oomphtravel_way_slugs() ) ) ) );
}
add_filter( 'oomph_service_page_slugs', 'oomphtravel_way_service_slugs' );

/**
 * @param array<int,array{question:string,answer:string}> $faqs
 * @param WP_Post|null                                    $post
 */
function oomphtravel_way_page_faqs( array $faqs, $post ): array {
	if ( $faqs || ! $post instanceof WP_Post ) {
		return $faqs;
	}
	$key = array_search( (string) $post->post_name, oomphtravel_way_slugs(), true );
	if ( false === $key ) {
		return $faqs;
	}
	return array_map(
		static fn( array $qa ): array => array( 'question' => $qa['q'], 'answer' => $qa['a'] ),
		oomphtravel_way_faqs( (string) $key )
	);
}
add_filter( 'oomph_page_faqs', 'oomphtravel_way_page_faqs', 10, 2 );
