<?php
/**
 * The editorial pages: About (plan §6.11), Client stories (§6.12), the
 * Journal index and article (§6.13) and the Travel Trends guide (§6.14).
 *
 * Four page records from `wp oomph seed pages` with empty bodies, mounted by
 * templates/page-{slug}.html, plus single.html for journal posts. The data
 * every pattern reads lives here so the page and its schema cannot drift:
 * the four verified client reviews (D37, D38), the second advisor (D15, D28),
 * the journal topics, the trends teasers.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Which page                                                           */
/* ------------------------------------------------------------------ */

/**
 * 'about', 'client-stories', 'journal', 'travel-trends', 'post', or '' when
 * the request is none of these.
 */
function oomphtravel_editorial_key(): string {
	if ( is_singular( 'post' ) ) {
		return 'post';
	}
	if ( ! is_page() ) {
		return '';
	}
	$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	return in_array( $slug, array( 'about', 'client-stories', 'journal', 'travel-trends' ), true ) ? $slug : '';
}

/* ------------------------------------------------------------------ */
/* Client stories (D37, D38, D39)                                       */
/* ------------------------------------------------------------------ */

/**
 * The four verified reviews, newest first, exactly as the clients posted
 * them. Bodies and titles are never reworded (D38); the plugin reads the
 * same rows through `oomph_client_testimonials` for the Review and
 * AggregateRating nodes, so the schema describes what the page shows.
 *
 * travellover52 stays as a client review by Eric's decision (D39).
 *
 * @return array<int,array{author:string,location:string,trip:string,date_pub:string,rating:int,title:string,body:string,cruise:bool}>
 */
function oomphtravel_client_stories(): array {
	return array(
		array(
			'author'   => 'LCMurray',
			'location' => 'Port Angeles, WA',
			'trip'     => 'Cruise · Mexican Riviera · March 2025',
			'date_pub' => '2025-04-21',
			'rating'   => 5,
			'title'    => 'Amazing Cruise and Fantastic Service!',
			'body'     => 'I recently went on a cruise to the Mexican Riviera in March, and it was absolutely wonderful! From the moment we booked, our travel agent made the entire process smooth and stress-free. Communication was excellent — every detail was clearly laid out in our itinerary, and everything went exactly as planned. The cruise itself was incredible. Our room had a balcony with a stunning view, and it was both comfortable and beautifully maintained. The restaurants onboard were top-notch with a wide variety of delicious food, and the entertainment options kept us busy and smiling every night. We truly had an unforgettable experience, and I can\'t thank our travel agent enough for making it all so easy. Highly recommend!', // verbatim client review (D38): never reworded, No List exempt.
			'cruise'   => true,
		),
		array(
			'author'   => 'travellover52',
			'location' => 'Port Angeles, WA',
			'trip'     => 'Cruise · Miami before and after',
			'date_pub' => '2025-04-20',
			'rating'   => 5,
			'title'    => 'Responsive, thoughtful, and detail oriented!',
			'body'     => 'We couldn\'t be more thrilled with the incredible travel experience Eric curated for us. While the cruise itself was a blast, the highlights of our trip were actually the moments he planned before we set sail and after we returned. Eric arranged for us to fly into Miami and stay at a charming boutique hotel, perfectly located in the heart of vibrant Miami Beach. From evening strolls to dining out and soaking in the local atmosphere, everything felt effortless and exciting. On the return side of our journey, Eric once again delivered. He secured a lovely hotel situated near a lively boardwalk filled with unique shops, cozy cafés, and inviting restaurants — an ideal setting to unwind and transition back to everyday life. What truly sets Eric apart is his eye for detail and his ability to tailor experiences that reflect who we are. His thoughtful planning and personal touches made this vacation not only seamless but unforgettable. We\'re already looking forward to booking our next adventure.', // verbatim client review (D38): never reworded, No List exempt.
			'cruise'   => true,
		),
		array(
			'author'   => 'Ak Chris',
			'location' => 'Port Angeles, WA',
			'trip'     => 'Cruise · Norwegian Cruise Line · four sailings and airfare',
			'date_pub' => '2025-04-19',
			'rating'   => 5,
			'title'    => 'NCL Cruze, mistake',
			'body'     => 'On our last cruise we ended up having a problem getting our travel documents from NCL. With them changing the rewards system, for some reason our docs did not show. Eric spent time with me on the phone and then 45 minutes with NCL getting it corrected. With that kind of customer service and not just dropping us off to NCL customer support gave us the feeling he cared and went the extra mile to help. We have used Eric for the last 4 cruses and airfare. Any problems that arose he got corrected quickly. Thank you and until next time.', // verbatim client review (D38): never reworded, No List exempt.
			'cruise'   => true,
		),
		array(
			'author'   => 'Gary T.',
			'location' => 'Poulsbo, WA',
			'trip'     => 'Cruise · Oceania Cruises',
			'date_pub' => '2025-04-18',
			'rating'   => 5,
			'title'    => 'Great Cruise Experience',
			'body'     => 'We had an absolutely fantastic cruise experience with Oceania Cruises, all thanks to Eric Hempel at Oomph Travel. From start to finish, Eric\'s planning was spot-on — every detail was handled with care, and his knowledge of cruise travel really showed. What stood out most was his outstanding communication; he was always available to answer questions and kept us informed every step of the way. His expertise and personal touch made the entire process easy and stress-free. We\'ll definitely be working with Eric again for our future adventures!', // verbatim client review (D38): never reworded, No List exempt.
			'cruise'   => true,
		),
	);
}
add_filter( 'oomph_client_testimonials', 'oomphtravel_client_stories', 10, 0 );

