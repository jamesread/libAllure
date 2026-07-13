#!/usr/bin/env bash
set -euo pipefail

line="${1:?release line required}"
php_version="${2:?php version required}"

if ! php -m | grep -qi '^zip$'; then
	if grep -q stretch /etc/apt/sources.list 2>/dev/null; then
		cat >/etc/apt/sources.list <<'EOF'
deb [check-valid-until=no] http://archive.debian.org/debian stretch main
EOF
	elif grep -q buster /etc/apt/sources.list 2>/dev/null; then
		cat >/etc/apt/sources.list <<'EOF'
deb [check-valid-until=no] http://archive.debian.org/debian buster main
deb [check-valid-until=no] http://archive.debian.org/debian-security buster/updates main
EOF
	fi
	if apt-get -o Acquire::Check-Valid-Until=false update -qq; then
		apt-get install -y -o Acquire::AllowInsecureRepositories=true -o Acquire::AllowDowngradeToInsecureRepositories=true --allow-unauthenticated -qq git unzip zip libzip-dev >/dev/null \
			|| apt-get install -y -o Acquire::AllowInsecureRepositories=true --allow-unauthenticated -qq git unzip >/dev/null \
			|| true
		docker-php-ext-install zip >/dev/null 2>&1 || true
	fi
fi

if ! command -v composer >/dev/null 2>&1; then
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

./scripts/compat-test-inner.sh "$line" "$php_version"
