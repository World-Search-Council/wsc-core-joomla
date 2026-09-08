# WSC Analytics & Bot Shield for Joomla (Joomla 4 & 5)

Official Joomla integration of the **World Search Council (WSC) Hybrid Analytics & AI Bot Shield Standard**.

Provides cookieless, privacy-first web analytics, edge AI crawler tracking via `plg_system_wsc_analytics`, automated AES-256 encrypted micro-batch synchronization to the WSC Gateway, and the standard **3-Card Smart Dashboard** for Joomla 4 and 5.

---

## 🌟 Key Features

* **Cookieless & Privacy-First**: 100% GDPR, TTDSG/TDDDG, and ePrivacy compliant without requiring cookie banners.
* **Dual-Layer Architecture**:
  * **Edge AI Sensor (`plg_system_wsc_analytics`)**: Hooks into Joomla's early `onAfterInitialise` lifecycle to detect AI crawlers (`GPTBot`, `ChatGPT-User`, `ClaudeBot`, `Perplexity`, etc.) and search engines (`Googlebot`, `Bingbot`) without rendering overhead.
  * **Client Runtime (`wsc-tracker.js`)**: Pure Vanilla JS (< 4 KB) with automated micro-batching and `sendBeacon` lifecycle dispatch.
* **Local SQLite Queuing & WAL Acceleration**:
  * Writes into `queue_temp.sqlite` without creating database lock overhead on Joomla's main MySQL/MariaDB/PostgreSQL database.
  * Reports cached in `reports_temp.sqlite` for instant dashboard loading.
* **3-Card Anti-GA4 Smart Dashboard**:
  * **Card 1**: Traffic & Acquisition Channels (AI Search Citations, Organic Search, Social, Direct, Referral).
  * **Card 2**: Strategic Human Insights & Anomaly Detection.
  * **Card 3**: Bot Shield Sensor Network & Data Quality.
  * Progressive disclosure detail row with 7-day and 30-day toggles.
* **Cryptographic Security**:
  * Micro-batches encrypted with local 256-bit AES-CBC keys.
  * Every transmission signed with `HMAC-SHA256` (`X-WSC-Sig`).

---

## 📦 Extension Structure

```text
wsc-core-joomla/
├── com_wsc_analytics.xml           # Component Manifest (Joomla 4 & 5)
├── script.php                      # Installer & auto-activation script
├── admin/
│   ├── access.xml                  # Joomla ACL permissions
│   ├── config.xml                  # Component configuration form
│   ├── services/
│   │   └── provider.php            # Joomla 4/5 DI Container Service Provider
│   ├── src/
│   │   ├── Controller/
│   │   │   ├── DisplayController.php # Admin dashboard controller
│   │   │   └── ApiController.php     # Manual sync AJAX controller
│   │   ├── Helper/
│   │   │   ├── WscCoreHelper.php     # SQLite WAL queue, AES-256, HMAC, Gateway sync
│   │   │   └── WscBotHelper.php      # AI/LLM & Search crawler taxonomy
│   │   ├── View/
│   │   │   └── Dashboard/
│   │   │       └── HtmlView.php      # Dashboard HTML View class
│   │   └── Extension/
│   │       └── WscAnalyticsComponent.php
│   └── tmpl/
│       └── dashboard/
│           └── default.php           # 3-Card Dashboard Template
├── site/
│   └── src/
│       └── Controller/
│           └── ApiController.php     # Ingestion & webhook receiver endpoint
├── plugins/
│   └── system/
│       └── wsc_analytics/
│           ├── wsc_analytics.xml     # System Plugin manifest
│           ├── wsc_analytics.php     # Legacy plugin proxy
│           └── src/
│               └── Extension/
│                   └── WscAnalytics.php # onAfterInitialise & onBeforeCompileHead
└── media/
    ├── joomla.asset.json             # Web Asset Manager definition
    ├── js/
    │   ├── wsc-tracker.js            # Canonical Vanilla JS tracker
    │   └── wsc-dashboard.js          # Interactive dashboard behavior
    └── css/
        └── wsc-dashboard.css         # Bootstrap 5 / Atum styling
```

---

## 🚀 Installation & Setup

1. Compress the contents of `wsc-core-joomla` into a ZIP archive (`pkg_wsc_analytics.zip`).
2. In the Joomla Administrator panel, navigate to **System → Extensions → Install**.
3. Upload the ZIP package. The component `com_wsc_analytics` and the system plugin `plg_system_wsc_analytics` will be installed and enabled automatically.
4. Access the dashboard under **Components → WSC Analytics & Bot Shield**.