/**
 * The attribution line under a story: name, town, month and year, and the
 * rating in words rather than stars (docs/02-components.md), so the
 * AggregateRating the schema carries is visible on the page.
 *
 * @param array<string,mixed> $story
 */
function oomphtravel_story_attribution( array $story ): string {
	$when = (string) $story['date_pub'];
	$time = $when ? strtotime( $when ) : false;
	$parts = array( (string) $story['author'], (string) $story['location'] );
	if ( $time ) {
		$parts[] = date_i18n( 'F Y', $time );
	}
	/* translators: %d: rating out of five */
	$parts[] = sprintf( __( 'Rated %d of 5', 'oomphtravel' ), (int) $story['rating'] );
	return implode( ' · ', $parts );
}

/* ------------------------------------------------------------------ */
/* The second advisor (D15, D28)                                        */
/* ------------------------------------------------------------------ */

/**
 * Amy Hempel, as About prints her. The plugin reads the same array through
 * `oomph_second_advisor` for her Person node, and for the author of any
 * journal post her WordPress user wrote (login `amy`).
 *
 * The bio is adapted from her CruiseOomph profile (plan §6.11). The portrait
 * is a square crop of the original Eric supplied, at two widths; the monogram
 * in the pattern is what shows if the image is ever filtered away.
 *
 * @return array{name:string,first:string,login:string,url:string,jobTitle:string,description:string,credentials:string[],focus:string,image:string}
 */
function oomphtravel_second_advisor(): array {
	$data = array(
		'name'        => 'Amy Hempel',
		'first'       => 'Amy',
		'login'       => 'amy',
		'url'         => home_url( '/about/#amy' ),
		'jobTitle'    => __( 'Associate travel advisor', 'oomphtravel' ),
		'description' => __( 'Amy has been on every cruise I have taken, which makes her the second opinion I trust most on a ship, a cabin or a port day. By day she is a real estate agent, and she brings the same patience for a big decision to a trip: nobody is hurried, and nothing is booked until it is right. She works alongside me on the planning, and on cruise bookings through CruiseOomph.', 'oomphtravel' ),
		'credentials' => array( 'Nexion', 'Travel Leaders Network' ),
		'focus'       => __( 'Cruise bookings, resort stays, and the second read on every plan.', 'oomphtravel' ),
		'image'       => OOMPHTRAVEL_THEME_URI . 'assets/img/advisor-amy-720.webp',
	);

	/**
	 * Filters the second advisor's details as About prints them.
	 *
	 * @param array $data name, first, login, url, jobTitle, description, credentials, focus, image.
	 */
	$data = apply_filters( 'oomphtravel_second_advisor', $data );
	return is_array( $data ) ? $data : array();
}
add_filter( 'oomph_second_advisor', 'oomphtravel_second_advisor', 10, 0 );

