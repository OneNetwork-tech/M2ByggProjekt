#!/bin/bash
# M2 Platform — Start local development server (Mac/Linux)
# Requires: PHP 8.x  (mac: brew install php   ubuntu: sudo apt install php-cli php-sqlite3)

cd "$(dirname "$0")"
echo "═══════════════════════════════════════════"
echo "  M2 Platform — Local Development Server"
echo "═══════════════════════════════════════════"
echo ""
echo "  Website : http://localhost:8080"
echo "  CRM     : http://localhost:8080/crm/login.php"
echo "            (admin@m2team.se / admin123)"
echo ""
echo "  Stop with Ctrl+C"
echo "═══════════════════════════════════════════"
php -S localhost:8080 router.php
