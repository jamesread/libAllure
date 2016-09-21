default: tests lint

test: tests

tests:
	phpunit 
	
lint:
	phpcs --standard=PSR2 src/main/php/


.PHONY: test tests default lint
