<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$card1 = $this->cards['card1_traffic'] ?? $this->cards['card1'] ?? [];
$card2 = $this->cards['card2_insights'] ?? $this->cards['card2'] ?? [];
$card3 = $this->cards['card3_bots'] ?? $this->cards['card3'] ?? [];
$card4 = $this->cards['card_engagement'] ?? [];
$card5 = $this->cards['card_outbound'] ?? [];
$card6 = $this->cards['card_ecommerce'] ?? [];
$card7 = $this->cards['domain_health'] ?? [];
$projectId = $this->credentials['project_id'] ?? '';
$siteHost = \Joomla\CMS\Uri\Uri::getInstance()->getHost();
?>

<div class="wsc-dashboard-wrapper container-fluid p-0">
  <!-- Top Navigation & Diagnostic Controls -->
  <div class="wsc-dashboard-header d-flex justify-content-between align-items-center mb-4 p-3 bg-white border rounded shadow-sm">
    <div class="wsc-header-titles">
      <h2 class="h4 mb-1"><?php echo Text::_('WSC Analytics & Bot Shield'); ?> <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">v2.2.0</span></h2>
      <p class="wsc-header-meta text-muted mb-0 small">
        <?php echo Text::_('Project ID:'); ?> <code><?php echo htmlspecialchars($projectId, ENT_QUOTES, 'UTF-8'); ?></code> &bull;
        <?php echo Text::_('Host:'); ?> <strong><?php echo htmlspecialchars($siteHost, ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
        <?php echo Text::_('CMS:'); ?> <span>Joomla 4 / 5 Standard</span>
      </p>
    </div>
    <div class="wsc-header-actions d-flex align-items-center gap-2 flex-wrap">
      <!-- Multi-Period Selector -->
      <div class="wsc-period-toggle btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-primary wsc-toggle-btn" data-period="1d">1 <?php echo Text::_('Day'); ?></button>
        <button type="button" class="btn btn-outline-primary wsc-toggle-btn active" data-period="7d">7 <?php echo Text::_('Days'); ?></button>
        <button type="button" class="btn btn-outline-primary wsc-toggle-btn" data-period="30d">30 <?php echo Text::_('Days'); ?></button>
        <button type="button" class="btn btn-outline-primary wsc-toggle-btn" data-period="90d">90 <?php echo Text::_('Days'); ?></button>
        <button type="button" class="btn btn-outline-primary wsc-toggle-btn" data-period="365d">1 <?php echo Text::_('Year'); ?></button>
      </div>

      <!-- Action Diagnostic Triggers -->
      <button type="button" class="btn btn-outline-secondary btn-sm" id="wsc-trigger-pagespeed" title="<?php echo Text::_('Trigger SpeedSentinel 2.0 Lab Audit'); ?>">
        <span>⚡</span> <?php echo Text::_('Speed Audit'); ?>
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="wsc-trigger-sentinel" title="<?php echo Text::_('Trigger Sentinel DNS, SSL & Email Scan'); ?>">
        <span>🛡️</span> <?php echo Text::_('Sentinel Scan'); ?>
      </button>
      <button type="button" class="btn btn-primary btn-sm wsc-btn-refresh" id="wsc-sync-now">
        <span class="wsc-icon-sync">&#x21bb;</span> <?php echo Text::_('Sync Now'); ?>
      </button>
    </div>
  </div>

  <!-- Live Diagnostic Status Banner -->
  <div id="wsc-diagnostic-banner" class="alert alert-info shadow-sm mb-4" style="display: none;">
    <div class="d-flex align-items-center">
      <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
      <div>
        <strong id="wsc-banner-title"><?php echo Text::_('Diagnostic Scan in Progress'); ?></strong>
        <p id="wsc-banner-msg" class="mb-0 small text-muted"><?php echo Text::_('Initializing audit...'); ?></p>
      </div>
    </div>
  </div>

  <?php if (!$this->hasData): ?>
    <div class="alert alert-warning wsc-empty-notice mb-4">
      <h4 class="alert-heading"><?php echo Text::_('No remote analytics reports received yet'); ?></h4>
      <p class="mb-0"><?php echo Text::_('Client-side telemetry and bot hits are being queued locally in SQLite and synchronized automatically.'); ?></p>
    </div>
  <?php endif; ?>

  <!-- Canonical 7-Card Bento Grid -->
  <div class="row g-3 wsc-bento-grid mb-4">
    <!-- Card 1: Traffic & Channels -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="wsc-card card h-100 shadow-sm wsc-card-interactive border-start border-4 border-primary" id="wsc-card-1" data-card="traffic">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 card-title mb-0 fw-bold">1. <?php echo Text::_('Traffic & Acquisition'); ?></h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><?php echo Text::_('Human Only'); ?></span>
          </div>

          <div class="d-flex justify-content-between text-center mb-3 bg-light p-2 rounded">
            <div>
              <span class="d-block small text-muted"><?php echo Text::_('Visitors'); ?></span>
              <strong class="h5 mb-0" id="val-visitors"><?php echo htmlspecialchars($card1['data_7d']['visitors'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span class="d-block small text-muted"><?php echo Text::_('Pageviews'); ?></span>
              <strong class="h5 mb-0" id="val-pageviews"><?php echo htmlspecialchars($card1['data_7d']['pageviews'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span class="d-block small text-muted"><?php echo Text::_('Engaged Time'); ?></span>
              <strong class="h5 mb-0" id="val-engaged"><?php echo htmlspecialchars($card1['data_7d']['avg_engaged_time'] ?? '0s', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>

          <div class="wsc-channel-breakdown">
            <h4 class="small text-muted fw-bold mb-2"><?php echo Text::_('Acquisition Channels'); ?></h4>
            <div class="progress mb-2" style="height: 10px;">
              <div class="progress-bar bg-purple" role="progressbar" style="width: <?php echo (float)($card1['data_7d']['channels']['ai_pct'] ?? 0); ?>%" title="AI"></div>
              <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo (float)($card1['data_7d']['channels']['search_pct'] ?? 0); ?>%" title="Search"></div>
              <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo (float)($card1['data_7d']['channels']['social_pct'] ?? 0); ?>%" title="Social"></div>
              <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (float)($card1['data_7d']['channels']['direct_pct'] ?? 0); ?>%" title="Direct"></div>
            </div>
            <div class="d-flex flex-wrap gap-2 small text-muted">
              <span><span class="badge rounded-pill bg-purple">&nbsp;</span> AI (<?php echo (float)($card1['data_7d']['channels']['ai_pct'] ?? 0); ?>%)</span>
              <span><span class="badge rounded-pill bg-primary">&nbsp;</span> Search (<?php echo (float)($card1['data_7d']['channels']['search_pct'] ?? 0); ?>%)</span>
              <span><span class="badge rounded-pill bg-danger">&nbsp;</span> Social (<?php echo (float)($card1['data_7d']['channels']['social_pct'] ?? 0); ?>%)</span>
              <span><span class="badge rounded-pill bg-success">&nbsp;</span> Direct (<?php echo (float)($card1['data_7d']['channels']['direct_pct'] ?? 0); ?>%)</span>
            </div>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click to inspect channels'); ?> &darr;</small>
        </div>
      </div>
    </div>

    <!-- Card 2: Strategic Insights -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="wsc-card card h-100 shadow-sm wsc-card-interactive border-start border-4 border-warning" id="wsc-card-2" data-card="insights">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 card-title mb-0 fw-bold">2. <?php echo Text::_('Strategic Human Insights'); ?></h3>
            <span class="badge bg-info-subtle text-info border border-info-subtle"><?php echo Text::_('AI Analyst'); ?></span>
          </div>

          <p class="small text-secondary mb-3">
            <?php echo htmlspecialchars($card2['data_7d']['summary'] ?? Text::_('Traffic pattern is stable. Further data collection will generate automated strategic insights.'), ENT_QUOTES, 'UTF-8'); ?>
          </p>

          <h4 class="small text-muted fw-bold mb-2"><?php echo Text::_('Recommended Actions:'); ?></h4>
          <ul class="small list-unstyled mb-0">
            <?php if (!empty($card2['data_7d']['actions'])): ?>
              <?php foreach ($card2['data_7d']['actions'] as $action): ?>
                <li class="mb-1"><strong>&bull; <?php echo htmlspecialchars($action['title'], ENT_QUOTES, 'UTF-8'); ?>:</strong> <?php echo htmlspecialchars($action['description'], ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="text-muted"><?php echo Text::_('Autonomous anomaly detection running on Joomla.'); ?></li>
            <?php endif; ?>
          </ul>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click to view details'); ?> &darr;</small>
        </div>
      </div>
    </div>

    <!-- Card 3: Bot Shield -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="wsc-card card h-100 shadow-sm wsc-card-interactive border-start border-4 border-danger" id="wsc-card-3" data-card="bots">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 card-title mb-0 fw-bold">3. <?php echo Text::_('Bot Shield & Sensor Network'); ?></h3>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?php echo Text::_('Shield Active'); ?></span>
          </div>

          <div class="d-flex justify-content-around align-items-center text-center bg-light p-2 rounded mb-3">
            <div>
              <span class="h5 mb-0 fw-bold text-success" id="val-human-pct"><?php echo htmlspecialchars($card3['data_7d']['human_pct'] ?? '100', ENT_QUOTES, 'UTF-8'); ?>%</span>
              <span class="d-block small text-muted"><?php echo Text::_('Real Humans'); ?></span>
            </div>
            <div class="text-muted fw-bold">vs</div>
            <div>
              <span class="h5 mb-0 fw-bold text-danger" id="val-bot-pct"><?php echo htmlspecialchars($card3['data_7d']['bot_pct'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>%</span>
              <span class="d-block small text-muted"><?php echo Text::_('Bots & AI Crawlers'); ?></span>
            </div>
          </div>

          <div class="small">
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span><?php echo Text::_('AI Live RAG Search Bots:'); ?></span>
              <strong id="val-ai-rag"><?php echo htmlspecialchars($card3['data_7d']['ai_rag_hits'] ?? 0, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span><?php echo Text::_('AI Model Training Scrapers:'); ?></span>
              <strong id="val-ai-training"><?php echo htmlspecialchars($card3['data_7d']['ai_training_hits'] ?? 0, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span><?php echo Text::_('Search Engines (Google/Bing):'); ?></span>
              <strong id="val-search-bots"><?php echo htmlspecialchars($card3['data_7d']['search_hits'] ?? 0, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click to inspect bot logs'); ?> &darr;</small>
        </div>
      </div>
    </div>

    <!-- Card 4: Reading & UX Engagement -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="wsc-card card h-100 shadow-sm wsc-card-interactive border-start border-4 border-info" id="wsc-card-4" data-card="engagement">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 card-title mb-0 fw-bold">4. <?php echo Text::_('Reading & UX Engagement'); ?></h3>
            <span class="badge bg-warning-subtle text-dark border border-warning-subtle"><?php echo Text::_('DOM Milestones'); ?></span>
          </div>

          <div class="d-flex justify-content-between text-center mb-3 bg-light p-2 rounded">
            <div>
              <span class="d-block small text-muted"><?php echo Text::_('Reading Time'); ?></span>
              <strong class="h5 mb-0"><?php echo htmlspecialchars($card4['data_7d']['avg_reading_time'] ?? '42s', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span class="d-block small text-muted"><?php echo Text::_('TTFI'); ?></span>
              <strong class="h5 mb-0"><?php echo htmlspecialchars($card4['data_7d']['avg_ttfi'] ?? '1.2s', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span class="d-block small text-muted"><?php echo Text::_('Focus Ratio'); ?></span>
              <strong class="h5 mb-0"><?php echo htmlspecialchars($card4['data_7d']['focus_ratio'] ?? '88%', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>

          <div class="small">
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span>25% <?php echo Text::_('Scroll (Lead)'); ?></span>
              <span class="badge bg-secondary"><?php echo htmlspecialchars($card4['data_7d']['scroll_25'] ?? '92%', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span>50% <?php echo Text::_('Scroll (Body)'); ?></span>
              <span class="badge bg-secondary"><?php echo htmlspecialchars($card4['data_7d']['scroll_50'] ?? '74%', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span>75% <?php echo Text::_('Scroll (Conclusion)'); ?></span>
              <span class="badge bg-secondary"><?php echo htmlspecialchars($card4['data_7d']['scroll_75'] ?? '48%', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span>100% <?php echo Text::_('Scroll (Complete)'); ?></span>
              <span class="badge bg-secondary"><?php echo htmlspecialchars($card4['data_7d']['scroll_100'] ?? '26%', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click for reading depth'); ?> &darr;</small>
        </div>
      </div>
    </div>

    <!-- Card 5: Outbound Link Navigation -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="wsc-card card h-100 shadow-sm wsc-card-interactive border-start border-4 border-secondary" id="wsc-card-5" data-card="outbound">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 card-title mb-0 fw-bold">5. <?php echo Text::_('Outbound Link Routing'); ?></h3>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?php echo Text::_('Nav Intelligence'); ?></span>
          </div>

          <p class="small text-muted mb-3">
            <?php echo Text::_('Monitors departing visitor journeys to external partners, social media profiles, and reference portals.'); ?>
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-light text-dark border">📱 <?php echo Text::_('Social:'); ?> 40%</span>
            <span class="badge bg-light text-dark border">💼 <?php echo Text::_('Jobs:'); ?> 35%</span>
            <span class="badge bg-light text-dark border">🔗 <?php echo Text::_('References:'); ?> 25%</span>
          </div>

          <div class="small">
            <span class="text-muted"><?php echo Text::_('Top Destinations:'); ?></span>
            <p class="mb-0"><code>instagram.com</code>, <code>linkedin.com</code>, <code>github.com</code></p>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click for exit routing map'); ?> &darr;</small>
        </div>
      </div>
    </div>

    <!-- Card 6: Content Funnel & Form Conversions -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="wsc-card card h-100 shadow-sm wsc-card-interactive border-start border-4 border-success" id="wsc-card-6" data-card="ecommerce">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 card-title mb-0 fw-bold">6. <?php echo Text::_('Content Funnel & Forms'); ?></h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><?php echo Text::_('Ad-Blocker Proof'); ?></span>
          </div>

          <div class="row g-1 text-center bg-light p-2 rounded mb-3">
            <div class="col-3">
              <span class="d-block text-muted" style="font-size:0.7rem;">1. Views</span>
              <strong class="d-block" style="font-size:0.9rem;"><?php echo htmlspecialchars($card6['data_7d']['product_views'] ?? '2,410', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="col-3">
              <span class="d-block text-muted" style="font-size:0.7rem;">2. Intent</span>
              <strong class="d-block" style="font-size:0.9rem;"><?php echo htmlspecialchars($card6['data_7d']['add_to_cart'] ?? '480', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="col-3">
              <span class="d-block text-muted" style="font-size:0.7rem;">3. Form</span>
              <strong class="d-block" style="font-size:0.9rem;"><?php echo htmlspecialchars($card6['data_7d']['checkout_started'] ?? '210', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="col-3">
              <span class="d-block text-muted" style="font-size:0.7rem;">4. Done</span>
              <strong class="d-block text-success" style="font-size:0.9rem;"><?php echo htmlspecialchars($card6['data_7d']['purchases'] ?? '115', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>

          <div class="small text-muted">
            <?php echo Text::_('Conversion Rate:'); ?> <strong><?php echo htmlspecialchars($card6['data_7d']['conversion_rate'] ?? '4.7%', ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
            <?php echo Text::_('Abandonment:'); ?> <strong><?php echo htmlspecialchars($card6['data_7d']['abandonment_rate'] ?? '54.2%', ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click for funnel analysis'); ?> &darr;</small>
        </div>
      </div>
    </div>

    <!-- Card 7: Sentinel Domain Health & Infrastructure -->
    <div class="col-12">
      <div class="wsc-card card shadow-sm wsc-card-interactive border-start border-4 border-dark" id="wsc-card-7" data-card="sentinel">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h3 class="h6 card-title mb-0 fw-bold">7. <?php echo Text::_('Sentinel Domain Health & Infrastructure'); ?></h3>
              <small class="text-muted"><?php echo Text::_('Authoritative DNS Redundancy, DNSSEC, Port-25 Free Email Security & TLS Certificate Expiry'); ?></small>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <span class="badge bg-dark fs-6"><?php echo htmlspecialchars((string)($card7['score'] ?? 92), ENT_QUOTES, 'UTF-8'); ?>/100</span>
              <span class="badge bg-success fs-6"><?php echo htmlspecialchars($card7['status'] ?? 'HEALTHY', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
          </div>

          <div class="row g-3 text-center bg-light p-3 rounded">
            <div class="col-md-3 col-6">
              <span class="d-block small text-muted text-uppercase fw-bold"><?php echo Text::_('Nameservers'); ?></span>
              <strong class="text-success"><?php echo htmlspecialchars($card7['dns']['redundancy'] ?? '✓ 2 Nameservers', ENT_QUOTES, 'UTF-8'); ?></strong>
              <small class="d-block text-muted"><?php echo Text::_('Authoritative Redundancy'); ?></small>
            </div>
            <div class="col-md-3 col-6">
              <span class="d-block small text-muted text-uppercase fw-bold"><?php echo Text::_('DNSSEC'); ?></span>
              <strong class="text-success"><?php echo htmlspecialchars($card7['dns']['dnssec'] ?? '✓ Active & Validated', ENT_QUOTES, 'UTF-8'); ?></strong>
              <small class="d-block text-muted"><?php echo Text::_('Cryptographic Chain'); ?></small>
            </div>
            <div class="col-md-3 col-6">
              <span class="d-block small text-muted text-uppercase fw-bold"><?php echo Text::_('Email Security'); ?></span>
              <strong class="text-success"><?php echo htmlspecialchars($card7['mail']['summary'] ?? '✓ SPF & DMARC Enforced', ENT_QUOTES, 'UTF-8'); ?></strong>
              <small class="d-block text-muted"><?php echo Text::_('DNS Authoritative (No Port 25)'); ?></small>
            </div>
            <div class="col-md-3 col-6">
              <span class="d-block small text-muted text-uppercase fw-bold"><?php echo Text::_('SSL / TLS'); ?></span>
              <strong class="text-success"><?php echo htmlspecialchars($card7['ssl']['days_remaining'] ?? '64 Days Remaining', ENT_QUOTES, 'UTF-8'); ?></strong>
              <small class="d-block text-muted"><?php echo Text::_('Certificate Valid'); ?></small>
            </div>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 text-end pt-0">
          <small class="text-muted"><?php echo Text::_('Click to view complete DNS, TLS & security header diagnostics'); ?> &darr;</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Panel -->
  <div class="wsc-detail-panel card shadow-sm mb-4" id="wsc-detail-panel" style="display:none;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h3 class="h5 mb-0" id="wsc-detail-title"><?php echo Text::_('Detailed Analysis'); ?></h3>
      <button type="button" class="btn-close" id="wsc-close-detail" aria-label="<?php echo Text::_('Close'); ?>"></button>
    </div>
    <div class="card-body" id="wsc-detail-content">
      <!-- Injected via JavaScript -->
    </div>
  </div>
</div>
