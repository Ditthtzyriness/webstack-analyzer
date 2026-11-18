(() => {
  const form = document.getElementById('scan-form');
  if (!form) return;
  const input = document.getElementById('url-input');
  const button = document.getElementById('scan-button');
  const results = document.getElementById('results');
  const content = document.getElementById('result-content');
  const exportButton = document.getElementById('export-button');
  let latestReport = null;

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[char]);

  function renderReport(report) {
    latestReport = report;
    const techs = report.technologies || [];
    const groups = {};
    techs.forEach((tech) => (groups[tech.category] ||= []).push(tech));
    const cards = Object.entries(groups).map(([category, items]) => `
      <section class="result-panel">
        <h3>${escapeHtml(category)} <span style="color:#7586a4;font-weight:500">(${items.length})</span></h3>
        <div class="tech-list">${items.map((item) => `
          <div class="tech"><div><strong>${escapeHtml(item.name)}</strong><small>${escapeHtml(item.evidence || 'Public fingerprint')}</small></div><span class="confidence">${escapeHtml((item.confidence || 'medium').toUpperCase())}</span></div>
        `).join('')}</div>
      </section>`).join('');
    content.innerHTML = `
      <div class="result-summary">
        <div class="metric"><small>HTTP status</small><strong class="${report.status >= 200 && report.status < 400 ? 'ok' : ''}">${escapeHtml(report.status)} ${escapeHtml(report.status_text)}</strong></div>
        <div class="metric"><small>Technologies</small><strong>${techs.length}</strong></div>
        <div class="metric"><small>Categories</small><strong>${(report.categories || []).length}</strong></div>
        <div class="metric"><small>Resolved IP</small><strong style="font-size:14px;overflow-wrap:anywhere">${escapeHtml(report.ip || '—')}</strong></div>
      </div>
      <section class="result-panel"><h3>Target overview</h3>
        <div class="overview-lines">
          <p><small>PAGE TITLE</small><br><strong>${escapeHtml(report.title || 'No title found')}</strong></p>
          <p><small>REQUESTED URL</small><br>${escapeHtml(report.url)}</p>
          <p><small>FINAL URL</small><br>${escapeHtml(report.final_url)}</p>
        </div>
      </section>
      ${cards || '<div class="empty-state">Belum ada teknologi yang terdeteksi dari fingerprint publik halaman ini.</div>'}
      <section class="result-panel"><h3>Detection notes</h3><ul>${(report.limitations || []).map((note) => `<li style="color:#8796b1;line-height:1.8;margin:6px 0">${escapeHtml(note)}</li>`).join('')}</ul></section>
      <section class="result-panel"><h3>Interesting response headers</h3><pre style="white-space:pre-wrap;color:#9cabc5;font:11px 'DM Mono',monospace">${escapeHtml(JSON.stringify(report.headers || {}, null, 2))}</pre></section>`;
    results.hidden = false;
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const url = input.value.trim();
    if (!url) return;
    button.disabled = true;
    button.querySelector('.button-label').textContent = 'Analyzing...';
    content.innerHTML = '<div class="loading-state">Menghubungi target dan membaca fingerprint publik…</div>';
    results.hidden = false;
    try {
      const response = await fetch('/api/scan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ url })
      });
      const payload = await response.json();
      if (!response.ok || !payload.success) throw new Error(payload.error || 'Scan gagal.');
      renderReport(payload.data);
    } catch (error) {
      content.innerHTML = `<div class="error-state">${escapeHtml(error.message || 'Terjadi kesalahan saat scan.')}</div>`;
    } finally {
      button.disabled = false;
      button.querySelector('.button-label').textContent = 'Run analysis';
    }
  });

  exportButton.addEventListener('click', () => {
    if (!latestReport) return;
    const blob = new Blob([JSON.stringify(latestReport, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = `webstack-report-${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
    anchor.click();
    URL.revokeObjectURL(url);
  });
})();