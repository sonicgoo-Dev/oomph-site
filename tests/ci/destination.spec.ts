import { test, expect } from '@playwright/test';

/**
 * Destination pages (plan §6.2 index, §6.3 template).
 *
 * The seed creates every destination as a draft with Italy's content; the
 * fixture publishes Italy. So Italy proves the full template and the index
 * proves that drafts are never listed.
 */

test.describe( 'destination template (Italy)', () => {
  test( 'hero: H1 from the record, the LCP image never lazy, one primary button', async ( { page } ) => {
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Italy, planned by someone who keeps going back.' );

    const img = page.locator( '.ot-dest-hero__picture img' );
    await expect( img ).toHaveAttribute( 'fetchpriority', 'high' );
    await expect( img ).not.toHaveAttribute( 'loading', 'lazy' );
    await expect( page.locator( 'link[rel="preload"][as="image"]' ).first() ).toHaveAttribute( 'fetchpriority', 'high' );

    await expect( page.locator( '.ot-dest-hero .ot-btn--primary' ) ).toHaveCount( 1 );
  } );

  test( 'exactly two primary buttons, both pre-set to Italy', async ( { page } ) => {
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );

    const primaries = page.locator( 'main .ot-btn--primary' );
    await expect( primaries ).toHaveCount( 2 );
    for ( const href of await primaries.evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).href ) ) ) {
      expect( href ).toMatch( /\/start-planning\/\?destination=italy$/ );
    }
  } );

  test( 'the sections the record fills are on the page', async ( { page } ) => {
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '.ot-dest-region' ) ).toHaveCount( 6 );
    await expect( page.locator( '.ot-dest-way' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-dest-way--lead .ot-dest-way__title' ) ).toHaveText( 'As a custom journey' );
    await expect( page.locator( '.ot-dest-itinerary .ot-accordion__item' ) ).toHaveCount( 10 );
    await expect( page.locator( '.ot-dest-stay' ) ).toHaveCount( 4 );
    await expect( page.locator( '.ot-months__month' ) ).toHaveCount( 12 );
    await expect( page.locator( '.ot-months__month.is-best' ) ).toHaveCount( 5 );
    await expect( page.locator( '.ot-faq__item' ) ).toHaveCount( 6 );
    await expect( page.locator( '.ot-dest-ship a[href*="cruiseoomph.com"]' ) ).toHaveAttribute( 'href', /region=Mediterranean/ );
  } );

  test( 'perk wording stays conditional', async ( { page } ) => {
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );
    const perks = await page.locator( '.ot-dest-stay__perks' ).allInnerTexts();
    expect( perks.length ).toBeGreaterThan( 0 );
    for ( const p of perks ) {
      expect( p ).toMatch( /^Depending on the property and the rate/ );
      expect( p ).not.toMatch( /you will receive/i );
    }
  } );

  test( 'the accordion opens and closes without JavaScript', async ( { browser } ) => {
    const context = await browser.newContext( { javaScriptEnabled: false } );
    const page = await context.newPage();
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );

    const first = page.locator( '.ot-faq__item' ).first();
    await expect( first.locator( '.ot-accordion__body' ) ).toBeHidden();
    await first.locator( 'summary' ).click();
    await expect( first.locator( '.ot-accordion__body' ) ).toBeVisible();

    await context.close();
  } );

  test( 'schema: TouristDestination and FAQPage with six questions, breadcrumb through Destinations', async ( { page } ) => {
    await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );
    const graphs = await page.locator( 'script[type="application/ld+json"]' ).allInnerTexts();
    const nodes = graphs.flatMap( ( g ) => JSON.parse( g )[ '@graph' ] ?? [] );

    const dest = nodes.find( ( n ) => n[ '@type' ] === 'TouristDestination' );
    expect( dest?.name ).toBe( 'Italy' );

    const faq = nodes.find( ( n ) => n[ '@type' ] === 'FAQPage' );
    expect( faq?.mainEntity ).toHaveLength( 6 );

    const crumbs = nodes.find( ( n ) => n[ '@type' ] === 'BreadcrumbList' );
    expect( crumbs?.itemListElement.map( ( i: { name: string } ) => i.name ) ).toEqual( [ 'Home', 'Destinations', 'Italy' ] );
  } );
} );

test.describe( 'all destinations (index)', () => {
  test( 'lists published records only, under their group heading', async ( { page } ) => {
    const response = await page.goto( '/destinations/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveText( 'Where to' );
    await expect( page.locator( '.ot-dest-index__heading' ).first() ).toContainText( 'Europe' );
    await expect( page.locator( '.ot-card-dest[href$="/destinations/italy/"]' ) ).toHaveCount( 1 );
    await expect( page.locator( '.ot-card-dest[href$="/destinations/france/"]' ) ).toHaveCount( 0 );
  } );
} );
