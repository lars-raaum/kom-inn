#!/bin/bash

if [ $# -eq 0 ]
  then
    echo " - "
    echo "    Missing version. Usage: '$0 1.2.3'"
    echo " - "
    exit
fi

VERSION="$1"

echo " "
echo " == Building " $VERSION " === "

sleep 1

if git diff-index --quiet HEAD --; then
    # no changes
    echo " - Git state clean, proceeding"
else
    # changes
    echo " - "
    echo "    YOUR GIT IS DIRTY!"
    echo " - "
    exit 1
fi

echo " - Building"
./build.sh
echo " - Built"

echo " - Making commit"
git add admin/public/js
git add web/public/js
git commit -m "Build version ${VERSION}"
git tag $VERSION

echo " - Pushing branch and tag to github"
git push origin HEAD --tags