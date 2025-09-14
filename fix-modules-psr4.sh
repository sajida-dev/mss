#!/bin/bash

set -e

MODULES_DIR="Modules"
MODULES=$(find "$MODULES_DIR" -maxdepth 1 -mindepth 1 -type d)

echo "=== Moving App/ to src/ and updating namespaces for all modules ==="

for MODULE in $MODULES; do
    if [ -d "$MODULE/App" ]; then
        echo "Moving $MODULE/App to $MODULE/src"
        mv "$MODULE/App" "$MODULE/src"
    fi

    # Update namespaces and use statements in all PHP files
    find "$MODULE/src" -type f -name "*.php" | while read -r FILE; do
        # Remove \App\ from namespace and use statements
        sed -i 's/\\App\\/\\/g' "$FILE"
        sed -i 's/namespace \(Modules\\[A-Za-z0-9_]*\)\\App/namespace \1/g' "$FILE"
        sed -i 's/use \(Modules\\[A-Za-z0-9_]*\)\\App/use \1/g' "$FILE"
    done
done

echo "=== Done moving and updating namespaces. ==="
echo "Now update your composer.json autoload section as described, then run:"
echo "    composer dump-autoload"
echo "    php artisan optimize:clear"
echo "    php artisan package:discover"