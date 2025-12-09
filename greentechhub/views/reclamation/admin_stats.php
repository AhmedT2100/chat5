<?php
// views/reclamation/admin_stats.php
// THIS FILE MUST NOT require header.php or footer.php because Controller::view already does.
// Expects $priorityStats and $statusStats from controller.
$priorityStats = $priorityStats ?? [];
$statusStats   = $statusStats ?? [];
?>

<style>
/* local layout for the stats page */
.stats-page {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 28px;
  align-items: start;
}
.stats-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 18px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.04);
}
.chart-container {
  width: 100%;
  height: 420px;
  display:flex;
  align-items:center;
  justify-content:center;
}
.summary-table {
  width:100%;
  border-collapse: collapse;
}
.summary-table th, .summary-table td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
  text-align: left;
}
.legend-row { display:flex; gap:12px; align-items:center; font-size:0.95rem; margin-top:12px; flex-wrap:wrap; }
.legend-swatch { width:14px; height:14px; border-radius:3px; display:inline-block; margin-right:8px; }
.no-data { color: var(--text-muted); font-style: italic; text-align:center; padding:18px 0; }
@media (max-width: 980px) {
  .stats-page { grid-template-columns: 1fr; }
  .chart-container { height: 340px; }
}
</style>

<h1>Statistiques - Réclamations</h1>

<div class="stats-page">

  <div>
    <div class="stats-card" style="margin-bottom:18px;">
      <h3>Répartition par priorité</h3>
      <div class="chart-container">
        <canvas id="priorityChart" aria-label="Répartition par priorité" role="img"></canvas>
      </div>
      <div id="priority-legend" class="legend-row"></div>
    </div>

    <div class="stats-card">
      <h3>Répartition par statut</h3>
      <div class="chart-container">
        <canvas id="statusChart" aria-label="Répartition par statut" role="img"></canvas>
      </div>
      <div id="status-legend" class="legend-row"></div>
    </div>
  </div>

  <div>
    <div class="stats-card">
      <h3>Résumé - Priorités</h3>
      <?php if (empty($priorityStats)): ?>
        <div class="no-data">Aucune donnée de priorités disponible.</div>
      <?php else: ?>
        <table class="summary-table">
          <thead><tr><th>Priorité</th><th>Nombre</th><th>%</th></tr></thead>
          <tbody>
            <?php $totalP = 0; foreach ($priorityStats as $r) $totalP += (int)$r['count']; ?>
            <?php foreach ($priorityStats as $r): ?>
              <tr>
                <td><?= htmlspecialchars(ucfirst($r['label'])) ?></td>
                <td><?= (int)$r['count'] ?></td>
                <td><?= htmlspecialchars((string)$r['percent']) ?>%</td>
              </tr>
            <?php endforeach; ?>
            <tr><td><strong>Total</strong></td><td><strong><?= (int)$totalP ?></strong></td><td><strong>100%</strong></td></tr>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="stats-card" style="margin-top:18px;">
      <h3>Résumé - Statuts</h3>
      <?php if (empty($statusStats)): ?>
        <div class="no-data">Aucune donnée de statuts disponible.</div>
      <?php else: ?>
        <table class="summary-table">
          <thead><tr><th>Statut</th><th>Nombre</th><th>%</th></tr></thead>
          <tbody>
            <?php $totalS = 0; foreach ($statusStats as $r) $totalS += (int)$r['count']; ?>
            <?php foreach ($statusStats as $r): ?>
              <tr>
                <td><?= htmlspecialchars(ucfirst($r['label'])) ?></td>
                <td><?= (int)$r['count'] ?></td>
                <td><?= htmlspecialchars((string)$r['percent']) ?>%</td>
              </tr>
            <?php endforeach; ?>
            <tr><td><strong>Total</strong></td><td><strong><?= (int)$totalS ?></strong></td><td><strong>100%</strong></td></tr>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Chart.js CDN (deferred) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const priorityData = <?= json_encode(array_values($priorityStats), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  const statusData   = <?= json_encode(array_values($statusStats), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;

  function palette(base) {
    const palettes = {
      priority: ['#FFD36A','#44A6F9','#60D1C8'],
      status: ['#FFB74D','#42A5F5','#66BB6A','#9E9E9E']
    };
    return palettes[base] || palettes.priority;
  }
  function buildDonut(id, data, palKey, legendId) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (!data || data.length === 0) {
      canvas.parentNode.innerHTML = '<div class="no-data">Pas de données</div>';
      return;
    }
    const labels = data.map(d => String(d.label));
    const counts = data.map(d => Number(d.count || 0));
    const colors = palette(palKey).slice(0, labels.length);
    const ctx = canvas.getContext('2d');
    // size canvas to parent
    const parent = canvas.parentElement;
    canvas.width = parent.clientWidth;
    canvas.height = parent.clientHeight;
    const chart = new Chart(ctx, {
      type: 'doughnut',
      data: { labels, datasets: [{ data: counts, backgroundColor: colors, borderColor:'#fff', borderWidth:2 }] },
      options: { maintainAspectRatio:false, cutout:'56%', plugins:{legend:{display:false}, tooltip:{enabled:true}} }
    });
    // legend
    const legendWrap = document.getElementById(legendId);
    if (legendWrap) {
      legendWrap.innerHTML = '';
      labels.forEach((lab, idx) => {
        const el = document.createElement('div');
        el.style.display = 'flex';
        el.style.alignItems = 'center';
        el.style.marginRight = '12px';
        el.innerHTML = `<span class="legend-swatch" style="background:${colors[idx]}"></span>${lab}`;
        legendWrap.appendChild(el);
      });
    }
    window.addEventListener('resize', () => { canvas.width = parent.clientWidth; canvas.height = parent.clientHeight; chart.resize(); });
    return chart;
  }

  buildDonut('priorityChart', priorityData, 'priority', 'priority-legend');
  buildDonut('statusChart', statusData, 'status', 'status-legend');
});
</script>
