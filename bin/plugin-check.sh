#!/usr/bin/env bash
# Runs Plugin Check on the plugin as WordPress.org sees it and fails on any ERROR.
#
# - The whole repo is mapped into WordPress (.wp-env.json), so .pcpignore skips the same
#   development files as .distignore.
# - Plugin Check always exits with 0, so this script fails when the report has an ERROR row.
#
# Usage (wp-env must be running): bash bin/plugin-check.sh
set -euo pipefail

report="$(mktemp)"
trap 'rm -f "$report"' EXIT

npx @wordpress/env run cli wp plugin check asapchat \
	--exclude-directories=.ai,.github,docs,tests,vendor,node_modules \
	--use-pcpignore | tee "$report"

if grep -q $'\tERROR\t' "$report"; then
	echo "Plugin Check reported errors." >&2
	exit 1
fi

echo "Plugin Check: no errors."
