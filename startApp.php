<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";
$appel = 0;
$mdp = "admin0";
$email = "admin@gmail.com";
$type_u = 'admin';

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // ENREGISTREMENT DES JURYS & COURS > INFOS : PARAMETRAGE
    if($param == 001){
        $user0_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
        $user0_exist->execute(array($type_u,$email,$mdp));
        if($user0_exist->rowCount() == 0){

            $createUser = $bdd->prepare('INSERT INTO utilisateurs(type_u,email,mdp,mdp2)VALUES(?,?,?,?) ');
            $createUser->execute(array($type_u,$email,$mdp,$mdp));

            $user1_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
            $user1_exist->execute(array($type_u,$email,$mdp));
            if($user1_exist->rowCount() == 1){
                $user1_troune = $user1_exist->fetch();
                $id_user1 = $user1_troune['id'];
                $nom = "Idris SEDIDIA";

                $createAdmin = $bdd->prepare('INSERT INTO admins(id_utilisateur,nom)VALUES(?,?) ');
                $createAdmin->execute(array($id_user1,$nom));
            }
            
            $success = 1;
            $msg = "demarrage de l'app";
        }else{
            $success = 1;
            $msg = "...";
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