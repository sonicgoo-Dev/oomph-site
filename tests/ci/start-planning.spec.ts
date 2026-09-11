import { test, expect, type Page } from '@playwright/test';

/**
 * Start planning (plan §6.15, D14, D30, D31).
 *
 * The seed publishes the page with an empty body; the theme mounts the
 * pattern and the plugin (OomphTravel\Core\Inquiry) handles the post. These
 * specs walk the form the way a visitor would, in the browser with the
 * script running, and once without it (the plain post the plugin accepts
 * when JavaScript is off). The container has no mail server, so the
 * emails fail quietly; the receipt is what proves the record was made.
 *
 * The timing guard rejects a post under two seconds from first touch, so
 * the browser walks wait it out rather than race it.
 */

const SETTLE_MS = 2300;

async function fillTrip( page: Page ) {
  await page.locator( 'label[for="ot-plan-trip_type-custom"]' ).click();
  await page.locator( 'label[for="ot-plan-destinations-italy"]' ).click();
  await page.locator( '#ot-plan-when' ).selectOption( 'flexible' );
  await page.locator( '#ot-plan-travelers' ).fill( 'Two adults' );
  await page.locator( 'label[for="ot-plan-budget-20k-40k"]' ).click();
}

test.describe( '/start-planning/', () => {
  test( 'renders: one H1, the two steps, one visible primary button', async ( { page } ) => {
    const response = await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'What are you imagining?' );

    // Step 1 is on screen, step 2 waits; the only visible primary is Continue.
    await expect( page.locator( '[data-ot-panel="1"]' ) ).toBeVisible();
    await expect( page.locator( '[data-ot-panel="2"]' ) ).toBeHidden();
    await expect( page.locator( '.ot-plan .ot-btn--primary:visible' ) ).toHaveCount( 1 );
    await expect( page.locator( '[data-ot-continue]' ) ).toBeVisible();

    // The four budget bands carry no qualifying language.
    const budgets = await page.locator( '[data-ot-group="budget"] .ot-plan__card-text' ).allTextContents();
    expect( budgets ).toEqual( [ 'Up to $10,000', '$10,000 to $20,000', '$20,000 to $40,000', 'Guide me' ] );

    // No FAQ or Service schema on a form page; the breadcrumb is enough.
    const html = await page.content();
    expect( html ).not.toContain( '"FAQPage"' );
    expect( html ).not.toContain( '"@type":"Service"' );
  } );

  test( 'step 1 refuses to continue until the trip type and budget are chosen', async ( { page } ) => {
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );
    await page.locator( '[data-ot-continue]' ).click();

    await expect( page.locator( '[data-ot-error="trip_type"]' ) ).toBeVisible();
    await expect( page.locator( '[data-ot-error="budget"]' ) ).toBeVisible();
    await expect( page.locator( '[data-ot-panel="1"]' ) ).toBeVisible();
  } );

  test( 'the cruise choice hands off to CruiseOomph with the UTM tag instead of continuing', async ( { page } ) => {
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );
    await page.locator( 'label[for="ot-plan-trip_type-cruise"]' ).click();

    const note = page.locator( '[data-ot-cruise]' );
    await expect( note ).toBeVisible();
    await expect( page.locator( '[data-ot-continue]' ) ).toBeDisabled();

    const href = ( await note.locator( 'a' ).getAttribute( 'href' ) ) ?? '';
    expect( href ).toContain( 'cruiseoomph.com/plan/' );
    expect( href ).toContain( 'utm_source=oomphtravel' );
    expect( href ).toContain( 'utm_medium=site' );
    expect( href ).toContain( 'utm_campaign=start-planning' );
  } );

  test( 'a full walk ends on the receipt with the first name and a reference', async ( { page } ) => {
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );
    await fillTrip( page );
    await page.locator( '[data-ot-continue]' ).click();

    await expect( page.locator( 'h1' ) ).toHaveText( 'How should I reach you?' );
    await expect( page.locator( '[data-ot-panel="2"]' ) ).toBeVisible();
    await expect( page.locator( '[data-ot-recap-list] li' ).first() ).toContainText( 'ustom' );

    await page.locator( '#ot-plan-name' ).fill( 'Playwright Tester' );
    await page.locator( 'label[for="ot-plan-contact_method-email"]' ).click();
    await expect( page.locator( '[data-ot-contact-label]' ) ).toHaveText( 'Email address' );
    await page.locator( '#ot-plan-contact' ).fill( 'ci-tester@example.com' );
    await page.locator( '#ot-plan-consent' ).check();

    await page.waitForTimeout( SETTLE_MS );
    await Promise.all( [
      page.waitForURL( /\/start-planning\/received\/\?r=/ ),
      page.locator( '[data-ot-send]' ).click(),
    ] );

    await expect( page.locator( 'h1' ) ).toHaveText( 'Thank you, Playwright.' );
    await expect( page.locator( '[data-ot-receipt]' ) ).toHaveAttribute( 'data-ot-reference', /^OT-\d+$/ );
    await expect( page.locator( '.calendly-inline-widget' ) ).toHaveAttribute( 'data-url', /calendly\.com/ );
    await expect( page.locator( '.ot-plan-hero__lead' ) ).toContainText( 'within one business day' );

    // The receipt is personal: never indexed.
    await expect( page.locator( 'meta[name="robots"]' ) ).toHaveAttribute( 'content', /noindex/ );
  } );

  test( 'a phone reply asks for a number and never offers the trip notes', async ( { page } ) => {
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );
    await fillTrip( page );
    await page.locator( '[data-ot-continue]' ).click();
    await page.locator( 'label[for="ot-plan-contact_method-phone"]' ).click();

    await expect( page.locator( '[data-ot-contact-label]' ) ).toHaveText( 'Phone number' );
    await expect( page.locator( '#ot-plan-contact' ) ).toHaveAttribute( 'type', 'tel' );
    await expect( page.locator( '[data-ot-newsletter-row]' ) ).toBeHidden();
  } );

  test( 'without the script both steps show and a plain post still reaches the receipt', async ( { browser } ) => {
    const context = await browser.newContext( { javaScriptEnabled: false } );
    const page = await context.newPage();
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '[data-ot-panel="1"]' ) ).toBeVisible();
    await expect( page.locator( '[data-ot-panel="2"]' ) ).toBeVisible();
    await expect( page.locator( '[data-ot-continue]' ) ).toBeHidden();

    await page.locator( '#ot-plan-trip_type-escorted' ).check( { force: true } );
    await page.locator( '#ot-plan-budget-guide' ).check( { force: true } );
    await page.locator( '#ot-plan-name' ).fill( 'Plain Post' );
    await page.locator( '#ot-plan-contact_method-text' ).check( { force: true } );
    await page.locator( '#ot-plan-contact' ).fill( '360 775 4644' );
    await page.locator( '#ot-plan-consent' ).check();
    const [ posted ] = await Promise.all( [
      page.waitForResponse( ( r ) => r.request().method() === 'POST' ),
      page.locator( '[data-ot-send]' ).click(),
    ] );
    await page.waitForLoadState( 'domcontentloaded' );
    console.log( 'plain post:', posted.status(), posted.headers()[ 'location' ] ?? '', '->', page.url() );
    console.log( ( await page.locator( 'main' ).innerHTML() ).slice( 0, 1500 ) );

    await expect( page ).toHaveURL( /\/start-planning\/received\/\?r=/ );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Thank you, Plain.' );
    await context.close();
  } );

  test( 'the honeypot and a missing consent are refused, and what was typed stays', async ( { browser } ) => {
    const context = await browser.newContext( { javaScriptEnabled: false } );
    const page = await context.newPage();
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );

    // A bot fills the off-screen field.
    await page.locator( '#ot-plan-trip_type-custom' ).check( { force: true } );
    await page.locator( '#ot-plan-budget-guide' ).check( { force: true } );
    await page.locator( '#ot-plan-name' ).fill( 'Robot' );
    await page.locator( '#ot-plan-contact_method-email' ).check( { force: true } );
    await page.locator( '#ot-plan-contact' ).fill( 'robot@example.com' );
    await page.locator( '#ot-plan-consent' ).check();
    await page.locator( '#ot-plan-website' ).fill( 'https://spam.example', { force: true } );
    const [ response ] = await Promise.all( [
      page.waitForResponse( ( r ) => r.request().method() === 'POST' ),
      page.locator( '[data-ot-send]' ).click(),
    ] );
    expect( response.status() ).toBe( 400 );
    await expect( page.locator( '.ot-plan__alert' ) ).toContainText( 'Request rejected' );

    // A person who forgot the consent line: the field is marked, the rest kept.
    await page.goto( '/start-planning/', { waitUntil: 'domcontentloaded' } );
    await page.locator( '#ot-plan-trip_type-custom' ).check( { force: true } );
    await page.locator( '#ot-plan-budget-guide' ).check( { force: true } );
    await page.locator( '#ot-plan-name' ).fill( 'Forgetful Person' );
    await page.locator( '#ot-plan-contact_method-email' ).check( { force: true } );
    await page.locator( '#ot-plan-contact' ).fill( 'person@example.com' );
    await Promise.all( [
      page.waitForResponse( ( r ) => r.request().method() === 'POST' ),
      page.locator( '[data-ot-send]' ).click(),
    ] );
    await page.waitForLoadState( 'domcontentloaded' );

    await expect( page.locator( '[data-ot-error="consent"]' ) ).toBeVisible();
    await expect( page.locator( '#ot-plan-name' ) ).toHaveValue( 'Forgetful Person' );
    await expect( page.locator( '#ot-plan-trip_type-custom' ) ).toBeChecked();
    await context.close();
  } );

  test( 'a tour link pre-fills the form and names the tour', async ( { page } ) => {
    await page.goto( '/start-planning/?tour=globus-rome-florence-venice', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '.ot-plan__context' ) ).toContainText( 'Asking about' );
    await expect( page.locator( '#ot-plan-trip_type-escorted' ) ).toBeChecked();
    await expect( page.locator( 'input[name="tour"]' ) ).not.toHaveValue( '' );
  } );

  test( 'the old /discovery-call/ address redirects permanently', async ( { request } ) => {
    const response = await request.get( '/discovery-call/', { maxRedirects: 0 } );
    expect( response.status() ).toBe( 301 );
    expect( response.headers()[ 'location' ] ).toMatch( /\/start-planning\/$/ );
  } );

  test( 'the receipt address without a token still renders, without a name', async ( { page } ) => {
    const response = await page.goto( '/start-planning/received/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Thank you.' );
  } );
} );
