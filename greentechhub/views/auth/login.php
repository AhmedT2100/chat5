<h1>Connexion</h1>

<?php if(!empty($error)): ?>
  <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="/greentechhub/public/index.php?route=auth/login" class="form">
  <div class="form-group">
    <label>Email</label>
    <input name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label>Mot de passe</label>
    <input type="password" name="password">
  </div>
  <button class="btn btn-default" type="submit">Se connecter</button>
  <p><a href="/greentechhub/public/index.php?route=auth/register">Créer un compte</a></p>
</form>
