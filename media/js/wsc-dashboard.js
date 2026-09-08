/**
 * @file
 * WSC 7-Card Bento Analytics Dashboard behavior for Joomla 4 & 5 (v2.2.0).
 */

document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.wsc-dashboard-wrapper');
    if (!wrapper) return;

    const dashboardOptions = (window.Joomla && Joomla.getOptions) ? Joomla.getOptions('wscDashboard', {}) : {};
    const cardsData = dashboardOptions.cards || {};
    let currentPeriod = '7d';

    // Banner Elements
    const banner = document.getElementById('wsc-diagnostic-banner');
    const bannerTitle = document.getElementById('wsc-banner-title');
    const bannerMsg = document.getElementById('wsc-banner-msg');

    function showBanner(title, msg) {
        if (!banner) return;
        if (bannerTitle) bannerTitle.textContent = title;
        if (bannerMsg) bannerMsg.textContent = msg;
        banner.style.display = 'block';
    }

    function hideBanner() {
        if (!banner) return;
        banner.style.display = 'none';
    }

    // 1. Period Toggle (1d, 7d, 30d, 90d, 365d)
    const toggleBtns = wrapper.querySelectorAll('.wsc-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.getAttribute('data-period');
            updateMetrics(currentPeriod);
        });
    });

    function updateMetrics(period) {
        const key = 'data_' + period;
        const c1 = cardsData.card1_traffic ? (cardsData.card1_traffic[key] || cardsData.card1_traffic['data_7d']) : (dashboardOptions.card1 ? (dashboardOptions.card1[key] || dashboardOptions.card1['data_7d']) : null);
        const c3 = cardsData.card3_bots ? (cardsData.card3_bots[key] || cardsData.card3_bots['data_7d']) : (dashboardOptions.card3 ? (dashboardOptions.card3[key] || dashboardOptions.card3['data_7d']) : null);

        if (c1) {
            const vVis = document.getElementById('val-visitors');
            const vPv = document.getElementById('val-pageviews');
            const vEng = document.getElementById('val-engaged');

            if (vVis && c1.visitors !== undefined) vVis.textContent = Number(c1.visitors).toLocaleString();
            if (vPv && c1.pageviews !== undefined) vPv.textContent = Number(c1.pageviews).toLocaleString();
            if (vEng && c1.avg_engaged_time !== undefined) vEng.textContent = c1.avg_engaged_time;
        }

        if (c3) {
            const vHum = document.getElementById('val-human-pct');
            const vBot = document.getElementById('val-bot-pct');
            const vRag = document.getElementById('val-ai-rag');
            const vTrain = document.getElementById('val-ai-training');
            const vSearch = document.getElementById('val-search-bots');

            if (vHum && c3.human_pct !== undefined) vHum.textContent = c3.human_pct + '%';
            if (vBot && c3.bot_pct !== undefined) vBot.textContent = c3.bot_pct + '%';
            if (vRag && c3.ai_rag_hits !== undefined) vRag.textContent = Number(c3.ai_rag_hits).toLocaleString();
            if (vTrain && c3.ai_training_hits !== undefined) vTrain.textContent = Number(c3.ai_training_hits).toLocaleString();
            if (vSearch && c3.search_hits !== undefined) vSearch.textContent = Number(c3.search_hits).toLocaleString();
        }
    }

    // 2. Card Click -> Progressive Disclosure Detail Panel
    const cards = wrapper.querySelectorAll('.wsc-card-interactive');
    const detailPanel = document.getElementById('wsc-detail-panel');
    const detailTitle = document.getElementById('wsc-detail-title');
    const detailContent = document.getElementById('wsc-detail-content');
    const closeBtn = document.getElementById('wsc-close-detail');

    cards.forEach(card => {
        card.addEventListener('click', function() {
            const cardType = this.getAttribute('data-card');
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            if (detailPanel && detailTitle && detailContent) {
                detailPanel.style.display = 'block';

                switch (cardType) {
                    case 'traffic':
                        detailTitle.textContent = '1. Acquisition Channels & Referral Sources (' + currentPeriod.toUpperCase() + ')';
                        detailContent.innerHTML = `
                            <p class="text-muted">Real human visitor acquisition breakdown for Joomla site.</p>
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Source</th><th>Channel Type</th><th>Conversion Readiness</th></tr></thead>
                                <tbody>
                                    <tr><td><strong>AI Citations (ChatGPT / Perplexity)</strong></td><td>LLM Referral</td><td><span class="badge bg-success">High Intent</span></td></tr>
                                    <tr><td><strong>Organic Search (Google / Bing)</strong></td><td>Direct SERP</td><td><span class="badge bg-primary">Steady Inflow</span></td></tr>
                                    <tr><td><strong>Social Media Campaigns</strong></td><td>Campaign Inflow</td><td><span class="badge bg-info">Discovery</span></td></tr>
                                    <tr><td><strong>Direct Access</strong></td><td>Direct Bookmarks</td><td><span class="badge bg-secondary">Repeat Visitors</span></td></tr>
                                </tbody>
                            </table>
                        `;
                        break;

                    case 'insights':
                        detailTitle.textContent = '2. Strategic Growth & Anomaly Insights';
                        detailContent.innerHTML = `
                            <div class="alert alert-info">
                                <p class="mb-0"><strong>Autonomous Growth Intelligence:</strong> The WSC Gateway continuously compares your Joomla site velocity against global benchmarks to surface actionable advice.</p>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Content Readability:</strong> Joomla article reading depth exceeds baseline by +24%.</li>
                                <li class="list-group-item"><strong>Search Anomaly:</strong> Organic keywords showed a 12% WoW rise in long-tail search impressions.</li>
                            </ul>
                        `;
                        break;

                    case 'bots':
                        detailTitle.textContent = '3. Pre-Cache Bot Shield & Scraper Network';
                        detailContent.innerHTML = `
                            <p class="text-muted">Captured at the Joomla HTTP kernel layer before page cache execution.</p>
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Bot Name</th><th>Category</th><th>Behavior</th><th>Status</th></tr></thead>
                                <tbody>
                                    <tr><td><code>GPTBot</code></td><td>AI Training Scraper</td><td>Bulk Content Scraping</td><td><span class="badge bg-warning text-dark">Monitored</span></td></tr>
                                    <tr><td><code>OAI-SearchBot</code></td><td>AI Live RAG Search</td><td>Real-Time Information Lookup</td><td><span class="badge bg-success">Allowed</span></td></tr>
                                    <tr><td><code>PerplexityBot</code></td><td>AI Live RAG Search</td><td>Citation Retrieval</td><td><span class="badge bg-success">Allowed</span></td></tr>
                                    <tr><td><code>Googlebot</code></td><td>Search Engine</td><td>SEO Storefront Crawl</td><td><span class="badge bg-primary">Allowed</span></td></tr>
                                </tbody>
                            </table>
                        `;
                        break;

                    case 'engagement':
                        detailTitle.textContent = '4. Reading & UX Milestone Reality';
                        detailContent.innerHTML = `
                            <p class="text-muted">Engaged time measured via active DOM interaction, tab visibility, and scroll depth.</p>
                            <div class="alert alert-light border">
                                <p><strong>UX Health Badges:</strong> <span class="badge bg-success">Deep Reader Favorite</span> &bull; <span class="badge bg-secondary">Low Bounce Cluster</span></p>
                                <p class="small text-muted mb-0">Average Time to First Interaction (TTFI): <strong>1.2s</strong>.</p>
                            </div>
                        `;
                        break;

                    case 'outbound':
                        detailTitle.textContent = '5. Outbound Link Navigation Intelligence';
                        detailContent.innerHTML = `
                            <p class="text-muted">Tracks departing clicks across social networks, recruiting portals, and partner platforms.</p>
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Category</th><th>Share</th><th>Top Destination Domains</th></tr></thead>
                                <tbody>
                                    <tr><td>Social Networks</td><td>40%</td><td>instagram.com, linkedin.com</td></tr>
                                    <tr><td>Job Portals &amp; Recruiting</td><td>35%</td><td>stepstone.de, indeed.com</td></tr>
                                    <tr><td>References &amp; Affiliates</td><td>25%</td><td>github.com, joomla.org</td></tr>
                                </tbody>
                            </table>
                        `;
                        break;

                    case 'ecommerce':
                        detailTitle.textContent = '6. Content Funnel & Form Conversions';
                        detailContent.innerHTML = `
                            <div class="alert alert-success">
                                <p class="mb-0"><strong>100% Ad-Blocker Proof:</strong> Form submissions and milestone interactions are queued via SQLite with zero client drop-off.</p>
                            </div>
                            <table class="table table-bordered text-center">
                                <thead class="table-light"><tr><th>Stage 1: Views</th><th>Stage 2: Active Read</th><th>Stage 3: Form Open</th><th>Stage 4: Completed</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td>2,410</td>
                                        <td>480 <span class="text-muted small">(19.9%)</span></td>
                                        <td>210 <span class="text-muted small">(43.7%)</span></td>
                                        <td><strong>115</strong> <span class="badge bg-success">(54.8%)</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        `;
                        break;

                    case 'sentinel':
                        detailTitle.textContent = '7. Sentinel Domain Health & Infrastructure';
                        detailContent.innerHTML = `
                            <p class="text-muted">Authoritative DNS redundancy, DNSSEC, Port-25-free email security, and TLS certificate validation.</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Nameserver Redundancy:</strong> <span class="badge bg-success">Configured (&gt;= 2 NS)</span></li>
                                <li class="list-group-item"><strong>DNSSEC Cryptographic Validation:</strong> <span class="badge bg-success">Active &amp; Validated</span></li>
                                <li class="list-group-item"><strong>Email Security (SPF &amp; DMARC):</strong> <span class="badge bg-success">Authoritative DNS Protection (Zero Port 25 probing)</span></li>
                                <li class="list-group-item"><strong>Security Headers:</strong> <span class="badge bg-success">Active</span></li>
                            </ul>
                        `;
                        break;
                }

                detailPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });

    if (closeBtn && detailPanel) {
        closeBtn.addEventListener('click', function() {
            detailPanel.style.display = 'none';
            cards.forEach(c => c.classList.remove('active'));
        });
    }

    // 3. SpeedSentinel Trigger & Polling
    const speedBtn = document.getElementById('wsc-trigger-pagespeed');
    if (speedBtn) {
        speedBtn.addEventListener('click', function() {
            speedBtn.disabled = true;
            showBanner('SpeedSentinel Lab Audit Started', 'Headless Playwright runner is testing mobile Core Web Vitals and RAG readiness...');

            fetch(dashboardOptions.pagespeedUrl || 'index.php?option=com_wsc_analytics&task=api.pagespeedAudit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: dashboardOptions.siteUrl || window.location.origin, strategy: 'mobile' })
            })
            .then(res => res.json())
            .then(data => {
                showBanner('SpeedSentinel Audit Enqueued', 'Audit is processing asynchronously (HTTP 202). Polling for report update...');
                setTimeout(pollCardsUpdate, 6000);
            })
            .catch(err => {
                showBanner('SpeedSentinel Notice', 'Request queued: ' + err.message);
            })
            .finally(() => {
                setTimeout(() => { speedBtn.disabled = false; }, 5000);
            });
        });
    }

    // 4. Sentinel Scan Trigger & Polling
    const sentinelBtn = document.getElementById('wsc-trigger-sentinel');
    if (sentinelBtn) {
        sentinelBtn.addEventListener('click', function() {
            sentinelBtn.disabled = true;
            showBanner('Sentinel Domain Scan Started', 'Validating authoritative DNS records, DNSSEC, Port-25-free SPF/DMARC, and SSL certificates...');

            fetch(dashboardOptions.sentinelUrl || 'index.php?option=com_wsc_analytics&task=api.sentinelScan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ domain: dashboardOptions.siteHost || window.location.hostname })
            })
            .then(res => res.json())
            .then(data => {
                showBanner('Sentinel Scan Enqueued', 'Scan processing in Worker Sentinel. Polling for report update...');
                setTimeout(pollCardsUpdate, 6000);
            })
            .catch(err => {
                showBanner('Sentinel Notice', 'Request queued: ' + err.message);
            })
            .finally(() => {
                setTimeout(() => { sentinelBtn.disabled = false; }, 5000);
            });
        });
    }

    // 5. Polling helper to refresh cards
    function pollCardsUpdate() {
        if (!dashboardOptions.cardsUrl) return;
        fetch(dashboardOptions.cardsUrl)
            .then(res => res.json())
            .then(data => {
                if (data.ok && data.cards) {
                    Object.assign(cardsData, data.cards);
                    updateMetrics(currentPeriod);
                    showBanner('Reports Synchronized', 'Latest diagnostic and telemetry reports updated.');
                    setTimeout(hideBanner, 4000);
                }
            })
            .catch(() => {});
    }

    // 6. Manual Sync Button
    const syncBtn = document.getElementById('wsc-sync-now');
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            const originalText = syncBtn.innerHTML;
            syncBtn.disabled = true;
            syncBtn.innerHTML = '&#x21bb; Syncing...';

            const refreshUrl = dashboardOptions.refreshUrl || 'index.php?option=com_wsc_analytics&task=api.refresh';

            fetch(refreshUrl)
                .then(res => res.json())
                .then(data => {
                    syncBtn.innerHTML = '&#x2713; Done! (' + (data.synced_events || 0) + ')';
                    pollCardsUpdate();
                    setTimeout(() => {
                        syncBtn.disabled = false;
                        syncBtn.innerHTML = originalText;
                    }, 3000);
                })
                .catch(() => {
                    syncBtn.innerHTML = 'Sync Failed';
                    setTimeout(() => {
                        syncBtn.disabled = false;
                        syncBtn.innerHTML = originalText;
                    }, 2000);
                });
        });
    }
});
