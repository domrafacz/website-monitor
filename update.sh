#!/bin/bash

git pull origin master

php_versions=("php8.1" "php8.2" "php8.3" "php81" "php82" "php83", "php")

for php_cmd in "${php_versions[@]}"; do
    if command -v $php_cmd &> /dev/null; then
        echo "Using PHP executable: $php_cmd"
        $php_cmd bin/console cache:clear
        $php_cmd bin/console doctrine:schema:update --force
        composer install
        exit 0
    fi
done
