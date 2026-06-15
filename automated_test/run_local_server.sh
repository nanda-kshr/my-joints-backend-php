#!/bin/zsh
set -euo pipefail
cd "$(dirname "$0")/.."
exec php -S localhost:8000 index.php
