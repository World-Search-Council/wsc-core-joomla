# WSC Analytics & Search Health for Joomla (`v2.2.3` — Joomla 4 & 5)

<p align="center">
  <strong>Official Joomla 4 & 5 Extension of the World Search Council (WSC) Standard</strong><br>
  100% Cookieless Zero-PII Analytics, Pre-Cache AI Bot Sensor, SQLite WAL Queue, Canonical 7-Card Bento Suite, Live WSC Member Bridge, Industry Benchmarks, <code>/llms.txt</code> &amp; Opt-In IndexNow
</p>

<p align="center">
  <a href="https://www.worldsearchcouncil.org"><img src="https://img.shields.io/badge/Portal-worldsearchcouncil.org-0F172A?style=flat-square" alt="World Search Council"></a>
  <img src="https://img.shields.io/badge/Version-2.2.3-5091CD?style=flat-square&logo=joomla" alt="Joomla v2.2.3">
  <img src="https://img.shields.io/badge/Joomla-4.x_|_5.x-5091CD?style=flat-square" alt="Joomla 4 & 5">
  <img src="https://img.shields.io/badge/Privacy-100%25_Cookieless_Zero--PII-10B981?style=flat-square" alt="Cookieless">
  <img src="https://img.shields.io/badge/License-GPL--2.0--or--later-orange?style=flat-square" alt="License">
</p>

---

## 🌟 Key Features (`v2.2.3`)

* **100% Cookieless & Zero-PII Privacy by Design**:
  * Zero tracking cookies and **zero stored raw IP addresses** — rotating daily salted session hashes (`SHA256(IP + UA + DailySalt)`).
  * **Zero Unconsented Phone-Home**: Hybrid Cloud Sync (`hybrid_enabled`) and `IndexNow` (`indexnow_enabled`) default strictly to `0` (Opt-In).
* **Dual-Layer Sensor Architecture**:
  * **Edge System Plugin (`plg_system_wsc_analytics`)**: Hooks into Joomla's early `onAfterInitialise` lifecycle to classify AI RAG citation agents (`ChatGPT-User`, `OAI-SearchBot`, `PerplexityBot`), Training scrapers (`GPTBot`, `ClaudeBot`, `CCBot`), and Search spiders (`Googlebot`, `Bingbot`).
  * **Client Runtime (`wsc-tracker.js`)**: Lightweight (< 5 KB) Vanilla ES6 runtime registered locally via Joomla's Web Asset Manager (`joomla.asset.json`) with zero external CDNs.
* **Zero DB-Locking SQLite WAL Storage**:
  * Buffers incoming telemetry in `queue_temp.sqlite` and stores pre-computed Bento snapshots in `reports_temp.sqlite` (`PRAGMA journal_mode = WAL`).
* **Canonical 7-Card Bento Suite & Multi-Period Switcher (`1d`, `7d`, `30d`, `90d`, `365d`)**:
  1. **`card1_traffic`** — Real Visitors, Pageviews, Engaged Time & Channel Attribution (AI, Search, Social, Direct)
  2. **`card2_insights`** — Algorithmic Growth Insights & Actionable SEO/UX Recommendations
  3. **`card3_bots`** — AI Bot Shield Sensor Network (RAG vs. Training vs. Search Crawler Ratio)
  4. **`card_engagement`** — Engaged Reading Time, TTFI, Scroll Milestones & UX Diagnosis Badges
  5. **`card_outbound`** — Categorized Outbound Link Destinations (Jobs, Social, Affiliate, Reference)
  6. **`card_ecommerce`** — 4-Stage Conversion Funnel & Form Attribution
  7. **`domain_health` + `pagespeed`** — Port-25-Free Domain Sentinel & **SpeedSentinel 2.0 Dual-Thermometer** (🤖 RAG Readiness vs. 👤 Human CWV)
