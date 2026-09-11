import { test, expect } from '@playwright/test';

/**
 * Escorted tours (plan §6.5 index, §6.6 operator page, §6.7 tour detail).
 *
 * The seed creates three tours per escorted operator as drafts; the fixture
 * publishes every operator and every tour except natgeo-andalusia. So the
 * index shows fourteen tours, Globus shows three, the Globus Italy tour
 * proves the full template, and the draft proves drafts stay private.
 */

test.describe( 'escorted tours index', () => {
  test( 'hero, one H1, the LCP image never lazy, one primary button (the closing band)', async ( { page } ) => {
    const response = await page.goto( '/escorted-tours/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Escorted tours, chosen for the way you travel.' );

    const img = page.locator( '.ot-tours-hero img' );
    await expect( img ).toHaveAttribute( 'fetchpriority', 'high' );
    await expect( img ).not.toHaveAttribute( 'loading', 'lazy' );
    await expect( page.locator( 'link[rel="preload"][as="image"]' ) ).toHaveCount( 2 );

    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 1 );
    await expect( page.locator( '.ot-closing .ot-btn--primary' ) ).toHaveCount( 1 );
  } );

  test( 'five escorted operator cards with tour counts; NatGeo carries the CruiseOomph line (D32)', async ( { page } ) => {
    await page.goto( '/escorted-tours/', { waitUntil: 'domcontentloaded' } );

    const cards = page.locator( '.ot-card-operator--escorted' );
    await expect( cards ).toHaveCount( 5 );
    await expect( page.locator( '.ot-card-operator--fit' ) ).toHaveCount( 0 );
    await expect( cards.filter( { hasText: 'Globus' } ).locator( '.ot-card-operator__meta' ) ).toHaveText( '3 tours' );
    await expect( cards.filter( { hasText: 'National Geographic' } ).locator( '.ot-card-operator__meta' ) ).toHaveText( '2 tours' );

    const cruise = page.locator( '.ot-card-operator__cruise a' );
    await expect( cruise ).toHaveCount( 1 );
    await expect( cruise ).toHaveAttribute( 'href', /cruiseoomph\.com\/cruises\/.*utm_source=oomphtravel/ );
  } );

  test( 'every published tour, each with the fixed caption (D34) and an Ask Eric link', async ( { page } ) => {
    await page.goto( '/escorted-tours/', { waitUntil: 'domcontentloaded' } );

    const cards = page.locator( '.ot-tours-grid__cards .ot-card-tour' );
    await expect( cards ).toHaveCount( 14 );
    await expect( page.locator( '.ot-filters__count' ) ).toHaveText( '14 tours' );
    for ( const caption of await cards.locator( '.ot-card-tour__caption' ).allInnerTexts() ) {
      expect( caption ).toBe( 'Dates and availability confirmed on request.' );
    }
    await expect( cards.first().locator( '.ot-link[href*="/start-planning/?tour="]' ) ).toHaveCount( 1 );
    await expect( page.locator( 'meta[name="robots"][content*="noindex"]' ) ).toHaveCount( 0 );
  } );

  test( 'filters live in the URL and a filtered view is noindex', async ( { page } ) => {
    await page.goto( '/escorted-tours/?destination=italy', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( '.ot-tours-grid__cards .ot-card-tour' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-filters__count' ) ).toHaveText( '3 of 14 tours' );
    await expect( page.locator( '#ot-filter-destination' ) ).toHaveValue( 'italy' );
    await expect( page.locator( 'meta[name="robots"][content*="noindex"]' ) ).toHaveCount( 1 );

    await page.goto( '/escorted-tours/?operator=tauck&length=medium', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( '.ot-tours-grid__cards .ot-card-tour' ) ).toHaveCount( 2 );

    await page.goto( '/escorted-tours/?month=1', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( '.ot-tours-grid__cards .ot-card-tour' ) ).toHaveCount( 1 );

    // An unknown value is dropped, not an error: the full grid comes back.
    await page.goto( '/escorted-tours/?destination=atlantis', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( '.ot-tours-grid__cards .ot-card-tour' ) ).toHaveCount( 14 );
    await expect( page.locator( 'meta[name="robots"][content*="noindex"]' ) ).toHaveCount( 0 );
  } );

  test( 'changing a select submits the form and the URL carries the filter', async ( { page } ) => {
    await page.goto( '/escorted-tours/', { waitUntil: 'domcontentloaded' } );
    await page.locator( '#ot-filter-operator' ).selectOption( 'globus' );
    await page.waitForURL( /[?&]operator=globus/ );
    await expect( page.locator( '.ot-tours-grid__cards .ot-card-tour' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-filters__clear a' ) ).toBeVisible();
  } );
} );

test.describe( 'operator page (Globus)', () => {
  test( 'H1 in my words, the fit note, the facts and three tour cards', async ( { page } ) => {
    const response = await page.goto( '/escorted-tours/globus/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveText( 'Globus, in my words' );
    await expect( page.locator( '.ot-op-fit__note p' ).first() ).toContainText( 'Globus is where I send people' );
    await expect( page.locator( '.ot-facts__row' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-op-tours .ot-card-tour' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-dest-ship' ) ).toHaveCount( 0 );

    const primaries = page.locator( 'main .ot-btn--primary' );
    await expect( primaries ).toHaveCount( 2 );
    for ( const href of await primaries.evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).href ) ) ) {
      expect( href ).toMatch( /\/start-planning\/\?operator=globus$/ );
    }
  } );

  test( 'National Geographic carries the CruiseOomph hand-off (D32)', async ( { page } ) => {
    await page.goto( '/escorted-tours/national-geographic-expeditions/', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( '.ot-dest-ship a' ) ).toHaveAttribute( 'href', /cruiseoomph\.com/ );
  } );

  test( 'a FIT supplier lists ways and destinations instead of tours', async ( { page } ) => {
    await page.goto( '/escorted-tours/classic-vacations/', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Classic Vacations, in my words' );
    await expect( page.locator( '.ot-op-tours' ) ).toHaveCount( 0 );
    await expect( page.locator( '.ot-op-ways__list a' ).filter( { hasText: 'Resorts & villas' } ) ).toHaveCount( 1 );
  } );
} );

test.describe( 'tour page (Globus, Rome, Florence and Venice)', () => {
  test( 'hero: eyebrow, H1, the fixed caption, no price when unpriced, one primary', async ( { page } ) => {
    const response = await page.goto( '/tours/globus-rome-florence-venice/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveText( 'Rome, Florence and Venice' );
    await expect( page.locator( '.ot-tour-hero .ot-eyebrow' ) ).toHaveText( 'Globus · 9 nights · Italy' );
    await expect( page.locator( '.ot-tour-price__caption' ) ).toHaveText( 'Dates and availability confirmed on request.' );
    await expect( page.locator( '.ot-tour-price' ) ).toHaveCount( 0 );
    await expect( page.locator( '.ot-tour-hero .ot-btn--primary' ) ).toHaveAttribute( 'href', /\/start-planning\/\?tour=globus-rome-florence-venice$/ );
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 2 );
  } );

  test( 'at a glance, ten days, the note, related tours, and nothing that looks like a departures table (D35)', async ( { page } ) => {
    await page.goto( '/tours/globus-rome-florence-venice/', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '.ot-tour-glance .ot-facts__row' ) ).toHaveCount( 5 );
    await expect( page.locator( '.ot-tour-glance__included li' ) ).toHaveCount( 5 );
    await expect( page.locator( '.ot-tour-itinerary .ot-accordion__item' ) ).toHaveCount( 10 );
    await expect( page.locator( '.ot-tour-itinerary__overnight' ).first() ).toContainText( 'Rome' );
    await expect( page.locator( '.ot-tour-note p' ).first() ).toContainText( 'This is the trip I suggest' );
    await expect( page.locator( '.ot-tour-related .ot-card-tour' ) ).toHaveCount( 3 );
    await expect( page.locator( 'main table' ) ).toHaveCount( 0 );
    await expect( page.locator( 'main' ) ).not.toContainText( /waitlist|sold out|limited availability/i );
  } );

  test( 'schema: TouristTrip with the operator and the itinerary, no Offer when unpriced, breadcrumb through the operator', async ( { page } ) => {
    await page.goto( '/tours/globus-rome-florence-venice/', { waitUntil: 'domcontentloaded' } );
    const graphs = await page.locator( 'script[type="application/ld+json"]' ).allInnerTexts();
    const nodes = graphs.flatMap( ( g ) => JSON.parse( g )[ '@graph' ] ?? [] );

    const trip = nodes.find( ( n ) => n[ '@type' ] === 'TouristTrip' );
    expect( trip?.name ).toBe( 'Rome, Florence and Venice' );
    expect( trip?.provider?.name ).toBe( 'Globus' );
    expect( trip?.itinerary?.itemListElement ).toHaveLength( 10 );
    expect( trip?.offers ).toBeUndefined();

    const crumbs = nodes.find( ( n ) => n[ '@type' ] === 'BreadcrumbList' );
    expect( crumbs?.itemListElement.map( ( i: { name: string } ) => i.name ) ).toEqual( [ 'Home', 'Escorted tours', 'Globus', 'Rome, Florence and Venice' ] );
  } );

  test( 'a draft tour is not reachable', async ( { page } ) => {
    const response = await page.goto( '/tours/natgeo-andalusia/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 404 );
  } );
} );

test.describe( 'the tours feed the other pages', () => {
  test( 'the homepage featured row shows the three featured tours (D26)', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );
    const cards = page.locator( '.ot-card-tour' );
    await expect( cards ).toHaveCount( 3 );
    await expect( cards.locator( '.ot-card-tour__meta' ) ).toContainText( [ /Globus|Tauck|Insight/, /Globus|Tauck|Insight/, /Globus|Tauck|Insight/ ] );
  } );

  test( 'Italy lists its operators and three tour cards', async ( { page } ) => {
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );
    await expect( page.locator( '.ot-dest-way__operators a' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-dest-ways__tours .ot-card-tour' ) ).toHaveCount( 3 );
  } );
} );
