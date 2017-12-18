#!/bin/bash
#
# Run unit tests.
#

docker run -v "$(pwd)":/app phpunit/phpunit \
  --group myproject