/* ------------------------------------------------------------------ */
/* Journal                                                              */
/* ------------------------------------------------------------------ */

/**
 * The four topics the index offers as tabs (plan §6.13), by category slug.
 *
 * @return array<string,string>
 */
function oomphtravel_journal_topics(): array {
	return array(
		'destinations'   => __( 'Destinations', 'oomphtravel' ),
		'planning'       => __( 'Planning', 'oomphtravel' ),
		'resorts-villas' => __( 'Resorts & villas', 'oomphtravel' ),
		'tours'          => __( 'Tours', 'oomphtravel' ),
	);
}

/** The chosen topic from ?topic=, if it is one of the four; else ''. */
function oomphtravel_journal_topic(): string {
	$topic = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( (string) $_GET['topic'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
	return array_key_exists( $topic, oomphtravel_journal_topics() ) ? $topic : '';
}

/**
 * The tabs the index shows: All, then each topic whose category exists and
 * holds a published post. Nothing is offered that leads to an empty page.
 *
 * @return array<int,array{slug:string,label:string,url:string,current:bool}>
 */
function oomphtravel_journal_tabs(): array {
	$base    = get_permalink( get_queried_object_id() );
	$current = oomphtravel_journal_topic();
	$tabs    = array(
		array( 'slug' => '', 'label' => __( 'All', 'oomphtravel' ), 'url' => (string) $base, 'current' => '' === $current ),
	);
	foreach ( oomphtravel_journal_topics() as $slug => $label ) {
		$cat = get_category_by_slug( $slug );
		if ( ! $cat instanceof WP_Term || (int) $cat->count < 1 ) {
			continue;
		}
		$tabs[] = array(
			'slug'    => $slug,
			'label'   => $label,
			'url'     => add_query_arg( 'topic', $slug, (string) $base ),
			'current' => $slug === $current,
		);
	}
	return $tabs;
}

/** The index query: nine a page, the chosen topic only. */
function oomphtravel_journal_query(): WP_Query {
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$args  = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $paged,
	);
	$topic = oomphtravel_journal_topic();
	if ( '' !== $topic ) {
		$args['category_name'] = $topic;
	}
	return new WP_Query( $args );
}

/**
 * Who wrote a post, for the byline: the display name from the WordPress
 * user record, linked to About. The lead advisor's name comes from the
 * plugin's Advisor class when it is loaded, so the byline and the Person
 * node agree; the second advisor links to her anchor on the same page.
 *
 * @return array{name:string,url:string}
 */
function oomphtravel_post_byline( WP_Post $post ): array {
	$user   = get_userdata( (int) $post->post_author );
	$second = oomphtravel_second_advisor();
	if ( $user instanceof WP_User && '' !== (string) ( $second['login'] ?? '' ) && $user->user_login === $second['login'] ) {
		return array( 'name' => (string) $second['name'], 'url' => (string) $second['url'] );
	}
	$name = class_exists( '\OomphTravel\Core\Advisor' ) ? \OomphTravel\Core\Advisor::name() : '';
	if ( '' === $name ) {
		$name = $user instanceof WP_User ? (string) $user->display_name : 'Eric Hempel';
	}
	return array( 'name' => $name, 'url' => home_url( '/about/' ) );
}

/**
 * The destination a post is about, as a card: the first published
 * destination whose slug matches one of the post's tags or categories.
 * Null when none does, and the section is simply not shown.
 *
 * @return array<string,mixed>|null
 */
function oomphtravel_post_destination_card( WP_Post $post ): ?array {
	if ( ! function_exists( 'oomphtravel_destination_cards' ) ) {
		return null;
	}
	$slugs = array();
	foreach ( array( 'post_tag', 'category' ) as $tax ) {
		$terms = get_the_terms( $post, $tax );
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$slugs[] = (string) $term->slug;
			}
		}
	}
	if ( ! $slugs ) {
		return null;
	}
	$cards = oomphtravel_destination_cards( array_values( array_unique( $slugs ) ) );
	return $cards ? $cards[0] : null;
}

/**
 * Three more posts: the same category first, then the latest, never the
 * post itself.
 *
 * @return WP_Post[]
 */
