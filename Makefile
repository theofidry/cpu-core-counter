# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

.DEFAULT_GOAL := default

SRC_TESTS_FILES=$(shell find src/ tests/ -type f) phpunit.xml.dist
COVERAGE_DIR = .build/coverage
COVERAGE_XML = $(COVERAGE_DIR)/xml
COVERAGE_JUNIT = $(COVERAGE_DIR)/phpunit.junit.xml
COVERAGE_HTML = $(COVERAGE_DIR)/html
COVERAGE_INFECTION = $(COVERAGE_XML) $(COVERAGE_JUNIT)
TARGET_MSI = 100

INFECTION_BIN = tools/infection
INFECTION = $(INFECTION_BIN) --skip-initial-tests --coverage=$(COVERAGE_DIR) --only-covered --show-mutations --ansi
INFECTION_WITH_INITIAL_TESTS = $(INFECTION_BIN) --only-covered --show-mutations --ansi

PHPUNIT_BIN = vendor/bin/phpunit
PHPUNIT = php -d zend.enable_gc=0 $(PHPUNIT_BIN)
PHPUNIT_COVERAGE_INFECTION = XDEBUG_MODE=coverage $(PHPUNIT) --coverage-xml=$(COVERAGE_XML) --log-junit=$(COVERAGE_JUNIT)
PHPUNIT_COVERAGE_HTML = XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html=$(COVERAGE_HTML)

PHIVE_BIN = tools/phive
PHIVE = $(PHIVE_BIN)

PHP_CS_FIXER_BIN = tools/php-cs-fixer
PHP_CS_FIXER = $(PHP_CS_FIXER_BIN) fix --ansi --verbose --config=.php-cs-fixer.php

COMPOSER_NORMALIZE_BIN = tools/composer-normalize
COMPOSER_NORMALIZE = $(COMPOSER_NORMALIZE_BIN) normalize

YAMLLINT = yamllint

PHPSTAN_BIN = vendor/bin/phpstan
PHPSTAN = $(PHPSTAN_BIN)


#
# Commands
#---------------------------------------------------------------------------

.PHONY: help
help:
	@printf "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'

.PHONY: default
default:   ## Runs the default task: CS fix and all the tests
default: security cs autoreview test

.PHONY: diagnose
diagnose:	## Executes a diagnosis for all the finders
diagnose: vendor
	./bin/diagnose.php

.PHONY: execute
execute:	## Executes all the finders
execute: vendor
	./bin/execute.php

.PHONY: phive
phive:	## Updates a (registered) tool. E.g. make phive TOOL=infection
phive: $(PHIVE_BIN)
	$(PHIVE) update $(TOOL)

.PHONY: cs
cs: 	   ## Fixes CS
cs: gitignore_sort composer_normalize php_cs_fixer yaml_lint

.PHONY: cs_lint
cs_lint:   ## Lints CS
cs_lint: composer_normalize_lint php_cs_fixer_lint yaml_lint

.PHONY: gitignore_sort
gitignore_sort:
	LC_ALL=C sort -u .gitignore -o .gitignore

.PHONY: composer_normalize
composer_normalize: $(COMPOSER_NORMALIZE_BIN)
	$(COMPOSER_NORMALIZE)

.PHONY: composer_normalize_lint
composer_normalize_lint: $(COMPOSER_NORMALIZE_BIN)
	$(COMPOSER_NORMALIZE) --dry-run

.PHONY: php_cs_fixer
php_cs_fixer: $(PHP_CS_FIXER_BIN) .build/php-cs-fixer
	$(PHP_CS_FIXER)

.PHONY: php_cs_fixer_lint
php_cs_fixer_lint: $(PHP_CS_FIXER_BIN) .build/php-cs-fixer
	$(PHP_CS_FIXER) --dry-run

.PHONY: yaml_lint
yaml_lint:
	# If yamllint is not installed check https://yamllint.readthedocs.io/en/stable/quickstart.html
	$(YAMLLINT) --config-file .yamllint.yaml --strict .


.PHONY: autoreview
autoreview:	## Runs the AutoReview tests
autoreview: composer_validate phpstan

