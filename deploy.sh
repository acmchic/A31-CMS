#!/bin/bash
set -e

echo "=== Building deploy package ==="

APP_DIR="$(pwd)"
DEPLOY_DIR="$APP_DIR/deploy"

# create deploy folder if not exists (do not delete existing files)
mkdir -p "$DEPLOY_DIR"

REMOVED_FILE="$DEPLOY_DIR/removed.txt"
if [ ! -f "$REMOVED_FILE" ]; then
    > "$REMOVED_FILE"
fi

# Helper: copy file or folder preserving structure
copy_path() {
    local SRC="$1"
    local DEST="$DEPLOY_DIR/$SRC"
    mkdir -p "$(dirname "$DEST")"
    cp -R "$SRC" "$DEST"
    echo "✔ Copied: $SRC"
}

# Get list from git status
STATUS_OUTPUT=$(git status --porcelain)

while IFS= read -r LINE; do
    STATUS="${LINE:0:2}"
    FILE="${LINE:3}"

    # ignore hidden files (.gitignore, .idea,...)
    [[ "$(basename "$FILE")" == .* ]] && continue

    # skip excluded paths
    [[ "$FILE" == .env* \
    ||  "$FILE" == storage/* \
    ||  "$FILE" == vendor/* \
    ||  "$FILE" == node_modules/* ]] && continue

    if [[ "$STATUS" == "??" ]]; then
        # untracked → copy full
        copy_path "$FILE"
    elif [[ "$STATUS" == " D" ]] || [[ "$STATUS" == "D " ]]; then
        # deleted → log only
        echo "$FILE" >> "$REMOVED_FILE"
        echo "✘ Deleted: $FILE (logged)"
    else
        # modified or other tracked changes → copy full
        copy_path "$FILE"
    fi

done <<< "$STATUS_OUTPUT"

echo "=== DONE ==="
echo "Deploy folder is ready at: $DEPLOY_DIR"