function oomphtravel_related_posts( WP_Post $post ): array {
	$args = array(
		'numberposts'      => 3,
		'post_status'      => 'publish',
		'exclude'          => array( $post->ID ),
		'suppress_filters' => false,
	);
	$cats = get_the_category( $post->ID );
	$found = $cats ? get_posts( $args + array( 'category' => (int) $cats[0]->term_id ) ) : array();
	if ( count( $found ) < 3 ) {
		$exclude = array_merge( array( $post->ID ), wp_list_pluck( $found, 'ID' ) );
		$more    = get_posts( array( 'exclude' => $exclude, 'numberposts' => 3 - count( $found ) ) + $args );
		$found   = array_merge( $found, $more );
	}
	return array_values( array_filter( $found, static fn( $p ): bool => $p instanceof WP_Post ) );
}

/* ------------------------------------------------------------------ */
/* Travel Trends guide (D17, D18, D30)                                  */
/* ------------------------------------------------------------------ */

/** The guide's year, from the guide itself. */
function oomphtravel_trends_year(): string {
	return (string) apply_filters( 'oomphtravel_trends_year', '2026' );
}

/**
 * The five teasers on the trends page, drawn from the guide's six chapters
 * (emerging destinations, wellness, sustainable travel, adventure, coastal,
 * leisure). Places named are the guide's own.
 *
 * @return string[]
 */
function oomphtravel_trends_teasers(): array {
	return array(
		__( 'The places rising before the crowds: Slovenia, Comporta, Medellín, the Balearics and Panama.', 'oomphtravel' ),
		__( 'Wellness that outlasts the trip: Okinawa, the Dolomites, Sri Lanka, Maui and Costa Rica’s Papagayo coast.', 'oomphtravel' ),
		__( 'Travel that looks after the place: Rwanda, Finland, the Peloponnese, northern Namibia and Dominica.', 'oomphtravel' ),
		__( 'Coastlines with a character of their own: Sardinia, Marbella, Fiji, coastal Vietnam and Turkey’s Black Sea.', 'oomphtravel' ),
		__( 'Slow weeks in one good base: Santa Fe, Lisbon, Marrakech, Killarney, and the resort towns that work for three generations.', 'oomphtravel' ),
	);
}

/**
 * The chapters, for the "what is inside" section.
 *
 * @return array<int,array{title:string,body:string}>
 */
function oomphtravel_trends_chapters(): array {
	return array(
		array( 'title' => __( 'Emerging destinations', 'oomphtravel' ), 'body' => __( 'Regions that feel new but are easy to reach, with strong food, design and local culture: the Balearics, Slovenia, Medellín, Comporta and Panama City.', 'oomphtravel' ) ),
		array( 'title' => __( 'Wellness destinations', 'oomphtravel' ), 'body' => __( 'Hot springs, forest walks and sea air over rigid programmes: Okinawa, the Dolomites, Sri Lanka, Maui and the Papagayo Peninsula.', 'oomphtravel' ) ),
		array( 'title' => __( 'Travel with purpose', 'oomphtravel' ), 'body' => __( 'Places that protect what people come to see: Rwanda, Finland, the Peloponnese, northern Namibia and Dominica.', 'oomphtravel' ) ),
		array( 'title' => __( 'Adventure with a story', 'oomphtravel' ), 'body' => __( 'Heli-assisted ski days, long walks and small ships: Savoie, the Galápagos, southern Peru, Andermatt and the Indian Himalayas.', 'oomphtravel' ) ),
		array( 'title' => __( 'Coastal classics', 'oomphtravel' ), 'body' => __( 'Historic ports, quiet shores and islands with an identity: Fiji, Sardinia, Marbella, coastal Vietnam and the Black Sea coast.', 'oomphtravel' ) ),
		array( 'title' => __( 'Leisure, taken slowly', 'oomphtravel' ), 'body' => __( 'One comfortable base, good food and wine, and days that are not a checklist: Las Vegas, Santa Fe, Marrakech, Lisbon and Killarney.', 'oomphtravel' ) ),
	);
}

/** The success line the signup shows (docs/03-rules-and-readiness.md: double opt-in, in plain words). */
function oomphtravel_trends_success_text(): string {
	return __( 'Check your email and press the button, then the guide is on its way.', 'oomphtravel' );
}

/* ------------------------------------------------------------------ */
/* Assets                                                               */
/* ------------------------------------------------------------------ */

