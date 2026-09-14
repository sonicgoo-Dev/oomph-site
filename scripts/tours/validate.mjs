#!/usr/bin/env node
// Check a tour record (content/tours/<slug>.json) before it goes to the site.
//
// Mirrors the checks in the plugin's Tour_Import::validate() so a problem is
// caught here, on the developer's machine, rather than on the server. The
// plugin re-checks everything anyway; this exists because PHP is not
// installed locally.
//
//   node scripts/tours/validate.mjs content/tours/globus-italian-treasures.json
//
// Exit code 0 when the record is sound, 1 with one line per problem otherwise.

import { readFileSync } from 'node:fs';
import { basename } from 'node:path';

const OPERATORS = ['globus', 'tauck', 'insight-vacations', 'abercrombie-kent', 'national-geographic-expeditions', 'classic-vacations', 'avanti-destinations'];
const DESTINATIONS = ['italy', 'uk-ireland', 'france', 'spain', 'portugal', 'greece', 'croatia', 'hawaii', 'mexico', 'caribbean', 'africa'];
const TEXT = ['blurb', 'nights', 'start_city', 'end_city', 'from_price', 'price_note', 'group_size', 'pace', 'inclusions', 'brochure_link', 'erics_note'];
const KNOWN = ['slug', 'title', 'operator', 'destination', 'months', 'featured', 'itinerary', ...TEXT];
const PROSE = ['title', 'blurb', 'price_note', 'group_size', 'pace', 'inclusions', 'erics_note'];

// The No List as tests/e2e/content-guard.spec.ts spells it.
const NO_LIST = [
  'bespoke', 'wanderlust', 'magical', 'breathtaking', 'curated', 'jaw-dropping',
  'paradise', 'bucket list', 'hidden gem', 'iconic', 'stunning', 'ultimate',
  'epic', 'unforgettable', 'escape', 'getaway', 'luxe', '5-star', 'world-class',
  'indulge', 'pampered', 'transformative', 'white-glove', 'foodie', 'vibes',
  'once in a lifetime', 'dream destination', 'bestie', 'slay', 'obsessed',
  'hubby', 'adventure of a lifetime', 'pop of color', 'main character energy',
  'the best',
];
const NO_LIST_RE = new RegExp(`\\b(${NO_LIST.map((w) => w.replace(/[-\s]/g, '[\\s-]')).join('|')})\\b`, 'gi');
const PLACEHOLDER_RE = /\[[A-Z][^\]]{2,}\]|\$X,XXX/g;
// Superlatives and "we" are the voice guide's other two rules (R48, R50).
const SUPERLATIVE_RE = /\b(amazing|incredible|the (?:most|finest|greatest)|best[- ]known|must[- ]see)\b/gi;
const WE_RE = /\b(we|our|us)\b/gi;

const file = process.argv[2];
if (!file) {
  console.error('Usage: node scripts/tours/validate.mjs content/tours/<slug>.json');
  process.exit(2);
}

const problems = [];
const warnings = [];
let record;
try {
  record = JSON.parse(readFileSync(file, 'utf8'));
} catch (e) {
  console.error(`${file}: not valid JSON — ${e.message}`);
  process.exit(1);
}
if (typeof record !== 'object' || record === null || Array.isArray(record)) {
  console.error(`${file}: the top level must be an object`);
  process.exit(1);
}

