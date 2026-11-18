<div class="shell">
  <aside class="sidebar">
    <a class="brand" href="/"><span class="brand-mark">W</span><span>webstack<span class="brand-muted">.intel</span></span></a>
    <div class="side-label">WORKSPACE</div>
    <a class="nav-item active" href="/"><span>◈</span> Overview</a>
    <a class="nav-item" href="#scanner"><span>⌕</span> Technology scanner</a>
    <a class="nav-item" href="#api"><span>⌘</span> API reference</a>
    <div class="side-bottom">
      <div class="status-dot"></div><span>Scanner engine online</span>
      <div class="side-version">BUILD <?= htmlspecialchars($version ?? '2.0.0') ?></div>
    </div>
  </aside>
  <main class="main">
    <header class="topbar"><div class="breadcrumb">Workspace <span>/</span> Overview</div><div class="top-right"><span class="live-pill"><i></i> SYSTEM OPERATIONAL</span><div class="avatar">MC</div></div></header>
    <section class="hero">
      <div class="hero-copy"><div class="eyebrow"><span></span> WEBSITE INTELLIGENCE PLATFORM</div><h1>Know what powers<br><em>the internet.</em></h1><p>Uncover the technologies behind any public website. Frameworks, CMS, infrastructure, analytics — all in one scan.</p></div>
      <div class="hero-art"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="orb-core">W<span>✳</span></div><div class="float-chip chip-a">PHP 8.x</div><div class="float-chip chip-b">CDN</div><div class="float-chip chip-c">FRAMEWORK</div></div>
    </section>
    <section class="scanner-card" id="scanner">
      <div class="card-head"><div><div class="eyebrow">01 / SCAN TARGET</div><h2>Analyze a website</h2></div><span class="secure-label">↗ PUBLIC URL ONLY</span></div>
      <form id="scan-form">
        <label for="url-input">TARGET URL</label>
        <div class="input-row"><span class="input-icon">⌕</span><input id="url-input" name="url" type="text" placeholder="https://example.com" autocomplete="url" required><button id="scan-button" type="submit"><span class="button-label">Run analysis</span><span class="button-arrow">↗</span></button></div>
        <div class="form-foot"><span>↳ Include a domain or full URL</span><span>HTTP / HTTPS supported</span></div>
      </form>
    </section>
    <section class="results-section" id="results" hidden>
      <div class="results-heading"><div><div class="eyebrow">02 / SCAN REPORT</div><h2>Analysis results</h2></div><button class="ghost-button" id="export-button">↓ Export JSON</button></div>
      <div id="result-content"></div>
    </section>
    <section class="feature-grid">
      <article class="feature-card"><div class="feature-icon">⌘</div><div class="feature-index">CAPABILITY / 01</div><h3>Fingerprint engine</h3><p>Identify technologies through public HTML, asset paths, cookies and HTTP response headers.</p></article>
      <article class="feature-card"><div class="feature-icon">◉</div><div class="feature-index">CAPABILITY / 02</div><h3>Evidence-first results</h3><p>Each match includes its detection source and a confidence label. No fabricated versions.</p></article>
      <article class="feature-card"><div class="feature-icon">⌁</div><div class="feature-index">CAPABILITY / 03</div><h3>Developer API</h3><p>Use the JSON endpoint to connect your own tools, dashboards or Telegram integrations.</p></article>
    </section>
    <section class="api-strip" id="api"><div><div class="eyebrow">BUILT FOR YOUR WORKFLOW</div><h2>One endpoint. Structured intelligence.</h2><p>POST a URL and receive a normalized technology report.</p></div><code>POST&nbsp; /api/scan</code><a href="/api/docs" target="_blank" rel="noreferrer">View API docs ↗</a></section>
    <footer><span>WEBSTACK INTEL <b>·</b> BUILT FOR DEVELOPERS</span><span>PHP ENGINE / <?= htmlspecialchars(PHP_VERSION) ?></span></footer>
  </main>
</div>
