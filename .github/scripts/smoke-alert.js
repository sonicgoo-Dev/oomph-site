'use strict';

/**
 * Says so when the smoke tests fail, and stops saying so when they pass.
 *
 * Called by .github/workflows/e2e.yml after the test step. The suite never
 * blocks a deploy, so until this existed a failure was a red mark on a page
 * nobody had a reason to open: the nightly run against the live site failed
 * for eighteen days in a row.
 *
 * One issue per site, found by its title:
 *
 *   failed, no issue open   ->  open one, listing the tests that failed
 *   failed, issue open      ->  rewrite its body with the latest run; comment
 *                               only if WHAT is failing changed, so a fault
 *                               left for a week is one email, not seven
 *   passed, issue open      ->  comment and close it
 *   passed, no issue        ->  nothing
 *
 * Every GitHub call is inside a try. An alert that cannot be raised must not
 * turn a passing run red, and must not hide the real result of a failing one.
 */

const fs = require('fs');
const path = require('path');

const LABEL = 'smoke-test';
const MAX_ERROR = 1500;

/** Every failing test in Playwright's json report, with its first error. */
function failures(report) {
  const found = [];
  const walk = (suite, trail) => {
    const here = suite.title && !/\.spec\.ts$/.test(suite.title) ? trail.concat(suite.title) : trail;
    for (const spec of suite.specs || []) {
      if (spec.ok) continue;
      let message = '';
      for (const t of spec.tests || []) {
        for (const r of t.results || []) {
          const e = (r.errors && r.errors[0]) || r.error;
          if (e && e.message && !message) message = e.message;
        }
      }
      found.push({
        name: here.concat(spec.title).join(' › '),
        file: spec.file || suite.file || '',
        // Colour codes are for a terminal and are noise in an issue.
        error: message.replace(/\u001b\[[0-9;]*m/g, '').trim().slice(0, MAX_ERROR),
      });
    }
    for (const child of suite.suites || []) walk(child, here);
  };
  for (const suite of report.suites || []) walk(suite, []);
  return found;
}

function readReport() {
  try {
    const file = path.join(process.env.GITHUB_WORKSPACE || '.', 'smoke-results.json');
    return failures(JSON.parse(fs.readFileSync(file, 'utf8')));
  } catch (err) {
    return null; // no report: the run died before Playwright could write one
  }
}

module.exports = async function smokeAlert({ github, context, core }) {
  const outcome = process.env.SMOKE_OUTCOME;
  const site = process.env.SMOKE_SITE || '';
  const host = site.replace(/^https?:\/\//, '');
  const title = 'Smoke tests are failing on ' + host;
  const runUrl = context.serverUrl + '/' + context.repo.owner + '/' + context.repo.repo + '/actions/runs/' + context.runId;
  const today = new Date().toISOString().slice(0, 10);

  try {
    const open = await github.paginate(github.rest.issues.listForRepo, {
      ...context.repo,
      state: 'open',
      labels: LABEL,
      per_page: 100,
    });
    const issue = open.find((i) => i.title === title && !i.pull_request);

    if (outcome === 'success') {
      if (!issue) return;
      await github.rest.issues.createComment({
        ...context.repo,
        issue_number: issue.number,
        body: 'Passing again as of ' + today + '. [This run](' + runUrl + ') found nothing wrong, so this is closed.',
      });
      await github.rest.issues.update({ ...context.repo, issue_number: issue.number, state: 'closed' });
      core.info('Closed #' + issue.number);
      return;
    }

    if (outcome !== 'failure') return;

    const failed = readReport();
    const names = failed ? failed.map((f) => f.name).sort() : [];
    // What is failing, as one line the next run can compare itself against.
    const marker = '<!-- failing: ' + Buffer.from(names.join('|')).toString('base64') + ' -->';

    const lines = [];
    lines.push('The smoke tests failed against **' + site + '**.');
    lines.push('');
    if (!failed) {
      lines.push('The run ended before a report was written, so which test failed is only in the log.');
    } else if (failed.length === 0) {
      lines.push('The report lists no failing test, so the failure is in the run itself. The log has it.');
    } else {
      lines.push(failed.length === 1 ? 'One test failed:' : failed.length + ' tests failed:');
      for (const f of failed) {
        lines.push('');
        lines.push('**' + f.name + '**' + (f.file ? '  (' + '`' + f.file + '`' + ')' : ''));
        if (f.error) {
          lines.push('');
          lines.push('```');
          lines.push(f.error);
          lines.push('```');
        }
      }
    }
    lines.push('');
    lines.push('Latest failing run: ' + runUrl + ' (' + today + ')');
    lines.push('');
    lines.push('This issue is kept up to date by the workflow and closes itself on the first run that passes. The suite does not block deploys, so nothing else will stop for this.');
    lines.push('');
    lines.push(marker);
    const body = lines.join('\n');

    if (!issue) {
      const created = await github.rest.issues.create({ ...context.repo, title, body, labels: [LABEL] });
      core.info('Opened #' + created.data.number);
      // Assigned so that it reaches an inbox whatever the watch settings are.
      // An organisation cannot be assigned; that is not worth failing over.
      try {
        await github.rest.issues.addAssignees({
          ...context.repo,
          issue_number: created.data.number,
          assignees: [context.repo.owner],
        });
      } catch (err) {
        core.info('Not assigned: ' + err.message);
      }
      return;
    }

    const changed = !(issue.body || '').includes(marker);
    await github.rest.issues.update({ ...context.repo, issue_number: issue.number, body });
    if (changed) {
      await github.rest.issues.createComment({
        ...context.repo,
        issue_number: issue.number,
        body: 'What is failing changed on ' + today + '. The description above now lists it. Run: ' + runUrl,
      });
    }
    core.info('Updated #' + issue.number + (changed ? ', and said what changed' : ''));
  } catch (err) {
    core.warning('The smoke alert could not be raised: ' + err.message);
  }
};

module.exports.failures = failures;
