#!/bin/bash

# Handler for Ctrl+C
cleanup() {
    docker ps -q --filter ancestor=jakzal/phpqa | xargs -r docker stop
    if [ -f composer.json.backup ]; then
        cp composer.json.backup composer.json
        rm composer.json.backup
    fi
    exit 1
}

trap cleanup SIGINT

# Help function
show_help() {
    echo "Usage: $0 [options]"
    echo "Options:"
    echo "  --php <version>      Specific PHP version (7.4|8.0|8.1|8.2|8.3)"
    echo "  --symfony <version>  Specific Symfony version (^4.0|^5.0|^6.0|^7.0)"
    echo "  --phpunit <version>  Specific PHPUnit version (^9.5|^10.1|^11.0|^12.0)"
    echo "  --debug             Show composer and test output"
    echo "  -h, --help          Show this help message"
    echo
    echo "Example:"
    echo "  $0 --php 8.1 --symfony ^6.0 --phpunit ^10.1"
    exit 0
}

# Parse command line arguments
PHP_FILTER=""
SYMFONY_FILTER=""
PHPUNIT_FILTER=""
DEBUG=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --php)
            PHP_FILTER="$2"
            shift 2
            ;;
        --symfony)
            SYMFONY_FILTER="$2"
            shift 2
            ;;
        --phpunit)
            PHPUNIT_FILTER="$2"
            shift 2
            ;;
        --debug)
            DEBUG=true
            shift
            ;;
        -h|--help)
            show_help
            ;;
        *)
            echo "Unknown option: $1"
            show_help
            ;;
    esac
done

# Modify the docker command execution part
QUIET_FLAGS=""
if [ "$DEBUG" = false ]; then
    QUIET_FLAGS="--quiet"
fi

# Associative array to store results
declare -A RESULTS

# Define versions to test
ALL_PHP_VERSIONS=("7.4" "8.0" "8.1" "8.2" "8.3")
ALL_SYMFONY_VERSIONS=("^4.0" "^5.0" "^6.0" "^7.0")
ALL_PHPUNIT_VERSIONS=("^9.5" "^10.1" "^11.0" "^12.0")

# Filter versions based on arguments
PHP_VERSIONS=(${PHP_FILTER:-${ALL_PHP_VERSIONS[@]}})
SYMFONY_VERSIONS=(${SYMFONY_FILTER:-${ALL_SYMFONY_VERSIONS[@]}})
PHPUNIT_VERSIONS=(${PHPUNIT_FILTER:-${ALL_PHPUNIT_VERSIONS[@]}})

echo "----------------------------------------"
echo "PHP versions: ${PHP_VERSIONS[@]}"
echo "Symfony versions: ${SYMFONY_VERSIONS[@]}"
echo "PHPUnit versions: ${PHPUNIT_VERSIONS[@]}"
echo "----------------------------------------"

# Validate provided versions
validate_version() {
    local version=$1
    local valid_versions=("${@:2}")
    if [[ -n "$version" ]]; then
        for valid_version in "${valid_versions[@]}"; do
            if [[ "$version" == "$valid_version" ]]; then
                return 0
            fi
        done
        echo "Invalid version: $version"
        show_help
    fi
}

validate_version "$PHP_FILTER" "${ALL_PHP_VERSIONS[@]}"
validate_version "$SYMFONY_FILTER" "${ALL_SYMFONY_VERSIONS[@]}"
validate_version "$PHPUNIT_FILTER" "${ALL_PHPUNIT_VERSIONS[@]}"

# Function to check PHP-Symfony-PHPUnit compatibility
check_compatibility() {
    local php_version=$1
    local symfony_version=$2
    local phpunit_version=$3

    # Symfony 7 requires PHP 8.2
    if [[ "$symfony_version" == "^7.0" && $(echo "$php_version < 8.2" | bc -l) == 1 ]]; then
        return 1
    fi

    # Symfony 6 requires PHP 8.0
    if [[ "$symfony_version" == "^6.0" && $(echo "$php_version < 8.0" | bc -l) == 1 ]]; then
        return 1
    fi

    # PHPUnit 10 requires PHP 8.1
    if [[ "$phpunit_version" == "^10.1" && $(echo "$php_version < 8.1" | bc -l) == 1 ]]; then
        return 1
    fi

    # PHPUnit 11 requires PHP 8.2
    if [[ "$phpunit_version" == "^11.0" && $(echo "$php_version < 8.2" | bc -l) == 1 ]]; then
        return 1
    fi

    # PHPUnit 12 requires PHP 8.3
    if [[ "$phpunit_version" == "^12.0" && $(echo "$php_version < 8.3" | bc -l) == 1 ]]; then
        return 1
    fi

    return 0
}

