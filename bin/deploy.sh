#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${DIR}/.."
WORKING_DIR=$(pwd)


if [ $# -lt 1 ]
  then
    echo " - "
    echo "    Missing version. Usage: '$0 1.2.3 ENV'"
    echo " - "
    exit
fi

VERSION="$1"
ENV=${2:-"dev"}

echo " - Checking if environment is reachable"
export ANSIBLE_CONFIG="./ansible/ansible.cfg"
ansible ${ENV} -i ansible/hosts -m ping

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
    NODE_ENV="production" bin/build.sh
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
    git push origin HEAD --tags -q
else
    echo " - Tag exists, deploying existing tag"
    git checkout $VERSION -q
fi

echo " - Deploying ${VERSION} to ${ENV}"

sleep 1

ansible-playbook ./ansible/roles/kom-inn.yaml -i ./ansible/hosts/${ENV}

echo " - Deployed. Gracias."
