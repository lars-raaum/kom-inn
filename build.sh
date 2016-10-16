#!/bin/bash

WORKING_DIR=$(pwd)
DIR=$(dirname "$0")

cd "$DIR/web"
npm run build

cd "../admin"
npm run build

cd $WORKING_DIR
