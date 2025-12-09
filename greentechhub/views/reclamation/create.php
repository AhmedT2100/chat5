<h1>Nouvelle Réclamation</h1>

<div class="card p-20" style="background:#fff; border-radius:8px; border:1px solid #eee;">
    <form id="reclamationForm" method="post" action="/greentechhub/public/index.php?route=reclamation/store" class="form">

        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_csrf) ?>">

        <div class="form-group">
            <label for="full_name">Nom complet</label>
            <input id="full_name" type="text" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="mobile_phone">Téléphone</label>
            <input id="mobile_phone" type="tel" name="mobile_phone" value="<?= htmlspecialchars($old['mobile_phone'] ?? '') ?>" placeholder="ex: 12345678 or +21612345678">
        </div>

        <div class="form-group">
            <label for="sujet">Sujet</label>
            <input id="sujet" type="text" name="sujet" value="<?= htmlspecialchars($old['sujet'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="priority">Priorité</label>
            <select id="priority" name="priority">
                <option value="low">Basse</option>
                <option value="normal" selected>Normale</option>
                <option value="high">Haute</option>
            </select>
        </div>

        <button class="btn btn-default">Créer</button>

    </form>
</div>
