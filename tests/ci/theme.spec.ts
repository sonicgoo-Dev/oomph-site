import { test, expect } from '@playwright/test';

/**
 * The oomphtravel block theme, rendered by a real WordPress.
 *
 * Stage 3 was verified through a stub harness that faked WordPress. These
 * tests are the first thing that proves the patterns, template parts and
 * enqueues survive contact with the real block editor pipeline.
 */

test.describe( 'theme chrome', () => {
  test( 'the homepage renders the header and footer parts', async ( { page } ) => {
    const response = await page.goto( '/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status(), 'homepage HTTP status' ).toBe( 200 );

    await expect( page.locator( '.ot-header' ) ).toBeVisible();
    await expect( page.locator( '.ot-footer' ) ).toBeVisible();
  } );

  test( 'the primary nav carries the four sections and the Start planning button', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const nav = page.locator( '.ot-header .ot-nav' );
    for ( const label of [ 'Destinations', 'Ways to travel', 'Journal', 'About' ] ) {
      await expect( nav.getByText( label, { exact: true } ).first() ).toBeVisible();
    }

    const cta = page.locator( '.ot-header__cta' ).first();
    await expect( cta ).toBeVisible();
    await expect( cta ).toHaveAttribute( 'href', /\/start-planning\/?/ );
  } );

  test( 'every CruiseOomph link carries the UTM tag', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const links = await page.locator( 'a[href*="cruiseoomph.com"]' ).evaluateAll( ( els ) =>
      els.map( ( el ) => ( el as HTMLAnchorElement ).href )
    );
    expect( links.length, 'no CruiseOomph links found on the page' ).toBeGreaterThan( 0 );

    for ( const href of links ) {
      expect( href, `missing utm_source on ${ href }` ).toContain( 'utm_source=oomphtravel' );
      expect( href, `missing utm_medium on ${ href }` ).toContain( 'utm_medium=site' );
      expect( href, `missing utm_campaign on ${ href }` ).toContain( 'utm_campaign=' );
    }
  } );

  test( 'no hex colours leak into the rendered stylesheet', async ( { page, request } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const hrefs = await page.locator( 'link[rel="stylesheet"][href*="/themes/oomphtravel/"]' ).evaluateAll(
      ( els ) => els.map( ( el ) => ( el as HTMLLinkElement ).href )
    );
    expect( hrefs.length, 'the theme loaded no stylesheet of its own' ).toBeGreaterThan( 0 );

    for ( const href of hrefs ) {
      const css = await ( await request.get( href ) ).text();
      // theme.json is the only place a literal colour may appear.
      const hex = css.match( /#[0-9a-fA-F]{3,8}\b/g ) ?? [];
      expect( hex, `hex colours in ${ href }: ${ hex.join( ', ' ) }` ).toHaveLength( 0 );
    }
  } );

  test( 'the page loads without console errors', async ( { page } ) => {
    const errors: string[] = [];
    page.on( 'console', ( msg ) => {
      if ( msg.type() === 'error' ) {
        errors.push( msg.text() );
      }
    } );
    page.on( 'pageerror', ( err ) => errors.push( err.message ) );

    await page.goto( '/', { waitUntil: 'load' } );
    expect( errors, `console errors: ${ errors.join( ' | ' ) }` ).toHaveLength( 0 );
  } );

  test( '@mobile the drawer toggle replaces the desktop nav', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const toggle = page.locator( '.ot-menu-toggle' );
    await expect( toggle ).toBeVisible();

    await toggle.click();
    await expect( page.locator( '#ot-drawer' ) ).toBeVisible();
  } );
} );
