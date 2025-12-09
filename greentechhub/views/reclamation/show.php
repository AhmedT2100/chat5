<h1>Réclamation #<?= $rec['id_reclamation'] ?></h1>

<div class="card p-20" style="background:#fff; border-radius:10px; border:1px solid #eee;">

    <h3><?= htmlspecialchars($rec['sujet']) ?></h3>

    <p><strong>Nom complet:</strong> <?= htmlspecialchars($rec['full_name']) ?></p>
    <p><strong>Téléphone:</strong> <?= htmlspecialchars($rec['mobile_phone']) ?></p>
    <p><strong>Priorité:</strong> <span class="badge"><?= htmlspecialchars($rec['priority']) ?></span></p>
    <p><strong>Statut:</strong> <span class="badge"><?= htmlspecialchars($rec['statut']) ?></span></p>

    <hr>

    <h4>Description</h4>
    <p><?= nl2br(htmlspecialchars($rec['description'])) ?></p>

    <?php if (!empty($rec['response_text'])): ?>
        <hr>
        <h4>Réponse de l’Admin</h4>
        <div class="alert info">
            <?= nl2br(htmlspecialchars($rec['response_text'])) ?><br>
            <small><em>Répondu le <?= $rec['response_date'] ?></em></small>
        </div>
    <?php endif; ?>

    <?php if(!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <hr>
        <h4>Ajouter / Modifier la réponse</h4>

        <form method="post" action="/greentechhub/public/index.php?route=admin/reclamation/respond&id=<?= $rec['id_reclamation'] ?>">
<input type="hidden" name="_csrf" value="<?= htmlspecialchars($_csrf ?? '') ?>">
            <textarea name="response_text" rows="5"><?= htmlspecialchars($rec['response_text']) ?></textarea>
            <button class="btn btn-default mt-10">Enregistrer</button>
        </form>
    <?php endif; ?>

    <br>
    <a href="/greentechhub/public/index.php?route=reclamation" class="btn btn-outline">Retour</a>

</div>
