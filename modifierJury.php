<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";


// variables de statistiques
$n_etudiants = 0;
$n_consultation = 0;
$reussite = 0;
// variables de statistiques

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // MISE A JOUR D'UN JURY
    if($param == 1310 && !empty($_GET['id_jury']) && !empty($_GET['nom']) && !empty($_GET['email']) && !empty($_GET['president']) && !empty($_GET['secretaire']) && !empty($_GET['appariteur']) && !empty($_GET['annee']) && !empty($_GET['emailAppariteur'])){
        $id_jury = $_GET['id_jury'];
        $nom = $_GET['nom'];
        $email = $_GET['email'];
        $president = $_GET['president'];
        $secretaire = $_GET['secretaire'];
        $appariteur = $_GET['appariteur'];
        $annee = $_GET['annee'];
        $emailAppariteur = $_GET['emailAppariteur'];

        // mettre le jury à  jour
        $rec_jury2 = $bdd->prepare("SELECT * FROM jurys WHERE id = ?"); 
        $rec_jury2->execute(array($id_jury));
        if($rec_jury2->rowCount() == 1){
            $jr_founded = $rec_jury2-> fetch();
            $id_utilisateur = $jr_founded['id_utilisateur'];
            $id_appariteur = $jr_founded['id_appariteur'];
            
            $update_jr = $bdd->prepare("UPDATE jurys SET nom = ? WHERE id = ?");
            $update_jr->execute(array($nom,$id_jury));
            
            $update_jr = $bdd->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
            $update_jr->execute(array($email,$id_utilisateur));
            
            $rec_apparit = $bdd->prepare("SELECT * FROM appariteurs WHERE id = ?"); 
            $rec_apparit->execute(array($id_appariteur));
            if($rec_apparit->rowCount() == 1){
                $ap_founded = $rec_apparit-> fetch();
                $id_utilisateur = $ap_founded['id_utilisateur'];

                $update_jr = $bdd->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
                $update_jr->execute(array($emailAppariteur,$id_utilisateur));
            }
            
            $success = 1;
            $msg = "Mis à jour avec succès !";
        }else{
            $success = 0;
            $msg = "Ce Jury n'existe pas !";            
        }
        // mettre le jury à  jour
        $rec_membres = $bdd->prepare("SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ? "); 
        $rec_membres->execute(array($id_jury,$annee));
        if($rec_membres->rowCount() >= 1){
            $jr_founded = $rec_membres-> fetch();
            $id_mem = $jr_founded['id'];
            $id_apparit = $jr_founded['id_appariteur'];
            
            $update_membre = $bdd->prepare("UPDATE membres_jury SET president = ?, secretaire = ?, appariteur = ? WHERE id = ?");
            $update_membre->execute(array($president,$secretaire,$appariteur,$id_mem));

            $update_membre = $bdd->prepare("UPDATE membres_jury SET appariteur = ? WHERE id_appariteur = ?");
            $update_membre->execute(array($appariteur,$id_apparit));
            
            $success = 1;
            $msg = "Mis à jour avec succès !";
        }else{
            $update_membre = $bdd->prepare("INSERT INTO membres_jury(id_jury,president,secretaire,appariteur,annee)VALUES(?,?,?,?,?)");
            $update_membre->execute(array($id_jury,$president,$secretaire,$appariteur,$annee));

            $success = 1;
            $msg = "Membres pour l'année ".$annee." ajoutés !";
        }
    }else{
        $success = 0;
        $msg = "Tous les champs sont obligatoires pour effectuer cette operation.";
    }

}

// table de cles
// 0    = échec
// 1    = succès
// 11   = enregistrement de cours           : Admin
// 12   = enregistre jurys                  : Admin
// 13   = consultation jurys                : Admin
// 131  = consultation jury                 : Admin
// 1310  = mise à jour jury                  : Admin
// 21   = archivage relevés                 : jury
// 22   = consultation releves              : jury
// 22/3 = consultation resultat             : etudiant
// 231  = consultation resultats publiés    : jury
// 3    = connexion                         : utilisateur


// Envoi d'une réponse JSON
header('Content-Type: application/json');
if(isset($type_user)){
    $res = [
        "type_user" => $type_user,
        "id" => $id_utilisateur,
        "id_utilisateur" => $id_user,
        "email_user" => $email_user,
        "nom_user" => $nom_user,
        "success" => $success,
        "msg" => $msg
    ];
}else{
    $res = [
        "success" => $success,
        "msg" => $msg
    ];
}
echo json_encode($res);
?>

