<?php
require_once __DIR__ . '/functions.php'; exiger_connexion();
$liste = array_values(lire_etudiants()); $q = trim((string)($_GET['q'] ?? '')); $tri = (string)($_GET['tri'] ?? 'ajout');
if ($q !== '') $liste = array_values(array_filter($liste, static fn(array $e): bool => stripos($e['nom'], $q) !== false || stripos($e['prenom'], $q) !== false));
if (!in_array($tri, ['ajout','nom','filiere','note'], true)) $tri = 'ajout';
usort($liste, static function(array $a, array $b) use ($tri): int { return match($tri) {
    'nom' => strcasecmp($a['nom'],$b['nom']) ?: strcasecmp($a['prenom'],$b['prenom']),
    'filiere' => strcasecmp($a['filiere'],$b['filiere']) ?: strcasecmp($a['nom'],$b['nom']),
    'note' => ($b['note'] <=> $a['note']) ?: strcasecmp($a['nom'],$b['nom']),
    default => $a['id'] <=> $b['id'],
}; });
$n=count($liste); $moy=$n ? array_sum(array_column($liste,'note'))/$n : null; $max=$n ? max(array_column($liste,'note')) : null; $parFiliere=[];
foreach($liste as $e) $parFiliere[$e['filiere']][]=$e['note'];
$message=$_SESSION['flash'] ?? null; unset($_SESSION['flash']); $titre='Liste des étudiants'; include __DIR__ . '/header.php';
?>
<section class="page-heading"><div><span class="eyebrow">VOTRE PROMOTION</span><h1>Étudiants</h1><p>Retrouvez et gérez les dossiers de votre promotion.</p></div><a class="button button-primary" href="ajouter.php">＋ Ajouter un étudiant</a></section>
<?php if(is_array($message)): ?><div class="alert <?= ($message['type']??'')==='error'?'alert-error':'alert-success' ?>" role="status"><?= h((string)($message['message']??'')) ?></div><?php endif; ?>
<section class="stats-grid" aria-label="Statistiques">
  <article class="stat-card"><span class="stat-label">Étudiants affichés</span><strong><?= $n ?></strong><span class="stat-caption">résultat<?= $n===1?'':'s' ?> actuel<?= $n===1?'':'s' ?></span></article>
  <article class="stat-card"><span class="stat-label">Moyenne des notes</span><strong><?= $moy===null?'—':h(number_format($moy,2,',',' ')) ?><small><?= $moy===null?'':'/ 20' ?></small></strong><span class="stat-caption">sur la sélection actuelle</span></article>
  <article class="stat-card"><span class="stat-label">Meilleure note</span><strong><?= $max===null?'—':h(number_format((float)$max,2,',',' ')) ?><small><?= $max===null?'':'/ 20' ?></small></strong><span class="stat-caption">sur la sélection actuelle</span></article>
  <article class="stat-card"><span class="stat-label">Moyenne par filière</span><div class="program-list"><?php foreach(FILIERES as $f): $mf=!empty($parFiliere[$f])?array_sum($parFiliere[$f])/count($parFiliere[$f]):null; ?><span><b><?= h($f) ?></b><?= $mf===null?'—':h(number_format($mf,2,',',' ').' / 20') ?></span><?php endforeach; ?></div></article>
</section>
<section class="card directory-card"><div class="directory-toolbar"><div><h2>Annuaire</h2><p><?= $n ?> fiche<?= $n===1?'':'s' ?> dans les résultats</p></div>
  <form method="get" class="filter-form"><label class="sr-only" for="q">Rechercher un étudiant</label><div class="search-wrap"><span aria-hidden="true">⌕</span><input type="search" id="q" name="q" value="<?= h($q) ?>" placeholder="Rechercher un nom ou prénom…" data-live-search></div>
  <label class="sr-only" for="tri">Trier par</label><select id="tri" name="tri"><option value="ajout" <?= $tri==='ajout'?'selected':'' ?>>Ordre d’ajout</option><option value="nom" <?= $tri==='nom'?'selected':'' ?>>Nom</option><option value="filiere" <?= $tri==='filiere'?'selected':'' ?>>Filière</option><option value="note" <?= $tri==='note'?'selected':'' ?>>Note décroissante</option></select><button class="button button-secondary" type="submit">Filtrer</button></form></div>
  <div class="table-scroll"><table class="student-table"><thead><tr><th>Étudiant</th><th>Email</th><th>Filière</th><th>Note</th><th class="actions-cell">Actions</th></tr></thead><tbody>
  <?php if(!$n): ?><tr class="empty-row"><td colspan="5">Aucun étudiant ne correspond à votre recherche.</td></tr><?php endif; ?>
  <?php foreach($liste as $e): $grade=$e['note']>=10?'grade-good':'grade-low'; ?><tr data-student-row data-search="<?= h(mb_strtolower($e['nom'].' '.$e['prenom'])) ?>">
    <td><div class="student-cell"><?php if($e['photo']!=='' && is_file(__DIR__.'/uploads/'.basename($e['photo']))): ?><img class="avatar" src="uploads/<?= rawurlencode($e['photo']) ?>" alt="Photo de <?= h($e['prenom'].' '.$e['nom']) ?>"><?php else: ?><span class="avatar avatar-placeholder"><?= h(mb_strtoupper(mb_substr($e['prenom'],0,1))) ?></span><?php endif; ?><span><strong><?= h(mb_strtoupper($e['nom'])) ?></strong><small><?= h(mb_strtoupper(mb_substr($e['prenom'],0,1)).mb_strtolower(mb_substr($e['prenom'],1))) ?></small></span></div></td>
    <td><?= $e['email']!==''?h($e['email']):'<span class="muted">—</span>' ?></td><td><span class="tag"><?= h($e['filiere']) ?></span></td><td><span class="grade <?= $grade ?>"><?= h(number_format((float)$e['note'],2,',',' ')) ?><small>/20</small></span></td>
    <td class="actions-cell"><div class="row-actions"><a class="icon-button" href="ajouter.php?id=<?= (int)$e['id'] ?>" aria-label="Modifier <?= h($e['prenom'].' '.$e['nom']) ?>" title="Modifier">✎</a><form method="post" action="supprimer.php" data-delete-form><input type="hidden" name="csrf" value="<?= h(jeton_csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$e['id'] ?>"><button class="icon-button danger-icon" type="submit" aria-label="Supprimer <?= h($e['prenom'].' '.$e['nom']) ?>" title="Supprimer">⌫</button></form></div></td>
  </tr><?php endforeach; ?><tr class="live-empty" hidden><td colspan="5">Aucun étudiant ne correspond à votre recherche.</td></tr>
  </tbody></table></div></section>
<?php include __DIR__ . '/footer.php'; ?>
