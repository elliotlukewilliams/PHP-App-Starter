#!/bin/bash

# Preview the app in production mode on localhost, or switch back to development.
#
#   ./preview-app.sh        Build the front-end assets and restart the web container with APP_ENV=production
#   ./preview-app.sh dev    Restart the web container with APP_ENV from .env (normally local)
#
# Docker Compose gives shell variables priority over .env, so APP_ENV is overridden without editing .env.

set -e  # stop script on error

MODE="${1:-prod}"

case "$MODE" in
    prod)
        echo "Building front-end assets..."
        npm run build

        echo "Restarting web container in production mode..."
        APP_ENV=production docker-compose up -d web

        echo "Production preview running at http://localhost:8000"
        echo "Errors are hidden and pages use the built assets in dist/. Re-run this script after changing JS or CSS."
        echo "Note: the session cookie is now Secure. Chrome and Firefox allow it on http://localhost, Safari doesn't."
        echo "Switch back with: ./preview-app.sh dev"
        ;;
    dev)
        echo "Restarting web container in development mode..."
        docker-compose up -d web

        echo "Development mode restored. Start the front end with: npm run dev"
        ;;
    *)
        echo "Usage: ./preview-app.sh [prod|dev]" >&2
        exit 1
        ;;
esac
