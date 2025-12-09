<?php
// admin_index.php
// expects: $recs (array), 'search_query', 'total', '_csrf'
$recs = $recs ?? [];
$search_query = $search_query ?? '';
$total = $total ?? count($recs);
$_csrf = $_csrf ?? ($_SESSION['_csrf'] ?? '');
?>
<h1>Gestion des Réclamations</h1>

<form method="get" action="/greentechhub/public/index.php" style="margin-bottom:16px;">
  <input type="hidden" name="route" value="admin/reclamation">
  <div style="display:flex; gap:8px; align-items:center;">
    <input name="q" placeholder="Rechercher (sujet, description, utilisateur, priorité, statut)." value="<?= htmlspecialchars($search_query) ?>" style="flex:1; padding:10px; border-radius:6px; border:1px solid #ccc;">
    <button class="btn btn-default" type="submit" style="padding:10px 14px;">Rechercher</button>
    <div style="margin-left:auto; color:var(--text-muted);">Résultats: <?= (int)$total ?></div>
  </div>
</form>

<div class="card p-20" style="background:#fff; border-radius:10px;">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>UTILISATEUR</th>
                <th>SUJET</th>
                <th>PRIORITÉ</th>
                <th>STATUT</th>
                <th>RÉPONSE</th>
                <th>RÉPONDU PAR</th>
                <th>DATE CRÉATION</th>
                <th class="text-right">ACTIONS</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($recs as $r): ?>
            <tr>
                <td><?= $r['id_reclamation'] ?></td>
                <td><?= htmlspecialchars($r['user_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['sujet']) ?></td>
                <td><?= htmlspecialchars($r['priority']) ?></td>

                <td>
                  <!-- change status form -->
                  <form method="post" action="/greentechhub/public/index.php?route=admin/reclamation/changeStatus&id=<?= $r['id_reclamation'] ?>" style="display:inline-block;">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_csrf) ?>">
                    <select name="statut" onchange="this.form.submit()" style="padding:8px; border-radius:6px;">
                      <option value="en attente" <?= ($r['statut'] ?? '') === 'en attente' ? 'selected' : '' ?>>En attente</option>
                      <option value="en cours" <?= ($r['statut'] ?? '') === 'en cours' ? 'selected' : '' ?>>En cours</option>
                      <option value="résolue" <?= ($r['statut'] ?? '') === 'résolue' ? 'selected' : '' ?>>Résolue</option>
                    </select>
                  </form>
                </td>

                <td style="min-width:120px;">
                  <?php if (!empty($r['response_text'])): ?>
                    <?= nl2br(htmlspecialchars($r['response_text'])) ?>
                  <?php else: ?>
                    <!-- inline respond form -->
                    <form method="post" action="/greentechhub/public/index.php?route=admin/reclamation/respond&id=<?= $r['id_reclamation'] ?>" style="display:flex; gap:6px; align-items:center;">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_csrf) ?>">
                      <input type="text" name="response_text" placeholder="Réponse rapide..." style="padding:6px; border-radius:6px; min-width:160px;">
                      <button class="btn btn-sm btn-default" type="submit" style="padding:6px 8px;">OK</button>
                    </form>
                  <?php endif; ?>
                </td>

                <td style="white-space:nowrap;">
                    <?= htmlspecialchars($r['responder_name'] ?? '—') ?><br>
                    <small style="color:#888;"><?= htmlspecialchars($r['response_date'] ?? '') ?></small>
                </td>

                <td><?= htmlspecialchars($r['date_creation']) ?></td>

                <td class="text-right">
                    <a href="/greentechhub/public/index.php?route=admin/reclamation/show&id=<?= $r['id_reclamation'] ?>">Voir</a> |
                    <a href="/greentechhub/public/index.php?route=admin/reclamation/delete&id=<?= $r['id_reclamation'] ?>" onclick="return confirm('Supprimer ?')">Suppr</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</div>
