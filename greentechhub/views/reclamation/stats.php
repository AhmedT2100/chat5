<?php
// expects $stats array from controller
$stats = $stats ?? ['low'=>0,'normal'=>0,'high'=>0,'total'=>0,'percent'=>['low'=>0,'normal'=>0,'high'=>0]];

$low = (int)($stats['low'] ?? 0);
$normal = (int)($stats['normal'] ?? 0);
$high = (int)($stats['high'] ?? 0);
$total = (int)($stats['total'] ?? ($low + $normal + $high));
$plow = $stats['percent']['low'] ?? 0;
$pnormal = $stats['percent']['normal'] ?? 0;
$phigh = $stats['percent']['high'] ?? 0;
?>
<h1>Statistiques - Priorités des Réclamations</h1>

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

  <div style="flex:1; min-width:320px; max-width:780px;">
    <div class="card p-20" style="padding:18px; border-radius:10px; box-sizing:border-box;">
      <!-- fixed-size wrapper to avoid layout jumps -->
      <div id="chart-wrapper" style="width:100%; height:520px; display:flex; align-items:center; justify-content:center;">
        <canvas id="priorityChart" style="width:100%; height:100%;"></canvas>
      </div>

      <div style="margin-top:12px; display:flex; gap:12px; align-items:center; justify-content:center; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="display:inline-block;width:18px;height:10px;background:#f9d46d;border-radius:2px;"></span> Basse
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="display:inline-block;width:18px;height:10px;background:#53a9f6;border-radius:2px;"></span> Normale
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="display:inline-block;width:18px;height:10px;background:#66c5b4;border-radius:2px;"></span> Haute
        </div>
      </div>
    </div>
  </div>

  <div style="width:360px; min-width:260px;">
    <h3>Résumé</h3>
    <div class="card p-20" style="padding:14px;">
      <table class="table" style="width:100%;">
        <thead>
          <tr>
            <th>PRIORITÉ</th>
            <th>NOMBRE</th>
            <th>%</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Basse</td>
            <td><?= $low ?></td>
            <td><?= htmlspecialchars((string)$plow) ?>%</td>
          </tr>
          <tr>
            <td>Normale</td>
            <td><?= $normal ?></td>
            <td><?= htmlspecialchars((string)$pnormal) ?>%</td>
          </tr>
          <tr>
            <td>Haute</td>
            <td><?= $high ?></td>
            <td><?= htmlspecialchars((string)$phigh) ?>%</td>
          </tr>
          <tr style="font-weight:700;">
            <td>Total</td>
            <td><?= $total ?></td>
            <td>100%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  // ensure DOM ready
  function initChart() {
    const canvas = document.getElementById('priorityChart');
    if (!canvas) return;

    // get context and container
    const ctx = canvas.getContext('2d');
    const wrapper = document.getElementById('chart-wrapper');

    // Chart data
    const data = {
      labels: ['Basse','Normale','Haute'],
      datasets: [{
        data: [<?= $low ?>, <?= $normal ?>, <?= $high ?>],
        backgroundColor: ['#f9d46d','#53a9f6','#66c5b4'],
        hoverOffset: 6,
        borderWidth: 0
      }]
    };

    const options = {
      type: 'doughnut',
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false, // we control height via wrapper
        cutout: '60%',
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                const label = ctx.label || '';
                const value = ctx.raw || 0;
                const total = <?= max(1, $total) ?>;
                const pct = ((value / total) * 100).toFixed(1);
                return label + ': ' + value + ' (' + pct + '%)';
              }
            }
          }
        },
        animation: {
          // reduce animation so it feels snappier and less likely to move the page
          duration: 400,
          easing: 'easeOutQuart'
        }
      }
    };

    // destroy previous chart instance if present
    try {
      if (window._priorityChart && window._priorityChart.destroy) {
        window._priorityChart.destroy();
      }
    } catch (e) {}

    // Defer creation one frame so layout is stable (prevents jump)
    requestAnimationFrame(function(){
      window._priorityChart = new Chart(ctx, options);

      // Extra safety: resize after a short timeout in case fonts or other styles loaded late
      setTimeout(function(){
        try {
          window._priorityChart.resize();
        } catch (e) {}
      }, 80);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChart);
  } else {
    initChart();
  }
})();
</script>
