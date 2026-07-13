#!/usr/bin/env bash
# Installs dependencies and runs PHPUnit for one compatibility matrix entry.
set -euo pipefail

line="${1:?release line required}"
: "${2:?php version required}"

composer config audit.block-insecure false 2>/dev/null || true

composer_flags=(--no-interaction --prefer-dist)
if ! php -m | grep -qi '^zip$'; then
	composer_flags=(--no-interaction --prefer-source)
fi

case "$line" in
	8.x)
		composer install "${composer_flags[@]}" -q
		;;
	*)
		echo "Unknown or unsupported release line: $line" >&2
		echo "Versions 1.x and 2.x are deprecated; only 8.x is tested." >&2
		exit 1
		;;
esac

./vendor/bin/phpunit -c compat/phpunit.xml
