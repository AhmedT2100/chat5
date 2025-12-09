<h1>Mes Réclamations</h1>

<?php if (!empty($_GET['created'])): ?>
    <div class="alert success">Réclamation créée avec succès.</div>
<?php endif; ?>

<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert success">Réclamation supprimée.</div>
<?php endif; ?>

<div class="card p-20" style="background:#fff; border:1px solid #eee; border-radius:8px;">

    <div class="text-right mb-20">
        <a href="/greentechhub/public/index.php?route=reclamation/create" class="btn btn-default">
            Nouvelle Réclamation
        </a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Sujet</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Date</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recs as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['sujet']) ?></td>
                    <td><?= htmlspecialchars($r['priority']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($r['statut']) ?></span></td>
                    <td><?= htmlspecialchars($r['date_creation']) ?></td>

                    <td class="text-right">
                        <a href="/greentechhub/public/index.php?route=reclamation/show&id=<?= $r['id_reclamation'] ?>">Voir</a> |
                        <a href="/greentechhub/public/index.php?route=reclamation/edit&id=<?= $r['id_reclamation'] ?>">Modifier</a> |
                        <a href="/greentechhub/public/index.php?route=reclamation/delete&id=<?= $r['id_reclamation'] ?>" onclick="return confirm('Supprimer ?')">Suppr</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
