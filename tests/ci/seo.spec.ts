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

/**
 * The Organization and Person nodes (plugin class-schema.php, class-advisor.php)
 * describe the business as it is since the repositioning (D01): land travel
 * first, cruises through CruiseOomph, and the profiles the footer links. The
 * cruise-era wording was still in the graph three weeks after launch (SEO
 * audit 2026-10-08, A2).
 */
test.describe( 'organization and advisor schema', () => {
  test( 'the homepage graph describes land travel, links the profiles, and no longer leads with cruises', async ( { page } ) => {
    await page.goto( '/' );
    const nodes = await page.locator( 'script[type="application/ld+json"]' ).evaluateAll( ( els ) =>
      els.flatMap( ( el ) => { const j = JSON.parse( el.textContent || '{}' ); return j[ '@graph' ] || [ j ]; } )
    );
    const org = nodes.find( ( n ) => n[ '@type' ] === 'TravelAgency' && n.description );
    expect( org ).toBeTruthy();
    expect( org.description ).toMatch( /^Custom journeys, escorted tours/ );
    expect( org.sameAs ).toEqual( expect.arrayContaining( [ 'https://www.instagram.com/oomph_travel/' ] ) );
    expect( org.knowsAbout[ 0 ] ).not.toMatch( /cruise/i );

    // The home page is the trail's first item; it carries no breadcrumb of its own.
    expect( nodes.find( ( n ) => n[ '@type' ] === 'BreadcrumbList' ) ).toBeUndefined();

    const person = nodes.find( ( n ) => n[ '@type' ] === 'Person' && n[ '@id' ] && n[ '@id' ].endsWith( '/about/#advisor' ) );
    expect( person ).toBeTruthy();
    expect( person.jobTitle ).toContain( 'Travel Advisor' );
    expect( person.jobTitle ).not.toContain( 'Luxury Travel Advisor' );
    expect( person.sameAs ).toEqual( [ 'https://www.linkedin.com/in/erichempeloomphtravel/' ] );
    expect( person.knowsAbout ).not.toContain( 'Luxury cruising' );
  } );
} );

/**
 * The share image (inc/share-image.php): every page carries og:image and
 * twitter:image. Rank Math is not installed in CI, so these are the theme's
 * own tags; on the site they only appear when Rank Math printed none.
 */
test.describe( 'share image', () => {
  for ( const [ path, expected ] of [
    [ '/', /hero-varenna-1280\.webp$/ ],
    [ '/about/', /share-varenna-1200x630\.jpg$/ ],
    [ '/custom-journeys/', /\.webp$/ ],
  ] as const ) {
    test( `${ path } has og:image and twitter:image`, async ( { page } ) => {
      await page.goto( path, { waitUntil: 'domcontentloaded' } );
      console.log( 'HEAD-DEBUG ' + path + ' ' + ( await page.evaluate( () => document.head.innerHTML ) ).replace( /\s+/g, ' ' ).slice( 0, 6000 ) );
      await expect( page.locator( 'meta[property="og:image"]' ) ).toHaveAttribute( 'content', expected );
      await expect( page.locator( 'meta[name="twitter:image"]' ) ).toHaveAttribute( 'content', expected );
    } );
  }
} );
