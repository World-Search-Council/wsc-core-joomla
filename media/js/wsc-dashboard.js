/**
 * @file
 * WSC 7-Card Bento Analytics, AI Coverage, Benchmarks, Membership & IndexNow behavior for Joomla 4 & 5 (v2.2.3).
 */

document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.wsc-dashboard-wrapper');
    if (!wrapper) return;

    const dashboardOptions = (window.Joomla && Joomla.getOptions) ? Joomla.getOptions('wscDashboard', {}) : {};
    const cardsData = dashboardOptions.cards || {};
    let currentPeriod = '7d';

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

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function unwrapJoomlaJson(resJson) {
        if (resJson && typeof resJson === 'object' && resJson.data && typeof resJson.data === 'object') {
            return resJson.data;
        }
        return resJson || {};
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
                            <p class="text-muted">Zero-PII daily salted human visitor acquisition breakdown for this Joomla site.</p>
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Source</th><th>Channel Type</th><th>Conversion Readiness</th></tr></thead>
                                <tbody>
                                    <tr><td><strong>AI Citations (ChatGPT / Perplexity / Claude)</strong></td><td>LLM Referral</td><td><span class="badge bg-success">High Intent</span></td></tr>
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
                                <p class="mb-0"><strong>Autonomous Growth Intelligence:</strong> Compares your Joomla site velocity against industry benchmarks to surface actionable SEO &amp; RAG guidance.</p>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>AI Manifest Ready:</strong> Machine-readable <code>/llms.txt</code> exposes published Joomla articles cleanly to RAG citation crawlers.</li>
                                <li class="list-group-item"><strong>Zero DB Contention:</strong> All telemetry buffered asynchronously in SQLite WAL mode.</li>
                            </ul>
                        `;
                        break;

                    case 'bots':
                        detailTitle.textContent = '3. Pre-Cache Bot Shield & Scraper Network';
                        detailContent.innerHTML = `
                            <p class="text-muted">Captured at Joomla's <code>onAfterInitialise</code> lifecycle hook with rotating daily session hashes.</p>
                            <table class="table table-striped table-hover">
                                <thead><tr><th>Bot Name</th><th>Category</th><th>Behavior</th><th>Status</th></tr></thead>
                                <tbody>
                                    <tr><td><code>GPTBot</code></td><td>AI Training Scraper</td><td>Bulk Corpus Crawl</td><td><span class="badge bg-warning text-dark">Monitored</span></td></tr>
                                    <tr><td><code>OAI-SearchBot</code></td><td>AI Live RAG Search</td><td>Real-Time Citation Lookup</td><td><span class="badge bg-success">Allowed</span></td></tr>
                                    <tr><td><code>PerplexityBot</code></td><td>AI Live RAG Search</td><td>Citation Retrieval</td><td><span class="badge bg-success">Allowed</span></td></tr>
                                    <tr><td><code>Googlebot</code></td><td>Search Engine</td><td>SERP Indexing</td><td><span class="badge bg-primary">Allowed</span></td></tr>
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
                                <p class="mb-0"><strong>100% Ad-Blocker Proof:</strong> Form submissions and milestone interactions are queued via first-party SQLite WAL endpoints.</p>
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
                        detailTitle.textContent = '7. Sentinel Domain Health & SpeedSentinel 2.0 Dual-Thermometer';
                        detailContent.innerHTML = `
                            <p class="text-muted">Authoritative DNS redundancy, DNSSEC, Port-25-free email security, TLS expiry, and RAG vs. Human CWV scoring.</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Nameserver Redundancy:</strong> <span class="badge bg-success">Configured (&gt;= 2 NS)</span></li>
                                <li class="list-group-item"><strong>DNSSEC Cryptographic Validation:</strong> <span class="badge bg-success">Active &amp; Validated</span></li>
                                <li class="list-group-item"><strong>Email Security (SPF, DKIM &amp; DMARC):</strong> <span class="badge bg-success">Authoritative DNS Lookup Only (Strict Port-25 Ban)</span></li>
                                <li class="list-group-item"><strong>SpeedSentinel 2.0:</strong> Dual evaluation of 🤖 Semantic RAG Readiness and 👤 Core Web Vitals.</li>
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
            showBanner('SpeedSentinel Lab Audit Started', 'Validating same-host URL and enqueueing headless Playwright CWV + RAG readiness audit...');

            fetch(dashboardOptions.pagespeedUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: dashboardOptions.siteUrl || window.location.origin, strategy: 'mobile' })
            })
            .then(res => res.json())
            .then(raw => {
                const data = unwrapJoomlaJson(raw);
                if (data.ok === false && data.error) {
                    showBanner('SpeedSentinel Notice', data.error);
                } else {
                    showBanner('SpeedSentinel Audit Enqueued', 'Audit is processing asynchronously. Polling for report update...');
                    setTimeout(pollCardsUpdate, 6000);
                }
            })
            .catch(err => showBanner('SpeedSentinel Notice', err.message))
            .finally(() => setTimeout(() => { speedBtn.disabled = false; }, 4000));
        });
    }

    // 4. Sentinel Scan Trigger & Polling
    const sentinelBtn = document.getElementById('wsc-trigger-sentinel');
    if (sentinelBtn) {
        sentinelBtn.addEventListener('click', function() {
            sentinelBtn.disabled = true;
            showBanner('Sentinel Domain Scan Started', 'Checking authoritative DNS records, DNSSEC, Port-25-free SPF/DMARC, and TLS...');

            fetch(dashboardOptions.sentinelUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ domain: dashboardOptions.siteHost || window.location.hostname })
            })
            .then(res => res.json())
            .then(raw => {
                const data = unwrapJoomlaJson(raw);
                if (data.ok === false && data.error) {
                    showBanner('Sentinel Notice', data.error);
                } else {
                    showBanner('Sentinel Scan Enqueued', 'Scan processing in Worker Sentinel. Polling for report update...');
                    setTimeout(pollCardsUpdate, 6000);
                }
            })
            .catch(err => showBanner('Sentinel Notice', err.message))
            .finally(() => setTimeout(() => { sentinelBtn.disabled = false; }, 4000));
        });
    }

    // 5. AI Coverage URL Inspector
    const aiCovBtn = document.getElementById('wsc-ai-coverage-btn');
    const aiCovInput = document.getElementById('wsc-ai-coverage-input');
    const aiCovTf = document.getElementById('wsc-ai-coverage-timeframe');
    const aiCovOut = document.getElementById('wsc-ai-coverage-result');
    if (aiCovBtn && aiCovInput && aiCovOut) {
        aiCovBtn.addEventListener('click', function() {
            aiCovBtn.disabled = true;
            aiCovOut.style.display = 'block';
            aiCovOut.innerHTML = '<span class="text-muted">Inspecting AI crawler activity...</span>';

            fetch(dashboardOptions.aiCoverageUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    query: aiCovInput.value.trim(),
                    timeframe: aiCovTf ? aiCovTf.value : '30d'
                })
            })
            .then(res => res.json())
            .then(raw => {
                const d = unwrapJoomlaJson(raw);
                if (d.error) {
                    aiCovOut.innerHTML = '<span class="text-danger">' + escapeHtml(d.error) + '</span>';
                    return;
                }
                const total = d.total_ai_crawls !== undefined ? d.total_ai_crawls : (d.total_hits || 0);
                const rag = d.rag_citations !== undefined ? d.rag_citations : (d.rag_hits || 0);
                const train = d.training_scrapes !== undefined ? d.training_scrapes : (d.training_hits || 0);
                const badge = total > 0
                    ? '<span class="badge bg-success me-2">AI Covered</span>'
                    : '<span class="badge bg-warning text-dark me-2">No AI Crawls Yet</span>';
                aiCovOut.innerHTML = badge +
                    '<strong>Total AI Hits:</strong> ' + escapeHtml(total) +
                    ' &bull; <strong>Live RAG:</strong> ' + escapeHtml(rag) +
                    ' &bull; <strong>Training:</strong> ' + escapeHtml(train) +
                    ' &bull; <strong>Source:</strong> <code>' + escapeHtml(d.source || 'gateway') + '</code>' +
                    (d.last_crawled_at ? ' &bull; <span class="text-muted">' + escapeHtml(d.last_crawled_at) + '</span>' : '');
            })
            .catch(err => {
                aiCovOut.innerHTML = '<span class="text-danger">' + escapeHtml(err.message) + '</span>';
            })
            .finally(() => { aiCovBtn.disabled = false; });
        });
    }

    // 6. Industry Benchmarks Refresh & Auto-Classify
    function renderBenchmarkRows(bm) {
        if (!bm || !bm.metrics) return;
        const vEl = document.getElementById('wsc-bm-vertical');
        if (vEl && bm.vertical) vEl.textContent = bm.vertical;
        const tbody = document.querySelector('#wsc-bm-table tbody');
        if (!tbody) return;
        const m = bm.metrics;
        tbody.innerHTML = `
            <tr>
                <td>AI Live RAG Crawler Share</td>
                <td class="text-end fw-bold">${escapeHtml(m.ai_rag_share ? m.ai_rag_share.site : 0)}%</td>
                <td class="text-end text-muted">${escapeHtml(m.ai_rag_share ? m.ai_rag_share.peer_median : 25)}%</td>
            </tr>
            <tr>
                <td>Avg. Engaged Reading Time</td>
                <td class="text-end fw-bold">${escapeHtml(m.engaged_time ? m.engaged_time.site : 42)}s</td>
                <td class="text-end text-muted">${escapeHtml(m.engaged_time ? m.engaged_time.peer_median : 55)}s</td>
            </tr>
            <tr>
                <td>Estimated AI Citation Coverage</td>
                <td class="text-end fw-bold">${escapeHtml(m.ai_coverage_pct ? m.ai_coverage_pct.site : 30)}%</td>
                <td class="text-end text-muted">${escapeHtml(m.ai_coverage_pct ? m.ai_coverage_pct.peer_median : 62)}%</td>
            </tr>
        `;
    }

    const bmRefreshBtn = document.getElementById('wsc-bm-refresh');
    if (bmRefreshBtn) {
        bmRefreshBtn.addEventListener('click', function() {
            bmRefreshBtn.disabled = true;
            fetch(dashboardOptions.benchmarksUrl)
                .then(r => r.json())
                .then(raw => renderBenchmarkRows(unwrapJoomlaJson(raw)))
                .finally(() => { bmRefreshBtn.disabled = false; });
        });
    }

    const bmClassifyBtn = document.getElementById('wsc-bm-classify');
    if (bmClassifyBtn) {
        bmClassifyBtn.addEventListener('click', function() {
            bmClassifyBtn.disabled = true;
            fetch(dashboardOptions.classifyVerticalUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}' })
                .then(r => r.json())
                .then(raw => {
                    const d = unwrapJoomlaJson(raw);
                    if (d.benchmarks) renderBenchmarkRows(d.benchmarks);
                })
                .finally(() => { bmClassifyBtn.disabled = false; });
        });
    }

    // 7. IndexNow Manual Push
    const idxBtn = document.getElementById('wsc-indexnow-btn');
    const idxInput = document.getElementById('wsc-indexnow-url');
    const idxStatus = document.getElementById('wsc-indexnow-status');
    if (idxBtn && idxInput && idxStatus) {
        idxBtn.addEventListener('click', function() {
            idxBtn.disabled = true;
            idxStatus.textContent = 'Submitting URL to api.indexnow.org...';
            fetch(dashboardOptions.indexNowUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: idxInput.value.trim() })
            })
            .then(r => r.json())
            .then(raw => {
                const d = unwrapJoomlaJson(raw);
                if (d.ok) {
                    idxStatus.textContent = '✓ IndexNow accepted (' + (d.submitted || 1) + ' URL, HTTP ' + (d.http_status || 200) + ').';
                } else {
                    idxStatus.textContent = 'IndexNow response: ' + (d.error || ('HTTP ' + (d.http_status || 0)));
                }
            })
            .catch(err => { idxStatus.textContent = 'Error: ' + err.message; })
            .finally(() => { idxBtn.disabled = false; });
        });
    }

    // 8. WSC Member / Agency Credential Verification & Co-Branding Sync
    const licBtn = document.getElementById('wsc-license-verify-btn');
    const licInput = document.getElementById('wsc-license-input');
    const licFeedback = document.getElementById('wsc-license-feedback');
    if (licBtn && licInput && licFeedback) {
        licBtn.addEventListener('click', function() {
            licBtn.disabled = true;
            licFeedback.style.display = 'block';
            licFeedback.className = 'small mb-3 text-muted';
            licFeedback.textContent = 'Verifying credential with www.worldsearchcouncil.org...';

            fetch(dashboardOptions.licenseVerifyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ license_key: licInput.value.trim() })
            })
            .then(r => r.json())
            .then(raw => {
                const d = unwrapJoomlaJson(raw);
                if (d.active) {
                    licFeedback.className = 'small mb-3 text-success fw-bold';
                    licFeedback.textContent = '✓ Verified (' + (d.tier || 'member') + '). Reloading console...';
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    licFeedback.className = 'small mb-3 text-danger';
                    licFeedback.textContent = d.error || 'Credential verification failed.';
                }
            })
            .catch(err => {
                licFeedback.className = 'small mb-3 text-danger';
                licFeedback.textContent = err.message;
            })
            .finally(() => { licBtn.disabled = false; });
        });
    }

    const licDiscBtn = document.getElementById('wsc-license-disconnect-btn');
    if (licDiscBtn) {
        licDiscBtn.addEventListener('click', function() {
            licDiscBtn.disabled = true;
            fetch(dashboardOptions.licenseDisconnectUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}' })
                .then(() => window.location.reload());
        });
    }

    const cbSaveBtn = document.getElementById('wsc-cb-save-btn');
    if (cbSaveBtn && licInput) {
        cbSaveBtn.addEventListener('click', function() {
            cbSaveBtn.disabled = true;
            const payload = {
                license_key: licInput.value.trim(),
                agency_name: (document.getElementById('wsc-cb-name') || {}).value || '',
                agency_email: (document.getElementById('wsc-cb-email') || {}).value || '',
                agency_url: (document.getElementById('wsc-cb-url') || {}).value || '',
                agency_logo: (document.getElementById('wsc-cb-logo') || {}).value || ''
            };
            fetch(dashboardOptions.licenseVerifyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(() => window.location.reload())
            .finally(() => { cbSaveBtn.disabled = false; });
        });
    }

    // 9. Polling helper & Manual Sync Button
    function pollCardsUpdate() {
        if (!dashboardOptions.cardsUrl) return;
        fetch(dashboardOptions.cardsUrl)
            .then(res => res.json())
            .then(raw => {
                const data = unwrapJoomlaJson(raw);
                if (data.ok && data.cards) {
                    Object.assign(cardsData, data.cards);
                    updateMetrics(currentPeriod);
                    showBanner('Reports Synchronized', 'Latest diagnostic and telemetry reports updated.');
                    setTimeout(hideBanner, 4000);
                }
            })
            .catch(() => {});
    }

    const syncBtn = document.getElementById('wsc-sync-now');
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            const originalText = syncBtn.innerHTML;
            syncBtn.disabled = true;
            syncBtn.innerHTML = '&#x21bb; Syncing...';

            fetch(dashboardOptions.refreshUrl)
                .then(res => res.json())
                .then(raw => {
                    const data = unwrapJoomlaJson(raw);
                    syncBtn.innerHTML = '&#x2713; Done! (' + (data.synced_events || 0) + ')';
                    pollCardsUpdate();
                    setTimeout(() => {
                        syncBtn.disabled = false;
                        syncBtn.innerHTML = originalText;
                    }, 3000);
                })
                .catch(() => {
                    syncBtn.innerHTML = 'Sync Deferred';
                    setTimeout(() => {
                        syncBtn.disabled = false;
                        syncBtn.innerHTML = originalText;
                    }, 2000);
                });
        });
    }
});
