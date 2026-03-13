#!/bin/bash

# ══════════════════════════════════════════════════════════════════
#  Hope Phillips Charity Foundation — Frontend Deployment Script
#  Behaviour:
#    • Pulls only the `client/` directory from the repo (sparse)
#    • Copies files into the current working directory
#    • OVERWRITES files that already exist (update in place)
#    • NEVER deletes files that are not in the repo
#    • Safe to run on a live server — no downtime disruption
# ══════════════════════════════════════════════════════════════════

set -euo pipefail          # exit on error, unset var, or pipe failure
IFS=$'\n\t'

# ── Config ────────────────────────────────────────────────────────
REPO_URL="https://github.com/russiantech/hopephillips-charity-foundation.git"
BRANCH="main"
SPARSE_DIR="client"
WORK_DIR="/tmp/hopephillips_deploy_$$"   # unique tmp dir per run (PID-suffixed)
TARGET_DIR="$(pwd)"                      # wherever you run the script from

# ── Colours ───────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'   # no colour

log()    { echo -e "${CYAN}[deploy]${NC} $*"; }
success(){ echo -e "${GREEN}[deploy]${NC} ✅ $*"; }
warn()   { echo -e "${YELLOW}[deploy]${NC} ⚠️  $*"; }
error()  { echo -e "${RED}[deploy]${NC} ❌ $*" >&2; }

# ── Dependency check ──────────────────────────────────────────────
for cmd in git rsync; do
    if ! command -v "$cmd" &>/dev/null; then
        error "'$cmd' is required but not installed. Aborting."
        exit 1
    fi
done

# ── Cleanup trap (always runs, even on error) ─────────────────────
cleanup() {
    if [ -d "$WORK_DIR" ]; then
        log "Cleaning up temporary directory..."
        rm -rf "$WORK_DIR"
    fi
}
trap cleanup EXIT

# ── Step 1: Clone a fresh sparse copy into a temp directory ───────
#   We always use a clean temp clone so the working tree stays
#   independent of the live directory (no .git folder on the server).
log "🚀 Starting frontend deployment → ${TARGET_DIR}"
log "📦 Sparse-cloning '${SPARSE_DIR}/' from ${BRANCH}..."

mkdir -p "$WORK_DIR"

git -C "$WORK_DIR" init -q
git -C "$WORK_DIR" remote add origin "$REPO_URL"
git -C "$WORK_DIR" config core.sparseCheckout true
echo "${SPARSE_DIR}/*" > "$WORK_DIR/.git/info/sparse-checkout"
git -C "$WORK_DIR" pull -q origin "$BRANCH"

SOURCE_DIR="${WORK_DIR}/${SPARSE_DIR}"

if [ ! -d "$SOURCE_DIR" ]; then
    error "Expected directory '${SPARSE_DIR}/' was not found in the repository."
    exit 1
fi

# ── Step 2: Count what will change ───────────────────────────────
#   -n = dry-run  |  --itemize-changes prints one line per file
#   Lines starting with '>' = file would be transferred
#   --ignore-existing  = only count brand-new files
#   --existing         = only count files that already exist at dest
NEW_COUNT=$(rsync -rln --ignore-existing --itemize-changes \
    "${SOURCE_DIR}/" "${TARGET_DIR}/" 2>/dev/null | grep -c '^>' || true)
UPD_COUNT=$(rsync -rln --existing --itemize-changes \
    "${SOURCE_DIR}/" "${TARGET_DIR}/" 2>/dev/null | grep -c '^>' || true)

log "📊 Preview: ${UPD_COUNT} file(s) to update, ${NEW_COUNT} new file(s) to add."

# ── Step 3: Sync — override matches, add new, keep extras ─────────
#   Flags:
#     -r   recursive
#     -l   preserve symlinks
#     -t   preserve modification times (lets rsync skip unchanged files)
#     -h   human-readable output
#     --itemize-changes   one line per changed file (shows what happened)
#
#   NOT passed (intentional):
#     --delete  ← omitting this means destination-only files are NEVER removed.
#                 There is no "--no-delete" flag in rsync ≤ 3.1.x; the safe
#                 default is simply to not pass --delete at all.
log "🔄 Syncing files (override matches, keep extras, never delete)..."

rsync -rlth \
    --itemize-changes \
    "${SOURCE_DIR}/" \
    "${TARGET_DIR}/"

# ── Step 4: Summary ───────────────────────────────────────────────
success "Frontend deployment complete!"
echo ""
log "📁 Target directory contents:"
ls -lA "$TARGET_DIR"

