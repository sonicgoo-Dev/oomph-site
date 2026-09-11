<?php
/**
 * Start planning (plan §6.15, D14, D30): the page at /start-planning/ and
 * its receipt at /start-planning/received/.
 *
 * The plugin owns the enquiry (OomphTravel\Core\Inquiry: the POST, the
 * private Inquiry record, the emails, the receipt token). This file owns
 * what the visitor sees: the state the form renders from (a pre-fill from
 * a destination, tour or operator page, or the values they typed when a
 * post came back with errors), the small render helpers the pattern uses,
 * the stylesheet and script, and the Calendly widget on the receipt only.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

function oomphtravel_is_start_planning(): bool {
	return is_page( 'start-planning' );
}

/**
 * Whether the plugin's enquiry handler is present. Without it the pattern
 * renders a plain contact line rather than a form that posts nowhere.
 */
function oomphtravel_plan_ready(): bool {
	return class_exists( '\OomphTravel\Core\Inquiry' );
}

/**
 * Everything the form renders from, in one array.
 *
 * Precedence: what the visitor typed (a post that came back with errors)
 * beats the pre-fill from the query string (`?type=`, `?destination=`,
 * `?tour=`, `?operator=`), which beats empty. Ids are only kept for
 * published records, so a stale link pre-fills nothing rather than
 * something wrong.
 *
 * @return array<string,mixed>
 */
