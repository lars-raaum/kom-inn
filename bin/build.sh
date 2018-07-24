#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${DIR}/.."
WORKING_DIR=$(pwd)

cd "$DIR/../web"
npm run build &
WEB_PID=$!

cd "$DIR/../admin"
npm run build &
ADMIN_PID=$!

cd $WORKING_DIR

echo "Waiting for build to succeed"

wait $WEB_PID
wait $ADMIN_PID
