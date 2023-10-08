<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";


// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];
    // BLOQUAGE/DEBLOCAGE
    if($param == 21 && !empty($_GET['id_jury']) && !empty($_GET['annee'])){
        $id_jury = $_GET['id_jury'];
        $annee = $_GET['annee'];

        $verif_membres = $bdd->prepare('SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ?'); 
        $verif_membres->execute(array($id_jury,$annee));
        if($verif_membres->rowCount() == 1){
            
            $rec_jury = $bdd->prepare('SELECT * FROM jurys WHERE id = ?'); 
            $rec_jury->execute(array($id_jury));
            if($rec_jury->rowCount() == 1){
                $jury_troune = $rec_jury->fetch();
                $etat_jury = $jury_troune['etat'];
                
                if($etat_jury == 1){
                    // deblockage encours
                    $select_and_update = $bdd->prepare("UPDATE jurys SET etat = ? WHERE id = ?");
                    $select_and_update->execute(array(0,$id_jury));
                    
                    // suppression de la tantative
                    $verifTantative = $bdd->prepare('SELECT * FROM jurys_bloques WHERE id_jury = ?');
                    $verifTantative->execute(array($id_jury));
                    if($verifTantative->rowCount() >= 1){
                        $deleteThis = $verifTantative->fetch();
                        $id_jury = $deleteThis['id_jury'];

                        $deleteTantative = $bdd->prepare("DELETE FROM jurys_bloques WHERE id_jury = ?");
                        $deleteTantative->execute(array($id_jury)); 

                    }
                }

                if($etat_jury == 0){
                    // blockage encours
                    $select_and_update = $bdd->prepare("UPDATE jurys SET etat = ? WHERE id = ?");
                    $select_and_update->execute(array(1,$id_jury));
                }
                $msg = "";
                $success = 1;
            }
        }else{
            $success = 0;
            $msg = "Veillez ajouter les membres du jury avant de deblooquer ce jury.";
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

