<?php
require_once __DIR__ . '/functions.php'; exiger_connexion();
header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="etudiants_'.date('Y-m-d').'.csv"'); header('X-Content-Type-Options: nosniff');
$out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF"); fputcsv($out,['ID','Nom','Prénom','Filière','Note','Email'],';');
foreach(lire_etudiants() as $e) fputcsv($out,[$e['id'],$e['nom'],$e['prenom'],$e['filiere'],number_format((float)$e['note'],2,'.',''),$e['email']],';');
fclose($out); exit;
