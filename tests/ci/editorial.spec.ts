import { test, expect, type Page } from '@playwright/test';

/**
 * The editorial pages (plan §6.11 About, §6.12 Client stories, §6.13
 * Journal index and article, §6.14 Travel Trends guide).
 *
 * Four page records from the seed with empty bodies; the theme's templates
 * mount the patterns. The CI fixture adds a second advisor user (amy) and
 * two posts, one by each advisor, so the byline, the author node and the
 * destination card can be checked against something real.
 */

async function graphNodes( page: Page ) {
  const graphs = await page.locator( 'script[type="application/ld+json"]' ).allTextContents();
  return graphs.flatMap( ( text ) => {
    const data = JSON.parse( text );
    return Array.isArray( data[ '@graph' ] ) ? data[ '@graph' ] : [ data ];
  } );
}

async function onePrimaryPerSection( page: Page ) {
  for ( const count of await page.locator( 'main section' ).evaluateAll( ( els ) => els.map( ( el ) => el.querySelectorAll( '.ot-btn--primary' ).length ) ) ) {
    expect( count ).toBeLessThanOrEqual( 1 );
  }
}

test.describe( '/about/', () => {
  test( 'renders: one H1, the portrait first, the credential strip, Amy with her own anchor', async ( { page } ) => {
    const response = await page.goto( '/about/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'I plan the trips I’d take myself.' );

    const portrait = page.locator( '.ot-about-hero img' );
    await expect( portrait ).toHaveAttribute( 'fetchpriority', 'high' );
    await expect( portrait ).not.toHaveAttribute( 'loading', 'lazy' );
    await expect( page.locator( 'link[rel="preload"][as="image"]' ) ).toHaveCount( 1 );

    await expect( page.locator( '.ot-credentials' ) ).toBeVisible();
    await expect( page.locator( 'main' ) ).not.toContainText( 'Silversea' );
    await expect( page.locator( 'main' ) ).not.toContainText( 'BritAgent' );

    const amy = page.locator( '#amy' );
    await expect( amy ).toBeVisible();
    await expect( amy ).toContainText( 'Amy Hempel' );
    await expect( amy ).toContainText( 'Travel Leaders Network' );

    // Her portrait is below the fold, so it loads late, unlike the hero's.
    const portraitAmy = amy.locator( '.ot-about-team__portrait' );
    await expect( portraitAmy ).toHaveAttribute( 'loading', 'lazy' );
    await expect( portraitAmy ).toHaveAttribute( 'width', '720' );
    await expect( amy.locator( '.ot-about-team__monogram' ) ).toHaveCount( 0 );

    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 2 );
    await onePrimaryPerSection( page );
    await expect( page.locator( 'link[rel="stylesheet"][href*="editorial.css"]' ) ).toHaveCount( 1 );
  } );

  test( 'the schema carries both advisors as Person nodes, and the org', async ( { page } ) => {
    await page.goto( '/about/', { waitUntil: 'domcontentloaded' } );
    const nodes = await graphNodes( page );

    const people = nodes.filter( ( n ) => n[ '@type' ] === 'Person' );
    expect( people.map( ( n ) => n[ '@id' ] ).sort() ).toEqual( [
      expect.stringMatching( /\/about\/#advisor$/ ),
      expect.stringMatching( /\/about\/#amy$/ ),
    ].sort() );

    const amy = people.find( ( n ) => String( n[ '@id' ] ).endsWith( '#amy' ) );
    expect( amy.name ).toBe( 'Amy Hempel' );
    expect( amy.description ).toContain( 'real estate agent' );
    expect( amy.worksFor[ '@id' ] ).toMatch( /#organization$/ );
    expect( amy.image ).toContain( 'advisor-amy-720.webp' );

    expect( nodes.find( ( n ) => n[ '@type' ] === 'FAQPage' ) ).toBeUndefined();
    expect( nodes.find( ( n ) => n[ '@type' ] === 'Service' ) ).toBeUndefined();
  } );
} );

test.describe( '/client-stories/', () => {
  test( 'the four reviews, word for word, each with the CruiseOomph line', async ( { page } ) => {
    const response = await page.goto( '/client-stories/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveText( 'In their words.' );
    await expect( page.locator( '.ot-story' ) ).toHaveCount( 4 );
    await expect( page.locator( '.ot-story .ot-quote' ) ).toHaveCount( 4 );

    // D38: never reworded. One sentence from each, as posted.
    const main = await page.locator( 'main' ).innerText();
    for ( const line of [
      'From the moment we booked, our travel agent made the entire process smooth and stress-free.',
      'the highlights of our trip were actually the moments he planned before we set sail and after we returned.',
      'Eric spent time with me on the phone and then 45 minutes with NCL getting it corrected.',
      'What stood out most was his outstanding communication;',
    ] ) {
      expect( main ).toContain( line );
    }

    // Every story is about a cruise, so every story hands off, tagged.
    const links = page.locator( '.ot-story a[href*="cruiseoomph.com"]' );
    await expect( links ).toHaveCount( 4 );
    for ( const href of await links.evaluateAll( ( els ) => els.map( ( el ) => ( el as HTMLAnchorElement ).href ) ) ) {
      expect( href ).toContain( 'utm_source=oomphtravel' );
      expect( href ).toContain( 'utm_campaign=client-stories' );
    }

    // No stars: the rating is words.
    await expect( page.locator( '.ot-story' ).first() ).toContainText( 'Rated 5 of 5' );
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 1 );
  } );

  test( 'Review and AggregateRating describe the same four stories', async ( { page } ) => {
    await page.goto( '/client-stories/', { waitUntil: 'domcontentloaded' } );
    const nodes = await graphNodes( page );

    const org = nodes.find( ( n ) => n[ '@type' ] === 'TravelAgency' && Array.isArray( n.review ) );
    expect( org, 'no review node' ).toBeTruthy();
    expect( org.review ).toHaveLength( 4 );
    expect( org.aggregateRating.ratingValue ).toBe( '5.0' );
    expect( org.aggregateRating.reviewCount ).toBe( '4' );
    expect( org.review.map( ( r: { author: { name: string } } ) => r.author.name ) ).toEqual( [ 'LCMurray', 'travellover52', 'Ak Chris', 'Gary T.' ] );
  } );
} );

test.describe( '/journal/', () => {
  test( 'the index lists the posts as cards, with the topic tabs', async ( { page } ) => {
    const response = await page.goto( '/journal/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveText( 'Notes from the road.' );
    const cards = page.locator( '.ot-journal-grid .ot-card-journal' );
    expect( await cards.count() ).toBeGreaterThanOrEqual( 2 );
    await expect( cards.filter( { hasText: 'Puglia in May' } ) ).toHaveCount( 1 );

    // "All" and "Destinations": the two topics that exist. No empty tabs.
    const tabs = page.locator( '.ot-journal-tabs__tab' );
    await expect( tabs ).toHaveText( [ 'All', 'Destinations' ] );
    await expect( tabs.first() ).toHaveAttribute( 'aria-current', 'page' );
    await expect( page.locator( 'meta[name="robots"]' ) ).not.toHaveAttribute( 'content', /noindex/ );
  } );

  test( 'a chosen topic filters the grid and is not indexed', async ( { page } ) => {
    await page.goto( '/journal/?topic=destinations', { waitUntil: 'domcontentloaded' } );

    await expect( page.locator( '.ot-journal-tabs__tab.is-current' ) ).toHaveText( 'Destinations' );
    await expect( page.locator( '.ot-journal-grid__filter' ) ).toContainText( 'Showing Destinations' );
    await expect( page.locator( '.ot-journal-grid .ot-card-journal' ) ).toHaveCount( 2 );
    await expect( page.locator( 'meta[name="robots"]' ) ).toHaveAttribute( 'content', /noindex/ );
  } );

  test( 'an article: byline from the profile, At a glance, the destination card, the author node', async ( { page } ) => {
    await page.goto( '/journal/', { waitUntil: 'domcontentloaded' } );
    await page.locator( '.ot-card-journal__link' ).filter( { hasText: 'Puglia in May' } ).click( { force: true } );
    await page.waitForLoadState( 'domcontentloaded' );

    await expect( page.locator( 'h1' ) ).toHaveText( 'Puglia in May: the week that works' );
    const author = page.locator( '.ot-article__author' );
    await expect( author ).toHaveAttribute( 'href', /\/about\/$/ );
    await expect( page.locator( '.ot-article__glance' ) ).toContainText( 'Three nights in Lecce' );
    await expect( page.locator( '.ot-article__body h2' ) ).toHaveText( 'Where to base' );
    await expect( page.locator( '.ot-article__destination .ot-card-dest' ) ).toHaveAttribute( 'href', /\/destinations\/italy\/$/ );
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 1 );

    const nodes = await graphNodes( page );
    const article = nodes.find( ( n ) => n[ '@type' ] === 'BlogPosting' );
    expect( article, 'no BlogPosting node' ).toBeTruthy();
    expect( article.author[ '@id' ] ).toMatch( /\/about\/#advisor$/ );
    expect( article.articleSection ).toBe( 'Destinations' );
    expect( nodes.filter( ( n ) => n[ '@type' ] === 'Person' ) ).toHaveLength( 1 );
  } );

  test( 'a post by the second advisor names her and points its author node at her', async ( { page } ) => {
    await page.goto( '/journal/', { waitUntil: 'domcontentloaded' } );
    await page.locator( '.ot-card-journal__link' ).filter( { hasText: 'What I pack' } ).click( { force: true } );
    await page.waitForLoadState( 'domcontentloaded' );

    const author = page.locator( '.ot-article__author' );
    await expect( author ).toHaveText( 'Amy Hempel' );
    await expect( author ).toHaveAttribute( 'href', /\/about\/#amy$/ );

    const nodes = await graphNodes( page );
    const article = nodes.find( ( n ) => n[ '@type' ] === 'BlogPosting' );
    expect( article.author[ '@id' ] ).toMatch( /\/about\/#amy$/ );
    const people = nodes.filter( ( n ) => n[ '@type' ] === 'Person' ).map( ( n ) => String( n[ '@id' ] ) );
    expect( people.some( ( id ) => id.endsWith( '#amy' ) ) ).toBe( true );
  } );
} );

test.describe( '/travel-trends/', () => {
  test( 'the guide page: the cover first, the H1 with the year, one form that posts to PlainSend', async ( { page } ) => {
    const response = await page.goto( '/travel-trends/', { waitUntil: 'domcontentloaded' } );
    expect( response?.status() ).toBe( 200 );

    await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
    await expect( page.locator( 'h1' ) ).toHaveText( 'Travel Trends 2026: what I’m watching' );
    await expect( page.locator( '.ot-trends-hero__teasers li' ) ).toHaveCount( 5 );

    const cover = page.locator( '.ot-trends-hero__cover img' );
    await expect( cover ).toHaveAttribute( 'fetchpriority', 'high' );
    await expect( cover ).toHaveAttribute( 'width', '960' );
    await expect( page.locator( 'link[rel="preload"][as="image"]' ) ).toHaveCount( 1 );

    // The one form in the hero: a single email field (R43), the trends form,
    // its own source for GA4, and the double opt-in success line (D30).
    const form = page.locator( '.ot-trends-hero form.oomph-signup' );
    await expect( form ).toHaveCount( 1 );
    await expect( form ).toHaveAttribute( 'action', /\/f\/trends-guide-staging$/ );
    await expect( form ).toHaveAttribute( 'data-source', 'trends_guide' );
    await expect( form ).toHaveAttribute( 'data-success', 'Check your email and press the button, then the guide is on its way.' );
    await expect( form.locator( 'input[type="email"]' ) ).toHaveCount( 1 );
    await expect( form.locator( 'input:not([type="hidden"]):not([name="website"])' ) ).toHaveCount( 1 );
    await expect( form.locator( '.ot-btn--primary' ) ).toHaveText( /Send me the guide/ );

    // The hero's primary is the submit; the closing band has the other.
    await expect( page.locator( 'main .ot-btn--primary' ) ).toHaveCount( 2 );
    await onePrimaryPerSection( page );

    // The footer's own newsletter form is unchanged and separate.
    await expect( page.locator( '.ot-footer form.oomph-signup' ) ).toHaveAttribute( 'action', /\/f\/newsletter-staging$/ );

    const nodes = await graphNodes( page );
    expect( nodes.find( ( n ) => n[ '@type' ] === 'BreadcrumbList' ) ).toBeTruthy();
    expect( nodes.find( ( n ) => n[ '@type' ] === 'FAQPage' ) ).toBeUndefined();
  } );
} );
