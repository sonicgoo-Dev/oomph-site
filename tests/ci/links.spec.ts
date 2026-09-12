import { test, expect, type Page } from '@playwright/test';

/**
 * The link-in-bio page, /links/ (plan §6.16, §4.3).
 *
 * The one address the Instagram profile points at: eleven links, the same
 * count and shape as the page it replaces, two of them to CruiseOomph with
 * the UTM tag. noindex, follow on every robots tag (WordPress's and Rank
 * Math's both print one; the invariant is that no tag permits indexing).
 * The CI fixture publishes two posts, so the featured card has something
 * real to follow.
 */

async function onePrimaryPerSection( page: Page ) {
  for ( const count of await page.locator( 'main section, main .ot-links__cta' ).evaluateAll( ( els ) => els.map( ( el ) => el.querySelectorAll( '.ot-btn--primary' ).length ) ) ) {
    expect( count ).toBeLessThanOrEqual( 1 );
  }
}

test.describe( '/links/', () => {
  test( 'renders: one H1, the eleven links, the credentials line', async ( { page } ) => {
    const response = await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Everything, in one place.' );
    await expect( page.locator( '.ot-links' ) ).toBeVisible();

    // 1 card + 1 button + 4 rows + 5 text links.
    await expect( page.locator( '.ot-links .ot-card-journal__link' ) ).toHaveCount( 1 );
    await expect( page.locator( '.ot-links__cta .ot-btn--primary' ) ).toHaveCount( 1 );
    await expect( page.locator( 'a.ot-links__row' ) ).toHaveCount( 4 );
    await expect( page.locator( '.ot-links__more a' ) ).toHaveCount( 5 );
    await expect( page.locator( '.ot-links a[href]' ) ).toHaveCount( 11 );

    await expect( page.locator( '.ot-links__foot' ) ).toHaveText( 'CLIA Member · Nexion Affiliated' );
    await expect( page.locator( 'main' ) ).not.toContainText( 'Silversea' );
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 1 );
    await onePrimaryPerSection( page );
    await expect( page.locator( 'link[rel="stylesheet"][href*="utility.css"]' ) ).toHaveCount( 1 );
  } );

  test( 'the primary button goes to Start planning, with its microcopy', async ( { page } ) => {
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    const cta = page.locator( '.ot-links__cta .ot-btn--primary' );
    await expect( cta ).toHaveAttribute( 'href', /\/start-planning\/$/ );
    await expect( cta ).toContainText( 'Start planning' );
    await expect( page.locator( '.ot-links__cta-note' ) ).toHaveText( 'Email, text or a quick call, whatever’s easiest.' );
  } );

  test( 'exactly two links leave for CruiseOomph, tagged, in a new tab', async ( { page } ) => {
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    const out = page.locator( '.ot-links a[href*="cruiseoomph.com"]' );
    await expect( out ).toHaveCount( 2 );
    for ( const href of await out.evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).href ) ) ) {
      expect( href ).toContain( 'utm_source=oomphtravel' );
      expect( href ).toContain( 'utm_medium=site' );
      expect( href ).toContain( 'utm_campaign=links' );
    }
    for ( let i = 0; i < 2; i++ ) {
      await expect( out.nth( i ) ).toHaveAttribute( 'target', '_blank' );
      await expect( out.nth( i ) ).toHaveAttribute( 'rel', /noopener/ );
    }
    expect( await out.evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).pathname ) ) ).toEqual( [ '/', '/find-my-cruise/' ] );
  } );

  test( 'follows the newest Journal post, loaded eagerly', async ( { page } ) => {
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    const card = page.locator( '.ot-links .ot-card-journal' );
    await expect( card ).toHaveCount( 1 );

    // The fixture's newest post is the one the Journal index puts first.
    await page.goto( '/journal/', { waitUntil: 'domcontentloaded' } );
    const newest = await page.locator( '.ot-card-journal__link' ).first().getAttribute( 'href' );
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    await expect( card.locator( '.ot-card-journal__link' ) ).toHaveAttribute( 'href', newest ?? '' );

    const img = card.locator( 'img' );
    if ( await img.count() ) {
      await expect( img ).toHaveAttribute( 'fetchpriority', 'high' );
      await expect( img ).not.toHaveAttribute( 'loading', 'lazy' );
      await expect( page.locator( 'link[rel="preload"][as="image"]' ) ).toHaveCount( 1 );
    }
  } );

  test( 'is noindex, follow on every robots tag', async ( { page } ) => {
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    const robots = page.locator( 'meta[name="robots"]' );
    const count = await robots.count();
    expect( count ).toBeGreaterThan( 0 );
    for ( let i = 0; i < count; i++ ) {
      const content = ( await robots.nth( i ).getAttribute( 'content' ) ) ?? '';
      expect( content, `robots tag ${ i + 1 }/${ count }` ).toMatch( /\bnoindex\b/ );
      expect( content, `robots tag ${ i + 1 }/${ count }` ).toMatch( /\bfollow\b/ );
    }
  } );

  test( 'every internal link resolves', async ( { page } ) => {
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    const hrefs = await page.locator( '.ot-links a[href]:not([href*="cruiseoomph.com"])' ).evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).href ) );
    expect( hrefs.length ).toBe( 9 );
    for ( const href of hrefs ) {
      const res = await page.request.get( href );
      expect( res.status(), `${ href } resolves` ).toBeLessThan( 400 );
    }
  } );

  test( '@mobile the page reads in one column and the button spans it', async ( { page } ) => {
    await page.goto( '/links/', { waitUntil: 'domcontentloaded' } );
    const inner = await page.locator( '.ot-links__inner' ).boundingBox();
    const cta = await page.locator( '.ot-links__cta .ot-btn--primary' ).boundingBox();
    expect( inner && cta && cta.width ).toBeGreaterThan( ( inner?.width ?? 0 ) * 0.8 );
  } );
} );
