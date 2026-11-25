#!/usr/bin/env bash
# Clears saved pages on the server after a deploy, a tour import or a removal.
# Runs ON the server, fed over SSH:  ssh … "bash -s -- <wordpress-root>" < .github/scripts/purge-caches.sh
#
# Production is on Cloudways (since 2026-11-22): Breeze keeps saved pages on
# disk and the server keeps its own page cache in front of WordPress, so both
# are cleared. The object cache (Redis) is left alone; nothing here needs it
# emptied. Staging is still a SiteGround copy, which keeps its old purge.
# Every step reports and carries on: a cache that could not be cleared only
# means pages refresh a little later.
set -u

cd "$1" || { echo "purge: cannot enter '$1' — skipping"; exit 0; }
echo "purge: WordPress root $(pwd -P)"

if wp plugin is-active breeze >/dev/null 2>&1; then
  wp breeze purge --cache=local || echo 'purge: Breeze purge failed — skipping'
  home=$(wp option get home)
  host=${home#*://}
  host=${host%%/*}
  # Asked of the server itself, so the request never leaves the machine.
  curl -s -o /dev/null -m 20 --resolve "${host}:443:127.0.0.1" \
    -w 'purge: server page cache answered HTTP %{http_code}\n' \
    -X PURGE -H 'x-purge-method: regex' "https://${host}/.*" \
    || echo 'purge: server page cache purge failed — skipping'
else
  wp sg purge || echo 'purge: SG Optimizer not active — skipping wp sg purge'
  wp cache flush || echo 'purge: wp cache flush failed — skipping'
fi
