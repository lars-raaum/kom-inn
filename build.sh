#!/bin/bash

WORKING_DIR=$(pwd)

cd "$(dirname "$0")/web"
npm run build

cd "$(dirname "$0")/admin"
npm run build

cd $WORKING_DIR
