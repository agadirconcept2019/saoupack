#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR_NAME="saoupack-crm"
MAIN_FILE="${PLUGIN_DIR_NAME}/saoupack-crm.php"
DIST_DIR="${ROOT_DIR}/dist"
STAGE_DIR="${DIST_DIR}/.stage"
ZIP_PATH="${DIST_DIR}/${PLUGIN_DIR_NAME}.zip"

rm -rf "${STAGE_DIR}"
mkdir -p "${STAGE_DIR}/${PLUGIN_DIR_NAME}" "${DIST_DIR}"

while IFS= read -r file; do
    case "$file" in
        .git/*|dist/*)
            continue
            ;;
    esac

    if [[ -d "${ROOT_DIR}/${file}" ]]; then
        continue
    fi

    install -D "${ROOT_DIR}/${file}" "${STAGE_DIR}/${PLUGIN_DIR_NAME}/${file}"
done < <(git -C "${ROOT_DIR}" ls-files)

if [[ ! -f "${STAGE_DIR}/${MAIN_FILE}" ]]; then
    echo "Missing main plugin file in package: ${MAIN_FILE}" >&2
    exit 1
fi

(
    cd "${STAGE_DIR}"
    zip -rq "${ZIP_PATH}" "${PLUGIN_DIR_NAME}"
)

rm -rf "${STAGE_DIR}"

echo "Built: ${ZIP_PATH}"
unzip -l "${ZIP_PATH}" | sed -n '1,12p'
