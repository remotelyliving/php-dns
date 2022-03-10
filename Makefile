build:
	@make dependencies && make dependency-check && make static-analysis && make style-check && make unit-tests && make integration-tests

dependencies:
	@composer install

unit-tests:
	@vendor/bin/paratest -p8 --runner=WrapperRunner --bootstrap=./tests/bootstrap.php --testsuite Unit

integration-tests:
	@vendor/bin/paratest -p8 --runner=WrapperRunner --bootstrap=./tests/bootstrap.php --testsuite Integration

test-coverage-ci:
	@mkdir -p ./build/logs && ./vendor/bin/phpunit -c phpunit.xml.dist --coverage-clover ./build/logs/clover.xml && php vendor/bin/php-coveralls --root_dir=. -v

test-coverage-html:
	@vendor/bin/paratest -p8 --runner=WrapperRunner --bootstrap=./tests/bootstrap.php --coverage-html ./coverage

style-check:
	@vendor/bin/phpcs --standard=PSR12 ./src/* ./tests/*

dependency-check:
	@vendor/bin/composer-require-checker check -vvv ./composer.json

churn-report:
	@vendor/bin/churn run

static-analysis:
	@vendor/bin/phpstan analyze --level=8 ./src && ./vendor/bin/psalm --show-info=false

style-fix:
	@vendor/bin/phpcbf --standard=PSR12 ./src ./tests

repl:
	@vendor/bin/psysh bootstrap/repl.php