<?php
/**
 * Title: Travel Trends guide
 * Slug: oomphtravel/travel-trends
 * Inserter: no
 *
 * The yearly lead magnet (plan §6.14; D17, D18, D30), rendered by
 * templates/page-travel-trends.html at /travel-trends/. A navy hero with
 * the guide's cover, the H1, five teasers and the one-field signup that
 * posts straight to PlainSend's trends-guide form; then what the guide
 * covers, how it arrives (double opt-in, said in plain words), and the
 * closing invitation.
 *
 * The signup's submit is the hero's one primary button. The success line
 * tells the visitor to expect a confirmation email first, because a list
 * that is double opt-in loses people who assume the first post failed.
 * newsletter.js reports the lead to GA4 with lead_source "trends_guide".
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_img      = OOMPHTRAVEL_THEME_URI . 'assets/img/';
$ot_year     = oomphtravel_trends_year();
$ot_teasers  = oomphtravel_trends_teasers();
$ot_chapters = oomphtravel_trends_chapters();
$ot_steps    = array(
	__( 'Type your email and press the button.', 'oomphtravel' ),
	__( 'A short confirmation email arrives. Press the button in it.', 'oomphtravel' ),
	__( 'The guide follows straight after, as a PDF.', 'oomphtravel' ),
);
?>
<div class="ot-trends">

	<?php /* 1. Hero: cover, headline, teasers, the form. */ ?>
	<section class="ot-band ot-band--navy ot-trends-hero" aria-labelledby="ot-trends-title">
		<div class="ot-container ot-trends-hero__inner">
			<div class="ot-trends-hero__copy">
				<?php echo oomphtravel_eyebrow( sprintf( /* translators: %s: year */ __( 'Travel Trends %s', 'oomphtravel' ), $ot_year ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-trends-hero__title" id="ot-trends-title"><?php echo esc_html( sprintf( /* translators: %s: year */ __( 'Travel Trends %s: what I’m watching', 'oomphtravel' ), $ot_year ) ); ?></h1>
				<p class="ot-trends-hero__lead"><?php echo esc_html( sprintf( /* translators: %s: year */ __( 'Fifteen pages on where travel is heading in %s: the places rising before the crowds, the stays worth a week, and the ways of travelling that are changing. Free, by email.', 'oomphtravel' ), $ot_year ) ); ?></p>
				<ul class="ot-trends-hero__teasers">
					<?php foreach ( $ot_teasers as $ot_t ) : ?>
						<li><?php echo esc_html( $ot_t ); ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="ot-trends-hero__form">
					<?php
					oomphtravel_signup_form(
						'trends-guide',
						array(
							'label'   => __( 'Email address', 'oomphtravel' ),
							'button'  => __( 'Send me the guide', 'oomphtravel' ),
							'source'  => 'trends_guide',
							'id'      => 'ot-signup-trends',
							'success' => oomphtravel_trends_success_text(),
						)
					);
					?>
					<p class="ot-trends-hero__note"><?php esc_html_e( 'One email to confirm, then the guide. A few notes a year after that, and unsubscribe in one click.', 'oomphtravel' ); ?></p>
				</div>
			</div>
			<figure class="ot-trends-hero__cover">
				<img
					src="<?php echo esc_url( $ot_img . 'trends-cover-960.webp' ); ?>"
					srcset="<?php echo esc_attr( $ot_img . 'trends-cover-480.webp 480w, ' . $ot_img . 'trends-cover-960.webp 960w' ); ?>"
					sizes="(min-width: 1024px) 520px, 100vw"
					width="960" height="622" fetchpriority="high" decoding="async"
					alt="<?php echo esc_attr( sprintf( /* translators: %s: year */ __( 'The cover of the Travel Trends %s guide.', 'oomphtravel' ), $ot_year ) ); ?>">
			</figure>
		</div>
	</section>

	<?php /* 2. What is inside. */ ?>
	<section class="ot-band ot-trends-inside">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Inside', 'oomphtravel' ), __( 'Six chapters, thirty places.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-trends-chapters">
				<?php foreach ( $ot_chapters as $ot_i => $ot_ch ) : ?>
					<li class="ot-trends-chapter">
						<span class="ot-trends-chapter__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $ot_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3 class="ot-trends-chapter__title"><?php echo esc_html( $ot_ch['title'] ); ?></h3>
						<p class="ot-trends-chapter__body"><?php echo esc_html( $ot_ch['body'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ot-trends-note"><?php esc_html_e( 'A note on how I use it. The guide is the wide view. What I add is the narrowing: which two of these thirty places suit you, in which month, and what a week there actually costs. That part is a conversation, and there is no fee for it.', 'oomphtravel' ); ?></p>
		</div>
	</section>

	<?php /* 3. How it arrives. */ ?>
	<section class="ot-band ot-band--mist ot-trends-how">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'How it arrives', 'oomphtravel' ), __( 'Two emails, in this order.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ol class="ot-trends-steps">
				<?php foreach ( $ot_steps as $ot_i => $ot_s ) : ?>
					<li class="ot-trends-steps__step">
						<span class="ot-trends-steps__number" aria-hidden="true"><?php echo esc_html( (string) ( $ot_i + 1 ) ); ?></span>
						<p class="ot-trends-steps__body"><?php echo esc_html( $ot_s ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ot-trends-note"><?php esc_html_e( 'If the first email has not arrived within a few minutes, look in the promotions or junk folder once, then write to me and I will send the guide by hand.', 'oomphtravel' ); ?></p>
		</div>
	</section>

	<?php /* 4. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( home_url( '/start-planning/' ), __( 'Seen a place you want to talk about?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
