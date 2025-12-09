<h1>Modifier la réclamation</h1>

<form id="reclamationForm" method="post" action="/greentechhub/public/index.php?route=reclamation/update&id=<?= $rec['id_reclamation'] ?>" class="form">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_csrf ?? '') ?>">

  <div class="form-group">
    <label for="full_name">Nom complet</label>
    <input id="full_name" name="full_name" value="<?= htmlspecialchars($rec['full_name'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="mobile_phone">Téléphone mobile</label>
    <input id="mobile_phone" name="mobile_phone" value="<?= htmlspecialchars($rec['mobile_phone'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="priority">Priorité</label>
    <select id="priority" name="priority">
      <option value="low" <?= ($rec['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Faible</option>
      <option value="normal" <?= ($rec['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normale</option>
      <option value="high" <?= ($rec['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Haute</option>
    </select>
  </div>

  <div class="form-group">
    <label for="sujet">Sujet</label>
    <input id="sujet" name="sujet" value="<?= htmlspecialchars($rec['sujet'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description"><?= htmlspecialchars($rec['description'] ?? '') ?></textarea>
  </div>

  <button class="btn btn-default" type="submit">Mettre à jour</button>
</form>
