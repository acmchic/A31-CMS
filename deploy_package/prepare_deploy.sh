#!/usr/bin/env bash
# ===========================================
# prepare_deploy.sh
# Copy toàn bộ file mới / thay đổi (so với nhánh hoặc commit Git)
# vào thư mục deploy_package đúng cấu trúc thư mục.
#
# Usage:
#   ./prepare_deploy.sh [--since <branch|commit>]
# Mặc định: so sánh với HEAD
# ===========================================

set -e

PROJECT_ROOT="$(pwd)"
PACKAGE_DIR="$PROJECT_ROOT/deploy_package"
COMPARE_REF="HEAD"

# Parse arguments
while [[ $# -gt 0 ]]; do
  case "$1" in
    --since)
      COMPARE_REF="$2"
      shift 2
      ;;
    *)
      echo "❌ Unknown option: $1"
      exit 1
      ;;
  esac
done

echo "📂 Project: $PROJECT_ROOT"
echo "📦 Output : $PACKAGE_DIR"
echo "🔍 Compare with: $COMPARE_REF"
echo

# Ensure deploy_package is empty
rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR"

# Check if inside git repo
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "❌ Error: Not a git repository."
  exit 1
fi

# Get changed + new files
CHANGED_FILES=$(git diff --name-only "$COMPARE_REF" --diff-filter=ACMRTUXB)
UNTRACKED_FILES=$(git ls-files --others --exclude-standard)

# Combine and deduplicate
ALL_FILES=$(printf "%s\n%s\n" "$CHANGED_FILES" "$UNTRACKED_FILES" | sort -u)

if [[ -z "$ALL_FILES" ]]; then
  echo "✅ No changed or new files found (compared to $COMPARE_REF)."
  exit 0
fi

echo "📄 Files to package:"
echo "$ALL_FILES"
echo

# Copy preserving folder structure
while IFS= read -r relpath; do
  [[ -z "$relpath" ]] && continue
  src="$PROJECT_ROOT/$relpath"
  dest="$PACKAGE_DIR/$relpath"
  if [[ -f "$src" ]]; then
    mkdir -p "$(dirname "$dest")"
    cp -f "$src" "$dest"
    echo " + $relpath"
  fi
done <<< "$ALL_FILES"

echo
echo "✅ Done!"
echo "All changed files copied to: $PACKAGE_DIR"
