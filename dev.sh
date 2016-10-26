#!/bin/bash

WORKING_DIR=$(pwd)
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

php -S 0.0.0.0:8001 -t $DIR/web-api/public $DIR/web-api/public/index.php &
WEB_API_PID=$!

cd "${DIR}/web"
echo $(pwd)
API_URL="http://localhost:8001" npm run dev &
WEB_PID=$!

php -S 0.0.0.0:9001 -t $DIR/admin-api/public $DIR/admin-api/public/index.php &
ADMIN_API_PID=$!

cd "${DIR}/admin"
echo $(pwd)
API_URL="http://localhost:9001" npm run dev &
ADMIN_PID=$!


echo "WEB running on http://localhost:8000 with PID: ${WEB_PID}"
echo "WEB API running on http://localhost:8001 with PID: ${WEB_API_PID}"
echo "ADMIN running on http://localhost:9000 with PID: ${ADMIN_PID}"
echo "ADMIN API running on http://localhost:9001 with PID: ${ADMIN_API_PID}"

function gracefullyExit {
  echo "Stopping WEB API Server"
  kill $WEB_API_PID

  echo "Stopping ADMIN API Server"
  kill $ADMIN_API_PID

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
