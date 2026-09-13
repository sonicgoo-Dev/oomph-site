import { test, expect } from '@playwright/test';

/**
 * /llms.txt (plugin SEO class): the plain-text site map for AI crawlers,
 * generated from the pages, the destinations, the tour index and the
 * journal. It is served on init after the post types register, so every
 * link is a real address — a `?p=` link means it ran too early.
 */
test.describe( '/llms.txt', () => {
  test( 'is plain text with real addresses for the destinations and the tour index', async ( { request } ) => {
    const response = await request.get( '/llms.txt' );
    expect( response.status() ).toBe( 200 );
    expect( response.headers()[ 'content-type' ] ).toMatch( /^text\/plain/ );

    const body = await response.text();
    expect( body ).toMatch( /^# Oomph Travel/ );
    expect( body ).toContain( '## Ways to travel' );
    expect( body ).toContain( '## Escorted tours' );
    expect( body ).toContain( '## Destinations' );
    expect( body ).toMatch( /\/destinations\/italy\/\)/ );
    expect( body ).toMatch( /\/escorted-tours\/\)/ );
    expect( body ).toContain( 'CruiseOomph (https://cruiseoomph.com)' );
    expect( body ).not.toMatch( /\?p=\d+/ );
    expect( body ).not.toContain( 'Silversea' );
    expect( body ).not.toContain( '/luxury-cruise-planning/' );
    expect( body ).not.toContain( '/discovery-call/' );
  } );
} );

/**
 * The tab icon (theme setup.php): the luggage symbol shipped with the theme,
 * printed by the theme itself, never alongside core's Site Icon tags.
 */
test.describe( 'tab icon', () => {
  test( 'the head links the SVG icon, the PNG fallbacks and the Apple touch icon, and they all load', async ( { page, request } ) => {
    await page.goto( '/' );
    const icons = page.locator( 'head link[rel="icon"], head link[rel="apple-touch-icon"]' );
    await expect( icons ).toHaveCount( 4 );
    await expect( page.locator( 'head link[rel="icon"][type="image/svg+xml"]' ) ).toHaveAttribute( 'href', /favicon\.svg/ );
    await expect( page.locator( 'head link[rel="apple-touch-icon"]' ) ).toHaveAttribute( 'sizes', '180x180' );
    await expect( page.locator( 'head link[rel="icon"][sizes="512x512"]' ) ).toHaveCount( 0 ); // core's Site Icon output stays off.

    for ( const href of await icons.evaluateAll( ( els ) => els.map( ( el ) => el.getAttribute( 'href' ) ) ) ) {
      const response = await request.get( href! );
      expect( response.status(), href! ).toBe( 200 );
      expect( response.headers()[ 'content-type' ], href! ).toMatch( /^image\/(svg\+xml|png)/ );
    }
  } );
} );
