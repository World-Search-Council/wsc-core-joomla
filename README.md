# WSC Analytics & AI Bot Shield for Joomla (`v2.2.0` — Joomla 4 & 5)

<p align="center">
  <strong>Official Joomla 4 & 5 Extension of the World Search Council (WSC) Standard</strong><br>
  100% Cookieless Analytics, Early <code>onAfterInitialise</code> AI Bot Sensor, SQLite WAL Queue & Canonical 7-Card Bento Dashboard
</p>

<p align="center">
  <a href="https://www.worldsearchcouncil.org"><img src="https://img.shields.io/badge/Portal-worldsearchcouncil.org-0F172A?style=flat-square" alt="World Search Council"></a>
  <img src="https://img.shields.io/badge/Version-2.2.0-5091CD?style=flat-square&logo=joomla" alt="Joomla v2.2.0">
  <img src="https://img.shields.io/badge/Joomla-4.x_|_5.x-5091CD?style=flat-square" alt="Joomla 4 & 5">
  <img src="https://img.shields.io/badge/Privacy-100%25_Cookieless-10B981?style=flat-square" alt="Cookieless">
  <img src="https://img.shields.io/badge/License-GPL--2.0--or--later-orange?style=flat-square" alt="License">
</p>

---

## 🌟 Key Features (`v2.2.0`)

* **Cookieless & Privacy-First**: 100% GDPR, TDDDG, and ePrivacy compliant without cookie banners — rotating daily salted session hashes (`SHA256(IP + UA + DailySalt)`) and zero stored raw IPs.
* **Dual-Layer Sensor Architecture**:
  * **Edge System Plugin (`plg_system_wsc_analytics`)**: Hooks into Joomla's early `onAfterInitialise` lifecycle to classify AI RAG agents (`ChatGPT-User`, `OAI-SearchBot`, `PerplexityBot`), Training scrapers (`GPTBot`, `ClaudeBot`, `CCBot`), and Search spiders (`Googlebot`, `Bingbot`).
  * **Client Runtime (`wsc-tracker.js`)**: Lightweight (< 5 KB) Vanilla ES6 runtime registered via Joomla's Web Asset Manager (`joomla.asset.json`).
* **Zero DB-Locking SQLite WAL Storage**:
  * Buffers incoming telemetry in `queue_temp.sqlite` and stores pre-computed Bento snapshots in `reports_temp.sqlite` (`PRAGMA journal_mode = WAL`), keeping Joomla's MySQL/MariaDB database completely lock-free.
* **Canonical 7-Card Bento Suite & Multi-Period Switcher (`1d`, `7d`, `30d`, `90d`, `365d`)**:
  1. **`card1_traffic`** — Real Visitors, Pageviews, Engaged Time & Channel Attribution (AI, Search, Social, Direct)
  2. **`card2_insights`** — Algorithmic Growth Insights & Actionable SEO/UX Recommendations
  3. **`card3_bots`** — AI Bot Shield Sensor Network (RAG vs. Training vs. Search Crawler Ratio)
  4. **`card_engagement`** — Engaged Reading Time, TTFI, Scroll Milestones & UX Diagnosis Badges
  5. **`card_outbound`** — Categorized Outbound Link Destinations (Jobs, Social, Affiliate, Reference)
  6. **`card_ecommerce`** — 4-Stage Conversion Funnel & Revenue Attribution
  7. **`domain_health` + `pagespeed`** — Port-25-Free Domain Sentinel & **SpeedSentinel 2.0 Dual-Thermometer** (🤖 RAG Readiness vs. 👤 Human CWV)
* **Interactive Admin Diagnostics**:
  * 1-click triggers (`pagespeedAudit`, `sentinelScan`, and `cards` polling) inside the Bootstrap 5 / Atum administrator component (`com_wsc_analytics`) with a live progress banner.
* **Cryptographic Security**:
  * Optional synchronization with `https://api.worldsearchcouncil.org` encrypted locally via **AES-256-CBC** and signed with **HMAC-SHA256 (`X-WSC-Sig`)**.

---

## 📦 Extension Structure

```text
wsc-core-joomla/
├── com_wsc_analytics.xml            # Component Manifest (v2.2.0 — Joomla 4 & 5)
├── script.php                       # Package installer & automatic system plugin activation
├── admin/
│   ├── access.xml                   # Joomla ACL permissions
│   ├── config.xml                   # Component & Gateway configuration form
│   ├── services/
│   │   └── provider.php             # Joomla 4/5 DI Container Service Provider
│   ├── src/
│   │   ├── Controller/
│   │   │   ├── DisplayController.php# Admin Bento dashboard controller
│   │   │   └── ApiController.php    # Sync, cards(), pagespeedAudit() & sentinelScan() endpoints
│   │   ├── Helper/
│   │   │   ├── WscCoreHelper.php    # 7-Card SQLite WAL manager, AES-256, HMAC & Gateway triggers
│   │   │   └── WscBotHelper.php     # Canonical AI/RAG/Search crawler taxonomy
│   │   ├── View/Dashboard/
│   │   │   └── HtmlView.php         # Dashboard View class
│   │   └── Extension/
│   │       └── WscAnalyticsComponent.php
│   └── tmpl/dashboard/
│       └── default.php              # 7-Card Bento Dashboard template (Atum / Bootstrap 5)
├── site/src/Controller/
│   └── ApiController.php            # First-party event intake & signed Gateway webhook receiver
├── plugins/system/wsc_analytics/
│   ├── wsc_analytics.xml            # System Plugin manifest
│   └── src/Extension/
│       └── WscAnalytics.php         # onAfterInitialise bot sensor & head tracker injection
└── media/
    ├── joomla.asset.json            # Web Asset Manager manifest
    ├── js/
    │   ├── wsc-tracker.js           # Canonical Vanilla ES6 tracker
    │   └── wsc-dashboard.js         # Multi-period switcher & diagnostic progress polling
    └── css/
        └── wsc-dashboard.css        # Responsive 7-Card Bento Grid styling
```

---

## 🚀 Installation & Setup

1. Package the repository contents into `pkg_wsc_analytics.zip`.
2. In the Joomla Administrator backend, open **System → Install → Extensions** and upload the ZIP archive. Both `com_wsc_analytics` and `plg_system_wsc_analytics` are installed and enabled automatically.
3. Open **Components → WSC Analytics & Bot Shield** to view the 7-Card Bento Suite and configure optional Gateway pairing via **Options**.

---

## 🔗 Resources & License

- **Official Portal & Member Console:** [https://www.worldsearchcouncil.org](https://www.worldsearchcouncil.org)
- **Joomla Integration Docs:** [https://world-search-council.github.io/wsc-docs/platforms/joomla/](https://world-search-council.github.io/wsc-docs/platforms/joomla/)
- **License:** [GNU General Public License v2.0 or later (GPL-2.0-or-later)](LICENSE)
