#!/bin/bash
#
# Lint php files.
#

docker run -v "$(pwd)"/src:/code dcycle/php-lint \
  --standard=DrupalPractice /code
docker run -v "$(pwd)"/src:/code dcycle/php-lint \
  --standard=Drupal /code
