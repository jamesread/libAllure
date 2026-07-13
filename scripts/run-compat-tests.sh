#!/usr/bin/env bash
# Run libAllure release-line tests against supported PHP versions.
# Uses Docker when the requested PHP version does not match the local runtime.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MATRIX="${ROOT}/compat/matrix.json"
PHPUNIT_CONFIG="${ROOT}/compat/phpunit.xml"
INNER="${ROOT}/scripts/compat-test-inner.sh"
BOOTSTRAP="${ROOT}/scripts/compat-docker-bootstrap.sh"
WORKTREE_ROOT="${ROOT}/.compat-worktrees"
USE_DOCKER=1
FILTER_LINE=""
FILTER_PHP=""

usage() {
	cat <<'EOF'
Usage: scripts/run-compat-tests.sh [options]

Run the compatibility test matrix locally. By default every line/PHP pair
from compat/matrix.json is executed. When the host PHP version does not match,
tests run inside the official php:<version>-cli Docker image.

Options:
  --line <name>   Only run a release line (e.g. 8.x)
  --php <version> Only run a PHP version (e.g. 8.2)
  --native        Never use Docker; skip pairs where PHP is unavailable
  -h, --help      Show this help
EOF
}

while [[ $# -gt 0 ]]; do
	case "$1" in
		--line) FILTER_LINE="$2"; shift 2 ;;
		--php) FILTER_PHP="$2"; shift 2 ;;
		--native) USE_DOCKER=0; shift ;;
		-h|--help) usage; exit 0 ;;
		*) echo "Unknown option: $1" >&2; usage >&2; exit 1 ;;
	esac
done

if [[ ! -f "$MATRIX" ]]; then
	echo "Missing matrix file: $MATRIX" >&2
	exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
	echo "jq is required to read compat/matrix.json" >&2
	exit 1
fi

local_php_version() {
	php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;'
}

prepare_worktree() {
	local ref="$1"
	local slug="$2"
	local dir="${WORKTREE_ROOT}/${slug}"

	mkdir -p "$WORKTREE_ROOT"

	if [[ -d "$dir" ]]; then
		git -C "$ROOT" worktree remove --force "$dir" 2>/dev/null || rm -rf "$dir"
	fi

	git -C "$ROOT" worktree add --force "$dir" "$ref" >/dev/null

	echo "$dir"
}

sync_compat_files() {
	local tree="$1"
	mkdir -p "${tree}/compat" "${tree}/scripts"
	if [[ "$tree" != "$ROOT" ]]; then
		cp "$PHPUNIT_CONFIG" "${tree}/compat/phpunit.xml"
		cp "$INNER" "${tree}/scripts/compat-test-inner.sh"
		cp "$BOOTSTRAP" "${tree}/scripts/compat-docker-bootstrap.sh"
		chmod +x "${tree}/scripts/compat-test-inner.sh" "${tree}/scripts/compat-docker-bootstrap.sh"
	fi
}

run_with_docker() {
	local line="$1"
	local php_version="$2"
	local ref="$3"
	local slug="${line//./-}-${php_version}"
	local tree
	tree="$(prepare_worktree "$ref" "$slug")"
	sync_compat_files "$tree"

	set +e
	docker run --rm \
		-v "${tree}:/app" \
		-w /app \
		"php:${php_version}-cli" \
		./scripts/compat-docker-bootstrap.sh "$line" "$php_version"
	local docker_status=$?
	set -e

	chown -R "$(id -u):$(id -g)" "$tree" 2>/dev/null || \
		docker run --rm -v "${tree}:/app" alpine chown -R "$(id -u):$(id -g)" /app

	return "$docker_status"
}

should_run_pair() {
	local line="$1"
	local php="$2"

	if [[ -n "$FILTER_LINE" && "$line" != "$FILTER_LINE" ]]; then
		return 1
	fi
	if [[ -n "$FILTER_PHP" && "$php" != "$FILTER_PHP" ]]; then
		return 1
	fi
	return 0
}

FAILED=0
RAN=0
LOCAL_PHP="$(local_php_version)"

while IFS=$'\t' read -r line ref php_version; do
	if ! should_run_pair "$line" "$php_version"; then
		continue
	fi

	echo "==> ${line} @ ${ref} on PHP ${php_version}"

	if [[ "$USE_DOCKER" -eq 0 && "$LOCAL_PHP" != "$php_version" ]]; then
		echo "Skipping PHP ${php_version}; host is PHP ${LOCAL_PHP} and --native was set" >&2
		continue
	fi

	if [[ "$USE_DOCKER" -eq 1 && "$LOCAL_PHP" != "$php_version" ]]; then
		if ! command -v docker >/dev/null 2>&1; then
			echo "Docker is required to run PHP ${php_version} tests" >&2
			FAILED=1
			continue
		fi
		if ! run_with_docker "$line" "$php_version" "$ref"; then
			FAILED=1
		fi
	else
		if [[ "$ref" == "HEAD" ]]; then
			tree="$ROOT"
		else
			slug="${line//./-}-${php_version}-native"
			tree="$(prepare_worktree "$ref" "$slug")"
		fi
		sync_compat_files "$tree"
		if ! (cd "$tree" && ./scripts/compat-test-inner.sh "$line" "$php_version"); then
			FAILED=1
		fi
	fi

	RAN=$((RAN + 1))
done < <(jq -r '.lines[] | .name as $n | .ref as $r | .php[] | [$n, $r, .] | @tsv' "$MATRIX")

if [[ "$RAN" -eq 0 ]]; then
	echo "No matrix entries matched the requested filters." >&2
	exit 1
fi

if [[ "$FAILED" -ne 0 ]]; then
	echo "Compatibility tests failed." >&2
	exit 1
fi

echo "All compatibility tests passed (${RAN} matrix entries)."
