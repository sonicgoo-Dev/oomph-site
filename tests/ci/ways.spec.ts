import { test, expect } from '@playwright/test';

/**
 * Ways to travel (plan §6.4 Custom journeys, §6.8 Resorts & villas, §6.9
 * Multi-generational trips, §6.10 Cruise planning).
 *
 * The seed creates the four pages published with empty bodies; the theme's
 * page-{slug}.html templates mount the patterns, so the copy is in code and
 * these specs guard the rules the copy has to keep: one H1, the hero never
 * lazy, one primary button per section, the hotel perks and the villa
 * promise kept apart (D40), every CruiseOomph link tagged, and the schema
 * describing only what the page shows.
 */

const PAGES = [
  { path: '/custom-journeys/', h1: 'Independent travel, planned to the hour.', primaries: 2, type: 'custom' },
  { path: '/resorts-and-villas/', h1: 'Somewhere to stay put.', primaries: 2, type: 'resort' },
  { path: '/multi-generational-travel-planning/', h1: 'The trip that works for everyone, planned around the slowest walker.', primaries: 2, type: 'multi-gen' },
  { path: '/cruise-planning/', h1: 'Cruises live next door now.', primaries: 3, type: '' },
];

for ( const p of PAGES ) {
  test.describe( p.path, () => {
    test( 'renders: one H1, the hero preloaded and never lazy, the expected primary buttons', async ( { page } ) => {
      const response = await page.goto( p.path, { waitUntil: 'domcontentloaded' } );
      expect( response?.status() ).toBe( 200 );

      await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
      await expect( page.locator( 'h1' ) ).toHaveText( p.h1 );

      const img = page.locator( '.ot-way-hero img' );
      await expect( img ).toHaveAttribute( 'fetchpriority', 'high' );
      await expect( img ).not.toHaveAttribute( 'loading', 'lazy' );
      await expect( img ).toHaveAttribute( 'width', '1280' );
      await expect( page.locator( 'link[rel="preload"][as="image"]' ) ).toHaveCount( 2 );

      await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( p.primaries );
      // One primary per section: no section holds two.
      for ( const count of await page.locator( 'main section' ).evaluateAll( ( els ) => els.map( ( el ) => el.querySelectorAll( '.ot-btn--primary' ).length ) ) ) {
        expect( count ).toBeLessThanOrEqual( 1 );
      }

      // The stylesheets for these pages, and none of the tours script.
      await expect( page.locator( 'link[rel="stylesheet"][href*="ways.css"]' ) ).toHaveCount( 1 );
      await expect( page.locator( 'script[src*="tours.js"]' ) ).toHaveCount( 0 );
    } );

    test( 'the schema is a Service, a FAQPage that matches the accordion, and a breadcrumb', async ( { page } ) => {
      await page.goto( p.path, { waitUntil: 'domcontentloaded' } );

      const graphs = await page.locator( 'script[type="application/ld+json"]' ).allTextContents();
      const nodes = graphs.flatMap( ( text ) => {
        const data = JSON.parse( text );
        return Array.isArray( data[ '@graph' ] ) ? data[ '@graph' ] : [ data ];
      } );

      const service = nodes.find( ( n ) => n[ '@type' ] === 'Service' );
      expect( service, 'no Service node' ).toBeTruthy();
      expect( service[ '@id' ] ).toContain( p.path + '#service' );

      const faq = nodes.find( ( n ) => n[ '@type' ] === 'FAQPage' );
      expect( faq, 'no FAQPage node' ).toBeTruthy();
      const questions = await page.locator( '.ot-way-faq .ot-faq__item .ot-accordion__title' ).allInnerTexts();
      expect( questions.length ).toBeGreaterThanOrEqual( 5 );
      expect( faq.mainEntity.map( ( q: { name: string } ) => q.name ) ).toEqual( questions );

      const crumbs = nodes.find( ( n ) => n[ '@type' ] === 'BreadcrumbList' );
      expect( crumbs, 'no BreadcrumbList node' ).toBeTruthy();
      expect( crumbs.itemListElement ).toHaveLength( 2 );

      // The nodes this page adds claim nothing the site does not hold
      // (CLIA and Nexion only). The sitewide Organization/Person nodes are
      // the plugin's and are checked elsewhere.
      for ( const node of [ service, faq, crumbs ] ) {
        const text = JSON.stringify( node );
        expect( text ).not.toContain( 'Silversea' );
        expect( text ).not.toContain( 'BritAgent' );
      }
    } );

    if ( p.type ) {
      test( `Start planning is pre-set to type=${ p.type }`, async ( { page } ) => {
        await page.goto( p.path, { waitUntil: 'domcontentloaded' } );
        const hero = page.locator( '.ot-way-hero .ot-btn--primary' );
        await expect( hero ).toHaveAttribute( 'href', new RegExp( `/start-planning/\\?type=${ p.type }$` ) );
        await expect( page.locator( '.ot-closing .ot-btn--primary' ) ).toHaveAttribute( 'href', new RegExp( `/start-planning/\\?type=${ p.type }$` ) );
      } );
    }
  } );
}

