#!/bin/bash
#
# Lint shell scripts.
#

find . -name "*.sh" -print0 | \
  xargs -0 docker run -v "$(pwd)":/code dcycle/shell-lint
