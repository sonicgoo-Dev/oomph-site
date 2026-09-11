import { test, expect } from '@playwright/test';

/**
 * The homepage (plan §6.1), rendered by templates/front-page.html.
 *
 * These guard the rules the page must keep whatever its copy becomes: the
 * hero is the LCP image and is never lazy, the page has exactly two primary
 * buttons (hero and closing band), nothing on it names a sailing, a cabin or
 * a ship, and the tab row degrades to two visible panels without JavaScript.
 */

test.describe( 'homepage', () => {
  test( 'renders the hero with the H1 and the one Start planning button', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const hero = page.locator( '.ot-hero' );
    await expect( hero ).toBeVisible();
    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( hero.locator( 'h1' ) ).toContainText( 'The trip' );

    const cta = hero.locator( '.ot-btn--primary' );
    await expect( cta ).toHaveCount( 1 );
    await expect( cta ).toHaveAttribute( 'href', /\/start-planning\/?$/ );
  } );

  test( 'the hero photograph is the LCP image: preloaded, high priority, never lazy', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const img = page.locator( '.ot-hero__picture img' );
    await expect( img ).toHaveAttribute( 'fetchpriority', 'high' );
    await expect( img ).not.toHaveAttribute( 'loading', 'lazy' );
    await expect( img ).toHaveAttribute( 'width', /\d+/ );
    await expect( img ).toHaveAttribute( 'height', /\d+/ );

    const preloads = page.locator( 'link[rel="preload"][as="image"][fetchpriority="high"]' );
    await expect( preloads ).toHaveCount( 2 );
  } );

  test( 'exactly two primary buttons: the hero and the closing band', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const primaries = page.locator( 'main .ot-btn--primary' );
    await expect( primaries ).toHaveCount( 2 );
    await expect( primaries.first() ).toBeVisible();
    await expect( page.locator( '.ot-closing .ot-btn--primary' ) ).toHaveCount( 1 );
  } );

  test( 'nothing on the homepage names a sailing, a cabin or a ship', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );
    const text = await page.locator( 'main' ).innerText();

    expect( text ).not.toMatch( /\b(cabin|suite|sailing|stateroom|Silver Nova|Silver Moon|deck plan)\b/i );
  } );

  test( 'the Where to / How to travel row is a tablist with one panel showing', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'load' } );

    const tabs = page.locator( '.ot-tabs [role="tab"]' );
    await expect( tabs ).toHaveCount( 2 );
    await expect( tabs.nth( 0 ) ).toHaveAttribute( 'aria-selected', 'true' );
    await expect( page.locator( '#ot-panel-where' ) ).toBeVisible();
    await expect( page.locator( '#ot-panel-how' ) ).toBeHidden();

    await tabs.nth( 1 ).click();
    await expect( tabs.nth( 1 ) ).toHaveAttribute( 'aria-selected', 'true' );
    await expect( page.locator( '#ot-panel-how' ) ).toBeVisible();
    await expect( page.locator( '#ot-panel-where' ) ).toBeHidden();

    await tabs.nth( 1 ).press( 'ArrowLeft' );
    await expect( tabs.nth( 0 ) ).toHaveAttribute( 'aria-selected', 'true' );
    await expect( page.locator( '#ot-panel-where' ) ).toBeVisible();
  } );

  test( 'without JavaScript both panels show and no tab buttons are offered', async ( { browser } ) => {
    const context = await browser.newContext( { javaScriptEnabled: false } );
    const page = await context.newPage();
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '#ot-panel-where' ) ).toBeVisible();
    await expect( page.locator( '#ot-panel-how' ) ).toBeVisible();
    await expect( page.locator( '.ot-tabs__list' ) ).toBeHidden();

    await context.close();
  } );

  test( 'the two client quotations are on the page, unreworded', async ( { page } ) => {
    await page.goto( '/', { waitUntil: 'domcontentloaded' } );

    const quotes = page.locator( '.ot-stories .ot-quote' );
    await expect( quotes ).toHaveCount( 2 );
    await expect( quotes.nth( 0 ) ).toContainText( 'From the moment we booked' );
    await expect( quotes.nth( 1 ) ).toContainText( 'What stood out most' );
  } );
} );
