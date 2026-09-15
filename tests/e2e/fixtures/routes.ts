/**
 * The public route inventory — single source of truth for the page-smoke,
 * schema, accessibility (tests/audit) and Lighthouse (scripts/audit) runs.
 * One entry per page *type* on the resurfaced site (design-handoff, plan §6).
 *
 * - `h1`      : text to match when the H1 is hardcoded in the template.
 *               Omitted where the H1 comes from a field (the destination
 *               headline, the About page) — those routes only assert that a
 *               visible, non-empty H1 exists.
 * - `types`   : JSON-LD @type values that must appear in the page's combined
 *               @graph. TravelAgency + Person are sitewide and asserted
 *               globally, so they are not repeated here. BreadcrumbList is on
 *               every singular page; the archives (Journal, Destinations,
 *               Escorted tours) carry none.
 * - `isStartPlanning`: the header button points here, so the "one visible
 *               primary button" check reads the form's own Continue button
 *               instead of a second header CTA.
 *
 * Retired routes (/discovery-call/, /luxury-cruise-planning/,
 * /custom-italy-travel/) redirect or hand off to CruiseOomph; they are covered
 * by tests/ci/redirects.spec.ts, not here.
 */

export interface RouteFixture {
  path: string;
  name: string;
  h1?: string | RegExp;
  types: string[];
  isStartPlanning?: boolean;
}

export const ROUTES: RouteFixture[] = [
  {
    path: '/',
    name: 'Home',
    types: ['BreadcrumbList'],
  },
  {
    path: '/destinations/',
    name: 'Destinations (index)',
    h1: /Where to/i,
    types: [], // archive — no BreadcrumbList
  },
  {
    path: '/destinations/italy/',
    name: 'Destination — Italy',
    types: ['TouristDestination', 'FAQPage', 'BreadcrumbList'],
  },
  {
    path: '/escorted-tours/',
    name: 'Escorted tours (index)',
    h1: /Escorted tours/i,
    types: [], // archive — no BreadcrumbList
  },
  {
    path: '/custom-journeys/',
    name: 'Way — Custom journeys',
    types: ['Service', 'FAQPage', 'BreadcrumbList'],
  },
  {
    path: '/resorts-and-villas/',
    name: 'Way — Resorts & villas',
    types: ['Service', 'FAQPage', 'BreadcrumbList'],
  },
  {
    path: '/multi-generational-travel-planning/',
    name: 'Way — Multi-generational',
    types: ['Service', 'FAQPage', 'BreadcrumbList'],
  },
  {
    path: '/cruise-planning/',
    name: 'Way — Cruise planning (hands off to CruiseOomph)',
    types: ['Service', 'FAQPage', 'BreadcrumbList'],
  },
  {
    path: '/start-planning/',
    name: 'Start planning',
    h1: 'What are you imagining?',
    types: ['BreadcrumbList'],
    isStartPlanning: true,
  },
  {
    path: '/about/',
    name: 'About',
    types: ['BreadcrumbList'],
  },
  {
    path: '/client-stories/',
    name: 'Client stories',
    h1: /In their words/i,
    types: ['Review', 'AggregateRating', 'BreadcrumbList'],
  },
  {
    path: '/journal/',
    name: 'Journal (archive)',
    h1: /Notes from the road/i,
    types: ['BreadcrumbList'],
  },
  {
    path: '/travel-trends/',
    name: 'Travel Trends (lead magnet)',
    h1: /Travel Trends/i,
    types: ['BreadcrumbList'],
  },
  {
    path: '/links/',
    name: 'Link-in-bio',
    h1: /everything, in one place/i,
    types: ['BreadcrumbList'],
  },
];