function oomphtravel_plan_state(): array {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}

	$get = static function ( string $key ): string {
		return isset( $_GET[ $key ] ) ? sanitize_key( wp_unslash( (string) $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a pre-fill, read only.
	};

	$state = array(
		'trip_type'      => '',
		'destinations'   => array(),
		'when'           => '',
		'travelers'      => '',
		'budget'         => '',
		'notes'          => '',
		'name'           => '',
		'contact_method' => '',
		'contact_value'  => '',
		'consent'        => false,
		'newsletter'     => false,
		'tour_id'        => 0,
		'destination_id' => 0,
		'operator_id'    => 0,
		'source'         => '',
		'about'          => '',
		'about_url'      => '',
	);

	// 1. The query string.
	$types = array(
		'custom'      => 'custom',
		'escorted'    => 'escorted',
		'resort'      => 'resort',
		'multi-gen'   => 'multigen',
		'multigen'    => 'multigen',
		'cruise-land' => 'cruise-land',
	);
	$state['trip_type'] = $types[ $get( 'type' ) ] ?? '';

	$tour = '' !== $get( 'tour' ) ? get_page_by_path( $get( 'tour' ), OBJECT, 'oomph_tour' ) : null;
	if ( $tour instanceof WP_Post && 'publish' === $tour->post_status ) {
		$operator               = class_exists( '\OomphTravel\Core\CPT_Tour' ) ? \OomphTravel\Core\CPT_Tour::operator_id( (int) $tour->ID ) : 0;
		$state['tour_id']       = (int) $tour->ID;
		$state['trip_type']     = '' !== $state['trip_type'] ? $state['trip_type'] : 'escorted';
		$state['about']         = get_the_title( $tour ) . ( $operator ? ' ' . __( 'with', 'oomphtravel' ) . ' ' . get_the_title( $operator ) : '' );
		$state['about_url']     = (string) get_permalink( $tour );
		$places                 = get_the_terms( $tour, 'oomph_place' );
		$state['destinations']  = is_array( $places ) ? wp_list_pluck( $places, 'slug' ) : array();
	}

	$operator = '' !== $get( 'operator' ) ? get_page_by_path( $get( 'operator' ), OBJECT, 'oomph_operator' ) : null;
	if ( ! $state['tour_id'] && $operator instanceof WP_Post && 'publish' === $operator->post_status ) {
		$kind                  = class_exists( '\OomphTravel\Core\CPT_Operator' ) ? \OomphTravel\Core\CPT_Operator::kind( (int) $operator->ID ) : '';
		$state['operator_id']  = (int) $operator->ID;
		$state['trip_type']    = '' !== $state['trip_type'] ? $state['trip_type'] : ( 'fit' === $kind ? 'custom' : 'escorted' );
		$state['about']        = (string) get_the_title( $operator );
		$state['about_url']    = (string) get_permalink( $operator );
	}

	$destination = '' !== $get( 'destination' ) ? get_page_by_path( $get( 'destination' ), OBJECT, 'oomph_destination' ) : null;
	if ( $destination instanceof WP_Post && 'publish' === $destination->post_status ) {
		$state['destination_id'] = (int) $destination->ID;
		$state['destinations']   = array_values( array_unique( array_merge( $state['destinations'], array( $destination->post_name ) ) ) );
		if ( '' === $state['trip_type'] && 'resort' === (string) get_post_meta( $destination->ID, 'variant', true ) ) {
			$state['trip_type'] = 'resort';
		}
		if ( '' === $state['about'] ) {
			$state['about']     = (string) get_the_title( $destination );
			$state['about_url'] = (string) get_permalink( $destination );
		}
	}

	// 2. A post that came back with errors: what they typed wins.
	$values = oomphtravel_plan_ready() ? \OomphTravel\Core\Inquiry::values() : array();
	if ( $values ) {
		$text = static fn( string $k ): string => isset( $values[ $k ] ) && is_scalar( $values[ $k ] ) ? sanitize_text_field( (string) $values[ $k ] ) : '';
		$state['trip_type']      = sanitize_key( $text( 'trip_type' ) );
		$state['destinations']   = array_map( 'sanitize_key', array_filter( (array) ( $values['destinations'] ?? array() ), 'is_scalar' ) );
		$state['when']           = sanitize_key( $text( 'when' ) );
		$state['travelers']      = $text( 'travelers' );
		$state['budget']         = sanitize_key( $text( 'budget' ) );
		$state['notes']          = isset( $values['notes'] ) && is_scalar( $values['notes'] ) ? sanitize_textarea_field( (string) $values['notes'] ) : '';
		$state['name']           = $text( 'name' );
		$state['contact_method'] = sanitize_key( $text( 'contact_method' ) );
		$state['contact_value']  = $text( 'contact_value' );
		$state['consent']        = '' !== $text( 'consent' );
		$state['newsletter']     = '' !== $text( 'newsletter_opt_in' );
		$state['tour_id']        = (int) $text( 'tour' );
		$state['destination_id'] = (int) $text( 'destination' );
		$state['operator_id']    = (int) $text( 'operator' );
		$state['source']         = esc_url_raw( $text( 'source' ) );
	}

	return $state;
}

/**
 * The error for a field from the plugin, as a paragraph, or ''.
 */
function oomphtravel_plan_error( string $field ): string {
	if ( ! oomphtravel_plan_ready() ) {
		return '';
	}
	$errors = \OomphTravel\Core\Inquiry::errors();
	if ( empty( $errors[ $field ] ) ) {
		return '';
	}
	return sprintf( '<p class="ot-plan__error" id="ot-plan-error-%s" data-ot-error="%1$s">%s</p>', esc_attr( $field ), esc_html( (string) $errors[ $field ] ) );
}

function oomphtravel_plan_has_error( string $field ): bool {
	return oomphtravel_plan_ready() && ! empty( \OomphTravel\Core\Inquiry::errors()[ $field ] );
}

/**
 * A group of radio cards or chips.
 *
 * @param string               $name     Field name (a checkbox group gets [] appended).
 * @param array<string,string> $choices  value => label.
 * @param string|string[]      $selected The checked value(s).
 * @param string               $kind     'card' (radio), 'chip' (checkbox) or 'pill' (radio).
 */
function oomphtravel_plan_choices( string $name, array $choices, $selected, string $kind = 'card' ): string {
	$type     = 'chip' === $kind ? 'checkbox' : 'radio';
	$selected = array_map( 'strval', (array) $selected );
	$out      = '';
	foreach ( $choices as $value => $label ) {
		$id   = 'ot-plan-' . sanitize_key( $name ) . '-' . sanitize_key( (string) $value );
		$out .= sprintf(
			'<label class="ot-plan__%1$s" for="%2$s"><input class="ot-plan__%1$s-input" type="%3$s" id="%2$s" name="%4$s" value="%5$s"%6$s><span class="ot-plan__%1$s-text">%7$s</span></label>',
			esc_attr( $kind ),
			esc_attr( $id ),
			esc_attr( $type ),
			esc_attr( 'checkbox' === $type ? $name . '[]' : $name ),
			esc_attr( (string) $value ),
			in_array( (string) $value, $selected, true ) ? ' checked' : '',
			esc_html( $label )
		);
	}
	return $out;
}

/**
 * The Calendly link, with the traveler's first name pre-filled and
 * Calendly's own cookie banner off (the site has its own notice).
 */
function oomphtravel_plan_calendly_url( string $first_name = '' ): string {
	$url = (string) apply_filters( 'oomph_calendly_url', '' );
	if ( '' === $url ) {
		return '';
	}
	$args = array( 'hide_gdpr_banner' => '1' );
	if ( '' !== $first_name ) {
		$args['name'] = $first_name;
	}
	return add_query_arg( array_map( 'rawurlencode', $args ), $url );
}

/* ------------------------------------------------------------------ */
/* Assets                                                               */
/* ------------------------------------------------------------------ */

/**
 * The page's stylesheet and script; Calendly's widget on the receipt only
 * (D14: inline, never a modal; R65: one third-party script, on one page).
 */
function oomphtravel_enqueue_start_planning_assets(): void {
	if ( ! oomphtravel_is_start_planning() ) {
		return;
	}
	wp_enqueue_style( 'oomphtravel-plan', OOMPHTRAVEL_THEME_URI . 'assets/css/start-planning.css', array( 'oomphtravel-components' ), OOMPHTRAVEL_THEME_VERSION );
	wp_enqueue_script( 'oomphtravel-plan', OOMPHTRAVEL_THEME_URI . 'assets/js/start-planning.js', array(), OOMPHTRAVEL_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	if ( oomphtravel_plan_ready() && \OomphTravel\Core\Inquiry::is_receipt() && '' !== oomphtravel_plan_calendly_url() ) {
		wp_enqueue_script( 'calendly-widget', 'https://assets.calendly.com/assets/external/widget.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- third-party, unversioned by design.
	}
}
add_action( 'wp_enqueue_scripts', 'oomphtravel_enqueue_start_planning_assets', 20 );

/**
 * The receipt is personal and short-lived: never cached, never indexed.
 */
function oomphtravel_plan_receipt_headers(): void {
	if ( oomphtravel_is_start_planning() && oomphtravel_plan_ready() && \OomphTravel\Core\Inquiry::is_receipt() ) {
		nocache_headers();
	}
}
add_action( 'template_redirect', 'oomphtravel_plan_receipt_headers', 5 );

function oomphtravel_plan_robots( array $robots ): array {
	if ( oomphtravel_is_start_planning() && oomphtravel_plan_ready() && \OomphTravel\Core\Inquiry::is_receipt() ) {
		$robots['noindex'] = true;
		$robots['nofollow'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'oomphtravel_plan_robots' );

/**
 * Rank Math's description, until Eric writes his own in the page's SEO box.
 *
 * @param string $description
 */
function oomphtravel_plan_seo_description( $description ) {
	if ( ! oomphtravel_is_start_planning() || ( is_string( $description ) && '' !== trim( $description ) ) ) {
		return $description;
	}
	return __( 'Tell me what you are imagining: the kind of trip, where, when and who is going. Two short steps, a reply within one business day, and no planning fee.', 'oomphtravel' );
}
add_filter( 'rank_math/frontend/description', 'oomphtravel_plan_seo_description' );
