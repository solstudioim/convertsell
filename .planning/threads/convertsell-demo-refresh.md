---
slug: convertsell-demo-refresh
title: Refresh ConvertSell demo from the current Purple-inheriting staging design
status: in_progress
created: 2026-11-19
updated: 2026-11-19
---

# Thread: ConvertSell demo refresh

## Goal

Replace the historical GitHub Pages demo's obsolete design and feature story with
the current Purple-inheriting storefront, using public staging evidence and the
current theme/toolkit source. Cover previously omitted conversion features.

## Context

- User explicitly requested updating demo pages/media to match
  https://purplecrodemo.wpcomstaging.com/ and to include unmentioned features.
- Existing main db2a1d5 has ConvertSell 0.7.0 / Toolkit 0.12.0. Theme and toolkit
  source, parent presentation and saved merchant settings must be preserved.
- Public staging HTML returns HTTP 200, uses Purple stylesheet and still uses
  the legacy Purple Optimize Toolkit distribution. It is a visual reference,
  not proof that the renamed packages are deployed there.
- Current demo has old orange/cream custom design, 18-frame historical video,
  obsolete 0.5.12/0.7.4 version labels, and claims mobile sticky product action
  although the current feature deliberately hides that action on mobile.
- Reuse the existing FFmpeg-based screenshot demo pipeline, with fresh captured
  evidence. Do not install another video framework or alter the staging store.
- Existing local edits, .env, artifacts/ and old verification captures remain
  untouched. New retained artifacts go under
  /Users/sol/Codex/artifacts/convertsell-demo-refresh/.
- User also requested a domain recommendation from supplied prices. Recommend
  convertsell.app for the software product (quoted renewal $17/year); .org is
  the open-source/community alternative. No registration or DNS changes asked.

## References

- docs/demo/index.html
- docs/demo/styles.css
- tools/demo/build-video.sh
- tools/demo/captions.txt
- tests/demo-pages-contracts.test.mjs
- README.md
- https://github.com/solstudioim/convertsell
- https://purplecrodemo.wpcomstaging.com/

## Next Steps

- Public source, computed palette/font and current desktop/mobile captures saved.
  Search and wishlist journey is under journey/. Verified mini-cart/cart/upsell
  captures are under verified-cart-final/. Successful checkout capture is under
  checkout-optin/ using the scarf as base product, leaving the cardigan add-on
  genuinely unchecked with its configured 5% discount. No payment submitted.
- The local system PAC does not work in headless Chromium. Public capture uses
  Playwright's HTTP client; its final response URL is restored in history so offer
  step/query data remains accurate. Failures in earlier capture attempts were
  adapter/selector issues, not established storefront bugs. Only reviewed captures
  are mapped into the publication media.
- Historical index/styles/captions/video/poster preserved with matching file
  counts and SHA256 hashes in historical-demo/ (five original files).
- Refreshed static page uses Purple's exact palette and bundled Inter font,
  native details/transcript, updated versions, full feature coverage and limits.
  README's old orange/enclosed-layout claims corrected; no runtime source changed.
- Prepared 14 frames / 63 seconds, including explicitly labeled explanation cards
  for optional placements and post-purchase behavior. Staging has an upsell and
  declines directly to checkout; no fabricated downsell interface is shown.
- Video repeat-build verification passed: MP4 and decoded-frame/poster hashes
  match across two builds. H.264, 1920x1080, 30fps, silent, exactly 63 seconds,
  4,519,550 bytes. Publication copies hash-match task artifacts. Historical
  deployed video/poster removed only after backup/hash validation.
- All 40 Node contracts pass. Real browser checks passed at 1440/768/390/320px:
  no overflow, three images decoded, bundled font loaded, keyboard details and
  skip-link focus, JS-disabled access, no unsolicited video download, metadata
  and actual playback. Human-visible screenshots inspected for desktop/mobile
  and video playback. Results: preview/verification.json under artifact root.
- Parent, child and companion runtime sources remain unchanged. Prior combined
  compatibility baseline is still the verified db2a1d5 tree; this is demo-only.
- Sync approved demo updates to GitHub and verify the published Pages result.
