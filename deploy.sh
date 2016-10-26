#!/bin/bash

set -e

if [ $# -lt 1 ]
  then
    echo " - "
    echo "    Missing version. Usage: '$0 1.2.3 ENV'"
    echo " - "
    exit
fi

VERSION="$1"
ENV=${2:-"dev"}

git fetch --tags

if [ -z $(git tag | grep $VERSION) ]; then

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

    echo " - Building version ${VERSION}"
    sleep 1
    ./build.sh
    echo " - Built"

    echo " - Updating ansible version"
    perl -pi -e "s/version=.*/version=${VERSION}/g" ansible/roles/kom-inn.yaml

    echo $VERSION > VERSION

    echo " - Making commit"
    git add admin/public/js
    git add web/public/js
    git add ansible/roles/kom-inn.yaml
    git add VERSION
    git commit -m "Build version ${VERSION}"
    git tag $VERSION

    echo " - Pushing branch and tag to github"
    git push origin HEAD --tags
else
    echo " - Tag exists, deploying existing tag"
    git checkout $VERSION
fi

echo " - Deploying ${VERSION} to ${ENV}"

sleep 1

ansible-playbook ./ansible/roles/kom-inn.yaml -i ./ansible/hosts/${ENV}

echo " - Deployed. Gracias."
