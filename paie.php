<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";

// variables de statistiques
$n_etudiants = 0;
$n_consultation = 0;
$reussite = 0;
// variables de statistiques

// appel
$appel = 0;
// appel

// les fonctions
function resOfServer($success,$msg){
    $res = array(
        'success' => $success,
        'msg' => $msg
    );
    header('Content-Type: application/json');
    echo json_encode($res); 
}

// PAIEMENT EN COURS
function paiementEnCours($promotion,$success,$msg,$paiement,$id_jury,$appel){
    global $bdd;

    // traitement de l'operation d'archivage
    $noms =  $paiement["NOM, POST-NOM et PRENOM"];
    $jury = $paiement["jury"];
    $filiere = $paiement["filiere"];
    $annee = $paiement["annee"];
    $montant = $paiement["montant"];
    $paye = $paiement["paye"];

    $verif_stud = $bdd->prepare('SELECT * FROM etudiants WHERE id_jury = ? AND noms = ? AND filiere = ? AND promotion = ? AND annee = ?'); 
    $verif_stud->execute(array($id_jury,$noms,$filiere,$promotion,$annee));
    if($verif_stud->rowCount() == 1){
        $et_exist = $verif_stud->fetch();
        $id_etudiant = $et_exist['id'];
        $success = 1;
        $msg = "Paiements enregistrés.";

        $verif_central = $bdd->prepare('SELECT * FROM paiements WHERE id_jury = ? AND id_etudiant = ?'); 
        $verif_central->execute(array($id_jury,$id_etudiant));
        if($verif_central->rowCount() > 0){
            $update_paie = $bdd->prepare("UPDATE paiements SET paye = '$paye' WHERE id_etudiant = ? ");
            $update_paie->execute(array($id_etudiant));
        }else{
            $inserPaie = $bdd->prepare('INSERT INTO paiements(id_etudiant,id_jury,montant,paye)VALUES(?,?,?,?) ');
            $inserPaie->execute(array($id_etudiant,$id_jury,$montant,$paye));
        }

    }else{
        $success = 0;
        $msg = "Certains étudiants n'existent pas dans la base des données.";
    }                
    
    if($appel == 1){
        resOfServer($success,$msg);
    }
}
// PAIEMENT EN COURS

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // PAIE
    if($param == "ordre" && isset($_GET['id_jury']) && isset($_GET['nom_faculte']) && isset($_GET['jury']) ){
        $id_jury_source = $_GET['id_jury'];
        $nom_faculte = $_GET['nom_faculte'];
        $jury = $_GET['jury'];

        $verif_etat_jury = $bdd->prepare('SELECT * FROM jurys WHERE id = ? AND nom = ?'); 
        $verif_etat_jury->execute(array($id_jury_source,$jury));
        if($verif_etat_jury->rowCount() == 1){
            $etat_exist = $verif_etat_jury->fetch();
            $etat_j = $etat_exist['etat'];
            $id_jury = $etat_exist['id'];
            $id_appariteur = $etat_exist['id_appariteur'];
            $nom_du_jury = $etat_exist['nom'];
            
            $verif_faculte = $bdd->prepare('SELECT * FROM appariteurs WHERE id = ? AND nom = ?'); 
            $verif_faculte->execute(array($id_appariteur,$nom_faculte));
            if($verif_faculte->rowCount() == 1){
                // $etat_exist = $verif_faculte->fetch();
                // $etat_j = $etat_exist['etat'];
                // $id_jury = $etat_exist['id'];

                if($etat_j == "0"){                    
                    foreach ($dataReques as $paiement) {
                        $promotion = $paiement["promotion"];
                        $nom_du_jury_grille = $paiement["jury"];
                        if($nom_du_jury == $nom_du_jury_grille){
                            if($promotion == "Bac1" || $promotion == "bac1" || $promotion == "Bac 1" || $promotion == "bac 1" || $promotion == 1){
                                $promotion = 1;
                            }else if($promotion == "Bac2" || $promotion == "bac2" || $promotion == "Bac 2" || $promotion == "bac 2" || $promotion == 2){
                                $promotion = 2;                                
                            }else if($promotion == "Bac3" || $promotion == "bac3" || $promotion == "Bac 3" || $promotion == "bac 3" || $promotion == 3){
                                $promotion = 3;
                            }
                            $appel = $appel + 1;
                            echo paiementEnCours($promotion,$success,$msg,$paiement,$id_jury,$appel);
                            
                            // $success = 1;
                            // $msg = "Liste uploadée avec succès !";
                        }else{
                            $success = 0;
                            $msg = "Erreur : vous n'etes pas autorisé d'enregistrer les paiements de la faculté des ".$nom_faculte;
                            $appel = $appel + 1;
                            if($appel == 1){
                                resOfServer($success,$msg);
                            }
                        }                    
                    }
                }else{
                    $success = 0;
                    $msg = "L'opération a échoué ! Demandez à votre administrateur de vous debloquer pour pouvoir continuer.";
                    resOfServer($success,$msg);
                }
            }else{
                $success = 0;
                $msg = "Erreur : vous n'etes pas autorisé à enregistrer les paiement de la faculté des ".$nom_faculte;
                resOfServer($success,$msg);
            }
        }else{
            $success = 0;
            $msg = "Une erreur est survenue lors de l'enregistrement des paiements. Veillez recommencer, sinon contacter l'administrateur.";
            resOfServer($success,$msg);
        }
    }else{
        $success = 0;
        $msg = "Vos parametres d'enregistrement sont invalides !";
        resOfServer($success,$msg);
    }
}

 
?>