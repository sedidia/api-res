<?php
require_once("bdd.php");

// REQUETES par la methode GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    // Récupérer les paramètres de l'URL
    $param = $_GET['param'];

    // RECUPERATION DE RESULTATS PUBLIES PAR UNE FACULTE
    if($param == 231 AND !empty($_GET['nom_user']) AND !empty($_GET['id_utilisateur'])){
        $nom_user = $_GET['nom_user'];
        $id_utilisateur = $_GET['id_utilisateur'];

        $rec_resulatats = $bdd->prepare(
            "SELECT DISTINCT jurys.id, jurys.nom, etudiants.filiere, etudiants.promotion, etudiants.annee, releves.date_c 
            FROM jurys, etudiants, releves 
            WHERE jurys.id = etudiants.id_jury 
            AND jurys.id = ? 
            AND jurys.nom = ? 
            AND etudiants.id = releves.id_etudiant" );
        $rec_resulatats->execute(array($id_utilisateur,$nom_user));
        $data = $rec_resulatats->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
    }

    if(isset($_GET['fulanyi']) AND $_GET['fulanyi'] == "on" AND $param == 13){
        // $rec_liste_jury = $bdd->prepare(
        //     "SELECT jurys.id, jurys.nom, jurys.etat, utilisateurs.email, utilisateurs.date_c
        //     FROM jurys, utilisateurs, appariteurs
        //     WHERE jurys.id_utilisateur = utilisateurs.id
        //     ");
        $rec_liste_jury = $bdd->prepare(
            "SELECT jurys.id, jurys.nom, jurys.etat, utilisateurs.email, utilisateurs.date_c
            FROM jurys, utilisateurs
            WHERE jurys.id_utilisateur = utilisateurs.id
            ");
        $rec_liste_jury->execute(array());
        $data = $rec_liste_jury->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
    }
    
    
    if($param == 131 AND !empty($_GET['id_jury'])){
        $id_jury = $_GET['id_jury'];
        $rec_liste_jury = $bdd->prepare("SELECT jurys.id, jurys.nom, jurys.etat, utilisateurs.email, utilisateurs.date_c, SUM(etudiants.id_jury) as total_etudiant FROM jurys, etudiants, utilisateurs WHERE jurys.id = etudiants.id_jury AND jurys.id_utilisateur = utilisateurs.id AND jurys.id = ?");
        $rec_liste_jury->execute(array($id_jury));
        $data = $rec_liste_jury->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
    }

    if($param == 22){
        // CONSULTATION DE RELEVES : JURY
        if(!empty($_GET['jury']) AND !empty($_GET['promotion']) AND !empty($_GET['annee'])){
            $promotion = $_GET['promotion'];
            $annee = $_GET['annee'];
            $nom_jury = $_GET['jury'];
            
            // Traiter les données reçues
            $rec_releves = $bdd->prepare(
                "SELECT etudiants.id, jurys.id as id_jury, jurys.nom as nom_jury, etudiants.noms, etudiants.promotion, etudiants.annee 
                FROM jurys, etudiants 
                WHERE jurys.id = etudiants.id_jury 
                AND jurys.nom = ? AND etudiants.promotion = ? AND etudiants.annee = ?");
            $rec_releves->execute(array($nom_jury,$promotion,$annee));
            $data = $rec_releves->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($data);
            echo $json;
        }

        // CONSULTATION DE RESULTATATS : ETUDIANT
        if(!empty($_GET['id_etudiant']) AND !empty($_GET['promotion']) AND !empty($_GET['annee'])){
            $promotion = $_GET['promotion'];
            $annee = $_GET['annee'];
            $id_etudiant = $_GET['id_etudiant'];
            
            // Traiter les données reçues
            $rec_releves = $bdd->prepare("SELECT etudiants.id, jurys.id as id_jury, jurys.nom as nom_jury, etudiants.noms, etudiants.promotion, etudiants.annee FROM jurys, etudiants WHERE jurys.id = etudiants.id_jury AND etudiants.id = ? AND etudiants.promotion = ? AND etudiants.annee = ?");
            $rec_releves->execute(array($id_etudiant,$promotion,$annee));
            $data = $rec_releves->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($data);
            echo $json;
        }
    }

    // RECUPERATION DES ELEMENTS D'ENSEIGNEMENT POUR TOUS LES RELEVES RECUPERES
    if($param == 221 AND isset($_GET['id_releve'])){
        $id_releve = $_GET['id_releve'];
        

        // RECUPERER RELEVES
        $select = $bdd->query(
            "SELECT etudiants.id, jurys.nom, etudiants.noms, etudiants.filiere, etudiants.promotion 
            FROM jurys, etudiants
            WHERE jurys.id = etudiants.id_jury 
            -- AND etudiants.id = 1
            -- ORDER BY jurys.id
            ");
        $row = $select->fetch(PDO::FETCH_ASSOC);

        // Tableau pour stocker les résultats
        $tableau = array();

        
        // Si l'etudiant n'existe pas encore dans le tableau, on l'ajoute
        if(!isset($tableau[$row['id']])) {
            $tableau[$row['id']] = array(
                'nom' => $row['nom'],
                'noms' => $row['noms'],
                'filiere' => $row['filiere'],
                'promotion' => $row['promotion'],
                'el_enseignements' => array()
            );
        }

        // ELEMENTS D'ENSEIGNEMENT
        $rec_els_e = $bdd->prepare(
            "SELECT etudiants.id, cours.unite, cours.nom as nom_cours, cours.credit, cours.sur_cote, cotes.cote 
            FROM etudiants, cours, cotes 

            WHERE etudiants.id = cotes.id_etudiant 
            AND cotes.id_cours = cours.id
            AND etudiants.id = '1' 
        ");

        while($row = $rec_els_e->fetch(PDO::FETCH_ASSOC)) {
            // On ajoute les elements d'enseignements à l'etudiant correspondant
            $tableau[$row['id']]['elements_enseignements'][] = array(
                'nom' => $row['nom'],
                'nom_cours' => $row['nom_cours']
            );
        }
        

        // Affichage du tableau (pour vérification)
        $json = json_encode($tableau);
        echo $json;
        // print_r($tableau);


        // $rec_releves->execute(array());
        // $data = $rec_releves->fetch(PDO::FETCH_ASSOC);
        // $json = json_encode($data);
        // echo $json;

        // ELEMENTS D'ENSEIGNEMENT
        // $id_releve = $_GET['id_releve'];
        // $rec_releves = $bdd->prepare(
        //     "SELECT etudiants.id, cours.unite, cours.nom as nom_cours, cours.credit, cours.sur_cote, cotes.cote 
        //     FROM etudiants, cours, cotes 

        //     WHERE etudiants.id = cotes.id_etudiant 
        //     AND cotes.id_cours = cours.id
        // ");
        // $rec_releves->execute(array());
        // $data = $rec_releves->fetchAll(PDO::FETCH_ASSOC);
        // $json = json_encode($data);
        // echo $data;
       

                
    }
}




// Exécution de la requête
// $rec_releves = $bdd->query(
//     "SELECT e.id, e.noms, e.filiere, e.promotion, e.annee, j.nom as nom_jury, c.unite, c.nom as nom_cours, c.credit, c.sur_cote, co.cote
//     FROM utilisateurs u
//     INNER JOIN etudiants e ON u.id = e.id_utilisateur
//     LEFT JOIN jurys j ON e.id_jury = j.id
//     LEFT JOIN releves r ON e.id = r.id_etudiant
//     LEFT JOIN cours c ON r.id_jury = c.id_jury AND r.id = c.id
//     LEFT JOIN cotes co ON e.id = co.id_etudiant AND c.id = co.id_cours
//     "
// );
            
// $rec_releves->execute(array());
// $data = $rec_releves->fetchAll(PDO::FETCH_ASSOC);
// $json = json_encode($data);
// echo $json;


// $releves_rec = array();


// Boucle pour parcourir les résultats de la requête
// while($row = $rec_releves->fetch(PDO::FETCH_ASSOC)) {
//     // Si l'etudiant n'existe pas encore dans le tableau, on l'ajoute
//     if(!isset($releves_rec[$row['id']])) {
//         $releves_rec[$row['id']] = array(
//             'nom_jury' => $row['nom_jury'],
//             'annee' => $row['annee'],
//             'noms' => $row['noms'],
//             'filiere' => $row['filiere'],
//             'promotion' => $row['promotion'],
//             'elements_enseignements' => array()
//         );
//     }
//     // On ajoute les elements d'enseignements à l'etudiant correspondant
//     $releves_rec[$row['id']]['elements_enseignements'][] = array(
//         'unite' => $row['unite'],
//         'nom_cours' => $row['nom_cours']
//     );
// }

// Affichage du tableau
// $json = json_encode($releves_rec);
// echo $json;
// print_r($tableau);


?>