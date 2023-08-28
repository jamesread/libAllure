default: tests lint

test: tests
phpunit: tests

tests:
	composer run-script test
	
lint: phpcs

phpcs: 
	./vendor/bin/phpcs

phpstan:
	./vendor/bin/phpstan analyse --level 5 src/main/php/libAllure/

docs:
	doxygen


.PHONY: test tests default lint
