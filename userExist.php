<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";

$id = 0;
$id_utilisateur = 0;
$type_user = '';
$nom_user = '';
$success = 0;
$sendMail = 0;
$sendMailTo = '';
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
    if($param == 'verification' && isset($_GET['id_utilisateur']) && isset($_GET['type_u'])){
        
        $verif_user = $bdd->prepare('SELECT * FROM utilisateurs WHERE email = ?');
        $verif_user->execute(array($email));
        if($verif_user->rowCount() == 1){
            $user_exist = $verif_user->fetch();

            $id_utilisateur = $user_exist['id'];
            $type_user = $user_exist['type_u'];
            $mdp_user = $user_exist['mdp'];
            $n_connect = $user_exist['n_connect'];
            
            if($type_user == "jury"){
                if($password == $mdp_user){

                    $recJury = $bdd->prepare('SELECT * FROM jurys WHERE id_utilisateur = ?');
                    $recJury->execute(array($id_utilisateur));
                    if($recJury->rowCount() == 1){
                        $j_exist = $recJury->fetch();
                        $nom_user = $j_exist['nom'];
                        $id = $j_exist['id'];

                        if($n_connect == 0){
                            $update_jr = $bdd->prepare("UPDATE utilisateurs SET n_connect = ? WHERE id = ?");
                            $update_jr->execute(array(1,$id_utilisateur));
                            $n_connect = 1;
                        }
                        
                        $success = 1;
                        $msg = "Succès";
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

        }
    }
}


// Envoi d'une réponse JSON
header('Content-Type: application/json');
$res = [
    "sendMail" => $sendMail,
    "sendMailTo" => $sendMailTo,
    "mdp" => $mdp,
    "id" => $id,
    "id_utilisateur" => $id_utilisateur,
    "type_user" => $type_user,
    "nom_user" => $nom_user,
    "success" => $success,
    "msg" => $msg
];
echo json_encode($res);
?>

