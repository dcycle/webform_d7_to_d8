#!/bin/bash
#
# Run all tests and linting.
#

set -e

./scripts/lint-php.sh
./scripts/lint-sh.sh
./scripts/unit-tests.sh
