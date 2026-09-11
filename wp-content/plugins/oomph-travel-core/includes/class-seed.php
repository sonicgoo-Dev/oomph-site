<?php
/**
 * Seed the launch records: the eleven destinations (D07 + D33) and the seven
 * operators (D25). Run from WP-CLI: `wp oomph seed destinations`.
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
	 * Seed one set. Returns one row per record: slug, title, action, id.
	 *
	 * @param string $what    'destinations' or 'operators'.
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
			default:
				throw new \InvalidArgumentException( 'Unknown seed set: ' . $what );
		}

		$rows  = array();
		$order = 0;
		foreach ( $records as $record ) {
			++$order;
			$existing = get_page_by_path( $record['slug'], OBJECT, $post_type );
			if ( $existing instanceof \WP_Post ) {
				$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => 'exists (' . $existing->post_status . ')', 'id' => (int) $existing->ID );
				continue;
			}
			if ( $dry_run ) {
				$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => 'would create', 'id' => 0 );
				continue;
			}

			$id = 'destinations' === $what
				? self::create_destination( $record, $order )
				: self::create_operator( $record, $order );

			$rows[] = array( 'slug' => $record['slug'], 'title' => $record['title'], 'action' => $id ? 'created (draft)' : 'failed', 'id' => $id );
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
		return $id;
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
}
