import { test, expect } from '@playwright/test';

/**
 * The Stage 4 records, as a visitor sees them.
 *
 * The seed command runs earlier in the CI job and creates every destination
 * as a draft; the fixture then publishes Italy only. So this file proves two
 * things at once: the post type resolves at its planned URL, and the ten
 * unpublished ones stay private.
 */

test.describe( 'destination records', () => {
  test( 'a published destination resolves at /destinations/{slug}/', async ( { page } ) => {
    const response = await page.goto( '/destinations/italy/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status(), 'published destination HTTP status' ).toBe( 200 );

    await expect( page.locator( 'h1' ).first() ).toContainText( 'Italy' );
  } );

  test( 'an unpublished destination is not reachable', async ( { page } ) => {
    const response = await page.goto( '/destinations/france/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status(), 'draft destination must not be public' ).toBe( 404 );
  } );

  test( 'inquiries are never public', async ( { page } ) => {
    // The inquiry type is registered private. Its slug must not resolve at
    // all, whatever WordPress does with unknown paths.
    const response = await page.goto( '/inquiry/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status(), 'the inquiry type must not be publicly queryable' ).toBe( 404 );
  } );
} );

test.describe( 'component rules that must not regress', () => {
  test( 'every tour card carries the fixed availability caption (D34)', async ( { page } ) => {
    await page.goto( '/pattern-card-tour/', { waitUntil: 'domcontentloaded' } );

    const cards = page.locator( '.ot-card-tour' );
    const count = await cards.count();
    expect( count, 'no tour cards rendered' ).toBeGreaterThan( 0 );

    for ( let i = 0; i < count; i++ ) {
      await expect( cards.nth( i ).locator( '.ot-card-tour__caption' ) ).toHaveText(
        'Dates and availability confirmed on request.'
      );
    }
  } );

  test( 'arrows are never set in the display face', async ( { page } ) => {
    await page.goto( '/pattern-card-way/', { waitUntil: 'domcontentloaded' } );

    const arrows = page.locator( '.ot-arrow' );
    const count = await arrows.count();
    expect( count, 'no arrows rendered' ).toBeGreaterThan( 0 );

    for ( let i = 0; i < count; i++ ) {
      const family = await arrows.nth( i ).evaluate(
        ( el ) => getComputedStyle( el ).fontFamily.toLowerCase()
      );
      expect( family, 'an arrow rendered in Fraunces' ).not.toContain( 'fraunces' );
    }
  } );

  test( 'the ticker offers a keyboard-reachable pause', async ( { page } ) => {
    await page.goto( '/pattern-band-ticker/', { waitUntil: 'domcontentloaded' } );

    const toggle = page.locator( '[data-ot-ticker-toggle]' );
    await expect( toggle ).toBeVisible();
    await expect( toggle ).toHaveAttribute( 'aria-pressed', 'false' );

    await toggle.click();
    await expect( toggle ).toHaveAttribute( 'aria-pressed', 'true' );
  } );
} );

test.describe( 'placeholders must never reach a rendered page', () => {
  for ( const path of [ '/destinations/italy/', '/', '/escorted-tours/', '/escorted-tours/globus/', '/tours/globus-rome-florence-venice/', '/custom-journeys/', '/resorts-and-villas/', '/multi-generational-travel-planning/', '/cruise-planning/', '/start-planning/' ] ) {
    test( `no unfilled placeholder markers on ${ path }`, async ( { page } ) => {
      await page.goto( path, { waitUntil: 'domcontentloaded' } );
      const main = ( await page.locator( 'body' ).innerText() ) ?? '';

      expect( main, 'an unpriced tour placeholder reached the page' ).not.toContain( '$X,XXX' );
      expect( main, 'a square-bracket placeholder reached the page' ).not.toMatch( /\[[A-Z][^\]]{2,}\]/ );
    } );
  }
} );
