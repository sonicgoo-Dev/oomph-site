<?php
/**
 * Title: Privacy
 * Slug: oomphtravel/privacy-policy
 * Inserter: no
 *
 * The privacy page, rendered by templates/page-privacy-policy.html at
 * /privacy-policy/ (SEO audit 2026-10-08, B5: the site collected email
 * addresses on every page and had no policy to point at; the footer's
 * legal row already looked for this slug and printed nothing). Plain
 * words, in Eric's voice, and only what the site actually does: the
 * Start planning enquiry (class-inquiry.php), the PlainSend lists
 * (class-plainsend.php), Calendly on the receipt, Google Analytics through
 * Site Kit, Microsoft Clarity on production only (class-clarity-guard.php),
 * and the hosting logs. When any of those changes, this page changes with
 * it; it is kept in code for that reason.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

$ot_updated = '2026-10-08';
?>
<article class="ot-legal">

	<header class="ot-band ot-band--mist ot-legal__hero">
		<div class="ot-container">
			<?php echo oomphtravel_eyebrow( __( 'Legal', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<h1 class="ot-legal__title"><?php esc_html_e( 'Privacy', 'oomphtravel' ); ?></h1>
			<p class="ot-legal__lead"><?php esc_html_e( 'What this site collects, where it goes, and how to have it removed. Short, because there is not much of it.', 'oomphtravel' ); ?></p>
			<p class="ot-legal__updated"><?php esc_html_e( 'Updated', 'oomphtravel' ); ?> <time datetime="<?php echo esc_attr( $ot_updated ); ?>"><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $ot_updated ) ) ); ?></time></p>
		</div>
	</header>

	<div class="ot-band">
		<div class="ot-container">
			<div class="ot-legal__body">

				<h2><?php esc_html_e( 'Who is collecting it?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'Oomph Travel LLC, a travel advisory in Port Angeles, Washington, run by me, Eric Hempel. Anything you send through this site comes to me. You can reach me about any of it at eric@oomphtravel.com.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'What do you collect when I start planning a trip?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'The Start planning form asks for your name, how you would like to be reached (an email address or a phone number) and what you are imagining: the kind of trip, where, when and who is going. It is kept as a private record on this site and emailed to me, and I use it for one thing, which is to plan that trip with you. The form says so under the button: it is not marketing consent, and ticking it does not put you on any list.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'If you tick the box to hear from me by email as well, your address is added to the newsletter, which is described below. If you choose a cruise, the form sends you to CruiseOomph, my sister site, and its own privacy page applies there.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'When a trip goes ahead, the details a booking needs (names as they appear on passports, dates of birth, contact details, and payment details you give directly) are passed to the suppliers who provide it, such as a tour operator, a hotel or a cruise line, and to Nexion, my host agency, whose booking systems I work through. I pass on what a booking needs and nothing more.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'What about the newsletter and the Travel Trends guide?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'Both ask for an email address and nothing else. The address is held by PlainSend, the email service I use. You get one message asking you to confirm, and nothing is sent until you do. Every email after that has an unsubscribe link, and one click removes you.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'And if I book a call?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'The page after the Start planning form offers a thirty-minute call through Calendly. What you enter there is handled by Calendly under its own privacy policy; I see the booking and the name and email you give it.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'What does the site measure on its own?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'Two things, both to see which pages are read and where the site is confusing. Google Analytics counts visits and the pages they cover. Microsoft Clarity records how pages are used, as heatmaps and replays of scrolling and clicking, with the text you type masked out. Each sets its own cookies in your browser. Neither is used to show you advertising, and I do not run advertising.', 'oomphtravel' ); ?></p>
				<p><?php esc_html_e( 'The server that hosts the site, at SiteGround, keeps the usual access logs, which include your IP address, for security. The Start planning form also keeps a short-lived, scrambled note of the address a submission came from, so that one connection cannot flood it.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'Do you sell or share it?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'No. Nothing you give me is sold, rented or passed to anyone for their own marketing. It goes to the services named on this page, each for the job described, and to the suppliers a booking needs.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'How long do you keep it?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'Enquiries and trip records stay while we are planning and for the time afterwards that a travel file is kept for, so that I can answer a question about a past trip. Newsletter addresses stay until you unsubscribe. Analytics data is kept on the terms of the two services above.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'How do I see it, change it or have it deleted?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'Email me. I will tell you what I hold about you, correct it, or delete it, within a few days, apart from anything a completed booking obliges me to keep. If you are in the United Kingdom, the European Union or a US state with its own privacy law, those rights are yours in full and this is how to use them.', 'oomphtravel' ); ?></p>

				<h2><?php esc_html_e( 'Anything else?', 'oomphtravel' ); ?></h2>
				<p><?php esc_html_e( 'This site is not aimed at children and I do not knowingly collect information from anyone under eighteen. Links to other sites, CruiseOomph included, lead to their own privacy terms. When this page changes, the date at the top changes with it.', 'oomphtravel' ); ?></p>

			</div>
		</div>
	</div>

</article>