test.describe( 'custom journeys', () => {
  test( 'every published destination is a card, and the two suppliers are named (D25)', async ( { page } ) => {
    await page.goto( '/custom-journeys/', { waitUntil: 'domcontentloaded' } );

    // The fixture publishes Italy only; the other nine stay drafts and must not appear.
    const cards = page.locator( '.ot-way-destinations .ot-card-dest' );
    await expect( cards ).toHaveCount( 1 );
    await expect( cards.first() ).toHaveAttribute( 'href', /\/destinations\/italy\/$/ );

    const suppliers = page.locator( '.ot-way-suppliers' );
    await expect( suppliers ).toContainText( 'Avanti Destinations' );
    await expect( suppliers ).toContainText( 'Classic Vacations' );
    await expect( suppliers ).not.toContainText( 'Abercrombie' );
    // The fixture publishes every operator, so both names link to their pages.
    await expect( suppliers.locator( 'a[href*="/avanti-destinations/"]' ) ).toHaveCount( 1 );
    await expect( suppliers.locator( 'a[href*="/classic-vacations/"]' ) ).toHaveCount( 1 );

    await expect( page.locator( '.ot-way-stories .ot-quote' ) ).toHaveCount( 2 );
    await expect( page.locator( '.ot-way-fees' ) ).toContainText( 'no planning fee' );
  } );
} );

test.describe( 'resorts and villas', () => {
  test( 'hotel perks are conditional and kept apart from the villa promise (D40)', async ( { page } ) => {
    await page.goto( '/resorts-and-villas/', { waitUntil: 'domcontentloaded' } );

    const hotels = page.locator( '.ot-way-adds__block--hotels' );
    const villas = page.locator( '.ot-way-adds__block--villas' );
    await expect( hotels ).toHaveCount( 1 );
    await expect( villas ).toHaveCount( 1 );

    await expect( hotels ).toContainText( 'I can usually add' );
    await expect( hotels ).toContainText( 'depending on the hotel' );
    await expect( hotels ).not.toContainText( 'you will receive' );

    await expect( villas ).toContainText( 'professionally managed homes' );
    await expect( villas ).toContainText( 'do not apply to a private house' );
    await expect( villas ).not.toContainText( 'I can usually add' );

    const body = await page.locator( 'main' ).innerText();
    expect( body ).not.toMatch( /virtuoso/i );
    expect( body ).not.toMatch( /Fairmont|Royal Hawaiian/ );

    await expect( page.locator( '.ot-way-destinations .ot-card-dest' ) ).toHaveCount( 1 );
    await expect( page.locator( '.ot-dest-quote .ot-quote' ) ).toHaveCount( 1 );
  } );
} );

test.describe( 'multi-generational trips', () => {
  test( 'no client story, and the ship line hands off to CruiseOomph with the UTM tag', async ( { page } ) => {
    await page.goto( '/multi-generational-travel-planning/', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '.ot-quote' ) ).toHaveCount( 0 );
    const ship = page.locator( '.ot-dest-ship a' );
    await expect( ship ).toHaveCount( 1 );
    await expect( ship ).toHaveAttribute( 'href', /cruiseoomph\.com\/cruises\/.*utm_source=oomphtravel/ );
    await expect( page.locator( '.ot-way-items .ot-way-item' ) ).toHaveCount( 6 );
  } );
} );

test.describe( 'cruise planning', () => {
  test( 'every CruiseOomph link is tagged, the land button opens Start planning, and the credential strip is CLIA and Nexion only', async ( { page } ) => {
    await page.goto( '/cruise-planning/', { waitUntil: 'domcontentloaded' } );

    const outbound = page.locator( 'main a[href*="cruiseoomph.com"]' );
    expect( await outbound.count() ).toBeGreaterThanOrEqual( 5 );
    for ( const href of await outbound.evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).href ) ) ) {
      expect( href ).toContain( 'utm_source=oomphtravel' );
      expect( href ).toContain( 'utm_campaign=cruise-planning' );
    }

    await expect( page.locator( '.ot-way-hero .ot-btn--primary' ) ).toHaveAttribute( 'href', /cruiseoomph\.com\/cruises\// );
    await expect( page.locator( '.ot-way-hero .ot-btn--primary' ) ).toHaveText( /Explore cruises/ );
    await expect( page.locator( '.ot-way-land .ot-btn--primary' ) ).toHaveAttribute( 'href', /\/start-planning\/\?type=cruise-land$/ );
    await expect( page.locator( '.ot-way-closing .ot-btn--primary' ) ).toHaveAttribute( 'href', /cruiseoomph\.com\/cruises\// );

    // The lockup, always the two-line form.
    const lockup = page.locator( '.ot-way-closing .ot-cruiseoomph__lockup' );
    await expect( lockup.locator( '.ot-cruiseoomph__name' ) ).toHaveText( 'CruiseOomph' );
    await expect( lockup.locator( '.ot-cruiseoomph__by' ) ).toHaveText( /By Oomph Travel/i );

    await expect( page.locator( '.ot-way-links .ot-way-link' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-credentials' ) ).toHaveCount( 1 );
    await expect( page.locator( '.ot-credentials' ) ).toContainText( 'CLIA' );
    await expect( page.locator( '.ot-credentials' ) ).toContainText( 'Nexion' );
    await expect( page.locator( '.ot-credentials' ) ).not.toContainText( 'Silversea' );
  } );
} );
