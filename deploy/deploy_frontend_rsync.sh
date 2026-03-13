#!/bin/bash
# 🚀 Frontend Deployment Script
# This script deploys the frontend from a Git repository using sparse checkout.
# It ensures only the necessary frontend files are updated without wiping other content.

set -euo pipefail

REPO_URL="https://github.com/russiantech/hopephillips-charity-foundation.git"
BRANCH="main"
SPARSE_DIR="client"

echo "🚀 Starting frontend deployment..."

# Initialize repository if it doesn't exist
if [ ! -d ".git" ]; then
    echo "📦 First-time setup..."
    
    git init
    git remote add origin "$REPO_URL"

    git config core.sparseCheckout true
    echo "$SPARSE_DIR/*" > .git/info/sparse-checkout

    # Pull only the sparse directory
    git pull origin "$BRANCH"
else
    echo "📡 Pulling latest updates..."
    
    # Ensure sparse checkout is enabled and configured
    git config core.sparseCheckout true
    if ! grep -q "^$SPARSE_DIR/\*$" .git/info/sparse-checkout; then
        echo "$SPARSE_DIR/*" >> .git/info/sparse-checkout
    fi

    git pull origin "$BRANCH"
fi

echo "📂 Syncing frontend files..."

# Rsync updated files without deleting other local content
rsync -av --ignore-existing "$SPARSE_DIR"/ ./

# Optional: remove the temporary sparse directory after syncing
rm -rf "$SPARSE_DIR"

echo "✅ Deployment complete."
