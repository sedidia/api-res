<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";
$id_jury = 0;
$tantat = 3;
$id = 0;
$id_utilisateur = 0;
$type_user = '';
$etat = null;
$nom_user = '';
$success = 0;
$sendMail = 0;
$sendMailTo = '';


// jury
$emailAppariteur = '';
$nom_faculte_appariteur = '';
$president = "";
$secretaire = "";
$appariteur = "";
$i_a_c_s = null;
// jury


// generation du mot de passe
$mdp = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
// generation du mot de passe

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // AUTHENTIFICATION
    if($param == 3){
        
        if(!empty($_GET['email']) AND !empty($_GET['password'])){
            $password = $_GET['password'];
            $email = $_GET['email'];

            $verif_user = $bdd->prepare('SELECT * FROM utilisateurs WHERE email = ?');
            $verif_user->execute(array($email));
            if($verif_user->rowCount() == 1){
                $user_exist = $verif_user->fetch();

                $id_utilisateur = $user_exist['id'];
                $type_user = $user_exist['type_u'];
                $mdp_user = $user_exist['mdp'];
                $n_connect = $user_exist['n_connect'];
                $email = $user_exist['email'];
                
                if($type_user == "appariteur"){
                    if($password == $mdp_user){
                        $recApp = $bdd->prepare('SELECT * FROM appariteurs WHERE id_utilisateur = ?');
                        $recApp->execute(array($id_utilisateur));
                        
                        if($recApp->rowCount() == 1){
                            $a_exist = $recApp->fetch();
                            $id_appariteur = $a_exist['id'];
                            $nom_user = $a_exist['nom'];
                            
                            if($n_connect == 0){
                                $update_jr = $bdd->prepare("UPDATE utilisateurs SET n_connect = ? WHERE id = ?");
                                $update_jr->execute(array(1,$id_utilisateur));
                                $n_connect = 1;
                            }                            
                            $success = 1;
                            $msg = "";
                        }else{                            
                            $success = 0;
                            $msg = "Votre compte n'avais pas été bien créé !";
                        }  
                        $mdp = "";
                    }else{
                        $verif_user_jury = $bdd->prepare('SELECT * FROM appariteurs WHERE id_utilisateur = ?');
                        $verif_user_jury->execute(array($id_utilisateur));
                        if($verif_user_jury->rowCount() == 1){
                            $user_jury_exist = $verif_user_jury->fetch();            
                            $id_appariteur = $user_jury_exist['id'];
                            $nom_user = $user_jury_exist['nom'];

                            // update
                            $update_jr = $bdd->prepare("UPDATE utilisateurs SET mdp = ?, n_connect = ? WHERE id = ?");
                            $update_jr->execute(array($mdp,1,$id_utilisateur));
                            // update

                            $success = 1;
                            $msg = $email;                        
                            $sendMail = 1;
                            $sendMailTo = $email;
                        }
                    }
                }
                if($type_user == "jury"){
                    if($password == $mdp_user){
                        $recJury = $bdd->prepare('SELECT * FROM jurys WHERE id_utilisateur = ?');
                        $recJury->execute(array($id_utilisateur));
                        if($recJury->rowCount() == 1){
                            $j_exist = $recJury->fetch();
                            $nom_user = $j_exist['nom'];
                            $id = $j_exist['id'];
                            $id_appariteur = $j_exist['id_appariteur'];
                            $etat = $j_exist['etat'];

                            $recApparit = $bdd->prepare('SELECT * FROM appariteurs WHERE id = ?');
                            $recApparit->execute(array($id_appariteur));
                            if($recApparit->rowCount() == 1){
                                $apparit_exist = $recApparit->fetch();
                                $nom_faculte_appariteur = $apparit_exist['nom'];
    
                                $isBloqued = $bdd->prepare('SELECT * FROM jurys_bloques WHERE id_jury = ? AND tantative = ?');
                                $isBloqued->execute(array($id,$tantat));
                                if($isBloqued->rowCount() == 0){
                                    $getMembers = $bdd->prepare("SELECT president, secretaire, appariteur, annee FROM membres_jury WHERE id_jury = 1 AND annee = (SELECT MAX(annee) FROM membres_jury)");
                                    $getMembers->execute(array());
                                    $memberFounded = $getMembers->fetch();
                                    // $i_a_c_s = $memberFounded['id_utilisateur'];
                                    $president = $memberFounded['president'];
                                    $secretaire = $memberFounded['secretaire'];
                                    $appariteur = $memberFounded['appariteur'];
                                    
                                    if($n_connect == 0){
                                        $update_jr = $bdd->prepare("UPDATE utilisateurs SET n_connect = ? WHERE id = ?");
                                        $update_jr->execute(array(1,$id_utilisateur));
                                        $n_connect = 1;
                                    }
                                    
                                    $success = 1;
                                    $msg = "Succès";
                                }else{
                                    $success = 0;
                                    $msg = "Ce compte a été bloqué car quelqu'un l'a utilisé pour televerser des fichiers invalides.";
                                }
                            }


                        }else{                            
                            $success = 0;
                            $msg = "Votre compte n'avais pas été bien créé !";
                        }  
                    }else{
                        $verif_user_jury = $bdd->prepare('SELECT * FROM jurys WHERE id_utilisateur = ?');
                        $verif_user_jury->execute(array($id_utilisateur));
                        if($verif_user_jury->rowCount() == 1){
                            $user_jury_exist = $verif_user_jury->fetch();
            
                            $nom = $user_jury_exist['nom'];

                            // update
                            $update_jr = $bdd->prepare("UPDATE utilisateurs SET mdp = ?, n_connect = ? WHERE id = ?");
                            $update_jr->execute(array($mdp,1,$id_utilisateur));
                            // update

                            $success = 1;
                            $msg = $email;                        
                            $sendMail = 1;
                            $sendMailTo = $email;
                            $nom_user = $nom;
                        }
                    }
                }
                if($type_user == "etudiant"){
                    if($password == $mdp_user){
                        $recEtudiant = $bdd->prepare('SELECT * FROM etudiants WHERE id_utilisateur = ?');
                        $recEtudiant->execute(array($id_utilisateur));
                        if($recEtudiant->rowCount() > 0){
                            $e_exist = $recEtudiant->fetch();
                            $nom_user = $e_exist['noms'];
                            $id = $e_exist['id'];                        
                            $id_jury = $e_exist['id_jury'];                        
    
                            $success = 1;
                            $msg = "Succès";
                            $sendMail = 0;
                            
                            if($n_connect == 0){
                                $update_jr = $bdd->prepare("UPDATE utilisateurs SET n_connect = ? WHERE id = ?");
                                $update_jr->execute(array(1,$id_utilisateur));
                            }
                            
                            $success = 1;
                            $msg = '';                        
                            $sendMail = 0;
                            $sendMailTo = '';
                        }else{                        
                            $success = 0;
                            $msg = "Votre compte n'avais pas été bien créé !";
                        }
                    }else{
                        $success = 0;
                        $msg = "Le mot de passe fourni est invalide !";
                    }
                }
                if($type_user == "admin"){
                    if($password == $mdp_user){
                        
                        $recAdmin = $bdd->prepare('SELECT * FROM admins WHERE id_utilisateur = ?');
                        $recAdmin->execute(array($id_utilisateur));
                        if($recAdmin->rowCount() == 1){
                            $a_exist = $recAdmin->fetch();
                            $nom_user = $a_exist['nom'];
                            $id = $a_exist['id'];
        
                            $success = 1;
                            $msg = "Succès";
                        }else{                        
                            $success = 0;
                            $msg = "Votre compte n'avais pas été bien créé !";
                        }
                    }else{
                        $success = 0;
                        $msg = "Le mot de passe fourni est incorrect !";
                    }
                }

            }else{
                $verif_user2 = $bdd->prepare('SELECT * FROM utilisateurs WHERE mdp = ?');
                $verif_user2->execute(array($password));
                if($verif_user2->rowCount() == 1){
                    $u_trouve = $verif_user2->fetch();
                    $id_utilisateur = $u_trouve['id'];
                    $n_connect = $u_trouve['n_connect'];
                    $type_user = $u_trouve['type_u'];
                    
                    if($type_user == "etudiant"){

                        $recEtudiant = $bdd->prepare('SELECT * FROM etudiants WHERE id_utilisateur = ?');
                        $recEtudiant->execute(array($id_utilisateur));
                        if($recEtudiant->rowCount() >= 1){
                            $e_exist = $recEtudiant->fetch();
                            $nom_user = $e_exist['noms'];
                            $id = $e_exist['id'];
                            $id_jury = $e_exist['id_jury'];
    
                            if($n_connect == 0){  
                                $update_jr = $bdd->prepare("UPDATE utilisateurs SET email = ?, mdp = ? WHERE id = ?");
                                $update_jr->execute(array($email,$mdp,$id_utilisateur)); 
                            }

                            $success = 1;
                            $msg = '';
                            $sendMail = 1;
                            $sendMailTo = $email;
                        }else{                        
                            $success = 0;
                            $msg = "Votre compte n'avais pas été bien créé !";
                        }
                    }
                }else{
                    $success = 0;
                    $msg = "Email et mot de passe est incorrect !";
                }

            }
        }else{
            $success = 0;
            $msg = "Tous les champs sont obligatoire !";
        }
    }
}


