---
slug: convertsell-rename
title: Rename theme, toolkit, and GitHub repository to ConvertSell
status: in_progress
created: 2026-11-19
updated: 2026-11-19
---

# Thread: ConvertSell rename

## Goal

Apply the user-approved ConvertSell identity to the child theme, companion toolkit,
GitHub repository, packages, current documentation, and verification tooling.

## Context

- User explicitly approved the rename after reviewing conversion-focused names.
- Baseline is remote main/local HEAD 71b2b43, child 0.6.0 and toolkit 0.11.10.
- GitHub repository is solstudioim/purple-cro-optimized; authenticated role ADMIN.
- The older codex/commerce-clarity branch contains obsolete custom styling. Do not
  merge it or restore its layout changes. Preserve inherited Purple presentation.
- Preserve existing POT/pot settings, sessions, storage, saved block names, and
  published legacy shortcodes. Rename public identity and distribution paths.
- Parent Purple is independently maintained and must remain unchanged.
- Unrelated local thread edits, screenshot change, .env, and artifacts/ predate
  this work; preserve them and exclude them from commits.
- No live/staging deployment is authorized by this repository rename.

## References

- Codex chat 01a1f155-a328-77c1-9623-d7b00300e9f9
- README.md
- wp-content/themes/purple-optimize/
- wp-content/plugins/purple-optimize-toolkit/
- tools/build-packages.py
- .github/workflows/compatibility.yml

## Next Steps

- Public names/slugs, text domains, UI, current docs, CI and release paths updated.
- Child 0.7.0 and toolkit 0.12.0 packages built under
  /Users/sol/Codex/artifacts/convertsell/releases; no release tag published.
- All 40 Node contracts and standalone PHP contracts pass. Duplicate plugin
  activation guard is behavior-tested. ZIPs match source bytes and manifest hashes.
- Plugin runtime moved behind the bootstrap duplicate-install guard; otherwise
  PHP declares functions before a top-level return and cannot safely reject the
  second copy. Existing activation/deactivation hooks still target POT_FILE.
- New public shortcodes have legacy aliases; published wishlist page lookup and
  checkout review allowlisting support both. Saved checkout block names retained.
- Existing live/staging/local merchant installations are not switched by this
  repository rename. Theme-slug migration of Site Editor records is documented,
  never silently performed or claimed as verified.
- Run combined-stack CI on the renamed source, including new saved-data checks.
- Rename GitHub repo to convertsell, sync verified source, read back remote state.
- Record exact verification and deployment limitations here before finishing.
