default: tests lint

test: tests

tests:
	composer run-script test
	
lint:
	phpcs 

phpstan:
	./vendor/bin/phpstan analyse src/main/php/libAllure/


.PHONY: test tests default lint
