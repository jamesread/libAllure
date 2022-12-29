default: tests lint

test: tests

tests:
	composer run-script test
	
lint:
	phpcs 


.PHONY: test tests default lint