* **AI Coverage Inspector, Dynamic Benchmarks, `/llms.txt` & Opt-In IndexNow (`v2.2.3`)**:
  * **AI Coverage URL Inspector**: Per-URL AI crawler & RAG citation lookup (`/v1/ai-coverage/query`) with automatic local SQLite fallback.
  * **Dynamic Industry Benchmarks**: Peer-group comparison (`/v1/benchmarks/query` & auto-classification `/v1/benchmarks/classify`) with 6-hour SQLite cache and offline vertical baselines.
  * **Machine-Readable `/llms.txt` AI Manifest**: Dynamically generated Markdown overview of published `#__content` articles served cleanly at `/llms.txt`.
  * **Opt-In `IndexNow` Instant Push**: Automatic `onContentAfterSave` article ping + manual URL push (`https://api.indexnow.org/indexnow`) with virtual `/{key}.txt` ownership verification.
* **Live WSC Member & Agency Governance Bridge (`v2.2.3`)**:
  * Live credential verification against `https://www.worldsearchcouncil.org/api/v1/license/verify`, Domain Heartbeat registration, Agency Co-Branding sync, and 100% ad-free UI for active WSC Members.

---

## 📦 Extension Structure

```text
wsc-core-joomla/
├── com_wsc_analytics.xml            # Component Manifest (v2.2.3 — Joomla 4 & 5)
├── script.php                       # Package installer & automatic system plugin activation
├── admin/
│   ├── access.xml                   # Joomla ACL permissions
│   ├── config.xml                   # Privacy, Opt-In Gateway, Membership, Benchmarks & IndexNow config
│   ├── services/
│   │   └── provider.php             # Joomla 4/5 DI Container Service Provider
│   ├── src/
│   │   ├── Controller/
│   │   │   ├── DisplayController.php# Admin Bento dashboard controller
│   │   │   └── ApiController.php    # Sync, cards, SpeedSentinel, Sentinel, AI Coverage, Benchmarks, License & IndexNow
│   │   ├── Helper/
│   │   │   ├── WscCoreHelper.php    # SQLite WAL, Zero-PII, Gateway v2.3 AES-256/HMAC, Portal Bridge, /llms.txt & IndexNow
│   │   │   └── WscBotHelper.php     # Canonical AI/RAG/Search crawler taxonomy
│   │   ├── View/Dashboard/
│   │   │   └── HtmlView.php         # Dashboard View class
│   │   └── Extension/
│   │       └── WscAnalyticsComponent.php
│   └── tmpl/dashboard/
│       └── default.php              # 7-Card Bento + AI Coverage + Benchmarks + WSC Member Console (Bootstrap 5)
├── site/src/Controller/
│   └── ApiController.php            # Zero-PII intake, signed Gateway webhook receiver & /llms.txt controller fallback
├── plugins/system/wsc_analytics/
│   ├── wsc_analytics.xml            # System Plugin manifest (v2.2.3)
│   └── src/Extension/
│       └── WscAnalytics.php         # Virtual /llms.txt & /{key}.txt, Zero-PII bot sensor, IndexNow hook & tracker head tag
└── media/
    ├── joomla.asset.json            # Web Asset Manager manifest (v2.2.3)
    ├── js/
    │   ├── wsc-tracker.js           # Canonical Vanilla ES6 tracker
    │   └── wsc-dashboard.js         # Multi-period switcher, AI Coverage, Benchmarks, License & IndexNow handlers
    └── css/
        └── wsc-dashboard.css        # Responsive 7-Card Bento Grid styling
```

---

## 🚀 Installation & Setup

1. Package the repository contents into `pkg_wsc_analytics.zip`.
2. In the Joomla Administrator backend, open **System → Install → Extensions** and upload the ZIP archive. Both `com_wsc_analytics` and `plg_system_wsc_analytics` are installed and enabled automatically.
3. Open **Components → WSC Analytics & Search Health** to inspect the 7-Card Bento Suite, test `/llms.txt`, verify your WSC Member credential, or opt into Hybrid Cloud Sync via **Options**.

---

## 🔗 Resources & License

- **Official Portal & Member Console:** [https://www.worldsearchcouncil.org](https://www.worldsearchcouncil.org)
- **GitHub Organization & Maintainer:** [World-Search-Council](https://github.com/World-Search-Council) · Founding Lead Maintainer: **[@thebennos](https://github.com/thebennos)**
- **Joomla Integration Docs:** [https://world-search-council.github.io/wsc-docs/platforms/joomla/](https://world-search-council.github.io/wsc-docs/platforms/joomla/)
- **License:** [GNU General Public License v2.0 or later (GPL-2.0-or-later)](LICENSE)
