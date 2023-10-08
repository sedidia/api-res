<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";

$id_jury = 1;
$id_utilisateur = 2;
$promotion = 3;
$filiere = "informatique";
$nom_jury = "sciences";
// $annee = $_GET['annee'];

$rec_resulatats = $bdd->prepare(
    "SELECT jurys.nom, jurys.etat, jurys.date_c, membres_jury.president, membres_jury.secretaire, membres_jury.appariteur, membres_jury.annee, releves.filiere, releves.promotion, COUNT(releves.id) as nombre_bulletins
    FROM releves, jurys, membres_jury
    WHERE jurys.id = releves.id_jury AND jurys.id = membres_jury.id_jury AND jurys.id = '$id_jury'
    GROUP BY jurys.nom, jurys.etat, jurys.date_c, membres_jury.president, membres_jury.secretaire, membres_jury.appariteur, membres_jury.annee, releves.filiere, releves.promotion
    ");

$rec_resulatats->execute(array());
$data = $rec_resulatats->fetchAll(PDO::FETCH_ASSOC);
$json = json_encode($data);
echo $json;

?>