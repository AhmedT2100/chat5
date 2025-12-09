<h1>Créer un compte</h1>

<?php if(!empty($errors) && is_array($errors)): foreach($errors as $e): ?>
  <div class="alert error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; endif; ?>

<form method="post" action="/greentechhub/public/index.php?route=auth/register" class="form">
  <div class="form-group">
    <label>Nom complet</label>
    <input name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label>Email</label>
    <input name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label>Mot de passe</label>
    <input type="password" name="password">
  </div>

  <div class="form-group">
    <label>
      <input type="radio" name="role" value="user" <?= ( ($old['role'] ?? '') !== 'admin') ? 'checked' : '' ?>> Utilisateur
    </label>
    <label style="margin-left:10px;">
      <input type="radio" name="role" value="admin" <?= ( ($old['role'] ?? '') === 'admin') ? 'checked' : '' ?>> Administrateur
    </label>
  </div>

  <div class="form-group">
    <label>Code admin (si vous choisissez Administrateur)</label>
    <input name="admin_passcode" value="">
    <div class="form-help">Si vous voulez créer un compte admin, entrez le code administrateur requis.</div>
  </div>

  <button class="btn btn-default" type="submit">S'inscrire</button>
</form>
