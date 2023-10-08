<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";


if(isset($_GET['id_jury'])){
    $id_jury = $_GET['id_jury'];
    $rec_resulatats = $bdd->prepare(
        "SELECT membres_jury.president, membres_jury.secretaire, membres_jury.appariteur, membres_jury.annee, jurys.nom, utilisateurs.email
        FROM membres_jury, jurys, utilisateurs
        WHERE jurys.id = membres_jury.id_jury AND id_jury = $id_jury AND annee = (SELECT MAX(membres_jury.annee) FROM membres_jury)");
    
    $rec_resulatats->execute(array());
    $data = $rec_resulatats->fetchAll(PDO::FETCH_ASSOC);
    $json = json_encode($data);
    echo $json;
}


?>