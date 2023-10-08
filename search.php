<?php
require_once("bdd.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    // Récupérer les paramètres de l'URL
    $param = $_GET['param'];

    // consultation des notification
    if($param == 'public'){
        $rec_resultats = $bdd->prepare("SELECT * FROM notifications WHERE type_u != 'admin' ORDER BY id DESC");
        $rec_resultats->execute(array());
        $results = $rec_resultats->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
    }
}

?>