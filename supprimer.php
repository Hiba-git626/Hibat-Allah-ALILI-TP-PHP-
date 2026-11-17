<?php
require_once __DIR__ . '/functions.php'; exiger_connexion();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST') { header('Location: liste.php'); exit; }
verifier_csrf(); $id=(int)($_POST['id']??0); $etudiants=lire_etudiants();
if(isset($etudiants[$id])) { $e=$etudiants[$id]; unset($etudiants[$id]); sauver_etudiants($etudiants); supprimer_photo((string)($e['photo']??'')); flash('success','La fiche de '.$e['prenom'].' '.$e['nom'].' a été supprimée.'); }
else flash('error','Cet étudiant est introuvable.');
header('Location: liste.php'); exit;
