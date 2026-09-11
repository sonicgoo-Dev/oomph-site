<?php
/**
 * Seed the launch records: the eleven destinations (D07 + D33), the seven
 * operators (D25) and three representative tours per escorted operator
 * (plan §6.5). Run from WP-CLI: `wp oomph seed destinations|operators|tours`.
 *
 * Idempotent and additive. Each record is looked up by slug; an existing one
 * is left exactly as it is, so re-running after Eric has edited a page
 * changes nothing. New records are created as drafts with only the fields
 * the handoff fixes — slug, title, variant, kind, order, the CruiseOomph
 * region where it is unambiguous — and, for Italy only, the page content
 * that plan §6.3 fixes for the first destination page. Every
 * field value is written the way the fields plugin writes it (value under
 * the field name, field key under the underscored name) so the admin form
 * shows it straight away.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Seed {

	/**
	 * The eleven destinations in index order (plan §6.2; Africa per D33).
	 * cruiseoomph_region is set only where the CruiseOomph filter value is
	 * beyond doubt; Portugal, Hawaii, Mexico and Africa are left for Eric.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function destinations(): array {
		return array(
			array( 'slug' => 'italy',      'title' => 'Italy',                  'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean',   'headline' => 'Italy, planned by someone who keeps going back.' ),
			array( 'slug' => 'uk-ireland', 'title' => 'UK & Ireland',           'variant' => 'custom', 'cruiseoomph_region' => 'Northern Europe' ),
			array( 'slug' => 'france',     'title' => 'France',                 'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'spain',      'title' => 'Spain',                  'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'portugal',   'title' => 'Portugal',               'variant' => 'custom', 'cruiseoomph_region' => '' ),
			array( 'slug' => 'greece',     'title' => 'Greece',                 'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'croatia',    'title' => 'Croatia & the Adriatic', 'variant' => 'custom', 'cruiseoomph_region' => 'Mediterranean' ),
			array( 'slug' => 'hawaii',     'title' => 'Hawaii',                 'variant' => 'resort', 'cruiseoomph_region' => '' ),
			array( 'slug' => 'mexico',     'title' => 'Mexico',                 'variant' => 'resort', 'cruiseoomph_region' => '' ),
			array( 'slug' => 'caribbean',  'title' => 'Caribbean',              'variant' => 'resort', 'cruiseoomph_region' => 'Caribbean' ),
			array( 'slug' => 'africa',     'title' => 'Africa',                 'variant' => 'guided', 'cruiseoomph_region' => '' ),
		);
	}

	/**
	 * The seven operators in display order (D25, D32).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function operators(): array {
		return array(
			array( 'slug' => 'globus',                          'title' => 'Globus',                          'kind' => 'escorted' ),
			array( 'slug' => 'tauck',                           'title' => 'Tauck',                           'kind' => 'escorted' ),
			array( 'slug' => 'insight-vacations',               'title' => 'Insight Vacations',               'kind' => 'escorted' ),
			array( 'slug' => 'abercrombie-kent',                'title' => 'Abercrombie & Kent',              'kind' => 'escorted' ),
			array( 'slug' => 'national-geographic-expeditions', 'title' => 'National Geographic Expeditions', 'kind' => 'escorted', 'cruiseoomph_line' => true ),
			array( 'slug' => 'classic-vacations',               'title' => 'Classic Vacations',               'kind' => 'fit', 'ways' => array( 'custom', 'resorts' ) ),
			array( 'slug' => 'avanti-destinations',             'title' => 'Avanti Destinations',             'kind' => 'fit', 'ways' => array( 'custom' ) ),
		);
	}

	/**
	 * The four ways-to-travel pages (plan §6.4, §6.8, §6.9, §6.10) and Start
	 * planning (§6.15). Each is a page record with an empty body: the copy
	 * lives in the theme's pattern and templates/page-{slug}.html mounts it,
	 * so there is nothing for anyone to fill in. They are created published
	 * (draft on production) because the header, footer and destination pages
	 * already link to them.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function pages(): array {
		return array(
			array( 'slug' => 'custom-journeys',                    'title' => 'Custom journeys' ),
			array( 'slug' => 'resorts-and-villas',                 'title' => 'Resorts & villas' ),
			array( 'slug' => 'multi-generational-travel-planning', 'title' => 'Multi-generational trips' ),
			array( 'slug' => 'cruise-planning',                    'title' => 'Cruise planning' ),
			array( 'slug' => 'start-planning',                     'title' => 'Start planning' ),
		);
	}

	/**
	 * Seed one set. Returns one row per record: slug, title, action, id.
	 *
	 * @param string $what    'destinations', 'operators', 'tours' or 'pages'.
	 * @param bool   $dry_run Report without writing.
	 * @return array<int,array{slug:string,title:string,action:string,id:int}>
	 */
	public static function run( string $what, bool $dry_run = false ): array {
		switch ( $what ) {
			case 'destinations':
				$records   = self::destinations();
				$post_type = CPT_Destination::POST_TYPE;
				break;
			case 'operators':
				$records   = self::operators();
				$post_type = CPT_Operator::POST_TYPE;
				break;
			case 'tours':
				$records   = self::tours();
				$post_type = CPT_Tour::POST_TYPE;
				break;
			case 'pages':
				$records   = self::pages();
				$post_type = 'page';
				break;
			default:
				throw new \InvalidArgumentException( 'Unknown seed set: ' . $what );
		}

		$rows  = array();
		$order = 0;
		foreach ( $records as $record ) {
			++$order;
			$existing = get_page_by_path( $record['slug'], OBJECT, $post_type );
			if ( $existing instanceof \WP_Post ) {
				$action = 'exists (' . $existing->post_status . ')';
				// Operators seeded before the draft copy existed get their
				// empty fields filled; anything Eric has typed is left alone.
				if ( 'operators' === $what && ! $dry_run ) {
					$filled = self::fill_operator( (int) $existing->ID, $record['slug'] );
					if ( $filled ) {
						$action .= sprintf( ', %d empty field(s) filled', $filled );
					}
				}
				$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => $action, 'id' => (int) $existing->ID );
				continue;
			}
			if ( $dry_run ) {
				$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => 'would create', 'id' => 0 );
				continue;
			}

			switch ( $what ) {
				case 'destinations':
					$id = self::create_destination( $record, $order );
					break;
				case 'tours':
					$id = self::create_tour( $record, $order );
					break;
				case 'pages':
					$id = self::create_page( $record, $order );
					break;
				default:
					$id = self::create_operator( $record, $order );
			}

			$status = $id ? (string) get_post_status( $id ) : '';
			$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => $id ? 'created (' . $status . ')' : 'failed', 'id' => $id );
		}

		return $rows;
	}

	/** @param array<string,mixed> $record */
	private static function create_destination( array $record, int $order ): int {
		$id = self::insert( CPT_Destination::POST_TYPE, $record, $order );
		if ( ! $id ) {
			return 0;
		}
		self::field( $id, 'variant', 'field_oomph_dest_variant', $record['variant'] );
		self::field( $id, 'cruiseoomph_region', 'field_oomph_dest_cruiseoomph_region', (string) ( $record['cruiseoomph_region'] ?? '' ) );
		if ( ! empty( $record['headline'] ) ) {
			self::field( $id, 'headline', 'field_oomph_dest_headline', (string) $record['headline'] );
		}
		if ( 'italy' === $record['slug'] ) {
			self::write_italy( $id );
		}
		// wp_insert_post already fired save_post, which made the taxonomy term.
		return $id;
	}

	/** @param array<string,mixed> $record */
	private static function create_operator( array $record, int $order ): int {
		$id = self::insert( CPT_Operator::POST_TYPE, $record, $order );
		if ( ! $id ) {
			return 0;
		}
		self::field( $id, 'kind', 'field_oomph_op_kind', $record['kind'] );
		self::field( $id, 'cruiseoomph_line', 'field_oomph_op_cruiseoomph_line', empty( $record['cruiseoomph_line'] ) ? '0' : '1' );
		if ( 'fit' === $record['kind'] ) {
			self::field( $id, 'ways', 'field_oomph_op_ways', (array) ( $record['ways'] ?? array( 'custom' ) ) );
		}
		self::fill_operator( $id, (string) $record['slug'] );
		return $id;
	}

	/**
	 * A ways-to-travel page: empty body, template-driven, published unless
	 * this is production (where nothing the seed makes goes live by itself).
	 *
	 * @param array<string,mixed> $record
	 */
	private static function create_page( array $record, int $order ): int {
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => Environment::is_production() ? 'draft' : 'publish',
				'post_title'   => $record['title'],
				'post_name'    => $record['slug'],
				'post_content' => '',
				'post_author'  => self::author(),
				'menu_order'   => $order,
			),
			true
		);
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/** @param array<string,mixed> $record */
	private static function insert( string $post_type, array $record, int $order ): int {
		$id = wp_insert_post(
			array(
				'post_type'   => $post_type,
				'post_status' => 'draft',
				'post_title'  => $record['title'],
				'post_name'   => $record['slug'],
				'post_author' => self::author(),
				'menu_order'  => $order,
			),
			true
		);
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/**
	 * Write a field the way the fields plugin does: value under the name,
	 * field key under the underscored name.
	 *
	 * @param mixed $value
	 */
	private static function field( int $post_id, string $name, string $key, $value ): void {
		update_post_meta( $post_id, $name, $value );
		update_post_meta( $post_id, '_' . $name, $key );
	}

	/** The current user under WP-CLI, else the first administrator, else 0. */
	private static function author(): int {
		$current = get_current_user_id();
		if ( $current ) {
			return $current;
		}
		$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ) );
		return $admins ? (int) $admins[0] : 0;
	}

	/**
	 * Italy's page content (plan §6.3, Italy first). Written only when the
	 * record is created, so Eric's later edits are never overwritten.
	 *
	 * The intro, the first three regions and the six FAQs are Eric's own copy
	 * from the old Custom Italy page, lightly cut. Puglia, Sicily, the
	 * Dolomites, the sample itinerary and the stays are drafts in his voice
	 * for him to change in the admin form. Perk wording stays conditional
	 * (docs/03-rules-and-readiness.md).
	 */
	private static function write_italy( int $id ): void {
		Fields::write(
			$id,
			'intro',
			'field_oomph_dest_intro',
			'<p>Most Italy itineraries spend a day where you want three and three where you want one. Knowing the difference is the whole job, and it is why I keep going back: the town worth a night, the one worth an afternoon, and the drive between them that is worth doing in daylight.</p>'
			. '<p>I plan Italy one region at a time. Private drivers where the coast road jams, a guide who makes a hill town come alive, agriturismi and family-run hotels that live up to their photographs, restaurants booked before you land, and the trains, transfers, ZTL driving zones and luggage hand-offs handled before they become a problem.</p>'
			. '<p>It suits couples marking something, families across three generations, and anyone who would rather have one advisor who has walked these towns than a 1-800 number. If you want five countries in ten days, I plan slower trips than that.</p>'
		);

		Fields::write_repeater(
			$id,
			'regions',
			'field_oomph_dest_regions',
			array(
				array( 'name' => 'Tuscany & the hill towns', 'blurb' => 'Florence as an anchor, then the slower country: Siena, the Val d’Orcia, a villa with a view.' ),
				array( 'name' => 'The Lakes', 'blurb' => 'Como, Maggiore, Garda. Grand-hotel mornings and boat afternoons.' ),
				array( 'name' => 'The Amalfi Coast', 'blurb' => 'Positano, Ravello, the boat days. Best paced with somewhere quiet to come back to.' ),
				array( 'name' => 'Puglia', 'blurb' => 'The heel of Italy: trulli towns, olive groves running down to the sea, and Lecce’s baroque. A masseria makes the base.' ),
				array( 'name' => 'Sicily', 'blurb' => 'Palermo’s markets, the Greek temples at Agrigento, Etna, and the baroque towns of the south-east. Ten days is the right amount.' ),
				array( 'name' => 'The Dolomites', 'blurb' => 'Rifugio lunches, cable cars, a lake with a view. June to September, with Venice or Verona at either end.' ),
			),
			array( 'name' => 'field_oomph_dest_region_name', 'blurb' => 'field_oomph_dest_region_blurb' )
		);

		Fields::write_repeater(
			$id,
			'sample_itinerary',
			'field_oomph_dest_itinerary',
			array(
				array( 'day' => '1', 'title' => 'Rome', 'text' => 'Land, a driver waiting, a hotel near the Pantheon. An early dinner and an early night.' ),
				array( 'day' => '2', 'title' => 'Rome', 'text' => 'A private guide for the Forum and the Vatican before the crowds, then the afternoon to yourselves in Trastevere.' ),
				array( 'day' => '3', 'title' => 'Rome to Florence, by way of Orvieto', 'text' => 'A driver north with a long lunch on Orvieto’s cliff, then the fast train on to Florence.' ),
				array( 'day' => '4', 'title' => 'Florence', 'text' => 'The Uffizi with a guide who edits, a leather workshop, dinner across the river.' ),
				array( 'day' => '5', 'title' => 'Into the Val d’Orcia', 'text' => 'Collect a car, or keep the driver, and settle into a villa or agriturismo with a view for three nights.' ),
				array( 'day' => '6', 'title' => 'Siena and Montalcino', 'text' => 'Siena in the morning, a Brunello tasting after lunch, the pool before dinner.' ),
				array( 'day' => '7', 'title' => 'A day with no plan', 'text' => 'Pienza for pecorino, or nothing at all. The one day every good Italy trip needs.' ),
				array( 'day' => '8', 'title' => 'South to the coast', 'text' => 'Fast train to Naples, a driver over the hill to Positano or Ravello.' ),
				array( 'day' => '9', 'title' => 'The boat day', 'text' => 'A private boat along the coast, a swim, lunch in a cove, back in time for the evening light.' ),
				array( 'day' => '10', 'title' => 'Home from Naples', 'text' => 'A driver to the airport with time in hand. Ten days, one way to do it; there are others.' ),
			),
			array( 'day' => 'field_oomph_dest_itin_day', 'title' => 'field_oomph_dest_itin_title', 'text' => 'field_oomph_dest_itin_text' )
		);

		Fields::write_repeater(
			$id,
			'stays',
			'field_oomph_dest_stays',
			array(
				array( 'name' => 'A working agriturismo in Tuscany', 'type' => 'villa', 'note' => 'A farm with rooms, a pool and a view; dinner from the kitchen most nights.', 'perks' => 'Depending on the property and the rate, I can usually add breakfast and a welcome from the owners.' ),
				array( 'name' => 'A family-run hotel in Florence or Rome', 'type' => 'hotel', 'note' => 'Twenty rooms, a bar the neighbourhood uses, and a walk to everything.', 'perks' => 'Depending on the property and the rate, I can usually add breakfast, and often an upgrade on arrival.' ),
				array( 'name' => 'A villa with a view, Val d’Orcia or Amalfi', 'type' => 'villa', 'note' => 'The one the family remembers. I know which ones photograph better than they live.', 'perks' => 'Depending on the property and the rate, I can usually add a stocked kitchen on arrival and a chef for one night.' ),
				array( 'name' => 'A grand hotel on Lake Como', 'type' => 'resort', 'note' => 'Grand-hotel mornings and boat afternoons, with the lake doing the work.', 'perks' => 'Depending on the property and the rate, I can usually add breakfast and a resort credit.' ),
			),
			array( 'name' => 'field_oomph_dest_stay_name', 'type' => 'field_oomph_dest_stay_type', 'note' => 'field_oomph_dest_stay_note', 'perks' => 'field_oomph_dest_stay_perks' )
		);

		Fields::write( $id, 'best_months', 'field_oomph_dest_best_months', array( '4', '5', '6', '9', '10' ) );

		Fields::write_repeater(
			$id,
			'faq',
			'field_oomph_dest_faq',
			array(
				array( 'question' => 'Do you charge a planning fee?', 'answer' => 'No. Suppliers pay a commission on what you book, and that commission doesn’t change your price. You get itinerary design, the stays and guides worth booking, and the logistics handled, at no added cost to you.' ),
				array( 'question' => 'Can you take over a trip I’ve already started planning?', 'answer' => 'Often, yes. The earlier I’m involved the more I can shape, but if you’ve already booked a hotel or two, I can build the rest of the trip around them. Bring what you have to the call and I’ll tell you honestly where I can add value.' ),
				array( 'question' => 'How far ahead should I start?', 'answer' => 'For spring and fall in the popular regions, six to nine months is comfortable; the best villas and guides book early. I’ve turned around shorter timelines, but the runway buys you the good options.' ),
				array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. International air is often best handled a particular way; I’ll tell you when it’s worth using miles and when it isn’t.' ),
				array( 'question' => 'Which regions do you plan?', 'answer' => 'Puglia, Sicily, Tuscany, the Lakes, the Dolomites, the Amalfi Coast, and the classic anchors: Rome, Florence, Venice. If you have a region in mind that isn’t listed, ask.' ),
				array( 'question' => 'Can you plan an Italy trip for three generations?', 'answer' => 'Yes. Multi-generational Italy is one of the things I plan most. Pace, mobility and dietary needs change the plan; mention them on the call and I’ll build around them.' ),
			),
			array( 'question' => 'field_oomph_dest_faq_q', 'answer' => 'field_oomph_dest_faq_a' )
		);
	}

	/* ------------------------------------------------------------------ */
	/* Operator copy                                                        */
	/* ------------------------------------------------------------------ */

	/**
	 * Draft fit notes for the seven operators, in Eric's voice, for him to
	 * correct in the admin form. Written into a record only where the field
	 * is still empty (fill_operator), so nothing Eric has typed is touched.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function operator_copy(): array {
		return array(
			'globus'                          => array(
				'fit_line'   => 'First-time Europe, the classic cities, at a price that leaves room for the extras.',
				'fit_note'   => '<p>Globus is where I send people doing Europe by coach for the first time, and people who want the big cities covered without the planning. The hotels are good, central and reliable rather than grand; the tour directors are the reason people come back.</p><p>Days are full. If you want three cities in eight days with someone else handling the luggage, this fits. If you want long lunches and afternoons off, look at Tauck, or ask me about a custom journey.</p>',
				'group_size' => 'Up to about 45',
				'price_band' => 'Premium',
				'inclusions' => "Hotels, breakfast daily and some dinners\nA tour director throughout\nCoach travel between cities\nGuided sightseeing in the main stops\nLuggage handling at hotels",
			),
			'tauck'                           => array(
				'fit_line'   => 'Everything included, gratuities too. The one to choose when you never want to reach for your wallet.',
				'fit_note'   => '<p>Tauck is the operator I recommend most to couples marking something. The price looks high next to the others until you read what it covers: most meals, the guides, the gratuities, the airport transfers, the entries that other tours sell as extras. On the road there are no envelopes and no optional-excursion pitches.</p><p>Hotels are a step up, often the landmark property in town. The pace is measured, with more free time than a Globus or Insight itinerary. If you value not thinking about money for two weeks, this is the one.</p>',
				'group_size' => 'Around 40, with smaller-group departures on some itineraries',
				'price_band' => 'Luxury',
				'inclusions' => "Landmark hotels\nMost meals, with wine at dinner\nAll gratuities, including the tour director and driver\nAirport transfers on arrival and departure\nEntries, guides and the experiences other tours sell as extras",
			),
			'insight-vacations'               => array(
				'fit_line'   => 'Premium coach touring with more legroom, better hotels and a director who knows the back roads.',
				'fit_note'   => '<p>Insight sits between Globus and Tauck. The coaches carry fewer people than they were built for, so there is room to spread out; the hotels are a notch above; the itineraries linger where the classic tours rush. Their country-roads trips are the ones I like best: Tuscany, Puglia, the west of Ireland, at a pace that lets a place register.</p><p>It suits travellers who have done a first coach tour and want the next one to be more comfortable, and anyone who cares about where they sleep.</p>',
				'group_size' => 'Up to 40, on coaches built for more',
				'price_band' => 'Premium',
				'inclusions' => "Premium hotels, many in the centre of town\nBreakfast daily and a selection of dinners\nA travel director and a driver throughout\nCoaches with extra legroom\nSome experiences with local specialists",
			),
			'abercrombie-kent'                => array(
				'fit_line'   => 'Small groups, expert guides, the properties you would choose yourself. The top of the escorted market.',
				'fit_note'   => '<p>Abercrombie &amp; Kent runs its small-group journeys with a resident guide who lives where you are travelling, a group capped at eighteen, and hotels, lodges and camps that stand on their own. I sell A&amp;K on safari above anyone else, and its Europe journeys for travellers who want a group but not a coach.</p><p>It is the most expensive name on this page and the one with the fewest compromises. If the difference between good and exceptional matters to you, this is where it shows.</p>',
				'group_size' => 'Capped at 18',
				'price_band' => 'Ultra-luxury',
				'inclusions' => "Luxury hotels, lodges and camps\nMost meals\nA resident guide throughout, local experts at each stop\nInternal flights and private transfers\nGratuities for guides and drivers on most journeys",
			),
			'national-geographic-expeditions' => array(
				'fit_line'   => 'Travel with a National Geographic expert: a photographer, a biologist, a historian. For people who want to learn something.',
				'fit_note'   => '<p>National Geographic Expeditions pairs each departure with an expert in the subject, so a safari travels with a wildlife biologist and a Sicily trip with an archaeologist. Groups are small and the days are built around the expert’s access.</p><p>It suits curious travellers, and grandparents taking grandchildren who want the trip to be about something. The land expeditions are here; the ship-based voyages sell through CruiseOomph.</p>',
				'group_size' => 'Small groups, typically under 25',
				'price_band' => 'Luxury',
				'inclusions' => "Hotels, lodges and camps chosen for location\nMost meals\nA National Geographic expert and an expedition leader\nActivities and entries in the itinerary\nGratuities for local guides and drivers",
			),
			'classic-vacations'               => array(
				'fit_line'   => 'The supplier behind most of my Hawaii, Mexico and Caribbean resort stays, and a good deal of Europe.',
				'fit_note'   => '<p>Classic Vacations is a wholesale supplier, not a tour operator: the name never appears on your trip. I book resorts, villas and air through them because of the rates, the room categories they hold, and the way a problem on the ground gets fixed. Hawaii and Mexico are where they are strongest; Europe hotels and rail come through them too.</p>',
				'group_size' => '',
				'price_band' => 'Premium to luxury',
				'inclusions' => "Resort and hotel stays\nFlights packaged with the stay\nAirport transfers and car hire\nSelected tours and activities",
			),
			'avanti-destinations'             => array(
				'fit_line'   => 'Independent Europe, built piece by piece: hotels, rail, drivers, guides, day by day.',
				'fit_note'   => '<p>Avanti Destinations is the supplier I use to assemble custom journeys in Europe. Hotels of character, rail in the right class, private drivers for the days a train doesn’t work, and guides booked by name. It is the machinery behind an itinerary, not a product you buy off a shelf.</p>',
				'group_size' => '',
				'price_band' => 'Premium to luxury',
				'inclusions' => "Hotels and small properties across Europe\nRail and private transfers\nPrivate and small-group guided days\nCar hire where it suits",
			),
		);
	}

	/**
	 * Write the draft copy into an operator's empty fields. Returns the
	 * number of fields filled. Fields Eric has already filled are skipped.
	 */
	private static function fill_operator( int $id, string $slug ): int {
		$copy = self::operator_copy()[ $slug ] ?? array();
		$keys = array(
			'fit_line'   => 'field_oomph_op_fit_line',
			'fit_note'   => 'field_oomph_op_fit_note',
			'group_size' => 'field_oomph_op_group_size',
			'price_band' => 'field_oomph_op_price_band',
			'inclusions' => 'field_oomph_op_inclusions',
		);
		$filled = 0;
		foreach ( $keys as $name => $key ) {
			if ( '' === (string) ( $copy[ $name ] ?? '' ) || '' !== Fields::value( $id, $name ) ) {
				continue;
			}
			Fields::write( $id, $name, $key, $copy[ $name ] );
			++$filled;
		}
		return $filled;
	}

	/* ------------------------------------------------------------------ */
	/* Tours                                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Three representative tours per escorted operator (plan §6.5, §6.7),
	 * drafts for Eric to replace with the trips he sells (D26). Titles
	 * describe the route rather than quoting an operator's product name, so
	 * nothing here can be mistaken for a brochure. No prices: the from-price
	 * is the operator's standard rate and Eric enters it (D36). No dates
	 * (D35); `months` is only the filter on the index.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function tours(): array {
		return array(
			array(
				'slug'        => 'globus-rome-florence-venice',
				'title'       => 'Rome, Florence and Venice',
				'operator'    => 'globus',
				'destination' => 'italy',
				'nights'      => 9,
				'start_city'  => 'Rome',
				'end_city'    => 'Venice',
				'months'      => array( 3, 4, 5, 6, 7, 8, 9, 10 ),
				'featured'    => true,
				'blurb'       => 'The three cities most people mean when they say Italy, with the luggage and the trains handled.',
				'group_size'  => 'Up to about 45',
				'pace'        => 'Full days, with an afternoon free in each city',
				'inclusions'  => "Central hotels, breakfast daily and three dinners\nTour director throughout\nGuided walks in Rome, Florence and Venice\nVatican Museums and Uffizi entries\nCoach travel and luggage handling",
				'erics_note'  => '<p>This is the trip I suggest when a couple has never been to Italy and wants the three big cities in one go. Nine nights is enough to see each one properly and short enough to leave you wanting more. The hotels are central, the guides are good, and the days are full.</p><p>If you would rather have afternoons off and long lunches, ask me about the Tauck version or a custom journey.</p>',
				'itinerary'   => array(
					array( 'day' => '1', 'title' => 'Arrive in Rome', 'overnight' => 'Rome', 'text' => 'Transfer to the hotel and settle in. The evening is yours; I’ll have a trattoria booked nearby.' ),
					array( 'day' => '2', 'title' => 'The Vatican', 'overnight' => 'Rome', 'text' => 'A guided morning in the Vatican Museums and the Sistine Chapel, then St Peter’s. The afternoon is free.' ),
					array( 'day' => '3', 'title' => 'Ancient Rome', 'overnight' => 'Rome', 'text' => 'The Colosseum and the Forum with a local guide, then an evening walk through the centro storico.' ),
					array( 'day' => '4', 'title' => 'Rome to Florence, by way of Orvieto', 'overnight' => 'Florence', 'text' => 'North through Umbria with a stop in Orvieto for the cathedral and lunch. Florence by late afternoon.' ),
					array( 'day' => '5', 'title' => 'Florence on foot', 'overnight' => 'Florence', 'text' => 'The Accademia for Michelangelo’s David, then the Duomo and the old centre with a guide.' ),
					array( 'day' => '6', 'title' => 'A free day in Tuscany', 'overnight' => 'Florence', 'text' => 'Siena and San Gimignano make an easy excursion; or stay and do the Uffizi at a walking pace.' ),
					array( 'day' => '7', 'title' => 'Florence to Venice', 'overnight' => 'Venice', 'text' => 'Across the Apennines with lunch in Bologna, then on to Venice and a boat to the hotel.' ),
					array( 'day' => '8', 'title' => 'St Mark’s and the Grand Canal', 'overnight' => 'Venice', 'text' => 'St Mark’s Basilica and the Doge’s Palace with a guide, then a private boat along the Grand Canal.' ),
					array( 'day' => '9', 'title' => 'Venice at your pace', 'overnight' => 'Venice', 'text' => 'Murano and Burano by vaporetto, or nothing at all. Farewell dinner.' ),
					array( 'day' => '10', 'title' => 'Depart Venice', 'overnight' => '', 'text' => 'Transfer to the airport, or stay on. I can add nights on Lake Como or in the Dolomites.' ),
				),
			),
			array(
				'slug'        => 'globus-scotland-edinburgh-highlands',
				'title'       => 'Scotland, Edinburgh to the Highlands',
				'operator'    => 'globus',
				'destination' => 'uk-ireland',
				'nights'      => 8,
				'start_city'  => 'Edinburgh',
				'end_city'    => 'Edinburgh',
				'months'      => array( 4, 5, 6, 7, 8, 9, 10 ),
				'blurb'       => 'Edinburgh, castle country, Loch Ness and the Isle of Skye, with the driving done for you.',
				'group_size'  => 'Up to about 45',
				'pace'        => 'Moderate, with long scenic drives',
				'inclusions'  => "Hotels, breakfast daily and some dinners\nTour director throughout\nEdinburgh Castle and a Highland distillery visit\nThe Skye crossing\nCoach travel and luggage handling",
				'erics_note'  => '<p>Scotland by coach makes sense because the driving is the hard part: single-track roads, weather, and distances that look short on the map. This one gets Skye and the Highlands into eight nights without anyone gripping a steering wheel.</p><p>It suits first-timers and anyone with roots to trace. Pack for four seasons in a day.</p>',
			),
			array(
				'slug'        => 'globus-spain-portugal',
				'title'       => 'Spain and Portugal, Madrid to Lisbon',
				'operator'    => 'globus',
				'destination' => 'spain',
				'nights'      => 11,
				'start_city'  => 'Madrid',
				'end_city'    => 'Lisbon',
				'months'      => array( 3, 4, 5, 6, 9, 10, 11 ),
				'blurb'       => 'Madrid, Seville, the Alhambra and the Algarve on the way to Lisbon. Two countries, one coach.',
				'group_size'  => 'Up to about 45',
				'pace'        => 'Full days',
				'inclusions'  => "Hotels, breakfast daily and some dinners\nTour director throughout\nThe Alhambra and Seville cathedral with guides\nCoach travel and luggage handling",
				'erics_note'  => '<p>Spain and Portugal together is a lot of ground, and this is the sensible way to cover it: Madrid and the south of Spain first, then across to Lisbon by way of the Algarve. Granada and Seville are the days people talk about afterwards.</p><p>Go in spring or autumn. The south is hot in July and August, and coach days are long.</p>',
			),
			array(
				'slug'        => 'tauck-italy-rome-to-the-lakes',
				'title'       => 'Italy, Rome to the Lakes',
				'operator'    => 'tauck',
				'destination' => 'italy',
				'nights'      => 12,
				'start_city'  => 'Rome',
				'end_city'    => 'Lake Como',
				'months'      => array( 4, 5, 6, 9, 10 ),
				'featured'    => true,
				'blurb'       => 'Rome, Florence, Venice and Lake Como with everything included, gratuities too.',
				'group_size'  => 'Around 40',
				'pace'        => 'Measured, with free afternoons',
				'inclusions'  => "Landmark hotels in each city\nMost meals, with wine at dinner\nAll gratuities\nAirport transfers\nPrivate after-hours visits where Tauck arranges them",
				'erics_note'  => '<p>When a couple is marking a big anniversary and wants Italy done without a single decision on the road, this is my answer. The hotels are the ones you’d pick yourself, the meals are included, and the tipping is done. Finishing on Lake Como is the right way to end.</p><p>Best in May, June, September and October.</p>',
			),
			array(
				'slug'        => 'tauck-ireland',
				'title'       => 'Ireland, Dublin to the Wild Atlantic Way',
				'operator'    => 'tauck',
				'destination' => 'uk-ireland',
				'nights'      => 10,
				'start_city'  => 'Dublin',
				'end_city'    => 'Dublin',
				'months'      => array( 5, 6, 7, 8, 9 ),
				'blurb'       => 'Dublin, Kilkenny, Killarney and the Ring of Kerry, the Cliffs of Moher, and a castle stay.',
				'group_size'  => 'Around 40',
				'pace'        => 'Relaxed; two and three nights in each place',
				'inclusions'  => "Castle and manor hotels\nMost meals\nAll gratuities\nAirport transfers\nGuided days and entries throughout",
				'erics_note'  => '<p>Ireland rewards a slower tour, and Tauck’s is the slowest of the ones I sell: two and three nights in each place, castle and manor stays, and the west coast given the time it deserves. I send multi-generational groups on this one; there is something for every age.</p>',
			),
			array(
				'slug'        => 'tauck-france-paris-provence',
				'title'       => 'France, Paris and Provence',
				'operator'    => 'tauck',
				'destination' => 'france',
				'nights'      => 10,
				'start_city'  => 'Paris',
				'end_city'    => 'Nice',
				'months'      => array( 4, 5, 6, 9, 10 ),
				'blurb'       => 'Paris, then south by fast train to Avignon, the Luberon villages and the Riviera.',
				'group_size'  => 'Around 40',
				'pace'        => 'Measured',
				'inclusions'  => "Landmark hotels\nMost meals, with wine at dinner\nAll gratuities\nThe TGV from Paris to Provence\nGuided days and entries throughout",
				'erics_note'  => '<p>Paris and Provence are the two Frances most people want, and the TGV joins them in under three hours. Tauck keeps the group in the north for the museums and the food, then in the south for the markets and the light. Ending in Nice makes it easy to add a few days on the coast.</p>',
			),
			array(
				'slug'        => 'insight-italy-country-roads',
				'title'       => 'Italy’s country roads, Tuscany to Puglia',
				'operator'    => 'insight-vacations',
				'destination' => 'italy',
				'nights'      => 13,
				'start_city'  => 'Florence',
				'end_city'    => 'Rome',
				'months'      => array( 4, 5, 6, 7, 8, 9, 10 ),
				'featured'    => true,
				'blurb'       => 'Tuscany, Umbria, the Amalfi Coast and Puglia’s trulli towns, on a coach with room to spread out.',
				'group_size'  => 'Up to 40',
				'pace'        => 'Moderate; two and three nights in most stops',
				'inclusions'  => "Premium hotels, several in the countryside\nBreakfast daily and half the dinners\nTravel director throughout\nLocal guides in the main towns\nCoaches with extra legroom",
				'erics_note'  => '<p>This is the escorted tour closest to how I’d plan Italy myself: skip the museum queues, spend the time in the hill towns, the countryside and the south. Puglia on an escorted tour is still rare, and Insight does it well.</p><p>It suits second-time Italy travellers and anyone who cares more about a long lunch than a long list.</p>',
			),
			array(
				'slug'        => 'insight-britain-ireland',
				'title'       => 'Britain and Ireland',
				'operator'    => 'insight-vacations',
				'destination' => 'uk-ireland',
				'nights'      => 15,
				'start_city'  => 'London',
				'end_city'    => 'London',
				'months'      => array( 4, 5, 6, 7, 8, 9, 10 ),
				'blurb'       => 'London, the Cotswolds, Wales, Edinburgh, the Highlands and Ireland’s west, in one tour.',
				'group_size'  => 'Up to 40',
				'pace'        => 'Moderate',
				'inclusions'  => "Premium hotels\nBreakfast daily and a selection of dinners\nTravel director throughout\nFerry crossings between the islands\nCoaches with extra legroom",
				'erics_note'  => '<p>Two weeks that cover both islands without feeling rushed, thanks to the ferry crossings and the two-night stops. Pick it if you have one shot at Britain and Ireland and want the great cities and the countryside both.</p>',
			),
			array(
				'slug'        => 'insight-greece',
				'title'       => 'Greece, Athens and the Peloponnese',
				'operator'    => 'insight-vacations',
				'destination' => 'greece',
				'nights'      => 9,
				'start_city'  => 'Athens',
				'end_city'    => 'Athens',
				'months'      => array( 4, 5, 6, 9, 10 ),
				'blurb'       => 'Athens, Delphi, Olympia, Nafplio and Mycenae, then a few nights on the islands.',
				'group_size'  => 'Up to 40',
				'pace'        => 'Moderate',
				'inclusions'  => "Premium hotels\nBreakfast daily and a selection of dinners\nTravel director throughout\nLocal guides at the ancient sites\nFerry to the islands",
				'erics_note'  => '<p>Greece’s mainland is the part most people skip, and the part I’d send you to first: Delphi, Olympia and the Peloponnese are what the islands are not. This tour does them properly and adds the islands at the end. Spring and early autumn are the months.</p>',
			),
			array(
				'slug'        => 'ak-kenya-tanzania-safari',
				'title'       => 'Kenya and Tanzania safari',
				'operator'    => 'abercrombie-kent',
				'destination' => 'africa',
				'nights'      => 12,
				'start_city'  => 'Nairobi',
				'end_city'    => 'Arusha',
				'months'      => array( 1, 2, 3, 6, 7, 8, 9, 10 ),
				'blurb'       => 'The Masai Mara, the Serengeti and Ngorongoro Crater with a resident guide and a group of eighteen at most.',
				'group_size'  => 'Capped at 18',
				'pace'        => 'Early starts, afternoons at the lodge',
				'inclusions'  => "Lodges and tented camps\nAll meals on safari\nA resident guide throughout\nInternal flights between parks\nPark fees and game drives",
				'erics_note'  => '<p>If you are going to do one safari, do it with A&amp;K. The guide stays with you from Nairobi to Arusha, the camps are the ones you’d choose from the photographs, and the group is small enough that every vehicle has a window seat. The migration months, roughly July to October, book a year ahead.</p>',
			),
			array(
				'slug'        => 'ak-croatia-dalmatian-coast',
				'title'       => 'Croatia and the Dalmatian coast',
				'operator'    => 'abercrombie-kent',
				'destination' => 'croatia',
				'nights'      => 9,
				'start_city'  => 'Zagreb',
				'end_city'    => 'Dubrovnik',
				'months'      => array( 5, 6, 9, 10 ),
				'blurb'       => 'Zagreb, Plitvice, Split and Dubrovnik, with the islands by private boat.',
				'group_size'  => 'Capped at 18',
				'pace'        => 'Moderate',
				'inclusions'  => "Luxury hotels\nMost meals\nA resident guide throughout\nA private boat day on the islands\nEarly access to Dubrovnik’s walls",
				'erics_note'  => '<p>Croatia’s coast is easy to do badly, one crowded stop after another, and A&amp;K’s version avoids it: early access to Dubrovnik’s walls, an island day on a private boat, and Plitvice’s lakes before the coaches arrive. Small group, good hotels. Not July or August.</p>',
			),
			array(
				'slug'        => 'ak-portugal-porto-algarve',
				'title'       => 'Portugal, Porto to the Algarve',
				'operator'    => 'abercrombie-kent',
				'destination' => 'portugal',
				'nights'      => 9,
				'start_city'  => 'Porto',
				'end_city'    => 'Faro',
				'months'      => array( 3, 4, 5, 6, 9, 10, 11 ),
				'blurb'       => 'Porto and the Douro, Lisbon and Sintra, the Alentejo and the Algarve coast.',
				'group_size'  => 'Capped at 18',
				'pace'        => 'Moderate',
				'inclusions'  => "Luxury hotels and quintas\nMost meals, with wine\nA resident guide throughout\nPrivate transfers\nA Douro wine day",
				'erics_note'  => '<p>Portugal is small enough to see top to bottom in nine nights, and this is the well-run way to do it: Porto and the Douro first, Lisbon in the middle, the quiet Alentejo and then the coast. The food and wine carry the trip.</p>',
			),
			array(
				'slug'        => 'natgeo-tanzania-migration',
				'title'       => 'Tanzania, the great migration',
				'operator'    => 'national-geographic-expeditions',
				'destination' => 'africa',
				'nights'      => 9,
				'start_city'  => 'Arusha',
				'end_city'    => 'Arusha',
				'months'      => array( 6, 7, 8, 9, 10 ),
				'blurb'       => 'The Serengeti and Ngorongoro Crater with a National Geographic wildlife expert on board.',
				'group_size'  => 'Small groups, typically under 25',
				'pace'        => 'Early game drives, rest in the heat of the day',
				'inclusions'  => "Lodges and camps\nAll meals on safari\nA National Geographic expert and an expedition leader\nInternal flights\nPark fees",
				'erics_note'  => '<p>The difference on a National Geographic safari is the person in the seat next to you: a biologist or a photographer who has spent years in the Serengeti. It suits travellers who want to understand what they are watching, and families with older teenagers.</p>',
			),
			array(
				'slug'        => 'natgeo-southern-africa',
				'title'       => 'Southern Africa, Botswana and Victoria Falls',
				'operator'    => 'national-geographic-expeditions',
				'destination' => 'africa',
				'nights'      => 10,
				'start_city'  => 'Johannesburg',
				'end_city'    => 'Victoria Falls',
				'months'      => array( 5, 6, 7, 8, 9, 10 ),
				'blurb'       => 'The Okavango Delta by mokoro, Chobe’s elephants, and the Falls to finish.',
				'group_size'  => 'Small groups, typically under 25',
				'pace'        => 'Early starts, with time at camp',
				'inclusions'  => "Small camps in the Delta and Chobe\nAll meals on safari\nA National Geographic expert and an expedition leader\nLight-aircraft transfers\nPark fees",
				'erics_note'  => '<p>Botswana is the quieter safari: water, elephants and very few other vehicles. This expedition adds Victoria Falls at the end, which is the right order. Camps are small and the group is smaller.</p>',
			),
			array(
				'slug'        => 'natgeo-andalusia',
				'title'       => 'Spain, Andalusia and the Alhambra',
				'operator'    => 'national-geographic-expeditions',
				'destination' => 'spain',
				'nights'      => 8,
				'start_city'  => 'Seville',
				'end_city'    => 'Granada',
				'months'      => array( 3, 4, 5, 10, 11 ),
				'blurb'       => 'Seville, Córdoba, Ronda and Granada with a historian of Moorish Spain.',
				'group_size'  => 'Small groups, typically under 25',
				'pace'        => 'Moderate, with a lot of walking',
				'inclusions'  => "Hotels chosen for location\nMost meals\nA National Geographic expert and an expedition leader\nEntries, including the Alhambra\nGround transport",
				'erics_note'  => '<p>Andalusia is the part of Spain with the most to explain, and having a historian along changes it. Eight nights between Seville and Granada, with the Alhambra as the finale. Spring or late autumn; summer is too hot for the walking.</p>',
			),
		);
	}

	/**
	 * Create one tour as a draft: fields, the operator link and the
	 * destination term. The operator and destination are found by slug,
	 * whatever their status, so `seed tours` works right after the other two.
	 *
	 * @param array<string,mixed> $record
	 */
	private static function create_tour( array $record, int $order ): int {
		$id = self::insert( CPT_Tour::POST_TYPE, $record, $order );
		if ( ! $id ) {
			return 0;
		}

		$operator = get_page_by_path( (string) $record['operator'], OBJECT, CPT_Operator::POST_TYPE );
		Fields::write( $id, 'operator', 'field_oomph_tour_operator', $operator instanceof \WP_Post ? (string) $operator->ID : '' );

		$text = array(
			'blurb'      => 'field_oomph_tour_blurb',
			'nights'     => 'field_oomph_tour_nights',
			'start_city' => 'field_oomph_tour_start_city',
			'end_city'   => 'field_oomph_tour_end_city',
			'group_size' => 'field_oomph_tour_group_size',
			'pace'       => 'field_oomph_tour_pace',
			'inclusions' => 'field_oomph_tour_inclusions',
			'erics_note' => 'field_oomph_tour_erics_note',
		);
		foreach ( $text as $name => $key ) {
			Fields::write( $id, $name, $key, (string) ( $record[ $name ] ?? '' ) );
		}
		Fields::write( $id, 'months', 'field_oomph_tour_months', array_map( 'strval', (array) ( $record['months'] ?? array() ) ) );
		Fields::write( $id, 'featured', 'field_oomph_tour_featured', empty( $record['featured'] ) ? '0' : '1' );
		Fields::write_repeater(
			$id,
			'itinerary',
			'field_oomph_tour_itinerary',
			(array) ( $record['itinerary'] ?? array() ),
			array(
				'day'       => 'field_oomph_tour_itin_day',
				'title'     => 'field_oomph_tour_itin_title',
				'overnight' => 'field_oomph_tour_itin_overnight',
				'text'      => 'field_oomph_tour_itin_text',
			)
		);

		$destination = get_page_by_path( (string) $record['destination'], OBJECT, CPT_Destination::POST_TYPE );
		$term_id     = $destination instanceof \WP_Post ? CPT_Destination::term_id( (int) $destination->ID ) : 0;
		if ( $term_id ) {
			wp_set_object_terms( $id, array( $term_id ), Taxonomies::DESTINATION );
		}
		return $id;
	}
}
