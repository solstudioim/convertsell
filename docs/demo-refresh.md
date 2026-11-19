# ConvertSell demo refresh — November 19, 2026

The GitHub Pages demo now uses the current Purple presentation shown at
https://purplecrodemo.wpcomstaging.com/ rather than the former orange/cream
custom child design. The storefront itself is not changed by this update.

## Evidence and scope

- Fresh public home, catalog, product, search, wishlist, mini-cart, cart, offer and
  checkout captures supply the walkthrough. No login, settings change or payment
  submission is used. Products, reviews and orders visible there are demo data.
- Browser-observed typography is Inter. Colors agree with Purple's palette:
  white, contrast #111111, primary #1f0342, text #322f37, lilac #f2edff, and accent
  #720eec. The demo landing page uses these tokens and the same bundled font.
- The staging store retains its Purple CRO identity and legacy toolkit package
  name. The landing page identifies current source packages separately:
  ConvertSell 0.7.0 and ConvertSell Toolkit 0.12.0.
- The movie combines captured interfaces with explicitly labeled feature
  explanation cards for placement choices and post-purchase behavior. It does not
  portray a synthetic completed order or silently change the store's offer mode.
- Original historical demo media is retained byte-for-byte with a SHA256
  manifest under the task artifact directory before deployment copies change.

## Feature coverage added or corrected

- Instant search: category scope, SKU lookup, typo tolerance and keyboard use.
- Browser wishlist, saved-items page, header count and browser-local storage.
- Promotion strip/coupon copy and configurable contrast-aware background.
- Approved native reviews, featured review, review selection and static/rotating
  placement, verified-owner attribution and optional autoplay.
- Factual sale/stock/countdown messaging and product-content readiness guidance.
- Native mini-cart, shipping progress, required/optional field labels and
  checkout reassurance.
- Up to four opt-in checkout add-ons, optional discounts, live totals and
  accepted-price persistence; an already-present cart item is not a new opt-in.
- Published core-block helpful content and selected reviews at checkout.
- Upsell/downsell products, discounts and durations; three placement choices;
  guest/signed-in cart-scoped decisions and accepted-offer loop prevention.
- Post-purchase acceptance starts a separate checkout and order.
- Opt-in recent-purchase proof, anonymous/off defaults and merchant controls.
- Post-purchase account invitation and section-scoped guided settings.
- Correct desktop-only sticky product action versus optional mobile native
  cart/checkout controls. Removed the old mobile sticky-product-bar claim.
- Clear ownership: Purple provides native templates/gallery/filters; the optional
  toolkit provides conversion components. Wallet/payment buttons are gateway
  features. No quantified conversion improvement is claimed.

## Reproduce and review

The capture/prepare/browser-check scripts accept `PLAYWRIGHT_MODULE` and optional
`DEMO_BROWSER` executable paths; no dependency installation is performed by them.
New capture evidence, intermediate frames, builds and review screenshots belong
under `/Users/sol/Codex/artifacts/convertsell-demo-refresh/`.

1. `node tools/demo/capture-staging.mjs` captures the public visual reference at
   the artifact root. Capture `DEMO_JOURNEY=1` into its `journey/` directory,
   `DEMO_JOURNEY=cart` into `verified-cart-final/`, and
   `DEMO_JOURNEY=checkout` into `checkout-optin/` (set `DEMO_ARTIFACTS` for each).
   The final checkout uses `DEMO_BASE_PRODUCT` set to the public
   `/product/striped-knit-scarf/` URL, so the configured cardigan add-on is not
   already in the cart. These
   interact with a temporary guest cart but never submit an order.
2. `node tools/demo/prepare-media.mjs` assembles the reviewed capture set into
   task-artifact frames, explanatory cards, publication images and a hash map.
3. `tools/demo/build-video.sh FRAME_DIRECTORY OUTPUT_DIRECTORY` builds the
   14-scene, 63-second silent 1920×1080 walkthrough and poster. Both directories
   should be inside the task artifact root. The `captions.txt` rows define frame,
   duration and burned-in description; the page supplies a text transcript.
4. `tools/demo/verify-video-reproducibility.sh FRAME_DIRECTORY [OUTPUT_DIRECTORY]` builds twice and
   checks MP4, decoded-frame and poster hashes. Original capture files remain
   untouched. The optional output receives one verified copy for publication.
5. Copy reviewed publication assets into `docs/demo/assets/`, then run
   `node --test tests/*.test.mjs` and `node tools/demo/verify-page.mjs`.
   The browser check renders actual source bytes without a public preview server.

The static demo check covers desktop/tablet/mobile overflow, image/font loading,
keyboard details controls and skip link, JavaScript-disabled access, opt-in media
loading, video dimensions/duration and real playback. The child, parent and toolkit
runtime files are outside this demo-only change.
