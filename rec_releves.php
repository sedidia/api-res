<?php
require_once("bdd.php");
$type_utilisateur = "";
$enOrdre = false;
$appel = 0;

function addConsult($id_etudiant){
    global $bdd;
    $selectInfosStud = $bdd->prepare("SELECT id, id_jury, promotion, filiere, annee FROM etudiants WHERE id = ?");
    $selectInfosStud->execute(array($id_etudiant));
    if($selectInfosStud->rowCount() >= 1){
        $founded_stud = $selectInfosStud->fetch();
        $id_jury = $founded_stud['id_jury'];
        $promotion = $founded_stud['promotion'];
        $filiere = $founded_stud['filiere'];
        $annee = $founded_stud['annee'];

        $trait_stats = $bdd->prepare("SELECT * FROM statists WHERE id_jury = ? AND promotion = ? AND filiere = ? AND annee = ?"); 
        $trait_stats->execute(array($id_jury,$promotion,$filiere,$annee));
        if($trait_stats->rowCount() == 1){
            $stat_exist = $trait_stats->fetch();
            $id_stat = $stat_exist['id'];                                        
            
            $update_stat = $bdd->prepare("UPDATE statists SET n_consultation = n_consultation + ? WHERE id_jury = ? AND promotion = ? AND filiere = ? AND annee = ?");
            $update_stat->execute(array(1,$id_jury,$promotion,$filiere,$annee));
        }
        
    }
}
function renvoiReponse($tableau){
    $json = json_encode($tableau);
    echo $json;
}
function nonEnOrdre($msg,$success){
    header('Content-Type: application/json');
    $res = [
        "success" => $success,
        "msg" => $msg
    ];
    echo json_encode($res);
}
// REQUETES par la methode GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    // Récupérer les paramètres de l'URL
    $param = $_GET['param'];


    // RECUPERATION / LES RELEVES RECUPERES
    if($param == 221){
        $id_founded = 0;
        
        // consultation par le jury
        if(isset($_GET['nom_jury']) AND !empty($_GET['filiere']) AND !empty($_GET['promotion']) AND !empty($_GET['annee'])){
            $nom_jury = $_GET['nom_jury'];
            $filiere = $_GET['filiere'];
            $promotion = $_GET['promotion'];
            $annee = $_GET['annee'];

            $select = $bdd->query(
                "SELECT etudiants.id, etudiants.noms, membres_jury.president, membres_jury.secretaire, releves.somme_produits, releves.somme_credits, releves.credits, releves.credits_valides, releves.echecs, releves.decision_jury, etudiants.filiere, etudiants.promotion, etudiants.annee, jurys.nom as nom_jury, cours.unite, cours.nom, cours.credit, cotes.cote, appariteurs.nom as nom_faculte
                FROM etudiants, cotes, jurys, cours, releves, membres_jury, utilisateurs, appariteurs
                WHERE etudiants.id = cotes.id_etudiant
                AND  etudiants.id_jury = jurys.id
                AND  appariteurs.id = jurys.id_appariteur
                AND  etudiants.id_utilisateur = utilisateurs.id
                AND  membres_jury.id_jury = jurys.id
                AND  membres_jury.annee = '$annee'
                AND  cotes.id_cours = cours.id
                AND  releves.id_jury = jurys.id
                AND  releves.id_etudiant = etudiants.id
                AND  etudiants.filiere = '$filiere'
                AND  etudiants.promotion = '$promotion'
                AND  etudiants.annee = '$annee'
                -- AND  etudiants.annee = '$annee'
                AND jurys.nom = '$nom_jury'
                ORDER BY cours.unite
            ");
            // $select = $bdd->query(
            //     "SELECT etudiants.id, etudiants.noms, membres_jury.president, membres_jury.secretaire, releves.somme_produits, releves.somme_credits, releves.credits, releves.credits_valides, releves.echecs, releves.decision_jury, etudiants.filiere, etudiants.promotion, etudiants.annee, jurys.nom as nom_jury, cours.unite, cours.nom, cours.credit, cotes.cote
            //     FROM etudiants, cotes, jurys, cours, releves, membres_jury
            //     WHERE etudiants.id = cotes.id_etudiant
            //     AND  etudiants.id_jury = jurys.id
            //     AND  membres_jury.id_jury = jurys.id
            //     AND  membres_jury.annee = '$annee'
            //     AND  cotes.id_cours = cours.id
            //     AND  releves.id_etudiant = etudiants.id
            //     AND  etudiants.filiere = '$filiere'
            //     AND  etudiants.promotion = '$promotion'
            //     AND  etudiants.annee = '$annee'
            //     AND jurys.nom = '$nom_jury'
            //     ORDER BY cours.unite
            // ");
        }

        // consultation par l'etudiant
        // id_etudiant en id_utilisateur, etant donné qu'on peut avoir un meme etudiant mais + de fois dans etudiants
        if(isset($_GET['id_etudiant']) && !empty($_GET['promotion']) && !empty($_GET['annee']) ){
            $id_utilisateur_etudiant = $_GET['id_etudiant'];
            $id_etudiant = $_GET['id_etudiant'];
            $promotion = $_GET['promotion'];
            $annee = $_GET['annee']; 
            $type_utilisateur = "etudiant";

            $incrementerConsultation = 1;

            $verif_Annee_et = $bdd->prepare('SELECT * FROM etudiants WHERE id_utilisateur = ? AND annee = ?'); 
            $verif_Annee_et->execute(array($id_utilisateur_etudiant,$annee));
            if($verif_Annee_et->rowCount() == 1){
                $existAnnEt = $verif_Annee_et->fetch();
                $id_founded = $existAnnEt['id'];
                $id_us = $existAnnEt['id_utilisateur'];
                
                $verif_Paiement = $bdd->prepare('SELECT * FROM paiements WHERE id_etudiant = ?'); 
                $verif_Paiement->execute(array($id_founded));
                if($verif_Paiement->rowCount() == 1){
                    $existP = $verif_Paiement->fetch();
                    $montant_a_payer = $existP['montant'];
                    $montant_paye = $existP['paye'];
                    if($montant_a_payer == $montant_paye){
                        $select = $bdd->query(
                        "SELECT etudiants.id, etudiants.noms, membres_jury.president, membres_jury.secretaire, releves.somme_produits, releves.somme_credits, releves.credits, releves.credits_valides, releves.echecs, releves.decision_jury, etudiants.filiere, etudiants.promotion, etudiants.annee, jurys.nom as nom_jury, cours.unite, cours.nom, cours.credit, cotes.cote, appariteurs.nom as nom_faculte
                        FROM etudiants, cotes, jurys, cours, releves, membres_jury, appariteurs
                        WHERE etudiants.id = releves.id_etudiant
                        AND etudiants.id = cotes.id_etudiant
                        AND  etudiants.id_jury = jurys.id
                        AND  appariteurs.id = jurys.id_appariteur
                        AND  cotes.id_cours = cours.id
                        AND  membres_jury.id_jury = jurys.id
                        AND  membres_jury.annee = '$annee'
                        AND  etudiants.promotion = '$promotion'
                        AND  etudiants.annee = '$annee'
                        AND  etudiants.id_utilisateur = '$id_utilisateur_etudiant'
                        AND releves.diffuse = 1
                        ORDER BY cours.unite");
                    }else{
                        nonEnOrdre($msg,$success);
                        return false;
                    }
                }else {
                    $msg = "Vous n'etes pas en ordre avec les frais !<br>👵";
                    $success = 1;
                    nonEnOrdre($msg,$success);
                    return false;
                }
                
            }else{
                $msg = "etudiant inexistant pour cette année.";
                $success = 0;                
                nonEnOrdre($msg,$success);
                return false;
            }

        }

        // partagé
        if(isset($_GET['id_etudiant']) && !empty($_GET['partage']) && !empty($_GET['partage_code']) && !empty($_GET['annee'])){
            $id_etudiant = $_GET['id_etudiant'];
            $partage_code = $_GET['partage_code'];                 
            $annee = $_GET['annee'];                       

            $select = $bdd->query(
                "SELECT etudiants.id, SUM(cours.credit) AS coefficient, etudiants.noms, membres_jury.president, membres_jury.secretaire, releves.somme_produits, releves.somme_credits, releves.credits, releves.credits_valides, releves.echecs, releves.decision_jury, releves.somme_produits, releves.somme_credits, etudiants.filiere, etudiants.promotion, etudiants.annee, jurys.nom as nom_jury, cours.unite, cours.nom, cours.credit, cotes.cote, appariteurs.nom as nom_faculte
                FROM etudiants, cotes, jurys, cours, liens_p, releves, membres_jury, appariteurs
                WHERE etudiants.id = cotes.id_etudiant
                AND etudiants.id = releves.id_etudiant
                AND  etudiants.id_jury = jurys.id
                AND  etudiants.annee = '$annee'
                AND  membres_jury.id_jury = jurys.id
                AND  cotes.id_cours = cours.id
                AND  appariteurs.id = jurys.id_appariteur
                AND  membres_jury.annee = '$annee'
                AND  etudiants.id_utilisateur = '$id_etudiant'
                AND  liens_p.id_etudiant = etudiants.id_utilisateur
                AND  liens_p.code_c = '$partage_code'
                AND releves.diffuse = 1
                GROUP BY cotes.id_cours
                ORDER BY cours.unite
            ");
        }

        // $select->execute(array());

        // Tableau pour stocker les résultats
        $tableau = array();
        $p_moyenne = 0;
        $s_credit = 0;

        $moyenne_ponderee = 0;
        $credit_m = 0;
        $nombre_echecs = 0;
        $credit_valide = 60;
        $decision_jury = "";

        if(isset($incrementerConsultation) AND $select->rowCount() >= 1){
            addConsult($id_etudiant);            
        }

        // Boucle pour parcourir les résultats de la requête
        while($row = $select->fetch(PDO::FETCH_ASSOC)) {
            
            // Si le relevé n'existe pas encore dans le tableau, on l'ajoute
            // echo $moyenne_ponderee.'<br>';
            if(!isset($tableau[$row['id']])) {
                $tableau[$row['id']] = array(
                    'id_founded' => $id_founded,
                    'president' => $row['president'],
                    'secretaire' => $row['secretaire'],
                    'nom_jury' => $row['nom_jury'],
                    'nom_faculte' => $row['nom_faculte'],
                    'noms' => $row['noms'],
                    'filiere' => $row['filiere'],
                    'promotion' => $row['promotion'],
                    'annee' => $row['annee'],
                    'moyenne_ponderee' => $row['somme_produits'] / $row['somme_credits'],
                    'echecs' => $row['echecs'],
                    'credits_valides' => $row['credits_valides'],
                    'credits' => $row['credits'],
                    'decision_jury' => $row['decision_jury'],
                    'cotes' => array()
                );
            }
            // On ajoute les elements d'enseignement au relevé correspondant
            $tableau[$row['id']]['cotes'][] = array(
                'unite' => $row['unite'],
                'nom' => $row['nom'],
                'credit' => $row['credit'],
                'cote' => $row['cote']
            );
        }        

        // Affichage du tableau (pour vérification)
        echo renvoiReponse($tableau);
        
        // recuperer le type etudiant
        
        // recuperer le type etudiant
        
        
    }

    if($param == 22101 && isset($_GET['id_jury'])){
        $id_jury = $_GET['id_jury'];
        $rec_liste_jury = $bdd->prepare(
            "SELECT id, id_jury, filiere, promotion, annee
            FROM etudiants
            WHERE id_jury = $id_jury
            -- GROUP BY filiere
            ");
        $rec_liste_jury->execute(array());
        $data = $rec_liste_jury->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
        return;
    }
    if($param == 22101 && isset($_GET['id_etudiant'])){
        $id_etudiant = $_GET['id_etudiant'];

        // $rec_ets = $bdd->prepare('SELECT * FROM etudiants WHERE id = ?'); 
        // $rec_ets->execute(array($id_etudiant));
        // if($rec_ets->rowCount() == 1){
        //     $etat_exist = $rec_ets->fetch();
        //     $etat_j = $etat_exist['etat'];


        $rec_liste_jury = $bdd->prepare(
            "SELECT id, id_jury, filiere, promotion, annee
            FROM etudiants
            WHERE id = $id_etudiant
            -- GROUP BY filiere
            ");
        $rec_liste_jury->execute(array());
        $data = $rec_liste_jury->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
        return;
    }

    // consultation des notification
    if($param == 30){
        
        // consultation par le jury
        if(!empty($_GET['id_utilisateur']) && !empty($_GET['type_u'])){
            $id_utilisateur = $_GET['id_utilisateur'];
            $type_u = $_GET['type_u'];

            if($type_u == 'admin'){
                $select_notifications = $bdd->prepare(
                    "SELECT *
                    FROM notifications 
                    ORDER BY id DESC
                ");
                $select_notifications->execute(array());
            }
            if($type_u == 'jury'){
                $select_notifications = $bdd->prepare(
                    "SELECT notifications.id, notifications.id_author, notifications.titre, notifications.contenu, notifications.type_u, notifications.annee, notifications.date_c, jurys.id as id_jury 
                    FROM notifications, jurys WHERE jurys.id = notifications.id_author AND type_u = ?
                    ORDER BY id DESC
                ");
                $select_notifications->execute(array($id_utilisateur));
            }
            if($type_u == 'etudiant'){
                $select_notifications = $bdd->prepare(
                    "SELECT *
                    FROM notifications 
                    WHERE id_author = ?
                    ORDER BY id DESC
                ");
                $select_notifications->execute(array($id_utilisateur));
            }

            $notifs = $select_notifications->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($notifs);
        }
        
        if(isset($_GET['id_notif'])){
            $id_notif = $_GET['id_notif'];

            $select_notifications = $bdd->prepare(
                "SELECT id, titre, contenu
                FROM notifications 
                WHERE id = ?
            ");
            $select_notifications->execute(array($id_notif));
            $notifs = $select_notifications->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($notifs);
        }
    }

    if($param == 13100){
        // $id_jury = $_GET['id_jury'];        
        // $annee = $_GET['annee'];  
        
        // $select_membres = $bdd->prepare("SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ? ");
        // $select_membres->execute(array($id_jury,$annee));
        // if($select_membres->rowCount() == 1){
        //     $membre_exist = $select_membres->fetch();
        //     $id_stat = $membre_exist['id'];

        //     $president = $membre_exist['president'];
        //     $secretaire = $membre_exist['secretaire'];
        // }else{
        //     $select_membres = $bdd->prepare("SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ? ");
        //     $select_membres->execute(array($id_jury,$annee-1));
        //     if($select_membres->rowCount() == 1){
        //         $membre_exist = $select_membres->fetch();
        //         $id_stat = $membre_exist['id'];
    
        //         $president = $membre_exist['president'];
        //         $secretaire = $membre_exist['secretaire'];
                
        //     }else{
        //         $select_membres = $bdd->prepare("SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ? ");
        //         $select_membres->execute(array($id_jury,$annee-2));
        //         if($select_membres->rowCount() == 1){
        //             $membre_exist = $select_membres->fetch();
        //             $id_stat = $membre_exist['id'];
        
        //             $president = $membre_exist['president'];
        //             $secretaire = $membre_exist['secretaire'];
        //         }else{
        //             $president = '';
        //             $secretaire = '';
        //         }

        //     }
        // }

        // $res = [
        //     "president" => $president,
        //     "secretaire" => $secretaire
        // ];
        // echo json_encode($res);

        $select_membres = $bdd->prepare(
            "SELECT membres_jury.id, membres_jury.id_jury, membres_jury.president, membres_jury.secretaire, membres_jury.appariteur, membres_jury.annee, utilisateurs.email as emailAppariteur, appariteurs.nom as faculte
            FROM membres_jury, utilisateurs, appariteurs
            WHERE membres_jury.id_appariteur = appariteurs.id AND appariteurs.id_utilisateur = utilisateurs.id
            ");
        $select_membres->execute(array());
        $data = $select_membres->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
    }
}

// while($row = $select_moyenne_p->fetch(PDO::FETCH_ASSOC)) {
//     $produit = $row['produit'];
//     $coefficient = $row['coefficient'];
//     $moyenne_ponderee = $moyenne_ponderee + ($row['produit']/$row['coefficient']);

//     $tableau[$row['id']] = array(
//         'produit' => $produit,
//         'coefficient' => $coefficient,
//         'moyenne_ponderee' => $moyenne_ponderee
//     );
// }
?>