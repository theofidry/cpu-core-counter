#!/bin/sh

execute_finders() {
    php "$(pwd)/e2e/execute-finders.php"
}

execute_finders > $(pwd)/e2e/actual-output

diff --ignore-all-space --side-by-side --suppress-common-lines e2e/expected-output e2e/actual-output
