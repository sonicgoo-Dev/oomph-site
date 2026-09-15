import { test, expect } from '@playwright/test';

/**
 * The 404 page and the search results it leads to (plan §6.16).
 *
 * Every deleted cruise address lands on the 404 (D02), so it is a page
 * with somewhere to go: the symbol, one line, a search field, the three
 * doors. The search button is the page's one primary action. The results
 * page renders the main query as plain rows, and an empty search shows
 * the doors again.
 */
test.describe( 'the 404 page', () => {
  test( 'is a real 404 with the symbol, one H1, the search field and three doors', async ( { page } ) => {
    const response = await page.goto( '/this-page-took-a-wrong-turn/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 404 );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'This page took a wrong turn.' );
    await expect( page.locator( '.ot-404__symbol' ) ).toHaveCount( 1 );

    const form = page.locator( 'form.ot-search' );
    await expect( form ).toHaveCount( 1 );
    await expect( form ).toHaveAttribute( 'method', /get/i );
    await expect( form.locator( 'input[name="s"]' ) ).toBeVisible();
    await expect( form.locator( 'input[name="s"]' ) ).toHaveAttribute( 'id', /.+/ );
    await expect( page.locator( 'label[for="' + ( await form.locator( 'input[name="s"]' ).getAttribute( 'id' ) ) + '"]' ) ).toHaveCount( 1 );

    const doors = page.locator( '.ot-doors__door' );
    await expect( doors ).toHaveCount( 3 );
    await expect( doors.nth( 0 ).locator( 'a' ) ).toHaveAttribute( 'href', /\/custom-journeys\/$/ );
    await expect( doors.nth( 1 ).locator( 'a' ) ).toHaveAttribute( 'href', /\/escorted-tours\/$/ );
    await expect( doors.nth( 2 ).locator( 'a' ) ).toHaveAttribute( 'href', /\/resorts-and-villas\/$/ );
    await expect( doors.locator( 'h2' ) ).toHaveCount( 3 );

    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 1 );
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveAttribute( 'type', 'submit' );
    await expect( page.locator( 'link[rel="stylesheet"][href*="utility.css"]' ) ).toHaveCount( 1 );
    await expect( page.locator( 'header' ).first() ).toBeVisible();
    await expect( page.locator( 'footer' ).first() ).toBeAttached();
  } );

  test( 'the search field leads to results for the words typed', async ( { page } ) => {
    await page.goto( '/no-such-page/', { waitUntil: 'domcontentloaded' } );
    await page.locator( 'form.ot-search input[name="s"]' ).fill( 'Italy' );
    await page.locator( 'form.ot-search button[type="submit"]' ).click();
    await page.waitForLoadState( 'domcontentloaded' );

    expect( new URL( page.url() ).searchParams.get( 's' ) ).toBe( 'Italy' );
    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toContainText( 'Results for “Italy”' );
    await expect( page.locator( '.ot-search-result' ).first() ).toBeVisible();
    // The Italy destination is published by the fixture and is searchable.
    await expect( page.locator( '.ot-search-result__title a[href$="/destinations/italy/"]' ) ).toHaveCount( 1 );
    await expect( page.locator( 'form.ot-search input[name="s"]' ) ).toHaveValue( 'Italy' );
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 0 );
    await expect( page.locator( 'meta[name="robots"]' ).first() ).toHaveAttribute( 'content', /noindex/ );
  } );

  test( 'a search with no matches shows the three doors again, under h3s', async ( { page } ) => {
    const response = await page.goto( '/?s=zzqx-nothing-matches-this', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );
    await expect( page.locator( '.ot-search-empty' ) ).toBeVisible();
    await expect( page.locator( '.ot-search-empty__title' ) ).toHaveText( 'Nothing matched.' );
    await expect( page.locator( '.ot-doors__door' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-doors h3' ) ).toHaveCount( 3 );
    await expect( page.locator( '.ot-doors h2' ) ).toHaveCount( 0 );
  } );

  test( 'private inquiry records never appear in search', async ( { page } ) => {
    await page.goto( '/?s=oomph_inquiry', { waitUntil: 'domcontentloaded' } );
    const hrefs = await page.locator( '.ot-search-result__title a' ).evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).pathname ) );
    for ( const href of hrefs ) {
      expect( href ).not.toMatch( /inquir/ );
    }
  } );
} );
