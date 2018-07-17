#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${DIR}/.."

pushd admin-api
composer install
popd
pushd admin
npm install
popd
pushd web-api
composer install
popd
pushd web
npm install
popd
