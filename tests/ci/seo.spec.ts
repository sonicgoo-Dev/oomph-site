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