# Backup original composer.json
cp composer.json composer.json.backup

# For each PHP version
for php_version in "${PHP_VERSIONS[@]}"; do
    # For each Symfony version
    for symfony_version in "${SYMFONY_VERSIONS[@]}"; do
        # For each PHPUnit version
        for phpunit_version in "${PHPUNIT_VERSIONS[@]}"; do
            # Check compatibility
            if ! check_compatibility "$php_version" "$symfony_version" "$phpunit_version"; then
                # RESULTS["${php_version}|${symfony_version}|${phpunit_version}"]="SKIPPED"
                continue
            fi

            echo "🚀 Testing with PHP ${php_version}, Symfony ${symfony_version}, PHPUnit ${phpunit_version}"

            # Restore original composer.json
            cp composer.json.backup composer.json

            # Run tests in Docker container
            docker run --rm -v $(pwd):/app/ -v $(composer config --global cache-dir):/tools/.composer/cache -w /app jakzal/phpqa:php${php_version} bash -c "
                git config --global --add safe.directory /app && \
                composer config minimum-stability dev && \
                composer config prefer-stable true && \
                composer require --dev --no-update ${QUIET_FLAGS} \"symfony/serializer:${symfony_version}\" && \
                composer require --dev --no-update ${QUIET_FLAGS} \"symfony/property-access:${symfony_version}\" && \
                composer require --dev --no-update ${QUIET_FLAGS} \"symfony/yaml:${symfony_version}\" && \
                composer require --dev --no-update ${QUIET_FLAGS} \"phpunit/phpunit:${phpunit_version}\" && \
                composer update --no-interaction --with-all-dependencies --no-suggest --no-audit ${QUIET_FLAGS} && \
                php -d date.timezone=UTC vendor/bin/phpunit
            "

            # Store result
            if [ $? -ne 0 ]; then
                RESULTS["${php_version}|${symfony_version}|${phpunit_version}"]="FAILED"
                echo "❌ Tests failed"
            else
                RESULTS["${php_version}|${symfony_version}|${phpunit_version}"]="PASSED"
                echo "✅ Tests passed"
            fi

            echo "----------------------------------------"
        done
    done
done

# Restore original composer.json
cp composer.json.backup composer.json
rm composer.json.backup

# Display summary
echo -e "\n📊 Test Summary\n"

# Table header
printf "%-8s | %-12s | %-12s | %-10s\n" "PHP" "Symfony" "PHPUnit" "Status"
printf "%${COLUMNS:-$(tput cols)}s" | tr ' ' '-'
echo

# Sort and display results
for key in "${!RESULTS[@]}"; do
    IFS='|' read -r php_ver symfony_ver phpunit_ver <<< "$key"
    status="${RESULTS[$key]}"
    case $status in
        "PASSED")  status="✅ PASSED" ;;
        "FAILED")  status="❌ FAILED" ;;
        #"SKIPPED") status="⏭️ SKIPPED" ;;
    esac
    printf "%-8s | %-12s | %-12s | %-10s\n" "$php_ver" "$symfony_ver" "$phpunit_ver" "$status"
done

# Display statistics
echo -e "\n📈 Statistics"
passed=$(echo "${RESULTS[@]}" | grep -o "PASSED" | wc -l)
failed=$(echo "${RESULTS[@]}" | grep -o "FAILED" | wc -l)
#skipped=$(echo "${RESULTS[@]}" | grep -o "SKIPPED" | wc -l)
skipped=0
total=$((passed + failed + skipped))

echo "Total combinations: $total"
echo "✅ Passed: $passed"
echo "❌ Failed: $failed"
#echo "⏭️ Skipped: $skipped"