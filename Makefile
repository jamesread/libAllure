default: tests lint

test: tests
phpunit: tests

tests:
	composer run-script test

compat:
	./scripts/run-compat-tests.sh

compat-native:
	./scripts/run-compat-tests.sh --native
	
lint: phpcs

phpcs: 
	./vendor/bin/phpcs

phpstan:
	./vendor/bin/phpstan analyse --level 5 src/main/php/libAllure/

docs:
	doxygen


.PHONY: test tests default lint compat compat-native
