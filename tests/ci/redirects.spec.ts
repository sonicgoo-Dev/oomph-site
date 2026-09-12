import { test, expect } from '@playwright/test';

/**
 * The redirects (plan §8.4, D43), served from Redirects in the plugin so
 * they hold without a Rank Math table to keep in step.
 *
 *   - the three moved pages, and the broken /contact rule, go to their new
 *     addresses permanently, keeping any query string
 *   - anything under /group-cruises/ hands off to CruiseOomph's cruise
 *     index with the UTM tag (D43); nothing deeper is guessed at
 *   - the deleted pages are not redirected (D02): they land on the 404
 */
const MOVED: Array<[ string, RegExp ]> = [
  [ '/custom-italy-travel/', /\/destinations\/italy\/$/ ],
  [ '/luxury-cruise-planning/', /\/cruise-planning\/$/ ],
  [ '/discovery-call/', /\/start-planning\/$/ ],
  [ '/contact/', /\/start-planning\/$/ ],
];

test.describe( 'redirects', () => {
  for ( const [ from, to ] of MOVED ) {
    test( `${ from } is a 301 to its new address`, async ( { request } ) => {
      const response = await request.get( from, { maxRedirects: 0 } );
      expect( response.status() ).toBe( 301 );
      expect( response.headers()[ 'location' ] ).toMatch( to );
    } );
  }

  test( 'the old address without its trailing slash, and in capitals, still redirects', async ( { request } ) => {
    for ( const from of [ '/discovery-call', '/Discovery-Call/' ] ) {
      const response = await request.get( from, { maxRedirects: 0 } );
      expect( response.status(), from ).toBe( 301 );
      expect( response.headers()[ 'location' ], from ).toMatch( /\/start-planning\/$/ );
    }
  } );

  test( 'a query string survives the move', async ( { request } ) => {
    const response = await request.get( '/discovery-call/?utm_source=instagram', { maxRedirects: 0 } );
    expect( response.status() ).toBe( 301 );
    expect( response.headers()[ 'location' ] ).toMatch( /\/start-planning\/\?utm_source=instagram$/ );
  } );

  test( '/group-cruises/ and everything under it hands off to CruiseOomph (D43)', async ( { request } ) => {
    for ( const from of [ '/group-cruises/', '/group-cruises/mediterranean-2027/', '/group-cruises/anything/at/all/' ] ) {
      const response = await request.get( from, { maxRedirects: 0 } );
      expect( response.status(), from ).toBe( 301 );
      const location = response.headers()[ 'location' ] ?? '';
      expect( location, from ).toMatch( /^https:\/\/cruiseoomph\.com\/cruises\/\?/ );
      expect( location, from ).toContain( 'utm_source=oomphtravel' );
      expect( location, from ).toContain( 'utm_medium=site' );
      expect( location, from ).toContain( 'utm_campaign=group-cruises' );
    }
  } );

  test( 'a look-alike address is not caught by the prefix', async ( { request } ) => {
    const response = await request.get( '/group-cruises-old/', { maxRedirects: 0 } );
    expect( response.status() ).toBe( 404 );
  } );

  test( 'the deleted pages are not redirected (D02): they are 404s', async ( { request } ) => {
    for ( const from of [ '/trip-quiz/', '/cruise-travel-trends/', '/journal/silversea-vs-regent/' ] ) {
      const response = await request.get( from, { maxRedirects: 0 } );
      expect( response.status(), from ).toBe( 404 );
    }
  } );
} );
