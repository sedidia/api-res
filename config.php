<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";
$appel = 0;
$mdp = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

function responseServer($success,$msg){
    $res = array(
        'success' => $success,
        'msg' => $msg
    );
    header('Content-Type: application/json');
    // return json_encode($res);                 
    echo json_encode($res); 
    return false;                
}

function exec_suite($emailJury,$jury,$id_apparit,$nomCours,$president,$secretaire,$appariteur,$annee,$filiere,$promotion,$credit,$sur_cote,$unite,$success,$msg){
    global $bdd;
    $inserCours = $bdd->prepare('INSERT INTO utilisateurs(type_u,email,mdp,mdp2)VALUES(?,?,?,?) ');
    $inserCours->execute(array("jury",$emailJury,$jury,$jury));
    
    $rec_user = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
    $rec_user->execute(array("jury",$emailJury,$jury));
    if($rec_user->rowCount() == 1){
        $user_troune = $rec_user->fetch();
        $id_user = $user_troune['id'];
        $etat = 1;
        
        $inserJury = $bdd->prepare('INSERT INTO jurys(id_utilisateur,id_appariteur,nom,etat)VALUES(?,?,?,?) ');
        $inserJury->execute(array($id_user,$id_apparit,$jury,$etat));
        
        $jury2_exist = $bdd->prepare('SELECT * FROM jurys WHERE id_utilisateur = ? AND id_appariteur = ? AND nom = ?'); 
        $jury2_exist->execute(array($id_user,$id_apparit,$jury));
        if($jury2_exist->rowCount() == 1){
            $jury2_troune = $jury2_exist->fetch();
            $id_jury2 = $jury2_troune['id'];                                        
            // ajouter utilisateur et l'appariteur
            $inserJury = $bdd->prepare('INSERT INTO membres_jury(id_appariteur,id_jury,president,secretaire,appariteur,annee)VALUES(?,?,?,?,?,?) ');
            $inserJury->execute(array($id_apparit,$id_jury2,$president,$secretaire,$appariteur,$annee));
            
            $id_jury_exist = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND promotion = ? AND credit = ? AND sur_cote = ? AND unite = ?'); 
            $id_jury_exist->execute(array($id_jury2,$nomCours,$promotion,$credit,$sur_cote,$unite));
            if($id_jury_exist->rowCount() == 0){                
                $inserCours = $bdd->prepare('INSERT INTO cours(id_jury,nom,filiere,promotion,credit,sur_cote,unite)VALUES(?,?,?,?,?,?,?) ');
                $inserCours->execute(array($id_jury2,$nomCours,$filiere,$promotion,$credit,$sur_cote,$unite));                            
            }
            $success = 1;
            $msg = "Vous avez configuré la plateforme (enregistrement des utilisateurs, Jurys et cours) avec succès";   
                         
        }    
    } 
    
    $success = 1;
    $msg = "Enregistrés avec succès !";
}

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // ENREGISTREMENT DES JURYS & COURS > INFOS : PARAMETRAGE
    if($param == 11){

        foreach ($dataReques as $cours) {
            if(isset($cours["cours"]) && isset($cours["faculte"]) && isset($cours["promotion"]) && isset($cours["credit"]) && isset($cours["sur_cote"]) && isset($cours["unite"]) && isset($cours["annee"]) && isset($cours["filiere"]) && isset($cours["president"]) && isset($cours["secretaire"]) && isset($cours["appariteur"]) && isset($cours["jury"]) && isset($cours['emailAppariteur']) && isset($cours['emailJury']) ){

                $nomCours =  $cours["cours"];
                $faculte =  $cours["faculte"];
                $jury =  $cours["jury"];
                $promotion =  $cours["promotion"];
                $credit =  $cours["credit"];
                $sur_cote =  $cours["sur_cote"];
                $unite =  $cours["unite"];
                $annee =  $cours["annee"];
                $filiere =  $cours["filiere"];
                $president =  $cours["president"];
                $secretaire =  $cours["secretaire"];
                $appariteur =  $cours["appariteur"];
                $emailAppariteur = $cours["emailAppariteur"];
                $emailJury = $cours["emailJury"];

                // insertion de données  
                $appariteur_exist = $bdd->prepare('SELECT * FROM appariteurs WHERE nom = ?'); 
                $appariteur_exist->execute(array($faculte));
                if($appariteur_exist->rowCount() == 0){
                    
                    $createUser = $bdd->prepare('INSERT INTO utilisateurs(type_u,email,mdp,mdp2)VALUES(?,?,?,?) ');
                    $createUser->execute(array("appariteur",$emailAppariteur,$mdp,$mdp));   
                    
                    $user0_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
                    $user0_exist->execute(array("appariteur",$emailAppariteur,$mdp));
                    if($user0_exist->rowCount() == 1){
                        $user0_troune = $user0_exist->fetch();
                        $id_user0 = $user0_troune['id']; 
                        
                        $createAppariter = $bdd->prepare('INSERT INTO appariteurs(id_utilisateur,nom)VALUES(?,?) ');
                        $createAppariter->execute(array($id_user0,$faculte));   

                        $apparit_exist = $bdd->prepare('SELECT * FROM appariteurs WHERE id_utilisateur = ? AND nom = ?'); 
                        $apparit_exist->execute(array($id_user0,$faculte));
                        if($apparit_exist->rowCount() == 1){
                            $apparit_troune = $apparit_exist->fetch();
                            $id_apparit = $apparit_troune['id']; 

                            $jury_exist = $bdd->prepare('SELECT * FROM jurys WHERE nom = ? AND id_appariteur = ?'); 
                            $jury_exist->execute(array($jury,$id_apparit));
                            if($jury_exist->rowCount() == 0){
                                echo exec_suite($emailJury,$jury,$id_apparit,$nomCours,$president,$secretaire,$appariteur,$annee,$filiere,$promotion,$credit,$sur_cote,$unite,$success,$msg);
                                $success = 1;
                                $msg = "Enregistrés avec succès !";
                            }
                        }
                    }
                }else{
                    $else_appariteur_exist = $bdd->prepare('SELECT * FROM appariteurs WHERE nom = ?'); 
                    $else_appariteur_exist->execute(array($faculte));
                    if($else_appariteur_exist->rowCount() == 1){
                        $appFounded = $else_appariteur_exist->fetch();
                        $id_apparit = $appFounded['id'];
    
                        $jury_exist = $bdd->prepare('SELECT * FROM jurys WHERE id_appariteur = ? AND nom = ?'); 
                        $jury_exist->execute(array($id_apparit,$jury));
                        if($jury_exist->rowCount() == 1){
                            $jury_troune = $jury_exist->fetch();
                            $id_jury = $jury_troune['id'];
                            
                            $id_jury_exist = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ? AND credit = ? AND sur_cote = ? AND unite = ?'); 
                            $id_jury_exist->execute(array($id_jury,$nomCours,$filiere,$promotion,$credit,$sur_cote,$unite));
                            if($id_jury_exist->rowCount() == 0){    
                                $inserCours = $bdd->prepare('INSERT INTO cours(id_jury,nom,filiere,promotion,credit,sur_cote,unite)VALUES(?,?,?,?,?,?,?) ');
                                $inserCours->execute(array($id_jury,$nomCours,$filiere,$promotion,$credit,$sur_cote,$unite));    
                            }
            
                            $success = 1;
                            $msg = "Vous avez configuré la plateforme (enregistrement des utilisateurs, Jurys et cours) avec succès";
                        }else{
                            echo exec_suite($emailJury,$jury,$id_apparit,$nomCours,$president,$secretaire,$appariteur,$annee,$filiere,$promotion,$credit,$sur_cote,$unite,$success,$msg);
                            $success = 1;
                            $msg = "Enregistrés avec succès !";
                        }
                    }
                }
    
                // creation des notifiations
                $titre = "Configuration de la plateforme.";
                $contenu = "Vous avez configuré la plateforme (enregistrement des : utilisateurs, Jurys et cours) avec succès";
                $type_u = 'admin';
                $id_author = 1;

                $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?'); 
                $trait_notifs->execute(array($id_author,$titre,$contenu,$type_u,$annee));
                if($trait_notifs->rowCount() == 0){                                        
                    $creat_not = $bdd->prepare("INSERT INTO notifications(id_author,titre,contenu,type_u,annee)VALUES(?,?,?,?,?) ");
                    $creat_not->execute(array($id_author,$titre,$contenu,$type_u,$annee));
                }
                $appel = $appel + 1;
                if($appel == 1){
                    responseServer($success,$msg);
                }
            }else{
                $success = 0;
                $msg = "Le fichier selectionné ne respecte pas la norme de fichiers de configuration";
                $appel = $appel + 1;
                if($appel == 1){
                    responseServer($success,$msg);
                }
            }
        }
    }
}

?>