<?php
/**
 * Start planning — the enquiry behind /start-planning/ (plan §6.15, D14, D30).
 *
 * A sales enquiry, not a list signup: it never goes to PlainSend. The form
 * in the theme posts here, this validates it, keeps a private Inquiry post
 * (class-cpt-inquiry.php), emails Hello@oomphtravel.com a summary, sends
 * the traveler a short confirmation when they chose email, and hands the
 * browser to the receipt at /start-planning/received/. Same shape as
 * CruiseOomph's "Plan with Eric".
 *
 * The POST is a plain form submission handled at template_redirect, so the
 * form works with JavaScript off; the theme's script is an enhancement
 * (one step at a time, a fresh nonce for a cached page, analytics). The one
 * REST route is GET /oomph/v1/nonce, for that refresh.
 *
 * "A cruise" is never stored: choosing it sends the visitor to CruiseOomph's
 * own planning form with the UTM tag, whether or not the script ran.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

use WP_Error;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Inquiry {

	/** Every enquiry is sent here (plan §6.15). Filterable, never a public setting. */
	public const RECIPIENT = 'Hello@oomphtravel.com';

	/** The page the form lives on. */
	public const PAGE_SLUG = 'start-planning';

	public const NONCE_ACTION = 'oomph_inquiry';
	public const NONCE_FIELD  = 'oomph_inquiry_nonce';
	public const HONEYPOT     = 'website';

	/** Query var that marks the receipt URL, /start-planning/received/. */
	public const QUERY_VAR = 'oomph_received';

	/** The trip-type key that is not stored but handed to CruiseOomph. */
	public const CRUISE = 'cruise';

	/** Rate limit: posts per window, per hashed IP. */
	private const RATE_LIMIT  = 6;
	private const RATE_WINDOW = 10 * MINUTE_IN_SECONDS;

	/** How long the receipt can be reloaded after sending. */
	private const RECEIPT_TTL = 30 * MINUTE_IN_SECONDS;

	/** How many months ahead the "when" list runs. */
	private const MONTHS_AHEAD = 18;

	/**
	 * Budget for the whole trip, before flights: a four-choice grid with no
	 * qualification language (plan §6.15). Keys are what the form posts.
	 *
	 * @var array<string,string>
	 */
	public const BUDGETS = array(
		'to-10k'  => 'Up to $10,000',
		'10k-20k' => '$10,000 to $20,000',
		'20k-40k' => '$20,000 to $40,000',
		'guide'   => 'Guide me',
	);

	/**
	 * Preferred contact methods: the keys match the Inquiry field's choices.
	 *
	 * @var array<string,string>
	 */
	public const CONTACT_METHODS = array(
		'email' => 'Email',
		'phone' => 'Phone call',
		'text'  => 'Text message',
	);

	/** The consent the traveler gives: about this request only, never marketing. */
	public const CONSENT_TEXT = 'Eric may contact me about this trip. This is not marketing consent.';

	/** @var array<string,string> Field errors from the current request, for the theme. */
	private static array $errors = array();

	/** @var array<string,mixed> Submitted values from the current request, for the theme. */
	private static array $values = array();

	public static function init(): void {
		add_action( 'init', array( self::class, 'rewrite' ) );
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_filter( 'redirect_canonical', array( self::class, 'keep_receipt_url' ), 10, 2 );
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
		add_action( 'template_redirect', array( self::class, 'handle_post' ), 1 );
		// /discovery-call/ → /start-planning/ lives in Redirects::moved() (Stage 11).
		add_filter( 'allowed_redirect_hosts', array( self::class, 'allow_cruiseoomph' ) );
	}

	/**
	 * The one external host a safe redirect may send a visitor to: the
	 * cruise handoff. Everything else stays on this site.
	 *
	 * @param string[] $hosts
	 * @return string[]
	 */
	public static function allow_cruiseoomph( array $hosts ): array {
		$host = wp_parse_url( self::cruiseoomph_plan_url(), PHP_URL_HOST );
		if ( is_string( $host ) && '' !== $host && ! in_array( $host, $hosts, true ) ) {
			$hosts[] = $host;
		}
		return $hosts;
	}

	/* ------------------------------------------------------------------ */
	/* URLs                                                                */
	/* ------------------------------------------------------------------ */

	/** /start-planning/received/ renders the Start planning page in receipt mode. */
	public static function rewrite(): void {
		add_rewrite_rule(
			'^' . self::PAGE_SLUG . '/received/?$',
			'index.php?pagename=' . self::PAGE_SLUG . '&' . self::QUERY_VAR . '=1',
			'top'
		);
	}

	/** @param string[] $vars */
	public static function query_vars( array $vars ): array {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * The canonical redirect must not fold /received/ back onto the page.
	 *
	 * @param string|false $redirect
	 * @return string|false
	 */
	public static function keep_receipt_url( $redirect, string $requested ) {
		return self::is_receipt() ? false : $redirect;
	}

	public static function form_url(): string {
		return home_url( '/' . self::PAGE_SLUG . '/' );
	}

	public static function receipt_url( string $token = '' ): string {
		$url = home_url( '/' . self::PAGE_SLUG . '/received/' );
		return '' === $token ? $url : add_query_arg( 'r', rawurlencode( $token ), $url );
	}

	/** Whether the current request is the receipt. */
	public static function is_receipt(): bool {
		return '' !== (string) get_query_var( self::QUERY_VAR );
	}

	/**
	 * CruiseOomph's planning form, tagged. The theme's helper builds the UTM
	 * set every other hand-off uses; this falls back to the same values.
	 */
	public static function cruiseoomph_plan_url(): string {
		if ( function_exists( 'oomphtravel_cruiseoomph_url' ) ) {
			return oomphtravel_cruiseoomph_url( '/plan/', self::PAGE_SLUG );
		}
		return add_query_arg(
			array(
				'utm_source'   => 'oomphtravel',
				'utm_medium'   => 'site',
				'utm_campaign' => self::PAGE_SLUG,
			),
			'https://cruiseoomph.com/plan/'
		);
	}

	/* ------------------------------------------------------------------ */
	/* Choices the theme renders                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * The step 1 radio cards, in the plan's order: the stored types, with
	 * "A cruise" between them where the plan puts it.
	 *
	 * @return array<string,string>
	 */
	public static function trip_types(): array {
		$out = array();
		foreach ( CPT_Inquiry::TRIP_TYPES as $key => $label ) {
			if ( 'cruise-land' === $key ) {
				$out[ self::CRUISE ] = __( 'A cruise', 'oomph-travel-core' );
			}
			$out[ $key ] = $label;
		}
		return $out;
	}

	/**
	 * When: flexible, then the next eighteen months, then "not sure".
	 *
	 * @return array<string,string> key => label.
	 */
	public static function when_options(): array {
		$out = array( 'flexible' => __( 'Flexible', 'oomph-travel-core' ) );
		$ts  = strtotime( gmdate( 'Y-m-01' ) );
		for ( $i = 1; $i <= self::MONTHS_AHEAD; $i++ ) {
			$month         = strtotime( "+{$i} month", $ts );
			$out[ gmdate( 'Y-m', $month ) ] = gmdate( 'F Y', $month );
		}
		$out['not-sure'] = __( 'Not sure yet', 'oomph-travel-core' );
		return $out;
	}

	/**
	 * Destination chips: every published destination page, in menu order.
	 *
	 * @return array<string,array{id:int,title:string}> keyed by slug.
	 */
	public static function destinations(): array {
		$posts = get_posts(
			array(
				'post_type'      => CPT_Destination::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 30,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		$out = array();
		foreach ( $posts as $post ) {
			$out[ $post->post_name ] = array(
				'id'    => (int) $post->ID,
				'title' => (string) get_the_title( $post ),
			);
		}
		return $out;
	}

	public static function nonce(): string {
		return wp_create_nonce( self::NONCE_ACTION );
	}

	/** @return array<string,string> */
	public static function errors(): array {
		return self::$errors;
	}

	/** @return array<string,mixed> */
	public static function values(): array {
		return self::$values;
	}

	/* ------------------------------------------------------------------ */
	/* REST: a fresh nonce for a page served from cache                    */
	/* ------------------------------------------------------------------ */

	public static function routes(): void {
		register_rest_route(
			'oomph/v1',
			'/nonce',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'rest_nonce' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function rest_nonce(): WP_REST_Response {
		$response = new WP_REST_Response( array( 'nonce' => self::nonce() ) );
		$response->header( 'Cache-Control', 'no-store, max-age=0' );
		return $response;
	}

	/* ------------------------------------------------------------------ */
	/* The POST                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * A post to /start-planning/ from the form. Valid: redirect to the
	 * receipt (or to CruiseOomph). Invalid: fall through and let the page
	 * render with the errors and the values the visitor typed.
	 */
	public static function handle_post(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! is_page( self::PAGE_SLUG ) || ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return;
		}
		nocache_headers();

		$input  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		$result = self::submit( is_array( $input ) ? $input : array() );

		if ( is_wp_error( $result ) ) {
			$data          = (array) $result->get_error_data();
			self::$errors  = (array) ( $data['fields'] ?? array( '_form' => $result->get_error_message() ) );
			self::$values  = is_array( $input ) ? $input : array();
			status_header( (int) ( $data['status'] ?? 400 ) );
			return;
		}

		wp_safe_redirect( $result['redirect'], 303 );
		exit;
	}

	/**
	 * Guard, validate, store, notify. Returns where to send the browser.
	 *
	 * @param array<string,mixed> $in Raw fields.
	 * @return array{redirect:string,id?:int}|WP_Error
	 */
	public static function submit( array $in ) {
		$guard = self::guard( $in );
		if ( is_wp_error( $guard ) ) {
			return $guard;
		}

		$type = strtolower( self::text( $in['trip_type'] ?? '', 20 ) );
		if ( self::CRUISE === $type ) {
			return array( 'redirect' => self::cruiseoomph_plan_url() );
		}

		$data = self::validate( $in );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$post_id = self::store( $data );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, '_oomph_email_sent', self::notify( $post_id, $data ) ? '1' : '0' );
		update_post_meta( $post_id, '_oomph_confirmation_sent', self::confirm( $post_id, $data ) ? '1' : '0' );

		$token = wp_generate_password( 24, false, false );
		set_transient(
			'oomph_receipt_' . $token,
			array(
				'first_name'   => self::first_name( $data['name'] ),
				'method'       => $data['contact_method'],
				'method_label' => self::CONTACT_METHODS[ $data['contact_method'] ],
				'reference'    => self::reference( $post_id ),
				'trip_label'   => $data['trip_label'],
			),
			self::RECEIPT_TTL
		);

		return array(
			'redirect' => self::receipt_url( $token ),
			'id'       => $post_id,
		);
	}

	/**
	 * What the receipt shows for a token, or null when it has expired. The
	 * receipt still renders without one, just without the name.
	 *
	 * @return array<string,string>|null
	 */
	public static function receipt( string $token ): ?array {
		$token = (string) preg_replace( '/[^A-Za-z0-9]/', '', $token );
		if ( '' === $token ) {
			return null;
		}
		$data = get_transient( 'oomph_receipt_' . $token );
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Nonce, honeypot, timing, rate limit. Bots get a flat refusal.
	 *
	 * @param array<string,mixed> $in
	 * @return true|WP_Error
	 */
	private static function guard( array $in ) {
		if ( ! wp_verify_nonce( (string) ( $in[ self::NONCE_FIELD ] ?? '' ), self::NONCE_ACTION ) ) {
			return new WP_Error( 'oomph_inquiry_nonce', __( 'This page has been open a while. Please refresh it and send again; what you typed is still here.', 'oomph-travel-core' ), array( 'status' => 403 ) );
		}
		if ( '' !== trim( (string) ( $in[ self::HONEYPOT ] ?? '' ) ) ) {
			return new WP_Error( 'oomph_inquiry_rejected', __( 'Request rejected.', 'oomph-travel-core' ), array( 'status' => 400 ) );
		}
		// The script records how long the form was in front of a person.
		// Empty means no script, which is allowed; a number under two
		// seconds means nobody read the form.
		$elapsed = trim( (string) ( $in['elapsed_ms'] ?? '' ) );
		if ( '' !== $elapsed && is_numeric( $elapsed ) && (float) $elapsed < 2000 ) {
			return new WP_Error( 'oomph_inquiry_rejected', __( 'Request rejected.', 'oomph-travel-core' ), array( 'status' => 400 ) );
		}
		if ( ! self::within_rate_limit() ) {
			return new WP_Error( 'oomph_inquiry_rate_limited', __( 'Too many requests from this connection. Please wait a few minutes and try again.', 'oomph-travel-core' ), array( 'status' => 429 ) );
		}
		return true;
	}

	private static function within_rate_limit(): bool {
		$key   = 'oomph_rl_' . md5( self::ip_hash() );
		$count = (int) get_transient( $key );
		if ( $count >= self::RATE_LIMIT ) {
			return false;
		}
		set_transient( $key, $count + 1, self::RATE_WINDOW );
		return true;
	}

	/** Salted hash of the client IP: enough to rate limit, never the address. */
	private static function ip_hash(): string {
		$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );
		return hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
	}

	/**
	 * Validate and normalise the submitted fields.
	 *
	 * @param array<string,mixed> $in Raw fields.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function validate( array $in ) {
		$errors = array();

		$type = strtolower( self::text( $in['trip_type'] ?? '', 20 ) );
		if ( ! array_key_exists( $type, CPT_Inquiry::TRIP_TYPES ) ) {
			$errors['trip_type'] = __( 'Choose the kind of trip, or "Not sure yet".', 'oomph-travel-core' );
		}

		$budget = strtolower( self::text( $in['budget'] ?? '', 20 ) );
		if ( ! array_key_exists( $budget, self::BUDGETS ) ) {
			$errors['budget'] = __( 'Choose a budget range, or "Guide me".', 'oomph-travel-core' );
		}

		$when_key = strtolower( self::text( $in['when'] ?? '', 20 ) );
		$whens    = self::when_options();
		if ( '' !== $when_key && ! array_key_exists( $when_key, $whens ) ) {
			$errors['when'] = __( 'Pick a month from the list, or "Flexible".', 'oomph-travel-core' );
		}

		$name = self::text( $in['full_name'] ?? '', 120 );
		if ( '' === $name ) {
			$errors['name'] = __( 'Please tell me your name.', 'oomph-travel-core' );
		}

		$method = strtolower( self::text( $in['contact_method'] ?? '', 10 ) );
		if ( ! array_key_exists( $method, self::CONTACT_METHODS ) ) {
			$errors['contact_method'] = __( 'Choose email, a phone call or a text.', 'oomph-travel-core' );
		}

		$contact = self::text( $in['contact_value'] ?? '', 120 );
		if ( 'email' === $method ) {
			$contact = sanitize_email( $contact );
			if ( ! is_email( $contact ) ) {
				$errors['contact_value'] = __( 'That email address does not look right.', 'oomph-travel-core' );
			}
		} elseif ( '' !== $method ) {
			$digits = preg_replace( '/\D+/', '', $contact ) ?? '';
			if ( strlen( $digits ) < 7 || strlen( $digits ) > 15 ) {
				$errors['contact_value'] = __( 'That number does not look right.', 'oomph-travel-core' );
			}
		}

		$consent = $in['consent'] ?? '';
		if ( ! in_array( is_scalar( $consent ) ? strtolower( (string) $consent ) : '', array( '1', 'on', 'yes', 'true' ), true ) ) {
			$errors['consent'] = __( 'Please confirm Eric may contact you about this trip.', 'oomph-travel-core' );
		}

		if ( $errors ) {
			return new WP_Error( 'oomph_inquiry_invalid', __( 'A few details need attention.', 'oomph-travel-core' ), array( 'status' => 422, 'fields' => $errors ) );
		}

		// Destinations: only slugs that are published destination pages.
		$known = self::destinations();
		$slugs = array();
		foreach ( (array) ( $in['destinations'] ?? array() ) as $slug ) {
			$slug = sanitize_key( (string) $slug );
			if ( isset( $known[ $slug ] ) ) {
				$slugs[] = $slug;
			}
		}
		$slugs = array_values( array_unique( $slugs ) );

		$newsletter = $in['newsletter_opt_in'] ?? '';
		$newsletter = in_array( is_scalar( $newsletter ) ? strtolower( (string) $newsletter ) : '', array( '1', 'on', 'yes', 'true' ), true );

		return array(
			'trip_type'          => $type,
			'trip_label'         => CPT_Inquiry::trip_type_label( $type ),
			'destinations'       => $slugs,
			'destination_labels' => array_map( static fn( string $s ): string => $known[ $s ]['title'], $slugs ),
			'when'               => '' === $when_key ? '' : $whens[ $when_key ],
			'travelers'          => self::text( $in['travelers'] ?? '', 160 ),
			'budget'             => $budget,
			'budget_label'       => self::BUDGETS[ $budget ],
			'notes'              => self::text( $in['notes'] ?? '', 3000, true ),
			'name'               => $name,
			'contact_method'     => $method,
			'contact_value'      => $contact,
			'newsletter_opt_in'  => $newsletter,
			'tour_id'            => self::published_id( (int) ( $in['tour'] ?? 0 ), CPT_Tour::POST_TYPE ),
			'destination_id'     => self::published_id( (int) ( $in['destination'] ?? 0 ), CPT_Destination::POST_TYPE ),
			'operator_id'        => self::published_id( (int) ( $in['operator'] ?? 0 ), CPT_Operator::POST_TYPE ),
			'source_page'        => self::internal_url( (string) ( $in['source'] ?? '' ) ),
		);
	}

	/**
	 * Store the record the way the fields plugin would, so Eric reads it in
	 * the Inquiry screen (acf-json/group_oomph_inquiry.json).
	 *
	 * @param array<string,mixed> $d Clean fields.
	 * @return int|WP_Error
	 */
	private static function store( array $d ) {
		$title = implode(
			' — ',
			array_filter(
				array(
					$d['name'],
					$d['trip_label'],
					$d['destination_labels'] ? implode( ', ', $d['destination_labels'] ) : '',
					wp_date( 'Y-m-d H:i' ),
				)
			)
		);

		$post_id = wp_insert_post(
			array(
				'post_type'    => CPT_Inquiry::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => wp_slash( $title ),
				'post_content' => '',
				'post_author'  => 0,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( 'oomph_inquiry_store', __( 'Your request could not be saved. Please try again, or email hello@oomphtravel.com.', 'oomph-travel-core' ), array( 'status' => 500 ) );
		}
		$post_id = (int) $post_id;

		$fields = array(
			'trip_type'         => array( 'field_oomph_inq_trip_type', $d['trip_type'] ),
			'when'              => array( 'field_oomph_inq_when', $d['when'] ),
			'travelers'         => array( 'field_oomph_inq_travelers', $d['travelers'] ),
			'budget'            => array( 'field_oomph_inq_budget', $d['budget_label'] ),
			'notes'             => array( 'field_oomph_inq_notes', $d['notes'] ),
			'name'              => array( 'field_oomph_inq_name', $d['name'] ),
			'contact_method'    => array( 'field_oomph_inq_contact_method', $d['contact_method'] ),
			'contact_value'     => array( 'field_oomph_inq_contact_value', $d['contact_value'] ),
			'consent'           => array( 'field_oomph_inq_consent', '1' ),
			'newsletter_opt_in' => array( 'field_oomph_inq_newsletter', $d['newsletter_opt_in'] ? '1' : '0' ),
			'source_page'       => array( 'field_oomph_inq_source_page', $d['source_page'] ),
			'tour'              => array( 'field_oomph_inq_tour', $d['tour_id'] ? (string) $d['tour_id'] : '' ),
			'destination'       => array( 'field_oomph_inq_destination', $d['destination_id'] ? (string) $d['destination_id'] : '' ),
		);
		foreach ( $fields as $name => $pair ) {
			Fields::write( $post_id, $name, $pair[0], $pair[1] );
		}
		update_post_meta( $post_id, '_oomph_operator', $d['operator_id'] );
		update_post_meta( $post_id, '_oomph_reference', self::reference( $post_id ) );
		update_post_meta( $post_id, '_oomph_consent_at', time() );
		update_post_meta( $post_id, '_oomph_consent_text', self::CONSENT_TEXT );
		update_post_meta( $post_id, '_oomph_ip_hash', self::ip_hash() );
		update_post_meta( $post_id, '_oomph_user_agent', substr( (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ), 0, 255 ) );

		if ( $d['destinations'] && taxonomy_exists( Taxonomies::DESTINATION ) ) {
			wp_set_object_terms( $post_id, $d['destinations'], Taxonomies::DESTINATION );
		}

		return $post_id;
	}

	/**
	 * The summary Eric gets.
	 *
	 * @param array<string,mixed> $d Clean fields.
	 */
	private static function notify( int $post_id, array $d ): bool {
		$to        = (string) apply_filters( 'oomph_inquiry_recipient', self::RECIPIENT );
		$reference = self::reference( $post_id );
		$about     = self::about( $d );

		$subject = sprintf(
			/* translators: 1: traveler name, 2: trip type, 3: reference. */
			__( 'Start planning: %1$s — %2$s (%3$s)', 'oomph-travel-core' ),
			$d['name'],
			$d['trip_label'],
			$reference
		);

		$lines = array(
			sprintf( 'Reference: %s', $reference ),
			sprintf( 'Name: %s', $d['name'] ),
			sprintf( 'Preferred contact: %s — %s', self::CONTACT_METHODS[ $d['contact_method'] ], $d['contact_value'] ),
			sprintf( 'Consent: agreed %s — "%s"', wp_date( 'F j, Y g:i a' ), self::CONSENT_TEXT ),
			sprintf( 'Trip notes: %s', $d['newsletter_opt_in'] ? 'asked to be added' : 'not asked' ),
			'',
			sprintf( 'Trip type: %s', $d['trip_label'] ),
			sprintf( 'Destinations: %s', $d['destination_labels'] ? implode( ', ', $d['destination_labels'] ) : '—' ),
			sprintf( 'When: %s', '' !== $d['when'] ? $d['when'] : '—' ),
			sprintf( 'Travelers: %s', '' !== $d['travelers'] ? $d['travelers'] : '—' ),
			sprintf( 'Budget (whole trip, before flights): %s', $d['budget_label'] ),
			'',
			'Notes:',
			'' !== $d['notes'] ? $d['notes'] : '—',
			'',
			'' !== $about ? sprintf( 'Asking about: %s', $about ) : null,
			'' !== $d['source_page'] ? sprintf( 'Came from: %s', $d['source_page'] ) : null,
			sprintf( 'Record: %s', admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ),
			sprintf( 'Environment: %s', Environment::type() ),
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		if ( 'email' === $d['contact_method'] && is_email( $d['contact_value'] ) ) {
			$headers[] = 'Reply-To: ' . $d['name'] . ' <' . $d['contact_value'] . '>';
		}

		return wp_mail( $to, $subject, implode( "\n", array_filter( $lines, static fn( $l ) => null !== $l ) ), $headers );
	}

	/**
	 * A short confirmation to the traveler, only when they chose email.
	 *
	 * @param array<string,mixed> $d Clean fields.
	 */
	private static function confirm( int $post_id, array $d ): bool {
		if ( 'email' !== $d['contact_method'] || ! is_email( $d['contact_value'] ) ) {
			return false;
		}

		$reference = self::reference( $post_id );
		$about     = self::about( $d );
		$subject   = sprintf(
			/* translators: %s: reference, e.g. OT-123. */
			__( 'Your trip request is with Eric (%s)', 'oomph-travel-core' ),
			$reference
		);

		$lines = array(
			sprintf( 'Hi %s,', self::first_name( $d['name'] ) ),
			'',
			'Thank you. I have your request and the details you shared, and I will reply by email within one business day.',
			'Nothing is booked or charged; this is the start of a conversation.',
			'',
			sprintf( 'Your reference: %s', $reference ),
			sprintf( 'Trip: %s', $d['trip_label'] ),
			$d['destination_labels'] ? sprintf( 'Destinations: %s', implode( ', ', $d['destination_labels'] ) ) : null,
			'' !== $d['when'] ? sprintf( 'When: %s', $d['when'] ) : null,
			'' !== $about ? sprintf( 'Asking about: %s', $about ) : null,
			'',
			'If you would rather talk sooner, pick a time here: ' . (string) apply_filters( 'oomph_calendly_url', '' ),
			'',
			'Eric Hempel',
			'Oomph Travel · ' . self::RECIPIENT,
		);

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: Eric Hempel <' . self::RECIPIENT . '>',
		);

		return wp_mail( $d['contact_value'], $subject, implode( "\n", array_filter( $lines, static fn( $l ) => null !== $l ) ), $headers );
	}

	/* ------------------------------------------------------------------ */
	/* Small helpers                                                        */
	/* ------------------------------------------------------------------ */

	/** "OT-123": the reference the traveler and Eric both see. */
	public static function reference( int $post_id ): string {
		return 'OT-' . $post_id;
	}

	/** The first word of a name, for greetings. */
	public static function first_name( string $name ): string {
		$parts = preg_split( '/\s+/', trim( $name ) ) ?: array();
		return (string) ( $parts[0] ?? $name );
	}

	/**
	 * The tour, operator or destination page the visitor arrived from, as a line.
	 *
	 * @param array<string,mixed> $d Clean fields.
	 */
	private static function about( array $d ): string {
		if ( $d['tour_id'] ) {
			$op = CPT_Tour::operator_id( (int) $d['tour_id'] );
			return get_the_title( (int) $d['tour_id'] ) . ( $op ? ' with ' . get_the_title( $op ) : '' ) . ' (' . get_permalink( (int) $d['tour_id'] ) . ')';
		}
		if ( $d['operator_id'] ) {
			return get_the_title( (int) $d['operator_id'] ) . ' (' . get_permalink( (int) $d['operator_id'] ) . ')';
		}
		if ( $d['destination_id'] ) {
			return get_the_title( (int) $d['destination_id'] ) . ' (' . get_permalink( (int) $d['destination_id'] ) . ')';
		}
		return '';
	}

	/** The id when it is a published post of the type, else 0. */
	private static function published_id( int $id, string $type ): int {
		if ( $id <= 0 ) {
			return 0;
		}
		$post = get_post( $id );
		return ( $post && $type === $post->post_type && 'publish' === $post->post_status ) ? $id : 0;
	}

	/** A URL on this site, else ''. */
	private static function internal_url( string $url ): string {
		$url = esc_url_raw( trim( $url ) );
		if ( '' === $url ) {
			return '';
		}
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$home = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		return ( $host && $home && strtolower( (string) $host ) === strtolower( (string) $home ) ) ? substr( $url, 0, 255 ) : '';
	}

	/** Trim, strip tags, cap length. */
	private static function text( $value, int $max, bool $multiline = false ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = $multiline ? sanitize_textarea_field( (string) $value ) : sanitize_text_field( (string) $value );
		return mb_substr( $value, 0, $max );
	}
}
