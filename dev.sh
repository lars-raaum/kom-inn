#!/bin/bash

WORKING_DIR=$(pwd)
DIR=$(dirname "$0")

php -S 0.0.0.0:8080 -t api/public api/public/index.php &
API_PID=$!

cd "${DIR}/admin"
echo $(pwd)
API_URL="http://localhost:8080" npm run dev &
ADMIN_PID=$!

cd "../web"
echo $(pwd)
API_URL="http://localhost:8080" npm run dev &
WEB_PID=$!

echo "API running on http://localhost:8080 with PID: ${API_PID}"
echo "WEB running on http://localhost:7000 with PID: ${WEB_PID}"
echo "ADMIN running on http://localhost:9000 with PID: ${ADMIN_PID}"

function gracefullyExit {
  echo "Stopping API Server"
  kill $API_PID

  echo "Stopping WEB Server"
  kill $WEB_PID

  echo "Stopping ADMIN Server"
  kill $ADMIN_PID

  cd $WORKING_DIR

  exit 0
}

trap gracefullyExit SIGINT

while true; do
    sleep 1;
done
