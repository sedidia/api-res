<?php
require_once("bdd.php");
$msg = "Erreur $";
$success = 0;

// REQUETES par la methode GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    // Récupérer les paramètres de l'URL
    $param = $_GET['param'];
    
    if($param == 'studentsManager'){

        $selectStudentInfos = $bdd->prepare("SELECT etudiants.id, etudiants.noms, etudiants.filiere, etudiants.promotion, etudiants.annee, jurys.nom, utilisateurs.email, utilisateurs.mdp, utilisateurs.id as 'id_u'
        FROM etudiants, utilisateurs, jurys
        WHERE etudiants.id_utilisateur = utilisateurs.id AND etudiants.id_jury = jurys.id
        ORDER BY etudiants.noms
        ");
        $selectStudentInfos->execute();
        $results = $selectStudentInfos->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
    }

    if($param == 'resetStudent' && isset($_GET['idStudent']) && isset($_GET['noms'])){
        $idStudent = $_GET['idStudent'];
        $noms = $_GET['noms'];
        $n_connect = 0;
        $mdp = $noms;
        $email = "idris@gmail.com";
        $success = 1;
        $msg = "Réinitialisé(e) avec succès !";

        $update_us = $bdd->prepare("UPDATE utilisateurs SET email = ?, mdp = ?, n_connect = ? WHERE id = ?");
        $update_us->execute(array($email, $mdp, $n_connect, $idStudent));

        header('Content-Type: application/json');
        $res = [
            "success" => $success,
            "msg" => $msg
        ];
        echo json_encode($res);
    }
    
    if($param == 'updateStudent' && isset($_GET['idEtudiant']) && isset($_GET['id_utilisateur']) && isset($_GET['noms']) && isset($_GET['email'])){
        $idEtudiant = $_GET['idEtudiant'];
        $id_utilisateur = $_GET['id_utilisateur'];
        $noms = $_GET['noms'];
        $email = $_GET['email'];

        // update student
        $update_us = $bdd->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
        $update_us->execute(array($email,$id_utilisateur));

        $update_st = $bdd->prepare("UPDATE etudiants SET noms = ? WHERE id_utilisateur = ?");
        $update_st->execute(array($noms,$id_utilisateur));

        $success = 1;
        $msg = "Mis à jour avec succès !";

        header('Content-Type: application/json');
        $res = [
            'success' => $success,
            'msg' => $msg
        ];
        echo json_encode($res);
    }
}
?>