<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue lors de l'envoi de la requete à l'api";

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    // $raw_data = file_get_contents('php://input');
    // $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // partage
    if($param == 21 && isset($_GET['partager']) && !empty($_GET['annee'])){
        $id_etudiant = $_GET['partager'];
        $annee = $_GET['annee'];

        $code_c = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);


        $partage_releve = $bdd->prepare('SELECT * FROM liens_p WHERE id_etudiant = ?');
        $partage_releve->execute(array($id_etudiant));
        if($partage_releve->rowCount() == 1){ 
            $code_found = $partage_releve->fetch();
            $code_partage = $code_found['code_c'];

            $select_and_update = $bdd->prepare("UPDATE liens_p SET code_c = ? WHERE id_etudiant = ?");
            $select_and_update->execute(array($code_c,$id_etudiant));
            
            $success = 1;
            $msg = $code_c;
        }else{
            $assurer_partage = $bdd->prepare('INSERT INTO liens_p(id_etudiant,code_c,annee)VALUES(?,?,?) ');
            $assurer_partage->execute(array($id_etudiant,$code_c,$annee));
            
            $partage_releve2 = $bdd->prepare('SELECT * FROM liens_p WHERE id_etudiant = ? AND code_c = ?');
            $partage_releve2->execute(array($id_etudiant,$code_c));
            if($partage_releve2->rowCount() == 1){ 
                $code_found = $partage_releve2->fetch();
                $code_partage = $code_found['code_c'];
                
                $success = 1;
                $msg = $code_partage;
            }
        }

    }
}

// Envoi d'une réponse JSON
header('Content-Type: application/json');
$res = [
    "success" => $success,
    "msg" => $msg
];
echo json_encode($res);
?>

