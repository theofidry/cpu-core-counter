#!/bin/sh

execute_finders() {
    php -d open_basedir="$(cd .. && pwd):$(php -r 'echo sys_get_temp_dir();')" -d disable_functions="pcntl_exec,pcntl_fork,exec,passthru,proc_open,shell_exec,system,popen" "$(pwd)/e2e/execute-finders.php" 2>&1
}

execute_finders > $(pwd)/e2e/actual-output

diff --ignore-all-space e2e/expected-output e2e/actual-output
