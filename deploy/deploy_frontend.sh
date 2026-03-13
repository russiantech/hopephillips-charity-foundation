#!/bin/bash

set -e

REPO_URL="https://github.com/russiantech/hopephillips-charity-foundation.git"
BRANCH="main"
SPARSE_DIR="client"

echo "🚀 Starting frontend deployment..."

# If repo not initialized
if [ ! -d ".git" ]; then
    echo "📦 Initializing sparse repository..."

    git init
    git remote add origin $REPO_URL

    git config core.sparseCheckout true
    echo "$SPARSE_DIR/*" > .git/info/sparse-checkout

    git pull origin $BRANCH

    echo "📂 Moving frontend files into current directory..."

    shopt -s dotglob
    mv $SPARSE_DIR/* .
    shopt -u dotglob

    rm -rf $SPARSE_DIR

else
    echo "Pulling latest frontend updates..."

    git pull origin $BRANCH

    shopt -s dotglob
    mv $SPARSE_DIR/* .
    shopt -u dotglob

    rm -rf $SPARSE_DIR
fi

echo "Frontend deployment complete."
ls -la