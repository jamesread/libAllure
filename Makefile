default: tests lint

test: tests

tests:
	composer run-script test
	
lint:
	./vendor/bin/phpcs

phpstan:
	./vendor/bin/phpstan analyse src/main/php/libAllure/


.PHONY: test tests default lint
