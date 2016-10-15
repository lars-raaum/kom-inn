#!/bin/bash

WORKING_DIR=$(pwd)

php -S 0.0.0.0:8080 -t api/public api/public/index.php &
API_PID=$!

cd "$(dirname "$0")/web"
API_URL="http://localhost:8080" npm run dev &
WEB_PID=$!

echo "API running on http://localhost:8080 with PID: ${API_PID}"
echo "WEB running on http://localhost:7000 with PID: ${WEB_PID}"

function gracefullyExit {
  echo "Stopping API Server"
  kill $API_PID

  echo "Stopping WEB Server"
  kill $WEB_PID
  cd $WORKING_DIR

  exit 0
}

trap gracefullyExit SIGINT

while true; do
    sleep 1;
done
