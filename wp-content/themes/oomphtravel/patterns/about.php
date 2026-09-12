<?php
/**
 * Title: About
 * Slug: oomphtravel/about
 * Inserter: no
 *
 * About (plan §6.11; D15, D28), rendered by templates/page-about.html at
 * /about/. Eric leads: a portrait split hero with the H1 kept from the old
 * site, his story, what the medicine adds, how he works; then Amy Hempel
 * with her own anchor and Person node; the credential strip; one client
 * story with the link to the rest; the closing invitation.
 *
 * First person singular everywhere except the paragraph that introduces
 * Amy (docs/03-rules-and-readiness.md). The two primary buttons are the
 * hero's and the closing band's. The story and the "what the medicine adds"
 * paragraphs are drafts in Eric's voice for him to change line by line.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_img    = OOMPHTRAVEL_THEME_URI . 'assets/img/';
$ot_amy    = oomphtravel_second_advisor();
$ot_story  = oomphtravel_client_stories();
$ot_quote  = $ot_story ? $ot_story[3] : null; // Gary T.: the one about communication.
$ot_plan   = home_url( '/start-planning/' );

// The second width only exists for the portrait the theme ships. If the image
// has been filtered to something else, that file is served on its own.
$ot_amy_srcset = ( ( $ot_amy['image'] ?? '' ) === $ot_img . 'advisor-amy-720.webp' )
	? $ot_img . 'advisor-amy-360.webp 360w, ' . $ot_img . 'advisor-amy-720.webp 720w'
	: '';

$ot_credentials = array(
	array( __( 'CLIA', 'oomphtravel' ), __( 'Cruise Lines International Association, the trade body that registers advisors who sell cruises in the United States.', 'oomphtravel' ) ),
	array( __( 'Nexion / Travel Leaders Network', 'oomphtravel' ), __( 'My host agency and its consortium: the supplier relationships, the booking tools and the hotel programmes behind the price you pay.', 'oomphtravel' ) ),
	array( __( 'Doctor of Osteopathic Medicine', 'oomphtravel' ), __( 'I still practise internal medicine part time. It is why I ask about mobility, medication and pacing before I ask about room categories.', 'oomphtravel' ) ),
	array( __( 'Port Angeles, Washington', 'oomphtravel' ), __( 'Home, on the Olympic Peninsula. Clients are anywhere in the country; the phone works the same from all of it.', 'oomphtravel' ) ),
);

$ot_how = array(
	array( __( 'A short client list', 'oomphtravel' ), __( 'I take on a limited number of trips at a time, so the one I am planning gets the hours it needs. If I cannot give yours that, I will say so at the first call.', 'oomphtravel' ) ),
	array( __( 'One proposal, not five', 'oomphtravel' ), __( 'You see one plan because I have done the narrowing. We revise it together until it is right, and nothing is booked before we both agree.', 'oomphtravel' ) ),
	array( __( 'On the trip with you', 'oomphtravel' ), __( 'A delayed flight, a closed restaurant, a better idea on the day: my number is in your phone for the whole trip, and I am the one who answers it.', 'oomphtravel' ) ),
);
?>
<div class="ot-about">

	<?php /* 1. Hero: portrait split. */ ?>
	<section class="ot-band ot-about-hero" aria-labelledby="ot-about-title">
		<div class="ot-container ot-about-hero__inner">
			<div class="ot-about-hero__copy">
				<?php echo oomphtravel_eyebrow( __( 'About', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<h1 class="ot-about-hero__title" id="ot-about-title"><?php esc_html_e( 'I plan the trips I’d take myself.', 'oomphtravel' ); ?></h1>
				<p class="ot-about-hero__lead"><?php esc_html_e( 'From Port Angeles, Washington, I plan custom journeys, escorted tours and resort stays for clients who want one named advisor across the whole trip, first call to last flight home.', 'oomphtravel' ); ?></p>
				<?php echo oomphtravel_button( __( 'Start planning', 'oomphtravel' ), $ot_plan ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<p class="ot-about-hero__note"><?php esc_html_e( 'Email, text or a quick call, whatever’s easiest.', 'oomphtravel' ); ?></p>
			</div>
			<figure class="ot-about-hero__portrait">
				<img
					src="<?php echo esc_url( $ot_img . 'advisor-eric-720.webp' ); ?>"
					srcset="<?php echo esc_attr( $ot_img . 'advisor-eric-360.webp 360w, ' . $ot_img . 'advisor-eric-720.webp 720w' ); ?>"
					sizes="(min-width: 1024px) 480px, (min-width: 768px) 40vw, 100vw"
					width="720" height="552" fetchpriority="high" decoding="async"
					alt="<?php esc_attr_e( 'Eric Hempel, travel advisor at Oomph Travel.', 'oomphtravel' ); ?>">
			</figure>
		</div>
	</section>

	<?php /* 2. Credential strip (plan §6.1.2). */ ?>
	<?php echo do_blocks( '<!-- wp:pattern {"slug":"oomphtravel/credential-strip"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pattern output. ?>

	<?php /* 3. The story. */ ?>
	<section class="ot-band ot-about-story">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'One advisor', 'oomphtravel' ), __( 'Why I do this.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-prose ot-about-prose">
				<p><?php esc_html_e( 'I started planning other people’s trips because I had been planning my own, in more detail than anyone asked for, for years.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'The notebook came first: which hill town was worth three nights and which was worth an afternoon, the restaurant to book before the flight, the train seat that faces forward. Friends borrowed the notebook. Then they asked me to plan the trip. Then their friends did. Oomph Travel is what that became.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'What has not changed is the way I work. I plan one region at a time, slowly, for people who are marking something: an anniversary, a retirement, a family that has not all been in one place for years. I book through suppliers who answer the phone. And I stay on the trip after it starts, because the planning is the easy half.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 4. Credentials, and what the medicine adds. */ ?>
	<section class="ot-band ot-band--mist ot-about-credentials">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Credentials', 'oomphtravel' ), __( 'The training behind the planning.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-about-items">
				<?php foreach ( $ot_credentials as $ot_item ) : ?>
					<li class="ot-about-item">
						<h3 class="ot-about-item__title"><?php echo esc_html( $ot_item[0] ); ?></h3>
						<p class="ot-about-item__body"><?php echo esc_html( $ot_item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="ot-prose ot-about-prose ot-about-medicine">
				<p><?php esc_html_e( 'The medical training shows up in the planning more than you would expect.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'I ask how far the walk is from the car park to the vineyard, whether the villa has a bedroom on the ground floor, how the medication schedule copes with a nine-hour time change, and where the nearest good hospital is to a resort on a remote coast. Not because anything will go wrong, but because a trip planned around the people on it is the one everybody enjoys, including the person who was quietly worried.', 'oomphtravel' ); ?></p>
			</div>
		</div>
	</section>

	<?php /* 5. How I work. */ ?>
	<section class="ot-band ot-about-how">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'How I work', 'oomphtravel' ), __( 'Three things you can count on.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<ul class="ot-about-items ot-about-items--3">
				<?php foreach ( $ot_how as $ot_item ) : ?>
					<li class="ot-about-item">
						<h3 class="ot-about-item__title"><?php echo esc_html( $ot_item[0] ); ?></h3>
						<p class="ot-about-item__body"><?php echo esc_html( $ot_item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="ot-about-note"><?php esc_html_e( 'There is no planning fee. The hotels, suppliers and operators I book pay me from their side, and their price is the price you pay.', 'oomphtravel' ); ?></p>
		</div>
	</section>

	<?php /* 6. Also at Oomph Travel: Amy (D15, D28). Her Person node reads the same array. */ ?>
	<?php if ( $ot_amy ) : ?>
	<section class="ot-band ot-band--mist ot-about-team" id="amy" aria-labelledby="ot-about-amy">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Also at Oomph Travel', 'oomphtravel' ), __( 'We are two.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-about-team__card">
				<?php if ( ! empty( $ot_amy['image'] ) ) : ?>
					<img
						class="ot-about-team__portrait"
						src="<?php echo esc_url( (string) $ot_amy['image'] ); ?>"
						<?php if ( '' !== $ot_amy_srcset ) : ?>srcset="<?php echo esc_attr( $ot_amy_srcset ); ?>" sizes="(min-width: 768px) 200px, 160px"<?php endif; ?>
						width="720" height="720" loading="lazy" decoding="async"
						alt="<?php echo esc_attr( sprintf( /* translators: %s: the second advisor's name */ __( '%s, associate travel advisor at Oomph Travel.', 'oomphtravel' ), (string) $ot_amy['name'] ) ); ?>">
				<?php else : ?>
					<span class="ot-about-team__monogram" aria-hidden="true"><?php echo esc_html( implode( '', array_map( static fn( string $w ): string => mb_substr( $w, 0, 1 ), preg_split( '/\s+/', (string) $ot_amy['name'] ) ?: array() ) ) ); ?></span>
				<?php endif; ?>
				<div class="ot-about-team__copy">
					<h3 class="ot-about-team__name" id="ot-about-amy"><?php echo esc_html( (string) $ot_amy['name'] ); ?></h3>
					<p class="ot-about-team__role"><?php echo esc_html( (string) $ot_amy['jobTitle'] ); ?></p>
					<p class="ot-about-team__bio"><?php echo esc_html( (string) $ot_amy['description'] ); ?></p>
					<?php if ( ! empty( $ot_amy['focus'] ) ) : ?>
						<p class="ot-about-team__focus"><?php echo esc_html( (string) $ot_amy['focus'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $ot_amy['credentials'] ) ) : ?>
						<ul class="ot-about-team__credentials" aria-label="<?php esc_attr_e( 'Credentials', 'oomphtravel' ); ?>">
							<?php foreach ( (array) $ot_amy['credentials'] as $ot_c ) : ?>
								<li><?php echo esc_html( (string) $ot_c ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 7. One client story, and the rest. */ ?>
	<?php if ( $ot_quote ) : ?>
	<section class="ot-band ot-about-stories">
		<div class="ot-container">
			<?php echo oomphtravel_section_heading( __( 'Client stories', 'oomphtravel' ), __( 'In their words.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div class="ot-about-stories__quote">
				<?php echo oomphtravel_quotation( (string) $ot_quote['body'], oomphtravel_story_attribution( $ot_quote ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
			<p class="ot-grid__foot"><?php echo oomphtravel_link( __( 'More client stories', 'oomphtravel' ), home_url( '/client-stories/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 8. Closing invitation. */ ?>
	<?php echo oomphtravel_closing_band( $ot_plan, __( 'Worth a thirty-minute conversation?', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

</div>