/**
 * destination.css for the hero, prose and card primitives, then
 * editorial.css, on these pages only.
 */
function oomphtravel_enqueue_editorial_assets(): void {
	if ( '' === oomphtravel_editorial_key() ) {
		return;
	}
	wp_enqueue_style( 'oomphtravel-destination', OOMPHTRAVEL_THEME_URI . 'assets/css/destination.css', array( 'oomphtravel-components' ), OOMPHTRAVEL_THEME_VERSION );
	wp_enqueue_style( 'oomphtravel-editorial', OOMPHTRAVEL_THEME_URI . 'assets/css/editorial.css', array( 'oomphtravel-destination' ), OOMPHTRAVEL_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_editorial_assets', 20 );

/**
 * Preload the image the page opens on (R3): the portrait on About, the
 * guide cover on Travel trends, the featured image on a post that has one.
 */
function oomphtravel_preload_editorial_image(): void {
	$key = oomphtravel_editorial_key();
	$img = OOMPHTRAVEL_THEME_URI . 'assets/img/';
	if ( 'about' === $key ) {
		printf( '<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="%s" fetchpriority="high">' . "\n", esc_url( $img . 'advisor-eric-720.webp' ), esc_attr( $img . 'advisor-eric-360.webp 360w, ' . $img . 'advisor-eric-720.webp 720w' ), esc_attr( '(min-width: 1024px) 480px, (min-width: 768px) 40vw, 100vw' ) );
	} elseif ( 'travel-trends' === $key ) {
		printf( '<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="%s" fetchpriority="high">' . "\n", esc_url( $img . 'trends-cover-960.webp' ), esc_attr( $img . 'trends-cover-480.webp 480w, ' . $img . 'trends-cover-960.webp 960w' ), esc_attr( '(min-width: 1024px) 520px, 100vw' ) );
	}
}
add_action( 'wp_head', 'oomphtravel_preload_editorial_image', 2 );

/* ------------------------------------------------------------------ */
/* Search                                                               */
/* ------------------------------------------------------------------ */

/** A filtered journal view is shareable but not indexable, like the tours index. */
function oomphtravel_journal_robots( array $robots ): array {
	if ( 'journal' === oomphtravel_editorial_key() && '' !== oomphtravel_journal_topic() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'oomphtravel_journal_robots' );
add_filter(
	'rank_math/frontend/robots',
	static function ( $robots ) {
		if ( 'journal' === oomphtravel_editorial_key() && '' !== oomphtravel_journal_topic() && is_array( $robots ) ) {
			$robots['index'] = 'noindex';
		}
		return $robots;
	}
);

/**
 * A search description for each page (R6) until Eric types his own in
 * Rank Math's box; the page bodies are empty, so there is nothing else to
 * fall back on.
 *
 * @param mixed $description
 */
function oomphtravel_editorial_seo_description( $description ) {
	$key = oomphtravel_editorial_key();
	if ( '' === $key || 'post' === $key ) {
		return $description;
	}
	$title = (string) get_the_title( get_queried_object_id() );
	if ( is_string( $description ) && '' !== trim( $description ) && trim( $description ) !== $title ) {
		return $description;
	}
	$year     = oomphtravel_trends_year();
	$defaults = array(
		'about'          => __( 'Eric Hempel plans custom journeys, escorted tours and resort stays from Port Angeles, Washington, with Amy Hempel alongside. One named advisor from the first call to the last flight home.', 'oomphtravel' ),
		'client-stories' => __( 'Four reviews from clients Eric Hempel has planned for, posted in their own words and changed in no way. Read them, then start planning your own trip.', 'oomphtravel' ),
		'journal'        => __( 'Notes from the road by Eric and Amy Hempel: destinations, planning, resorts and villas, and escorted tours, written after going there.', 'oomphtravel' ),
		/* translators: %s: the guide year */
		'travel-trends'  => sprintf( __( 'The Travel Trends %s guide: the places, stays and ways of travelling I am watching this year, free by email. One message to confirm, then the guide arrives.', 'oomphtravel' ), $year ),
	);
	return $defaults[ $key ];
}
add_filter( 'rank_math/frontend/description', 'oomphtravel_editorial_seo_description' );
