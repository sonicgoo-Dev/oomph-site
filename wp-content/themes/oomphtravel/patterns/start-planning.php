<?php
/**
 * Title: Start planning
 * Slug: oomphtravel/start-planning
 * Inserter: no
 *
 * Start planning, plan §6.15 (D14, D30), rendered by
 * templates/page-start-planning.html at /start-planning/, and the receipt
 * at /start-planning/received/. Two steps in one form: the trip (type as
 * radio cards, destinations as chips, when, who, a four-choice budget with
 * no qualification language, notes), then the person (name, one contact
 * method and only its field, a consent line, an optional trip-notes tick).
 * The receipt thanks them by first name, promises a reply within one
 * business day, and offers Calendly inline for anyone who wants to talk
 * sooner.
 *
 * Without JavaScript both steps show in order and the form posts to the
 * plugin (OomphTravel\Core\Inquiry) as a plain form; the script shows one
 * step at a time. "A cruise" is never stored: the plugin redirects to
 * CruiseOomph's planning form, and the script offers the same link inline.
 *
 * The primary button of the page is "Send to Eric"; "Continue" is shown by
 * the script in step 1 only, so one is visible at a time.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

if ( ! oomphtravel_plan_ready() ) :
	?>
	<section class="ot-band ot-band--mist ot-plan-hero">
		<div class="ot-container ot-plan-hero__inner">
			<?php echo oomphtravel_eyebrow( __( 'Start planning', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-plan-hero__title"><?php esc_html_e( 'What are you imagining?', 'oomphtravel' ); ?></h1>
			<p class="ot-plan-hero__lead"><?php esc_html_e( 'The planning form is resting for a moment. Email eric@oomphtravel.com or call (360) 775-4644 and I will pick it up from there.', 'oomphtravel' ); ?></p>
		</div>
	</section>
	<?php
	return;
endif;

$ot_inquiry = '\OomphTravel\Core\Inquiry';

/* ------------------------------------------------------------------ */
/* The receipt                                                           */
/* ------------------------------------------------------------------ */

