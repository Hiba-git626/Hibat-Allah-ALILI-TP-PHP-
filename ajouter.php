<?php
require_once __DIR__ . '/functions.php'; exiger_connexion();
$etudiants=lire_etudiants(); $id=isset($_GET['id'])?(int)$_GET['id']:0; $modif=$id>0;
if($modif && !isset($etudiants[$id])) { flash('error','Cet étudiant est introuvable.'); header('Location: liste.php'); exit; }
$ancien=$modif?$etudiants[$id]:['nom'=>'','prenom'=>'','filiere'=>'','note'=>'','email'=>'','photo'=>''];
$v=['nom'=>$ancien['nom'],'prenom'=>$ancien['prenom'],'filiere'=>$ancien['filiere'],'note'=>$ancien['note'],'email'=>$ancien['email']]; $erreurs=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST') {
    verifier_csrf(); foreach(['nom','prenom','filiere','note','email'] as $k) $v[$k]=trim((string)($_POST[$k]??''));
    $v['nom']=nettoyer($v['nom']); $v['prenom']=nettoyer($v['prenom']);
    if($v['nom']==='') $erreurs[]='Le nom est obligatoire.';
    if($v['prenom']==='') $erreurs[]='Le prénom est obligatoire.';
    if(!in_array($v['filiere'],FILIERES,true)) $erreurs[]='Choisissez une filière valide.';
    if($v['note']==='' || !is_numeric($v['note']) || (float)$v['note']<0 || (float)$v['note']>20) $erreurs[]='La note doit être comprise entre 0 et 20.';
    if($v['email']!=='' && !filter_var($v['email'],FILTER_VALIDATE_EMAIL)) $erreurs[]='L’adresse e-mail n’est pas valide.';
    foreach($etudiants as $autre) if((int)$autre['id']!==$id && mb_strtolower($autre['nom'].'|'.$autre['prenom'])===mb_strtolower($v['nom'].'|'.$v['prenom'])) { $erreurs[]='Un étudiant portant déjà ce nom et ce prénom existe.'; break; }
    $photoErreur=null; $nouvelle=null;
    if(isset($_FILES['photo'])) { $nouvelle=enregistrer_photo($_FILES['photo'],$photoErreur); if($photoErreur!==null) $erreurs[]=$photoErreur; }
    if($erreurs && $nouvelle!==null) { supprimer_photo($nouvelle); $nouvelle=null; }
    if(!$erreurs) {
        $numero=$modif?$id:($etudiants?max(array_keys($etudiants))+1:1);
        $photo=$nouvelle??(string)($ancien['photo']??'');
        if($modif && $nouvelle!==null) supprimer_photo((string)$ancien['photo']);
        $etudiants[$numero]=['id'=>$numero,'nom'=>$v['nom'],'prenom'=>$v['prenom'],'filiere'=>$v['filiere'],'note'=>(float)$v['note'],'photo'=>$photo,'email'=>$v['email']];
        sauver_etudiants($etudiants); flash('success',$modif?'La fiche de l’étudiant a été mise à jour.':'Le nouvel étudiant a été ajouté.'); header('Location: liste.php'); exit;
    }
}
$titre=$modif?'Modifier un étudiant':'Ajouter un étudiant'; include __DIR__.'/header.php';
?>
<section class="page-heading"><div><span class="eyebrow">DOSSIER ÉTUDIANT</span><h1><?= $modif?'Modifier la fiche':'Nouvelle inscription' ?></h1><p><?= $modif?'Mettez à jour les informations de cet étudiant.':'Renseignez les informations du nouvel étudiant.' ?></p></div><a class="button button-secondary" href="liste.php">← Retour à la liste</a></section>
<section class="card form-card"><?php if($erreurs): ?><div class="alert alert-error" role="alert"><strong>Veuillez corriger les points suivants :</strong><ul><?php foreach($erreurs as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-stack"><input type="hidden" name="csrf" value="<?= h(jeton_csrf()) ?>"><input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_PHOTO_BYTES ?>"><div class="form-grid">
  <div class="field"><label for="nom">Nom <span class="required">*</span></label><input id="nom" name="nom" maxlength="100" required value="<?= h((string)$v['nom']) ?>" placeholder="Ex. Benali"></div>
  <div class="field"><label for="prenom">Prénom <span class="required">*</span></label><input id="prenom" name="prenom" maxlength="100" required value="<?= h((string)$v['prenom']) ?>" placeholder="Ex. Sara"></div>
  <div class="field"><label for="filiere">Filière <span class="required">*</span></label><select id="filiere" name="filiere" required><option value="">Sélectionnez une filière</option><?php foreach(FILIERES as $f): ?><option value="<?= h($f) ?>" <?= $v['filiere']===$f?'selected':'' ?>><?= h($f) ?></option><?php endforeach; ?></select></div>
  <div class="field"><label for="note">Note <span class="required">*</span></label><div class="input-suffix"><input id="note" name="note" type="number" min="0" max="20" step="0.01" required value="<?= h((string)$v['note']) ?>" placeholder="14.5"><span>/ 20</span></div></div>
  <div class="field field-full"><label for="email">Adresse e-mail <span class="optional">Facultatif</span></label><input id="email" name="email" type="email" maxlength="190" value="<?= h((string)$v['email']) ?>" placeholder="sara@example.com"></div>
  <div class="field field-full"><label for="photo">Photo de profil <span class="optional">JPG ou PNG · 1 Mo max.</span></label><input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png"><?php if($modif && $ancien['photo']!==''): ?><small class="field-hint">Une photo est déjà enregistrée. Choisissez un nouveau fichier pour la remplacer.</small><?php endif; ?></div>
</div><div class="form-actions"><a class="button button-quiet" href="liste.php">Annuler</a><button class="button button-primary" type="submit"><?= $modif?'Enregistrer les modifications':'Ajouter l’étudiant' ?></button></div></form></section>
<?php include __DIR__.'/footer.php'; ?>
