default: tests lint

test: tests

tests:
	composer run-script test
	
lint: phpcs

phpcs: 
	./vendor/bin/phpcs

phpstan:
	./vendor/bin/phpstan analyse --level 1 src/main/php/libAllure/


.PHONY: test tests default lint