.PHONY: composer_validate
composer_validate:
	composer validate --strict

.PHONY: phpstan
phpstan: phpstan_src phpstan_tests

.PHONY: phpstan_src
phpstan_src: $(PHPSTAN_BIN) .build/phpstan/src
	$(PHPSTAN) clear-result-cache --configuration=phpstan.src.neon
	$(PHPSTAN) --configuration=phpstan.src.neon --memory-limit=-1

.PHONY: phpstan_tests
phpstan_tests: $(PHPSTAN_BIN) .build/phpstan/tests
	$(PHPSTAN) clear-result-cache --configuration=phpstan.tests.neon
	$(PHPSTAN) --configuration=phpstan.tests.neon --memory-limit=-1


.PHONY: test
test:	## Runs all the tests
test: infection

.PHONY: phpunit
phpunit:   ## Runs PHPUnit
phpunit: $(PHPUNIT_BIN) vendor
	$(PHPUNIT)

.PHONY: phpunit_coverage_infection
phpunit_coverage_infection: ## Runs PHPUnit with code coverage for Infection
phpunit_coverage_infection: $(PHPUNIT_BIN) vendor
	rm $(COVERAGE_INFECTION) || true
	$(PHPUNIT_COVERAGE_INFECTION)

.PHONY: phpunit_coverage_html
phpunit_coverage_html: ## Runs PHPUnit with code coverage with HTML report
phpunit_coverage_html: $(PHPUNIT_BIN) vendor
	$(PHPUNIT_COVERAGE_HTML)
	@echo "You can check the report by opening the file \"$(COVERAGE_HTML)/index.html\"."

.PHONY: infection
infection: ## Runs infection
infection: $(INFECTION_BIN) vendor
	$(INFECTION_WITH_INITIAL_TESTS)

.PHONY: _infection
_infection: $(INFECTION_BIN) $(COVERAGE_XML) $(COVERAGE_JUNIT) vendor
	$(INFECTION)

.PHONY: e2e
e2e: vendor
	./e2e/test-finders.sh

.PHONY: security
security:	## Runs the security check
security: composer_audit

.PHONY: composer_audit
composer_audit: ## Runs a security analysis with Composer
composer_audit:
	composer audit


#
# Rules
#---------------------------------------------------------------------------

# Vendor does not depend on the composer.lock since the later is not tracked
# or committed.
vendor: composer.json
	composer update --no-scripts
	touch -c $@
	touch -c $(PHPUNIT_BIN)
	touch -c $(PHPSTAN_BIN)

$(PHPUNIT_BIN): vendor
	touch -c $@

$(COVERAGE_XML): $(PHPUNIT_BIN) $(SRC_TESTS_FILES) phpunit.xml.dist
	$(PHPUNIT_COVERAGE_INFECTION)
	touch -c $@
	touch -c $(COVERAGE_JUNIT)

$(COVERAGE_JUNIT): $(PHPUNIT_BIN) $(SRC_TESTS_FILES) phpunit.xml.dist
	$(PHPUNIT_COVERAGE_INFECTION)
	touch -c $@
	touch -c $(COVERAGE_XML)

# PHP-CS-Fixer itself does not depend on the vendor but the config file yes
$(PHP_CS_FIXER_BIN): $(PHIVE_BIN) vendor
	$(PHIVE) install php-cs-fixer
	touch -c $@

$(INFECTION_BIN): $(PHIVE_BIN)
	$(PHIVE) install infection
	touch -c $@

$(PHIVE_BIN):
	./.phive/install-phive
	# Do not touch there: would mess up with the CI

$(PHPSTAN_BIN): vendor
	touch -c $@

$(COMPOSER_NORMALIZE_BIN): $(PHIVE_BIN)
	$(PHIVE) install composer-normalize
	touch -c $@

.build/php-cs-fixer:
	mkdir -p .build/php-cs-fixer/

.build/phpstan/src:
	mkdir -p .build/phpstan/src

.build/phpstan/tests:
	mkdir -p .build/phpstan/tests
