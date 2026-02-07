#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   bash scripts/verify-text-only-repo.sh [BASE_REF] [HEAD_REF]
# Examples:
#   bash scripts/verify-text-only-repo.sh
#   bash scripts/verify-text-only-repo.sh origin/main HEAD

base_ref="${1:-$(git rev-list --max-parents=0 HEAD | tail -n 1)}"
head_ref="${2:-HEAD}"

forbidden_ext='\.(png|jpg|jpeg|zip|pdf)$'

echo "[1/3] Checking tracked filenames..."
tracked=$(git ls-files | rg -n "$forbidden_ext" || true)
if [[ -n "$tracked" ]]; then
  echo "Forbidden binary-like tracked files found:"
  echo "$tracked"
  exit 1
fi

echo "[2/3] Checking commit range filenames ($base_ref..$head_ref)..."
range_names=$(git diff --name-only "$base_ref..$head_ref" | rg -n "$forbidden_ext" || true)
if [[ -n "$range_names" ]]; then
  echo "Forbidden binary-like filenames found in range $base_ref..$head_ref:"
  echo "$range_names"
  exit 1
fi

echo "[3/3] Checking commit objects in range ($base_ref..$head_ref)..."
range_objs=$(git rev-list --objects "$base_ref..$head_ref" | rg -n "$forbidden_ext" || true)
if [[ -n "$range_objs" ]]; then
  echo "Forbidden binary-like objects still present in history range $base_ref..$head_ref:"
  echo "$range_objs"
  echo "Hint: rewrite branch history and force-push with --force-with-lease."
  exit 1
fi

echo "OK: repository and history range are text-only for forbidden extensions."