// Envoi d'une réponse JSON
header('Content-Type: application/json');
if($type_user == 'appariteur'){
    $res = [
        "success" => $success,
        "msg" => $msg,
        "id" => $id_appariteur,
        "id_utilisateur" => $id_utilisateur,
        "email" => $email,
        "nom_user" => $nom_user,
        "mdp" => $mdp,
        "type_user" => $type_user,
        "sendMail" => $sendMail,
        "sendMailTo" => $sendMailTo,
    ];
}elseif($type_user == 'jury'){
    $res = [
        "sendMail" => $sendMail,
        "sendMailTo" => $sendMailTo,
        "mdp" => $mdp,
        "id" => $id,
        "id_utilisateur" => $id_utilisateur,
        "type_user" => $type_user,
        "nom_user" => $nom_user,
        "etat" => $etat,
        "success" => $success,
        "msg" => $msg,
        "president" => $president,
        "secretaire" => $secretaire,
        "appariteur" => $appariteur,
        "nom_faculte_appariteur" => $nom_faculte_appariteur,
        // "i_a_c_s" => $i_a_c_s, 
        "email" => $email,
        "ememailAppariteurail" => $emailAppariteur
    ];
}else{
    $res = [
        "sendMail" => $sendMail,
        "sendMailTo" => $sendMailTo,
        "mdp" => $mdp,
        "id" => $id,
        "id_utilisateur" => $id_utilisateur,
        "id_jury" => $id_jury,
        "type_user" => $type_user,
        "nom_user" => $nom_user,
        "etat" => $etat,
        "success" => $success,
        "msg" => $msg
    ];
}
echo json_encode($res);
?>

