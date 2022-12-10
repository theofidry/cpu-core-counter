#!/bin/sh

execute_finders() {
    php "$(pwd)/e2e/execute-finders.php" 2>&1
}

execute_finders > $(pwd)/e2e/actual-output

diff --ignore-all-space e2e/expected-output e2e/actual-output
