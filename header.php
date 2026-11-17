<?php $titre = $titre ?? 'Gestion des étudiants'; $theme = ($_COOKIE['theme'] ?? 'clair') === 'sombre' ? 'sombre' : 'clair'; ?>
<!doctype html>
<html lang="fr" data-theme="<?= h($theme) ?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($titre) ?> | Gestion des étudiants</title><link rel="stylesheet" href="style.css"><script src="script.js" defer></script>
</head>
<body>
<header class="site-header"><div class="header-inner">
  <a class="brand" href="<?= connecte() ? 'liste.php' : 'index.php' ?>"><span class="brand-mark">É</span>Campus<span class="brand-light">·</span>Étudiants</a>
  <?php if (connecte()): ?>
  <nav class="main-nav" aria-label="Navigation principale"><a href="liste.php">Étudiants</a><a href="ajouter.php">Nouvel étudiant</a><a href="export.php">Exporter CSV</a></nav>
  <div class="header-actions">
    <form method="post" class="theme-form"><input type="hidden" name="csrf" value="<?= h(jeton_csrf()) ?>"><input type="hidden" name="action" value="theme"><input type="hidden" name="theme" value="<?= $theme === 'clair' ? 'sombre' : 'clair' ?>"><button class="theme-toggle" type="submit" aria-label="Changer le thème"><?= $theme === 'clair' ? '☾' : '☀' ?></button></form>
    <span class="user-chip"><?= h((string)$_SESSION['login']) ?></span>
    <form method="post" action="logout.php" class="logout-form"><input type="hidden" name="csrf" value="<?= h(jeton_csrf()) ?>"><button class="button button-quiet button-small" type="submit">Déconnexion</button></form>
  </div><?php endif; ?>
</div></header><main class="container">
