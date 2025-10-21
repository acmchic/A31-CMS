#!/bin/bash
set -e
APP_DIR="$(pwd)"
DEPLOY_DIR="$APP_DIR/deploy"


# remove old deploy folder
rm -rf "$DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"


# get changed/untracked files
FILES=$(git status --porcelain | awk '{print $2}')


for FILE in $FILES; do
# skip excluded paths
if [[ "$FILE" == .env* || "$FILE" == storage/* || "$FILE" == vendor/* || "$FILE" == node_modules/* ]]; then
continue
fi
DEST="$DEPLOY_DIR/$FILE"
mkdir -p "$(dirname "$DEST")"
cp -R "$APP_DIR/$FILE" "$DEST"
echo "Added: $FILE"
done


echo "Deploy package built at $DEPLOY_DIR"