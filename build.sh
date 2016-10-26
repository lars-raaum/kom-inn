#!/bin/bash

WORKING_DIR=$(pwd)
DIR=`dirname $(realpath $0)`

cd "$DIR/web"
npm run build &
WEB_PID=$!

cd "$DIR/admin"
npm run build
ADMIN_PID=$!

cd $WORKING_DIR

wait $WEB_PID
wait $ADMIN_PID