const plain = (s) => String(s ?? '').replace(/<[^>]+>/g, ' ').replace(/&amp;/g, '&').replace(/&#8217;|&rsquo;/g, '’').replace(/&nbsp;/g, ' ');
const prose = (where, text) => {
  const t = plain(text);
  if (!t.trim()) return;
  const words = [...new Set((t.match(NO_LIST_RE) ?? []).map((w) => w.toLowerCase()))];
  if (words.length) problems.push(`${where} uses a No List word: ${words.join(', ')}.`);
  const ph = [...new Set(t.match(PLACEHOLDER_RE) ?? [])];
  if (ph.length) problems.push(`${where} has a placeholder: ${ph.join(' ')}.`);
  const sup = [...new Set((t.match(SUPERLATIVE_RE) ?? []).map((w) => w.toLowerCase()))];
  if (sup.length) warnings.push(`${where} has a superlative: ${sup.join(', ')} (R50 — rewrite unless it is a quotation).`);
  if (where === 'erics_note' && WE_RE.test(t)) warnings.push(`erics_note says "we" — Eric is one advisor; use "I" (R48).`);
};

const slug = String(record.slug ?? '');
if (!slug) problems.push('slug is missing.');
else if (!/^[a-z0-9]+(-[a-z0-9]+)*$/.test(slug)) problems.push(`slug "${slug}" is not lower-case letters, digits and hyphens.`);
else if (basename(file) !== `${slug}.json`) problems.push(`the file should be named ${slug}.json to match its slug.`);
if (slug && record.operator && !slug.startsWith(`${record.operator}-`)) warnings.push(`slug usually starts with the operator: "${record.operator}-…".`);

if (!String(record.title ?? '').trim()) problems.push('title is missing.');

if (!record.operator) problems.push('operator is missing (an operator slug, e.g. "globus").');
else if (!OPERATORS.includes(record.operator)) problems.push(`operator "${record.operator}" is not one of: ${OPERATORS.join(', ')}.`);

if (!record.destination) problems.push('destination is missing (a destination slug, e.g. "italy").');
else if (!DESTINATIONS.includes(record.destination)) problems.push(`destination "${record.destination}" is not one of: ${DESTINATIONS.join(', ')}.`);

if (record.nights === undefined || record.nights === '') problems.push('nights is missing.');
else if (!Number.isInteger(Number(record.nights)) || Number(record.nights) < 1) problems.push(`nights must be a whole number, not "${record.nights}".`);

if ('months' in record) {
  if (!Array.isArray(record.months)) problems.push('months must be a list of month numbers, 1 to 12.');
  else for (const m of record.months) if (!Number.isInteger(Number(m)) || m < 1 || m > 12) problems.push(`months: "${m}" is not a month number (1 to 12).`);
}

if (record.from_price !== undefined && record.from_price !== '' && record.from_price !== null) {
  if (typeof record.from_price !== 'number' || !Number.isInteger(record.from_price) || record.from_price < 0) {
    problems.push(`from_price must be a whole number of US dollars (no symbol, no commas), not ${JSON.stringify(record.from_price)}.`);
  }
}

if (record.brochure_link && !/^https?:\/\/\S+$/.test(record.brochure_link)) problems.push(`brochure_link "${record.brochure_link}" is not a full URL.`);

if ('itinerary' in record) {
  if (!Array.isArray(record.itinerary)) problems.push('itinerary must be a list of rows.');
  else {
    record.itinerary.forEach((row, i) => {
      const n = i + 1;
      if (typeof row !== 'object' || row === null) return problems.push(`itinerary row ${n} is not an object.`);
      if (!Number.isInteger(Number(row.day))) problems.push(`itinerary row ${n} needs a day number.`);
      if (!String(row.title ?? '').trim()) problems.push(`itinerary row ${n} needs a title.`);
      for (const sub of ['title', 'overnight', 'text']) prose(`itinerary row ${n} ${sub}`, row[sub]);
      for (const k of Object.keys(row)) if (!['day', 'title', 'overnight', 'text'].includes(k)) problems.push(`itinerary row ${n}: "${k}" is not a sub-field (day, title, overnight, text).`);
    });
    if (record.nights && record.itinerary.length && record.itinerary.length !== Number(record.nights) + 1) {
      warnings.push(`itinerary has ${record.itinerary.length} rows for ${record.nights} nights; a full itinerary has nights + 1 days.`);
    }
  }
}

for (const name of PROSE) prose(name, record[name]);
for (const k of Object.keys(record)) if (!KNOWN.includes(k)) problems.push(`"${k}" is not a tour field. Known: ${KNOWN.join(', ')}.`);
if (!record.erics_note) warnings.push('erics_note is empty — required before the tour can go live.');
if (!record.blurb) warnings.push('blurb is empty — the tour card will have no second line.');

for (const w of warnings) console.log(`warning: ${w}`);
for (const p of problems) console.log(`problem: ${p}`);
if (problems.length) {
  console.log(`${file}: ${problems.length} problem(s). Fix them before opening the pull request.`);
  process.exit(1);
}
console.log(`${file}: sound (${warnings.length} warning(s)).`);