if ( $ot_inquiry::is_receipt() ) :
	$ot_receipt   = $ot_inquiry::receipt( isset( $_GET['r'] ) ? (string) wp_unslash( $_GET['r'] ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a one-time token, read only.
	$ot_first     = (string) ( $ot_receipt['first_name'] ?? '' );
	$ot_method    = (string) ( $ot_receipt['method'] ?? '' );
	$ot_reference = (string) ( $ot_receipt['reference'] ?? '' );
	$ot_calendly  = oomphtravel_plan_calendly_url( $ot_first );
	$ot_by        = array(
		'email' => __( 'I reply by email within one business day.', 'oomphtravel' ),
		'phone' => __( 'I call within one business day, during business hours Pacific time.', 'oomphtravel' ),
		'text'  => __( 'I text within one business day.', 'oomphtravel' ),
	);
	?>
	<div class="ot-plan ot-plan--received" data-ot-receipt data-ot-reference="<?php echo esc_attr( $ot_reference ); ?>">

		<section class="ot-band ot-band--mist ot-plan-hero">
			<div class="ot-container ot-plan-hero__inner">
				<?php echo oomphtravel_eyebrow( __( 'Received', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-plan-hero__title">
					<?php
					if ( '' !== $ot_first ) {
						/* translators: %s: the traveler's first name. */
						echo esc_html( sprintf( __( 'Thank you, %s.', 'oomphtravel' ), $ot_first ) );
					} else {
						esc_html_e( 'Thank you.', 'oomphtravel' );
					}
					?>
				</h1>
				<p class="ot-plan-hero__lead"><?php echo esc_html( __( 'Your request is with me.', 'oomphtravel' ) . ' ' . ( $ot_by[ $ot_method ] ?? __( 'I reply within one business day.', 'oomphtravel' ) ) . ' ' . __( 'Nothing is booked or charged; this is the start of a conversation.', 'oomphtravel' ) ); ?></p>
				<?php if ( '' !== $ot_reference ) : ?>
					<p class="ot-plan__reference"><?php echo esc_html( sprintf( /* translators: %s: reference, e.g. OT-123. */ __( 'Your reference is %s.', 'oomphtravel' ), $ot_reference ) ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="ot-band ot-plan-sooner">
			<div class="ot-container ot-plan-sooner__inner">
				<?php echo oomphtravel_section_heading( __( 'Sooner', 'oomphtravel' ), __( 'Want to talk sooner? Pick a time.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<p class="ot-plan-sooner__lead"><?php esc_html_e( 'Thirty minutes, nothing to prepare. I will have your request in front of me.', 'oomphtravel' ); ?></p>
				<?php if ( '' !== $ot_calendly ) : ?>
					<div class="ot-plan-sooner__widget calendly-inline-widget" data-url="<?php echo esc_url( $ot_calendly ); ?>" data-resize="true" data-ot-calendly></div>
					<div class="ot-plan-sooner__booked" data-ot-booked hidden>
						<p><strong><?php esc_html_e( 'Booked.', 'oomphtravel' ); ?></strong> <?php esc_html_e( 'The invite is on its way to your inbox. See you then.', 'oomphtravel' ); ?></p>
					</div>
					<noscript><p class="ot-plan-sooner__alt"><a href="<?php echo esc_url( (string) apply_filters( 'oomph_calendly_url', '' ) ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Open the calendar', 'oomphtravel' ); ?></a></p></noscript>
				<?php endif; ?>
				<p class="ot-plan-sooner__alt">
					<?php esc_html_e( 'Or reach me directly:', 'oomphtravel' ); ?>
					<a href="mailto:eric@oomphtravel.com">eric@oomphtravel.com</a>
					<span aria-hidden="true">·</span>
					<a href="tel:+13607754644">(360) 775-4644</a>
				</p>
			</div>
		</section>

	</div>
	<?php
	return;
endif;

/* ------------------------------------------------------------------ */
/* The form                                                              */
/* ------------------------------------------------------------------ */

$ot_s        = oomphtravel_plan_state();
$ot_errors   = $ot_inquiry::errors();
$ot_cruise   = $ot_inquiry::cruiseoomph_plan_url();
$ot_types    = $ot_inquiry::trip_types();
$ot_budgets  = $ot_inquiry::BUDGETS;
$ot_methods  = $ot_inquiry::CONTACT_METHODS;
$ot_whens    = $ot_inquiry::when_options();
$ot_places   = $ot_inquiry::destinations();
$ot_chips    = array();
foreach ( $ot_places as $ot_slug => $ot_place ) {
	$ot_chips[ $ot_slug ] = $ot_place['title'];
}

// The form posts back to itself, keeping the pre-fill in the address.
$ot_keep = array();
foreach ( array( 'type', 'destination', 'tour', 'operator' ) as $ot_k ) {
	if ( isset( $_GET[ $ot_k ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a pre-fill, read only.
		$ot_keep[ $ot_k ] = sanitize_key( wp_unslash( (string) $_GET[ $ot_k ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
$ot_action = add_query_arg( $ot_keep, $ot_inquiry::form_url() );

$ot_newsletter = function_exists( 'oomphtravel_signup_form' ) && class_exists( '\OomphTravel\Core\Plainsend' )
	? \OomphTravel\Core\Plainsend::endpoint( 'newsletter' )
	: '';

$ot_contact_labels = array(
	'email' => __( 'Email address', 'oomphtravel' ),
	'phone' => __( 'Phone number', 'oomphtravel' ),
	'text'  => __( 'Mobile number', 'oomphtravel' ),
);
$ot_contact_help = array(
	'email' => __( 'I reply from eric@oomphtravel.com.', 'oomphtravel' ),
	'phone' => __( 'I call from (360) 775-4644, during business hours Pacific time.', 'oomphtravel' ),
	'text'  => __( 'Texts come from (360) 775-4644.', 'oomphtravel' ),
);
$ot_method_now = $ot_s['contact_method'];
?>
<div class="ot-plan" data-ot-plan data-ot-step="1" data-ot-nonce-api="<?php echo esc_url( rest_url( 'oomph/v1/nonce' ) ); ?>" data-ot-newsletter="<?php echo esc_url( $ot_newsletter ); ?>">

	<?php /* 1. Hero: type on Mist; the title and lead change with the step. */ ?>
	<section class="ot-band ot-band--mist ot-plan-hero">
		<div class="ot-container ot-plan-hero__inner">
			<?php echo oomphtravel_eyebrow( __( 'Start planning', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-plan-hero__title" tabindex="-1" data-ot-swap data-ot-step-1="<?php esc_attr_e( 'What are you imagining?', 'oomphtravel' ); ?>" data-ot-step-2="<?php esc_attr_e( 'How should I reach you?', 'oomphtravel' ); ?>"><?php esc_html_e( 'What are you imagining?', 'oomphtravel' ); ?></h1>
			<p class="ot-plan-hero__lead" data-ot-swap data-ot-step-1="<?php esc_attr_e( 'Two short steps. Tell me what you have in mind, even roughly, and I reply within one business day. There is no planning fee.', 'oomphtravel' ); ?>" data-ot-step-2="<?php esc_attr_e( 'Your name and one way to reach you. That is all I need to start.', 'oomphtravel' ); ?>"><?php esc_html_e( 'Two short steps. Tell me what you have in mind, even roughly, and I reply within one business day. There is no planning fee.', 'oomphtravel' ); ?></p>
			<ol class="ot-plan-progress" aria-label="<?php esc_attr_e( 'Steps', 'oomphtravel' ); ?>">
				<li class="ot-plan-progress__seg" data-ot-seg="1" aria-current="step"><span class="ot-plan-progress__n">1</span> <?php esc_html_e( 'The trip', 'oomphtravel' ); ?></li>
				<li class="ot-plan-progress__seg" data-ot-seg="2"><span class="ot-plan-progress__n">2</span> <?php esc_html_e( 'You', 'oomphtravel' ); ?></li>
			</ol>
		</div>
	</section>

	<?php /* 2. The form. */ ?>
	<section class="ot-band ot-plan-body">
		<div class="ot-container ot-plan-body__inner">

			<?php if ( '' !== $ot_s['about'] ) : ?>
				<p class="ot-plan__context">
					<?php esc_html_e( 'Asking about', 'oomphtravel' ); ?>
					<?php if ( '' !== $ot_s['about_url'] ) : ?>
						<a href="<?php echo esc_url( $ot_s['about_url'] ); ?>"><?php echo esc_html( $ot_s['about'] ); ?></a>.
					<?php else : ?>
						<strong><?php echo esc_html( $ot_s['about'] ); ?></strong>.
					<?php endif; ?>
					<a class="ot-plan__context-clear" href="<?php echo esc_url( $ot_inquiry::form_url() ); ?>"><?php esc_html_e( 'Something else?', 'oomphtravel' ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $ot_errors['_form'] ) ) : ?>
				<p class="ot-plan__alert" role="alert"><?php echo esc_html( (string) $ot_errors['_form'] ); ?></p>
			<?php elseif ( $ot_errors ) : ?>
				<p class="ot-plan__alert" role="alert"><?php esc_html_e( 'A few details need attention; they are marked below.', 'oomphtravel' ); ?></p>
			<?php endif; ?>

			<form class="ot-plan__form" method="post" action="<?php echo esc_url( $ot_action ); ?>" novalidate data-ot-form>
				<input type="hidden" name="<?php echo esc_attr( $ot_inquiry::NONCE_FIELD ); ?>" value="<?php echo esc_attr( $ot_inquiry::nonce() ); ?>">
				<input type="hidden" name="tour" value="<?php echo esc_attr( (string) $ot_s['tour_id'] ); ?>">
				<input type="hidden" name="destination" value="<?php echo esc_attr( (string) $ot_s['destination_id'] ); ?>">
				<input type="hidden" name="operator" value="<?php echo esc_attr( (string) $ot_s['operator_id'] ); ?>">
				<input type="hidden" name="source" value="<?php echo esc_attr( $ot_s['source'] ); ?>" data-ot-source>
				<input type="hidden" name="elapsed_ms" value="">
				<?php /* Hidden from people, irresistible to bots: off-screen, not display:none. */ ?>
				<div class="ot-plan__trap" aria-hidden="true">
					<label for="ot-plan-website">Website</label>
					<input type="text" id="ot-plan-website" name="<?php echo esc_attr( $ot_inquiry::HONEYPOT ); ?>" tabindex="-1" autocomplete="off">
				</div>

				<?php /* Step 1: the trip. */ ?>
				<fieldset class="ot-plan__step" data-ot-panel="1">
					<legend class="ot-plan__sr"><?php esc_html_e( 'Step 1 of 2: the trip', 'oomphtravel' ); ?></legend>

					<fieldset class="ot-plan__group<?php echo oomphtravel_plan_has_error( 'trip_type' ) ? ' is-invalid' : ''; ?>" data-ot-group="trip_type">
						<legend class="ot-plan__legend"><?php esc_html_e( 'What kind of trip?', 'oomphtravel' ); ?></legend>
						<div class="ot-plan__cards">
							<?php echo oomphtravel_plan_choices( 'trip_type', $ot_types, $ot_s['trip_type'], 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
						<?php echo oomphtravel_plan_error( 'trip_type' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						<div class="ot-plan__cruise" data-ot-cruise hidden>
							<p class="ot-plan__cruise-text"><?php esc_html_e( 'Cruises are planned on CruiseOomph, my sister site, on a form built for ships. Same advisor, same phone number, a different front door.', 'oomphtravel' ); ?></p>
							<?php echo oomphtravel_button( __( 'Continue on CruiseOomph', 'oomphtravel' ), $ot_cruise, 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
					</fieldset>

					<?php if ( $ot_chips ) : ?>
					<fieldset class="ot-plan__group" data-ot-group="destinations">
						<legend class="ot-plan__legend"><?php esc_html_e( 'Where?', 'oomphtravel' ); ?> <span class="ot-plan__optional"><?php esc_html_e( 'Optional. Choose any that appeal.', 'oomphtravel' ); ?></span></legend>
						<div class="ot-plan__chips">
							<?php echo oomphtravel_plan_choices( 'destinations', $ot_chips, $ot_s['destinations'], 'chip' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
					</fieldset>
					<?php endif; ?>

					<div class="ot-plan__row">
						<div class="ot-plan__field<?php echo oomphtravel_plan_has_error( 'when' ) ? ' is-invalid' : ''; ?>">
							<label class="ot-plan__label" for="ot-plan-when"><?php esc_html_e( 'When?', 'oomphtravel' ); ?> <span class="ot-plan__optional"><?php esc_html_e( 'Optional', 'oomphtravel' ); ?></span></label>
							<select class="ot-plan__select" id="ot-plan-when" name="when">
								<option value=""><?php esc_html_e( 'A month, or flexible', 'oomphtravel' ); ?></option>
								<?php foreach ( $ot_whens as $ot_k => $ot_label ) : ?>
									<option value="<?php echo esc_attr( (string) $ot_k ); ?>"<?php selected( $ot_s['when'], (string) $ot_k ); ?>><?php echo esc_html( $ot_label ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php echo oomphtravel_plan_error( 'when' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
						<div class="ot-plan__field">
							<label class="ot-plan__label" for="ot-plan-travelers"><?php esc_html_e( 'Who is going?', 'oomphtravel' ); ?> <span class="ot-plan__optional"><?php esc_html_e( 'Optional', 'oomphtravel' ); ?></span></label>
							<input class="ot-plan__input" type="text" id="ot-plan-travelers" name="travelers" value="<?php echo esc_attr( $ot_s['travelers'] ); ?>" maxlength="160" autocomplete="off" placeholder="<?php esc_attr_e( 'Two adults and two teenagers', 'oomphtravel' ); ?>">
						</div>
					</div>

					<fieldset class="ot-plan__group<?php echo oomphtravel_plan_has_error( 'budget' ) ? ' is-invalid' : ''; ?>" data-ot-group="budget">
						<legend class="ot-plan__legend"><?php esc_html_e( 'Budget for the whole trip, before flights', 'oomphtravel' ); ?></legend>
						<div class="ot-plan__cards ot-plan__cards--4">
							<?php echo oomphtravel_plan_choices( 'budget', $ot_budgets, $ot_s['budget'], 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
						<?php echo oomphtravel_plan_error( 'budget' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</fieldset>

					<div class="ot-plan__field">
						<label class="ot-plan__label" for="ot-plan-notes"><?php esc_html_e( 'Anything else?', 'oomphtravel' ); ?> <span class="ot-plan__optional"><?php esc_html_e( 'Optional', 'oomphtravel' ); ?></span></label>
						<textarea class="ot-plan__textarea" id="ot-plan-notes" name="notes" rows="4" maxlength="3000" placeholder="<?php esc_attr_e( 'An anniversary, a grandmother who walks slowly, a town you keep hearing about.', 'oomphtravel' ); ?>"><?php echo esc_textarea( $ot_s['notes'] ); ?></textarea>
					</div>

					<div class="ot-plan__actions" data-ot-actions="1" hidden>
						<button type="button" class="ot-btn ot-btn--primary" data-ot-continue><span class="ot-btn__label"><?php esc_html_e( 'Continue', 'oomphtravel' ); ?></span><?php echo oomphtravel_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></button>
						<p class="ot-plan__actions-note"><?php esc_html_e( 'Step 2 is your name and how to reach you.', 'oomphtravel' ); ?></p>
					</div>
				</fieldset>

				<?php /* Step 2: the person. */ ?>
				<fieldset class="ot-plan__step" data-ot-panel="2">
					<legend class="ot-plan__sr"><?php esc_html_e( 'Step 2 of 2: you', 'oomphtravel' ); ?></legend>

					<div class="ot-plan__recap" data-ot-recap hidden>
						<p class="ot-plan__recap-label"><?php esc_html_e( 'The trip', 'oomphtravel' ); ?></p>
						<ul class="ot-plan__recap-list" data-ot-recap-list></ul>
						<button type="button" class="ot-plan__textbtn" data-ot-back><?php esc_html_e( 'Change something', 'oomphtravel' ); ?></button>
					</div>

					<div class="ot-plan__field<?php echo oomphtravel_plan_has_error( 'name' ) ? ' is-invalid' : ''; ?>">
						<label class="ot-plan__label" for="ot-plan-name"><?php esc_html_e( 'Your name', 'oomphtravel' ); ?></label>
						<input class="ot-plan__input" type="text" id="ot-plan-name" name="name" value="<?php echo esc_attr( $ot_s['name'] ); ?>" maxlength="120" autocomplete="name" required>
						<?php echo oomphtravel_plan_error( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>

					<fieldset class="ot-plan__group<?php echo oomphtravel_plan_has_error( 'contact_method' ) ? ' is-invalid' : ''; ?>" data-ot-group="contact_method">
						<legend class="ot-plan__legend"><?php esc_html_e( 'How should I reply?', 'oomphtravel' ); ?></legend>
						<div class="ot-plan__pills">
							<?php echo oomphtravel_plan_choices( 'contact_method', $ot_methods, $ot_method_now, 'pill' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
						<?php echo oomphtravel_plan_error( 'contact_method' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</fieldset>

					<div class="ot-plan__field<?php echo oomphtravel_plan_has_error( 'contact_value' ) ? ' is-invalid' : ''; ?>" data-ot-contact>
						<label class="ot-plan__label" for="ot-plan-contact" data-ot-contact-label
							data-ot-email="<?php echo esc_attr( $ot_contact_labels['email'] ); ?>"
							data-ot-phone="<?php echo esc_attr( $ot_contact_labels['phone'] ); ?>"
							data-ot-text="<?php echo esc_attr( $ot_contact_labels['text'] ); ?>"><?php echo esc_html( $ot_contact_labels[ $ot_method_now ] ?? __( 'Email address or phone number', 'oomphtravel' ) ); ?></label>
						<input class="ot-plan__input" type="<?php echo 'email' === $ot_method_now ? 'email' : ( '' === $ot_method_now ? 'text' : 'tel' ); ?>" id="ot-plan-contact" name="contact_value" value="<?php echo esc_attr( $ot_s['contact_value'] ); ?>" maxlength="120" autocomplete="<?php echo 'email' === $ot_method_now ? 'email' : 'tel'; ?>" required data-ot-contact-input>
						<p class="ot-plan__help" data-ot-contact-help
							data-ot-email="<?php echo esc_attr( $ot_contact_help['email'] ); ?>"
							data-ot-phone="<?php echo esc_attr( $ot_contact_help['phone'] ); ?>"
							data-ot-text="<?php echo esc_attr( $ot_contact_help['text'] ); ?>"><?php echo esc_html( $ot_contact_help[ $ot_method_now ] ?? '' ); ?></p>
						<?php echo oomphtravel_plan_error( 'contact_value' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>

					<div class="ot-plan__check<?php echo oomphtravel_plan_has_error( 'consent' ) ? ' is-invalid' : ''; ?>" data-ot-group="consent">
						<label class="ot-plan__check-label" for="ot-plan-consent">
							<input class="ot-plan__checkbox" type="checkbox" id="ot-plan-consent" name="consent" value="1" required<?php checked( $ot_s['consent'] ); ?>>
							<span><?php echo esc_html( $ot_inquiry::CONSENT_TEXT ); ?></span>
						</label>
						<?php echo oomphtravel_plan_error( 'consent' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>

					<?php if ( '' !== $ot_newsletter ) : ?>
					<div class="ot-plan__check" data-ot-newsletter-row>
						<label class="ot-plan__check-label" for="ot-plan-newsletter">
							<input class="ot-plan__checkbox" type="checkbox" id="ot-plan-newsletter" name="newsletter_opt_in" value="1"<?php checked( $ot_s['newsletter'] ); ?>>
							<span><?php esc_html_e( 'Send me trip notes now and then, too. One email to confirm; unsubscribe in one click.', 'oomphtravel' ); ?></span>
						</label>
					</div>
					<?php endif; ?>

					<div class="ot-plan__actions" data-ot-actions="2">
						<button type="button" class="ot-btn ot-btn--ghost" data-ot-back-button hidden><?php esc_html_e( 'Back', 'oomphtravel' ); ?></button>
						<button type="submit" class="ot-btn ot-btn--primary" data-ot-send data-ot-sending="<?php esc_attr_e( 'Sending…', 'oomphtravel' ); ?>"><span class="ot-btn__label"><?php esc_html_e( 'Send to Eric', 'oomphtravel' ); ?></span><?php echo oomphtravel_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></button>
						<p class="ot-plan__actions-note"><?php esc_html_e( 'Used only to plan your trip. Never shared, never sold.', 'oomphtravel' ); ?></p>
					</div>
				</fieldset>

				<p class="ot-plan__status" role="status" aria-live="polite" data-ot-status></p>
			</form>
		</div>
	</section>

</div>
