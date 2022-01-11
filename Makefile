default: tests lint

test: tests

tests:
	phpunit --coverage-html coverage --whitelist src/main/php/libAllure/
	
lint:
	phpcs 


.PHONY: test tests default lint
