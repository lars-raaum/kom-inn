#!/bin/bash

WORKING_DIR=$(pwd)
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd "$DIR/web"
npm run build &
WEB_PID=$!

cd "$DIR/admin"
npm run build
ADMIN_PID=$!

cd $WORKING_DIR

wait $WEB_PID
wait $ADMIN_PID
