<?php
require_once __DIR__ . '/functions.php';
if (connecte()) { header('Location: liste.php'); exit; }
$erreur = ''; $login = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['login'], $_POST['mdp'])) {
    verifier_csrf();
    $login = trim((string)($_POST['login'] ?? '')); $mdp = (string)($_POST['mdp'] ?? '');
    if (isset(UTILISATEURS[$login]) && hash_equals(UTILISATEURS[$login], $mdp)) {
        session_regenerate_id(true); $_SESSION['login'] = $login;
        setcookie('dernier_user', $login, ['expires'=>time()+7*86400, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
        flash('success', 'Bienvenue, ' . $login . ' !'); header('Location: liste.php'); exit;
    }
    $erreur = 'Identifiant ou mot de passe incorrect.';
} else { $cookie = $_COOKIE['dernier_user'] ?? ''; $login = is_string($cookie) ? $cookie : ''; }
$titre = 'Connexion'; include __DIR__ . '/header.php';
?>
<section class="login-layout"><div class="login-intro"><span class="eyebrow">ESPACE DE GESTION</span><h1>Les étudiants,<br><span>en un seul endroit.</span></h1><p>Gérez les inscriptions, suivez les résultats et retrouvez les informations de votre promotion en quelques clics.</p><div class="intro-note"><span class="intro-icon">✓</span><span>Un outil simple pour garder le campus organisé.</span></div></div>
<section class="card login-card"><div class="card-heading"><span class="eyebrow">BON RETOUR</span><h2>Connexion</h2><p>Entrez vos identifiants pour continuer.</p></div>
<?php if ($erreur): ?><div class="alert alert-error" role="alert"><?= h($erreur) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf" value="<?= h(jeton_csrf()) ?>"><label for="login">Identifiant</label><input id="login" name="login" type="text" autocomplete="username" required value="<?= h($login) ?>" placeholder="Votre identifiant"><label for="mdp">Mot de passe</label><input id="mdp" name="mdp" type="password" autocomplete="current-password" required placeholder="Votre mot de passe"><button class="button button-primary button-wide" type="submit">Se connecter <span>→</span></button></form>
<p class="demo-hint">Compte de démonstration : <strong>admin</strong> / <strong>admin123</strong></p></section></section>
<?php include __DIR__ . '/footer.php'; ?>
