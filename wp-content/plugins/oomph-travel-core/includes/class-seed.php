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
	 * The four ways-to-travel pages (plan §6.4, §6.8, §6.9, §6.10), Start
	 * planning (§6.15), About, Client stories, the Journal index and the
	 * Travel Trends guide (§6.11–§6.14). Each is a page record with an empty body: the copy
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
			// Plan §6.11–§6.14 (Stage 10): the same shape, the copy in the theme.
			array( 'slug' => 'about',                              'title' => 'About' ),
			array( 'slug' => 'client-stories',                     'title' => 'Client stories' ),
			array( 'slug' => 'journal',                            'title' => 'Journal' ),
			array( 'slug' => 'travel-trends',                      'title' => 'Travel trends' ),
			// Plan §6.16 (Stage 11): the link-in-bio page the Instagram profile
			// points at. It already exists on staging and production (Stage 2 of
			// the old build), so there it is found, not created.
			array( 'slug' => 'links',                              'title' => 'Links' ),
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
				// Records seeded before the draft copy existed get their empty
				// fields filled; anything Eric has typed is left alone.
				if ( ! $dry_run && in_array( $what, array( 'operators', 'destinations' ), true ) ) {
					$filled = 'operators' === $what
						? self::fill_operator( (int) $existing->ID, $record['slug'] )
						: self::fill_destination( (int) $existing->ID, $record['slug'] );
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
		} else {
			self::fill_destination( $id, (string) $record['slug'] );
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

	/**
	 * Fill a destination's empty boxes from destination_copy(). A box that
	 * already holds anything, typed or seeded, is left alone, so running the
	 * seed again after Eric has edited a page changes nothing he wrote.
	 *
	 * @return int Fields written.
	 */
	private static function fill_destination( int $id, string $slug ): int {
		$copy = self::destination_copy()[ $slug ] ?? array();
		if ( ! $copy ) {
			return 0;
		}
		$filled = 0;

		foreach ( array( 'headline' => 'field_oomph_dest_headline', 'intro' => 'field_oomph_dest_intro' ) as $name => $key ) {
			if ( '' !== (string) ( $copy[ $name ] ?? '' ) && '' === Fields::value( $id, $name ) ) {
				Fields::write( $id, $name, $key, $copy[ $name ] );
				++$filled;
			}
		}

		if ( ! empty( $copy['best_months'] ) && ! Fields::choices( $id, 'best_months' ) ) {
			Fields::write( $id, 'best_months', 'field_oomph_dest_best_months', array_map( 'strval', $copy['best_months'] ) );
			++$filled;
		}

		$repeaters = array(
			'regions'          => array( 'field_oomph_dest_regions', array( 'name' => 'field_oomph_dest_region_name', 'blurb' => 'field_oomph_dest_region_blurb' ) ),
			'sample_itinerary' => array( 'field_oomph_dest_itinerary', array( 'day' => 'field_oomph_dest_itin_day', 'title' => 'field_oomph_dest_itin_title', 'text' => 'field_oomph_dest_itin_text' ) ),
			'stays'            => array( 'field_oomph_dest_stays', array( 'name' => 'field_oomph_dest_stay_name', 'type' => 'field_oomph_dest_stay_type', 'note' => 'field_oomph_dest_stay_note', 'perks' => 'field_oomph_dest_stay_perks' ) ),
			'faq'              => array( 'field_oomph_dest_faq', array( 'question' => 'field_oomph_dest_faq_q', 'answer' => 'field_oomph_dest_faq_a' ) ),
		);
		foreach ( $repeaters as $name => list( $key, $sub_keys ) ) {
			if ( empty( $copy[ $name ] ) || 0 !== (int) get_post_meta( $id, $name, true ) ) {
				continue;
			}
			Fields::write_repeater( $id, $name, $key, $copy[ $name ], $sub_keys );
			++$filled;
		}

		return $filled;
	}

	/**
	 * Draft page copy for the destinations after Italy, in Eric's voice, for
	 * him to correct in the admin form (plan §6.3: why this place, how I plan
	 * it, who it suits; regions; ten days one way to do it; four stays; best
	 * months; four to six questions). No prices, no placeholders, no perk
	 * lines: perks are written per property once it is confirmed SELECT or
	 * CURATED (D40). Hero photos come from Eric.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function destination_copy(): array {
		$fee = array( 'question' => 'Do you charge a planning fee?', 'answer' => 'No. Suppliers pay a commission on what you book, and that commission doesn’t change your price. You get itinerary design, the stays and guides worth booking, and the logistics handled, at no added cost to you.' );

		return array(

			'uk-ireland' => array(
				'headline'    => 'Britain and Ireland, planned a county at a time.',
				'intro'       => '<p>Distances look small on the map and long on the road. A Cotswolds village to the Lake District is a full day; Dublin to the Dingle Peninsula is another. The trips that work choose two or three regions and give each of them nights, and I plan them so the driving happens in daylight and the arrivals happen before the kitchen closes.</p>'
					. '<p>I plan Britain and Ireland with the car and the train doing different jobs: rail between the cities, a driver or a well-chosen rental for the country. Country-house hotels and castle stays where they earn their keep, a guide for Edinburgh or the Ring of Kerry who knows the back road, tables booked, tee times held, and the ferry and left-hand-drive questions answered before you land.</p>'
					. '<p>It suits couples with roots to trace, families with teenagers who need castles and coast, golfers, and anyone who wants London and Edinburgh but also the quiet in between. If you want the whole of both islands in a week, I plan slower trips than that.</p>',
				'regions'     => array(
					array( 'name' => 'London & the South', 'blurb' => 'Three or four nights as the anchor: theatre, the museums with a guide, a day to Windsor or Bath. Then the train out.' ),
					array( 'name' => 'The Cotswolds', 'blurb' => 'Honey-stone villages an hour and a half from London. Two nights in a country-house hotel, walks between pubs, a driver for the day.' ),
					array( 'name' => 'Edinburgh & the Highlands', 'blurb' => 'Edinburgh as the second city, then north: Glencoe, Skye, a distillery that pours what it makes. Single-track roads reward a driver.' ),
					array( 'name' => 'The Lake District & Yorkshire', 'blurb' => 'Fells, lakes and stone walls. A base near Ambleside or Grasmere and a walk chosen for the day’s weather.' ),
					array( 'name' => 'Dublin & the East', 'blurb' => 'Dublin for two nights, the Georgian squares and a proper pub, then Wicklow or Kilkenny on the way west.' ),
					array( 'name' => 'The Wild Atlantic Way', 'blurb' => 'Kerry, Dingle, Clare and Connemara. Castle and country-house stays, a boat to the Skelligs when the sea allows, and the cliffs at your pace.' ),
				),
				'sample_itinerary' => array(
					array( 'day' => '1', 'title' => 'London', 'text' => 'Land, a driver waiting, a hotel in Marylebone or Mayfair. A walk to reset the clock, dinner near the hotel.' ),
					array( 'day' => '2', 'title' => 'London', 'text' => 'A guide for Westminster and the Tower in the morning, an afternoon in the museum of your choosing, theatre tickets held for the evening.' ),
					array( 'day' => '3', 'title' => 'London to the Cotswolds', 'text' => 'A driver west with a long lunch in Oxford, then a country-house hotel for two nights.' ),
					array( 'day' => '4', 'title' => 'The Cotswolds', 'text' => 'A walk between two villages, lunch at the pub at the far end, a driver back. The garden before dinner.' ),
					array( 'day' => '5', 'title' => 'North to Edinburgh', 'text' => 'A driver to the station, the train up the east coast, an evening on the Royal Mile after the day visitors leave.' ),
					array( 'day' => '6', 'title' => 'Edinburgh', 'text' => 'A guide for the Old Town and the castle, then Leith for dinner by the water.' ),
					array( 'day' => '7', 'title' => 'Into the Highlands', 'text' => 'A driver north through Glencoe to a lodge or country hotel for two nights, with the road to Skye or Speyside ahead.' ),
					array( 'day' => '8', 'title' => 'Skye or Speyside', 'text' => 'A distillery that pours what it makes, a walk chosen for the weather, a long dinner.' ),
					array( 'day' => '9', 'title' => 'A day with no plan', 'text' => 'A loch, a book, a second breakfast. The one day every good Highlands trip needs.' ),
					array( 'day' => '10', 'title' => 'Home from Edinburgh or Inverness', 'text' => 'A driver to the airport with time in hand. Ten days, one way to do it; Ireland deserves its own ten.' ),
				),
				'stays'       => array(
					array( 'name' => 'A country-house hotel in the Cotswolds', 'type' => 'hotel', 'note' => 'Twenty rooms, log fires, a walled garden and a kitchen worth staying in for.', 'perks' => '' ),
					array( 'name' => 'A townhouse hotel in London or Edinburgh', 'type' => 'hotel', 'note' => 'Marylebone or the New Town: quiet streets, a walk to everything, breakfast done properly.', 'perks' => '' ),
					array( 'name' => 'A Highland lodge or estate house', 'type' => 'private-home', 'note' => 'A whole house for a family or a group, with a cook and a ghillie by arrangement.', 'perks' => '' ),
					array( 'name' => 'A castle or country house in Ireland', 'type' => 'hotel', 'note' => 'Kerry, Mayo or the Midlands: long drives in, big fires, and a pub within walking distance.', 'perks' => '' ),
				),
				'best_months' => array( 5, 6, 9 ),
				'faq'         => array(
					$fee,
					array( 'question' => 'Should we drive?', 'answer' => 'In the countryside, often yes, and I’ll book an automatic and route you around the motorways. In London, Edinburgh and Dublin, no: the train and a driver are faster and calmer. For the Highlands and the west of Ireland, a driver-guide for two or three days is the upgrade people are gladdest they made.' ),
					array( 'question' => 'How far ahead should I start?', 'answer' => 'Six to nine months for May, June and September, when the country-house hotels with a dozen rooms fill first. Golf on the famous links and Edinburgh in August need longer.' ),
					array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. An open-jaw ticket, into London and home from Edinburgh or Dublin, saves a day of backtracking, and Dublin’s US pre-clearance means you land at home as a domestic arrival.' ),
					array( 'question' => 'What about the weather?', 'answer' => 'Plan for it rather than around it. I choose bases where a wet morning has a gallery, a distillery or a long lunch in it, and I book the outdoor days with a fallback. Layers and a good coat solve most of the rest.' ),
					array( 'question' => 'Can you plan this for someone who walks slowly?', 'answer' => 'Yes. Cobbles, stairs in old inns and long castle approaches are the things to design around. Tell me on the call and I’ll choose ground-floor rooms, shorter walking days and a driver who waits.' ),
				),
			),

			'france' => array(
				'headline'    => 'France, planned beyond Paris.',
				'intro'       => '<p>Paris earns its four nights, and then the country begins. Provence and the Loire, Burgundy and Bordeaux, the Dordogne, Normandy’s coast, the Alps: each is a different trip, and the mistake is trying to string three of them together in a week. I plan France as Paris plus one region, sometimes two, with real nights in each.</p>'
					. '<p>The TGV does the long distances in two hours; a driver or a good rental does the rest. I book the small hotels and châteaux that live up to their photographs, a guide who makes the Louvre or a Burgundy cellar a conversation instead of a queue, tables reserved in the places that need reserving, and the market days, the Monday closures and the August rhythm accounted for before you land.</p>'
					. '<p>It suits couples marking an anniversary, families with a house and a pool in mind, wine people who want the cellar door rather than the tasting room, and anyone who has done Paris and wants to see what the French do on their own holidays. If you want Paris, Nice and Normandy in six days, I plan slower trips than that.</p>',
				'regions'     => array(
					array( 'name' => 'Paris', 'blurb' => 'Four nights, a neighbourhood hotel, a guide for one museum and one morning of markets. The rest on foot.' ),
					array( 'name' => 'The Loire', 'blurb' => 'Châteaux and gardens an hour from Paris by TGV. Two nights in a château hotel, a bicycle for an afternoon, a Vouvray tasting.' ),
					array( 'name' => 'Provence & the coast', 'blurb' => 'Hill villages, markets by the day of the week, a house with a pool; then Cassis or Nice for the sea. Lavender in late June and early July; the light all year.' ),
					array( 'name' => 'Burgundy & Lyon', 'blurb' => 'Beaune, the Côte d’Or by bicycle or car, cellar visits arranged with the growers. Lyon for the tables.' ),
					array( 'name' => 'Bordeaux & the Dordogne', 'blurb' => 'Saint-Émilion and the Médoc châteaux, then east to the river, the caves and the market towns of the Périgord.' ),
					array( 'name' => 'Normandy & Brittany', 'blurb' => 'The D-Day beaches with a guide who knows the names, Mont-Saint-Michel at dawn, Honfleur, and oysters in Cancale.' ),
				),
				'sample_itinerary' => array(
					array( 'day' => '1', 'title' => 'Paris', 'text' => 'Land, a driver waiting, a hotel in Saint-Germain or the Marais. A walk along the river, dinner near the hotel.' ),
					array( 'day' => '2', 'title' => 'Paris', 'text' => 'A guide for the Louvre or the Orsay before it fills, the afternoon in the Marais, a table booked for eight.' ),
					array( 'day' => '3', 'title' => 'Paris', 'text' => 'A morning market with a guide who cooks, then Versailles by car with the gardens quiet after four, or a day with no plan at all.' ),
					array( 'day' => '4', 'title' => 'TGV to Provence', 'text' => 'Two hours and forty minutes to Avignon, a driver waiting, a house or a hotel in the Luberon for four nights.' ),
					array( 'day' => '5', 'title' => 'The Luberon', 'text' => 'Market day in whichever village has it, lunch under a plane tree, the pool.' ),
					array( 'day' => '6', 'title' => 'Aix or Arles', 'text' => 'A driver for the day: Cézanne’s Aix or Roman Arles, back for dinner at the house.' ),
					array( 'day' => '7', 'title' => 'The Rhône', 'text' => 'A cellar visit arranged with the grower in Châteauneuf-du-Pape, the Pont du Gard on the way home.' ),
					array( 'day' => '8', 'title' => 'A day with no plan', 'text' => 'A walk to the next village, a long lunch, nothing else. Every good France trip needs one.' ),
					array( 'day' => '9', 'title' => 'To the coast', 'text' => 'A driver east to Cassis or Nice: a boat along the calanques, or a seafront hotel for the last night.' ),
					array( 'day' => '10', 'title' => 'Home from Marseille or Nice', 'text' => 'A driver to the airport with time in hand. Ten days, one way to do it; Burgundy and the Loire make another.' ),
				),
				'stays'       => array(
					array( 'name' => 'A château hotel in the Loire or Bordeaux', 'type' => 'hotel', 'note' => 'A dozen rooms in a house with a history, dinner in the old dining room, a park to walk before breakfast.', 'perks' => '' ),
					array( 'name' => 'A neighbourhood hotel in Paris', 'type' => 'hotel', 'note' => 'Saint-Germain or the Marais: forty rooms, a courtyard, a bar the street uses, a walk to everything.', 'perks' => '' ),
					array( 'name' => 'A house with a pool in the Luberon', 'type' => 'villa', 'note' => 'Stone, shutters, a pool and a market ten minutes away. The one a family remembers.', 'perks' => '' ),
					array( 'name' => 'A palace hotel on the coast or in the Alps', 'type' => 'resort', 'note' => 'Cap d’Antibes in June, Courchevel in February: the grand version, chosen for the season.', 'perks' => '' ),
				),
				'best_months' => array( 4, 5, 6, 9, 10 ),
				'faq'         => array(
					$fee,
					array( 'question' => 'Paris and where else?', 'answer' => 'One region, two if the TGV connects them. Paris and Provence is the classic pairing; Paris and the Loire suits a shorter trip; Paris, Burgundy and Lyon suits people who travel to eat. I’ll tell you on the call which fits your dates.' ),
					array( 'question' => 'How far ahead should I start?', 'answer' => 'Six to nine months for May, June, September and October; the châteaux with twelve rooms and the houses with pools go first. Paris during fashion week and the big trade fairs needs longer, or a different week.' ),
					array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. Into Paris and home from Nice or Marseille saves a day of backtracking; I’ll tell you when it’s worth using miles and when it isn’t.' ),
					array( 'question' => 'Do we need French?', 'answer' => 'No. A bonjour opens every door, and courtesy does the rest. Where it matters I book guides and drivers who work in English, and I brief the hotels on what you need before you arrive.' ),
					array( 'question' => 'Can you plan this for someone who walks slowly?', 'answer' => 'Yes. Hill villages and old hotels with stairs are the things to design around. I choose bases with lifts and flat centres, a driver who waits, and museum visits timed for the quiet hours.' ),
				),
			),

			'spain' => array(
				'headline'    => 'Spain, planned to the rhythm of its day.',
				'intro'       => '<p>Spain runs on a different clock: lunch at two, dinner at nine, a city that fills its streets at eleven. Trips that fight it are exhausting; trips built around it are some of the easiest in Europe. I plan Spain so the guide arrives when the museum opens, the long lunch is the day’s centrepiece, and the evening walk is the point.</p>'
					. '<p>The AVE trains join Madrid, Barcelona, Seville and Córdoba in a few hours, so the driving is saved for Andalusia’s white villages and the green north. I book the paradores and the palace hotels that live up to their reputations, a guide who makes the Alhambra or the Prado a conversation, tables in the places that fill, and the tickets that sell out weeks ahead held in your name.</p>'
					. '<p>It suits couples marking something, families across three generations who want a base with a pool, people who travel to eat, and anyone who has seen Barcelona once and wants the rest. If you want Madrid, Barcelona, Seville and Mallorca in eight days, I plan slower trips than that.</p>',
				'regions'     => array(
					array( 'name' => 'Madrid & Castile', 'blurb' => 'The Prado and the Reina Sofía with a guide, tapas by neighbourhood, Toledo or Segovia for a day. Three nights.' ),
					array( 'name' => 'Barcelona & Catalonia', 'blurb' => 'Gaudí with the tickets held, the Gothic Quarter early, the Costa Brava or the Penedès cellars by driver.' ),
					array( 'name' => 'Andalusia', 'blurb' => 'Seville, Córdoba and Granada by AVE and driver, the Alhambra timed right, a white village or a hacienda for the slow days.' ),
					array( 'name' => 'The Basque Country & Rioja', 'blurb' => 'San Sebastián’s tables, Bilbao’s museum, Rioja’s cellars an hour inland. Four nights, well spent.' ),
					array( 'name' => 'Mallorca & the islands', 'blurb' => 'The Tramuntana villages, a finca with a pool, a boat day. June and September rather than August.' ),
					array( 'name' => 'Galicia & the north coast', 'blurb' => 'Santiago, the rías, Asturias’ green coast. Cooler, greener and quieter: the Spain most visitors miss.' ),
				),
				'sample_itinerary' => array(
					array( 'day' => '1', 'title' => 'Madrid', 'text' => 'Land, a driver waiting, a hotel between the Prado and the Retiro. A late walk, a first plate of jamón.' ),
					array( 'day' => '2', 'title' => 'Madrid', 'text' => 'A guide for the Prado in the morning, a siesta, tapas in La Latina by night.' ),
					array( 'day' => '3', 'title' => 'Toledo', 'text' => 'A driver for the day, back for a late dinner or a flamenco tablao worth the name.' ),
					array( 'day' => '4', 'title' => 'AVE to Seville', 'text' => 'Two and a half hours south, a hotel in Santa Cruz, the cathedral and the Alcázar with a guide before they fill.' ),
					array( 'day' => '5', 'title' => 'Seville', 'text' => 'A morning in Triana’s market, the afternoon free, dinner late and outdoors.' ),
					array( 'day' => '6', 'title' => 'Córdoba to Granada', 'text' => 'A driver by way of Córdoba’s Mezquita and a lunch in a patio, on to Granada by evening.' ),
					array( 'day' => '7', 'title' => 'Granada', 'text' => 'The Alhambra at the hour on the ticket, the Albaicín at dusk with the palace lit across the valley.' ),
					array( 'day' => '8', 'title' => 'Into the white villages', 'text' => 'A driver west to Ronda, then a hacienda or a hotel with a pool for two nights.' ),
					array( 'day' => '9', 'title' => 'A day with no plan', 'text' => 'The pool, a walk into the village, a long lunch. The one day every good Spain trip needs.' ),
					array( 'day' => '10', 'title' => 'Home from Málaga', 'text' => 'A driver down to the coast with time in hand. Ten days, one way to do it; the Basque Country makes another.' ),
				),
				'stays'       => array(
					array( 'name' => 'A parador in a convent or a castle', 'type' => 'hotel', 'note' => 'The state-run historic hotels, often in the building the town grew up around. Granada’s sits inside the Alhambra walls.', 'perks' => '' ),
					array( 'name' => 'A palace hotel in Madrid or Seville', 'type' => 'hotel', 'note' => 'Grand rooms, a rooftop, and a location that makes the evening walk the day’s simplest decision.', 'perks' => '' ),
					array( 'name' => 'A hacienda or finca with a pool', 'type' => 'villa', 'note' => 'Andalusia or Mallorca: whitewashed walls, olive trees, and the slow days a family needs between the cities.', 'perks' => '' ),
					array( 'name' => 'A small hotel in San Sebastián or Barcelona', 'type' => 'hotel', 'note' => 'Thirty rooms, well run, a short walk to the tables you came for.', 'perks' => '' ),
				),
				'best_months' => array( 4, 5, 6, 9, 10 ),
				'faq'         => array(
					$fee,
					array( 'question' => 'When should we not go?', 'answer' => 'July and August in Andalusia, when Seville passes 40°C, and Holy Week and the Feria in Seville unless you are going for them. Spring and autumn are the answer for most of the country; the north coast is the summer exception.' ),
					array( 'question' => 'How far ahead should I start?', 'answer' => 'Six to nine months. Alhambra tickets are released ahead and sell out, the paradores’ good rooms go first, and the tables people fly for are booked when their diaries open.' ),
					array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. Into Madrid and home from Málaga or Barcelona saves a day of backtracking; I’ll tell you when it’s worth using miles and when it isn’t.' ),
					array( 'question' => 'Is the late dinner a problem?', 'answer' => 'Only if you fight it. I book the early sittings where they exist, choose hotels with a good bar for a proper eight o’clock plate, and plan the big meal at lunch. Most clients are on Spanish time by day three and miss it when they get home.' ),
					array( 'question' => 'Can you plan this for someone who walks slowly?', 'answer' => 'Yes. Cobbles, the Alhambra’s slopes and old hotels with stairs are the things to design around. I choose ground-floor rooms, a driver who waits, and the guide who knows the step-free routes.' ),
				),
			),

			'portugal' => array(
				'headline'    => 'Portugal, planned from Lisbon to the Douro.',
				'intro'       => '<p>Portugal is small enough to see properly and varied enough to fill two weeks: Lisbon on its seven hills, Porto and the Douro’s terraced river, the Alentejo’s cork plains, the Algarve’s cliffs and beaches. The trips that work give Lisbon and Porto three nights each and put real days in the country between them.</p>'
					. '<p>I plan Portugal with the train doing Lisbon to Porto and a driver doing the rest, because the Douro’s roads and the Alentejo’s distances are better from the back seat. Quintas in the vineyards, pousadas in old convents, a guide who knows Lisbon’s tiles and Porto’s cellars, the fado house worth the evening, and the tables booked in a country where the good rooms have twelve of them.</p>'
					. '<p>It suits couples who want Europe without the crush, families with a coast in mind, wine people who want to sit on the terrace where the port is made, and anyone doing Portugal for the first time who wants it done properly. If you want Lisbon, Porto and the Algarve in five days, I plan slower trips than that.</p>',
				'regions'     => array(
					array( 'name' => 'Lisbon & Sintra', 'blurb' => 'Three nights: the hills by tram and on foot with a guide, Belém’s pastries, Sintra’s palaces by driver before the coaches arrive.' ),
					array( 'name' => 'Porto & the Douro', 'blurb' => 'Porto for two nights and the lodges across the river, then the train or a boat up the Douro to a quinta among the terraces.' ),
					array( 'name' => 'The Alentejo', 'blurb' => 'Cork oaks, whitewashed towns, Évora’s Roman temple and a pace that slows everything down. One or two nights break the drive south.' ),
					array( 'name' => 'The Algarve', 'blurb' => 'The western cliffs and the quieter beaches near Lagos and Sagres; the resorts around Vilamoura if golf is the point.' ),
					array( 'name' => 'The Minho', 'blurb' => 'Green, Atlantic and old: Guimarães, Braga, the vinho verde estates. An easy day or two north of Porto.' ),
					array( 'name' => 'Madeira & the Azores', 'blurb' => 'The islands: levada walks and a mild winter on Madeira; crater lakes and whales in the Azores. Both are direct flights from the east coast in season.' ),
				),
				'sample_itinerary' => array(
					array( 'day' => '1', 'title' => 'Lisbon', 'text' => 'Land, a driver waiting, a hotel in Chiado or Príncipe Real. A miradouro at sunset, grilled fish for dinner.' ),
					array( 'day' => '2', 'title' => 'Lisbon', 'text' => 'A guide for Alfama and the tiles, the afternoon in Belém, a fado house worth the evening.' ),
					array( 'day' => '3', 'title' => 'Sintra', 'text' => 'A driver up before the coaches, the palaces and the gardens, lunch by the sea in Cascais on the way back.' ),
					array( 'day' => '4', 'title' => 'Train to Porto', 'text' => 'Three hours north, a hotel in the Ribeira or the Baixa, a port lodge across the river before dinner.' ),
					array( 'day' => '5', 'title' => 'Porto', 'text' => 'A guide for the Baixa and the bookshop everyone asks about, the Serralves museum after lunch, a francesinha if you dare.' ),
					array( 'day' => '6', 'title' => 'Up the Douro', 'text' => 'The riverside train to Pinhão, or a boat, then a quinta among the terraces for two nights.' ),
					array( 'day' => '7', 'title' => 'The Douro', 'text' => 'A morning in the vineyard with the family who makes the wine, a swim, the terrace at dusk.' ),
					array( 'day' => '8', 'title' => 'South to the coast', 'text' => 'A driver back down the country with lunch in Coimbra, then a house or a hotel near Comporta for two nights.' ),
					array( 'day' => '9', 'title' => 'A day with no plan', 'text' => 'The beach, the rice fields, a long lunch in the village. The one day every good Portugal trip needs.' ),
					array( 'day' => '10', 'title' => 'Home from Lisbon', 'text' => 'An hour to the airport with time in hand. Ten days, one way to do it; the Alentejo and the Algarve make another.' ),
				),
				'stays'       => array(
					array( 'name' => 'A quinta in the Douro', 'type' => 'hotel', 'note' => 'A wine estate with rooms, a pool above the river, and dinner from what the valley grows.', 'perks' => '' ),
					array( 'name' => 'A pousada in a former convent or castle', 'type' => 'hotel', 'note' => 'The national historic hotels: Évora, Óbidos, Guimarães. Thick walls, cloisters, and the town at the door.', 'perks' => '' ),
					array( 'name' => 'A house near Comporta or in the Alentejo', 'type' => 'villa', 'note' => 'Whitewashed, low, a pool, and a pine forest between you and the beach.', 'perks' => '' ),
					array( 'name' => 'A palace hotel in Lisbon or Sintra', 'type' => 'hotel', 'note' => 'The grand version: a tiled staircase, gardens, and a driver at the door for the day.', 'perks' => '' ),
				),
				'best_months' => array( 4, 5, 6, 9, 10 ),
				'faq'         => array(
					$fee,
					array( 'question' => 'Lisbon first, or Porto?', 'answer' => 'Either. Fly into one and home from the other and the country runs north to south or the reverse without backtracking. I usually start in Lisbon and finish on the coast, so the last days are the slowest.' ),
					array( 'question' => 'How far ahead should I start?', 'answer' => 'Six to nine months for May, June, September and October. The Douro quintas have a handful of rooms each, and the houses near Comporta are booked by the families who return every year.' ),
					array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. Lisbon and Porto both have direct flights from the east coast; I’ll tell you when it’s worth using miles and when it isn’t.' ),
					array( 'question' => 'Is Portugal a good first trip back to Europe?', 'answer' => 'Yes. English is widely spoken, distances are short, prices are gentler than France or Italy, and the food is simple and very good. It is one of the first countries I suggest to people returning after a long gap.' ),
					array( 'question' => 'Can you plan this for someone who walks slowly?', 'answer' => 'Yes. Lisbon’s hills and cobbles are the thing to design around: a hotel with a lift on flatter ground, a driver rather than the tram, and Porto’s riverside reached from above rather than climbed to. The Douro and the coast are easy.' ),
				),
			),

			'greece' => array(
				'headline'    => 'Greece, planned from Athens to the islands.',
				'intro'       => '<p>Greece is Athens, and then a choice: which islands, how many, and how you get between them. Two islands is the right number for ten days; three is the number people regret. I plan Greece around the ferry and flight timetables that actually exist, so the days are spent on a terrace or in the water rather than at a port.</p>'
					. '<p>Athens gets two proper nights and a guide who makes the Acropolis and its museum a story. Then the islands: Santorini and Mykonos when they fit, Naxos, Paros, Milos, Crete, Hydra or the Ionian when they fit better. Hotels chosen for the view that matters at breakfast, a boat day with a skipper who knows the coves, tavernas booked where booking is possible, and a driver on the mainland for Delphi, Nafplio and the Peloponnese.</p>'
					. '<p>It suits couples marking an anniversary, families who want one villa and a boat, first-timers who want the famous islands done well, and second-timers ready for the quieter ones. If you want Santorini, Mykonos, Crete and Rhodes in a week, I plan slower trips than that.</p>',
				'regions'     => array(
					array( 'name' => 'Athens', 'blurb' => 'Two or three nights: the Acropolis at opening with a guide, the museum, Plaka and Koukaki on foot, a rooftop for the view at dusk.' ),
					array( 'name' => 'Santorini', 'blurb' => 'The caldera view, the cave hotels of Oia and Imerovigli, a catamaran at sunset. May, June, September and October suit it.' ),
					array( 'name' => 'The Cyclades beyond', 'blurb' => 'Naxos, Paros, Milos, Sifnos: beaches, whitewashed villages and tavernas with a table free. Where I send people the second time.' ),
					array( 'name' => 'Crete', 'blurb' => 'A country of its own: Chania’s harbour, the gorges, the south-coast beaches, a villa in the olive groves. A week on its own.' ),
					array( 'name' => 'The Peloponnese', 'blurb' => 'Nafplio, Epidaurus, Mycenae, Monemvasia and the Mani, by driver from Athens. The mainland most visitors miss.' ),
					array( 'name' => 'The Ionian', 'blurb' => 'Corfu, Paxos, Kefalonia: greener, Venetian, calmer seas. A villa with a boat at the jetty.' ),
				),
				'sample_itinerary' => array(
					array( 'day' => '1', 'title' => 'Athens', 'text' => 'Land, a driver waiting, a hotel in Plaka or Koukaki with a rooftop. A walk under the Acropolis at dusk, dinner outdoors.' ),
					array( 'day' => '2', 'title' => 'Athens', 'text' => 'A guide for the Acropolis at opening and the museum after, the afternoon free, the rooftop for the evening.' ),
					array( 'day' => '3', 'title' => 'Fly to Santorini', 'text' => 'Forty minutes, a driver waiting, a cave hotel on the caldera in Imerovigli or Oia for three nights.' ),
					array( 'day' => '4', 'title' => 'Santorini', 'text' => 'A morning walk on the caldera path, a winery in the afternoon, the sunset from your own terrace rather than the crowd’s.' ),
					array( 'day' => '5', 'title' => 'The boat day', 'text' => 'A private catamaran around the caldera, a swim off the volcanic beaches, lunch on board.' ),
					array( 'day' => '6', 'title' => 'Ferry to Naxos', 'text' => 'Two hours by fast ferry, a hotel on the beach or in the old town for three nights.' ),
					array( 'day' => '7', 'title' => 'Naxos', 'text' => 'A driver into the mountain villages, lunch in Halki, a swim at Plaka beach on the way back.' ),
					array( 'day' => '8', 'title' => 'A day with no plan', 'text' => 'The beach, a taverna at lunch, nothing else. The one day every good Greece trip needs.' ),
					array( 'day' => '9', 'title' => 'Back to Athens', 'text' => 'A short flight or the ferry, then a last night by the sea on the Athens Riviera.' ),
					array( 'day' => '10', 'title' => 'Home from Athens', 'text' => 'Thirty minutes to the airport with time in hand. Ten days, one way to do it; Crete or the Peloponnese make another.' ),
				),
				'stays'       => array(
					array( 'name' => 'A cave hotel on the Santorini caldera', 'type' => 'hotel', 'note' => 'Imerovigli rather than Oia for the quiet, with the same view. A private plunge pool if it matters to you.', 'perks' => '' ),
					array( 'name' => 'A villa with a boat, in the Ionian or on Crete', 'type' => 'villa', 'note' => 'Stone, olive trees, a pool and a skipper on call. The one for a family week.', 'perks' => '' ),
					array( 'name' => 'A beach hotel on a quieter Cycladic island', 'type' => 'hotel', 'note' => 'Naxos, Paros or Milos: whitewashed rooms, a taverna on the sand, and a table free at eight.', 'perks' => '' ),
					array( 'name' => 'A neoclassical hotel in Athens or Nafplio', 'type' => 'hotel', 'note' => 'High ceilings, a rooftop, and the old town at the door.', 'perks' => '' ),
				),
				'best_months' => array( 5, 6, 9, 10 ),
				'faq'         => array(
					$fee,
					array( 'question' => 'How many islands?', 'answer' => 'Two, for ten days. Each move costs most of a day, and the point of an island is the day you don’t move. Athens plus two islands is the shape that works; a third means a longer trip, not a faster one.' ),
					array( 'question' => 'How far ahead should I start?', 'answer' => 'Six to nine months. The caldera hotels fill by winter for June and September, the ferry timetables publish in spring and the good sailings sell out in high season, and the villas with a boat are held by returning families.' ),
					array( 'question' => 'Ferry or fly?', 'answer' => 'Fly from Athens to Santorini, Mykonos or Crete; take the fast ferry between neighbouring islands. I book both, and on a windy day I hold a plan B, because the ferries answer to the weather and the flights mostly don’t.' ),
					array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. Athens has direct flights from the east coast in season; otherwise one connection in Europe, and I’ll tell you when it’s worth using miles and when it isn’t.' ),
					array( 'question' => 'Can you plan this for someone who walks slowly?', 'answer' => 'Yes. Santorini’s steps are the thing to design around: a hotel with level access rather than a hundred stairs down the cliff, a driver who waits, and a second island chosen for flat harbours and beaches. Naxos and Crete are easier than they look.' ),
				),
			),

			'croatia' => array(
				'headline'    => 'Croatia, planned along the coast and out to the islands.',
				'intro'       => '<p>The Dalmatian coast is one road, a string of walled towns and a scatter of islands close enough to see from the shore. The trip that works runs one direction along it, Split to Dubrovnik or the reverse, with a few nights on an island in between and the car and the boat sharing the driving.</p>'
					. '<p>I plan Croatia so the walled towns are seen early, before the day visitors, and the islands are given real nights, not a lunch stop. A driver on the coast road, a skipper for the day, a guide in Split’s palace and on Dubrovnik’s walls, a stone house or a small hotel on Hvar, Korčula or Vis, and the ferry timetable checked against the season. Slovenia, Montenegro and the Bay of Kotor fit at either end.</p>'
					. '<p>It suits couples who want a coast without the Italian prices, families who want a boat and a pool, sailors who would rather have a crewed boat than a bareboat, and anyone who has done Italy and Greece and wants what comes next. If you want Zagreb, Plitvice, Split, Hvar and Dubrovnik in six days, I plan slower trips than that.</p>',
				'regions'     => array(
					array( 'name' => 'Dubrovnik', 'blurb' => 'The walls at eight in the morning with a guide, then out: Cavtat, Lokrum, the Elaphiti islands by boat. Two or three nights in the old town or on Ploče.' ),
					array( 'name' => 'Split & the central coast', 'blurb' => 'Diocletian’s palace as a living town, Trogir, and the ferries to the islands. A good first or last stop.' ),
					array( 'name' => 'Hvar, Vis & Korčula', 'blurb' => 'Hvar for the harbour and the lavender hills, Vis for the quiet and the Blue Cave, Korčula for the walled town and the wine. Two or three nights each.' ),
					array( 'name' => 'Istria', 'blurb' => 'Truffles, hill towns and Venetian harbours in the north: Rovinj, Motovun, a farmhouse stay. Italy across the water.' ),
					array( 'name' => 'Plitvice & the interior', 'blurb' => 'The lakes and their walkways, early, between the coast and Zagreb. One night nearby does it.' ),
					array( 'name' => 'Montenegro & the Bay of Kotor', 'blurb' => 'An hour and a half south of Dubrovnik: the mountain-walled bay, Kotor’s ramparts, Perast. A day, or two nights.' ),
				),
				'sample_itinerary' => array(
					array( 'day' => '1', 'title' => 'Split', 'text' => 'Land, a driver waiting, a hotel inside or beside Diocletian’s palace. A walk on the Riva, dinner in a courtyard.' ),
					array( 'day' => '2', 'title' => 'Split & Trogir', 'text' => 'A guide for the palace in the morning, Trogir by driver in the afternoon, back for the evening.' ),
					array( 'day' => '3', 'title' => 'Ferry to Hvar', 'text' => 'An hour by catamaran, then a hotel in Hvar town or a stone house in Stari Grad for three nights.' ),
					array( 'day' => '4', 'title' => 'Hvar', 'text' => 'A morning on the lavender roads by driver, a swim at a cove the skipper suggests, dinner in the harbour.' ),
					array( 'day' => '5', 'title' => 'The boat day', 'text' => 'A private boat to the Pakleni islands, or across to Vis and the Blue Cave, lunch in a konoba on the water.' ),
					array( 'day' => '6', 'title' => 'Ferry to Korčula', 'text' => 'The catamaran down the coast, a hotel in the walled town for two nights, a tasting at Lumbarda before dinner.' ),
					array( 'day' => '7', 'title' => 'A day with no plan', 'text' => 'A swim, a walk on the walls, a long lunch. The one day every good Croatia trip needs.' ),
					array( 'day' => '8', 'title' => 'To Dubrovnik', 'text' => 'The catamaran, or a driver down the Pelješac peninsula with an oyster lunch at Ston, then a hotel on Ploče for two nights.' ),
					array( 'day' => '9', 'title' => 'Dubrovnik', 'text' => 'The walls at opening with a guide, then a boat to Lokrum or an afternoon in Cavtat, away from the crowds.' ),
					array( 'day' => '10', 'title' => 'Home from Dubrovnik', 'text' => 'Twenty minutes to the airport with time in hand. Ten days, one way to do it; Istria and Montenegro make another.' ),
				),
				'stays'       => array(
					array( 'name' => 'A hotel in Dubrovnik’s old town or on Ploče', 'type' => 'hotel', 'note' => 'Old-town rooms for the walls at your door; Ploče for the sea view and a pool, ten minutes’ walk away.', 'perks' => '' ),
					array( 'name' => 'A stone house on Hvar, Vis or Korčula', 'type' => 'villa', 'note' => 'Old walls, a pool and a boat at the jetty. The one for a family week.', 'perks' => '' ),
					array( 'name' => 'A small hotel inside Split’s palace', 'type' => 'hotel', 'note' => 'A dozen rooms inside Roman walls, breakfast in a courtyard, the ferries a short walk away.', 'perks' => '' ),
					array( 'name' => 'An Istrian farmhouse among the vines', 'type' => 'villa', 'note' => 'Stone, a pool, truffle country at the door and Rovinj half an hour away.', 'perks' => '' ),
				),
				'best_months' => array( 5, 6, 9, 10 ),
				'faq'         => array(
					$fee,
					array( 'question' => 'Split to Dubrovnik, or the reverse?', 'answer' => 'Either. Fly into one and home from the other and the coast runs in one direction without backtracking. I usually finish in Dubrovnik so the walls are the last thing you see, and I put the calmest island nights in the middle.' ),
					array( 'question' => 'How far ahead should I start?', 'answer' => 'Six to nine months for May, June, September and October. The island hotels are small, the stone houses with a pool are held by returning families, and the catamarans between islands sell out in July and August.' ),
					array( 'question' => 'Should we sail instead?', 'answer' => 'If you want the islands without the ferries, a crewed boat for a week does the whole coast with your luggage unpacked once. Bareboat only if you already sail. I can plan either, or a week ashore and a few days afloat.' ),
					array( 'question' => 'Do you book flights?', 'answer' => 'I advise on routing and timing and coordinate flights with the rest of the trip. Split and Dubrovnik connect through Frankfurt, Munich, Zurich or Vienna, with direct flights from the east coast in summer; I’ll tell you when it’s worth using miles and when it isn’t.' ),
					array( 'question' => 'Can you plan this for someone who walks slowly?', 'answer' => 'Yes. The walled towns are steps and cobbles and Dubrovnik’s walls are a climb, so I choose hotels with lifts, a driver rather than a bus, a boat rather than a hill, and the islands with flat harbours.' ),
				),
			),
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
