<?php
declare(strict_types=1);

const FICHIER = __DIR__ . '/data/etudiants.txt';
const FILIERES = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS = ['admin' => 'admin123'];
const MAX_PHOTO_BYTES = 1048576;

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function connecte(): bool { return isset($_SESSION['login']) && is_string($_SESSION['login']); }
function exiger_connexion(): void { if (!connecte()) { header('Location: index.php'); exit; } }
function nettoyer(string $s): string { return trim(str_replace([';', "\r", "\n", "\0"], '', $s)); }

function lire_etudiants(): array
{
    if (!is_file(FICHIER)) return [];
    $liste = [];
    foreach (file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $ligne) {
        $c = explode(';', $ligne);
        if (count($c) !== 7 || !ctype_digit($c[0])) continue;
        [$id, $nom, $prenom, $filiere, $note, $photo, $email] = $c;
        $liste[(int)$id] = ['id'=>(int)$id, 'nom'=>$nom, 'prenom'=>$prenom, 'filiere'=>$filiere, 'note'=>(float)$note, 'photo'=>$photo, 'email'=>$email];
    }
    return $liste;
}

function sauver_etudiants(array $liste): void
{
    $lignes = [];
    foreach ($liste as $e) $lignes[] = implode(';', [(int)$e['id'], nettoyer((string)$e['nom']), nettoyer((string)$e['prenom']), nettoyer((string)$e['filiere']), number_format((float)$e['note'], 2, '.', ''), nettoyer((string)($e['photo'] ?? '')), nettoyer((string)($e['email'] ?? ''))]);
    file_put_contents(FICHIER, $lignes ? implode(PHP_EOL, $lignes) . PHP_EOL : '', LOCK_EX);
}

function jeton_csrf(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifier_csrf(): void
{
    $v = $_POST['csrf'] ?? '';
    if (!is_string($v) || !hash_equals(jeton_csrf(), $v)) { http_response_code(400); exit('Requête invalide. Rechargez la page puis réessayez.'); }
}

function enregistrer_photo(array $f, ?string &$erreur): ?string
{
    $erreur = null;
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($f['error'] ?? -1) !== UPLOAD_ERR_OK) { $erreur = 'Le téléversement de la photo a échoué.'; return null; }
    if (($f['size'] ?? 0) > MAX_PHOTO_BYTES) { $erreur = 'La photo ne doit pas dépasser 1 Mo.'; return null; }
    $ext = strtolower(pathinfo((string)($f['name'] ?? ''), PATHINFO_EXTENSION));
    $types = ['jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png'];
    $tmp = (string)($f['tmp_name'] ?? '');
    $mime = $tmp !== '' && is_file($tmp) ? (new finfo(FILEINFO_MIME_TYPE))->file($tmp) : false;
    if (!isset($types[$ext]) || $mime !== $types[$ext]) { $erreur = 'Choisissez une image JPG, JPEG ou PNG valide.'; return null; }
    $nom = uniqid('etu_', true) . '.' . $ext;
    if (!move_uploaded_file($tmp, __DIR__ . '/uploads/' . $nom)) { $erreur = 'Impossible d’enregistrer la photo.'; return null; }
    return $nom;
}
function supprimer_photo(?string $nom): void
{
    if (!$nom || basename($nom) !== $nom) return;
    $p = __DIR__ . '/uploads/' . $nom;
    if (is_file($p)) unlink($p);
}
function flash(string $type, string $message): void { $_SESSION['flash'] = ['type'=>$type, 'message'=>$message]; }

function initialiser_theme(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'theme') return;
    verifier_csrf();
    $theme = ($_POST['theme'] ?? '') === 'sombre' ? 'sombre' : 'clair';
    setcookie('theme', $theme, ['expires'=>time()+31536000, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    $page = basename((string)($_SERVER['PHP_SELF'] ?? 'index.php'));
    $query = $_GET ? '?' . http_build_query($_GET) : '';
    header('Location: ' . $page . $query);
    exit;
}
initialiser_theme();
