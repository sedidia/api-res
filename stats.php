<?php
require_once("bdd.php");

// REQUETES par la methode GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    // Récupérer les paramètres de l'URL
    $param = $_GET['param'];
    if($param == 231 AND !empty($_GET['nom_user']) AND !empty($_GET['id_utilisateur'])){
        $nom_user = $_GET['nom_user'];
        $id_utilisateur = $_GET['id_utilisateur'];

        $rec_resulatats = $bdd->prepare(
            "SELECT id, promotion, n_etudiants, n_consultation, reussite, annee, date_c
            FROM statists
            WHERE id_jury = '$id_utilisateur'
            ");
        $rec_resulatats->execute(array());
        $data = $rec_resulatats->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
    }
}
?>