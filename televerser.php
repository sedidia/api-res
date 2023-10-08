<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";

// variables de statistiques
$n_etudiants = 0;
$n_consultation = 0;
$reussite = 0;
$appel = 0;
// variables de statistiques

// les fonctions
// EN CAS D'ERREUR
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

// le suppresion de grilles de deliberation
function deleteGrid($success,$msg,$id_jury_source,$id_utilisateur,$id_utilisateur_jury,$nom_faculte,$releve){
    global $bdd, $reussite, $n_etudiants, $n_consultation, $reussite, $appel;
    $noms =  $releve["NOM, POST-NOM et PRENOM"];
    $jury =  $releve["jury"];
    $annee =  $releve["annee"];
    $indice = 0;
    $type_u = "etudiant";
    $etudiantExistant = 0;
    $indice = $indice + 1;
    $email = "idris".$indice."@gmail.com";
    $mdp = $noms;
    $echecs = 0;
    
    $verifMembresJury = $bdd->prepare('SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ?'); 
    $verifMembresJury->execute(array($id_jury_source,$annee));
    if($verifMembresJury->rowCount() != 0){
        $verif_central = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND filiere = ? AND promotion = ?'); 
        $verif_central->execute(array($id_jury,$filiere,$promotion));
        if($verif_central->rowCount() >= 1){
    
            // annee courante
            $student_exist = $bdd->prepare('SELECT * FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?'); 
            $student_exist->execute(array($noms,$promotion,$annee,$filiere));
            if($student_exist->rowCount() == 1){
    
                $id_sutilisateur_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND mdp = ?'); 
                $id_sutilisateur_exist->execute(array($type_u,$mdp));
                if($id_sutilisateur_exist->rowCount() > 0){
                    $utilisateur_exist = $id_sutilisateur_exist->fetch();
                    $id_utilisateur = $utilisateur_exist['id'];
                    
                    $inserEtudiant = $bdd->prepare('INSERT INTO etudiants(id_utilisateur,id_jury,noms,filiere,promotion,annee)VALUES(?,?,?,?,?,?) ');
                    $inserEtudiant->execute(array($id_utilisateur,$id_jury,$noms,$filiere,$promotion,$annee));

                    // $delete0 = $bdd->prepare("DELETE FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?");
                    // $delete0->execute(array($noms,$promotion,$annee,$filiere));
                    
                    $select_etudiant_id = $bdd->prepare("SELECT * FROM etudiants WHERE id_utilisateur = ? AND filiere = ? AND annee = ?");
                    $select_etudiant_id->execute(array($id_utilisateur,$filiere,$annee));
                    if($select_etudiant_id->rowCount() == 1){
                        $id_trouve = $select_etudiant_id->fetch();
                        $id_etudiant = $id_trouve['id'];
    
                        // enregistrement d'un relevé
                        $inserReleve = $bdd->prepare('INSERT INTO releves(id_etudiant,id_jury,filiere,promotion,somme_produits,somme_credits,credits,credits_valides,echecs,decision_jury,diffuse,annee)VALUES(?,?,?,?,?,?,?,?,?,?,?,?) ');
                        $inserReleve->execute(array($id_etudiant,$id_jury,$filiere,$promotion,0,0,0,0,0,'R',1,$annee));
                        // enregistrement d'un relevé  
    
                        // inserer les notes de manière dynamique
                        $boucleCours = $bdd->query("SELECT * FROM cours WHERE id_jury = '$id_jury' AND promotion = '$promotion' AND filiere = '$filiere' ");
                        while($row = $boucleCours->fetch(PDO::FETCH_ASSOC)){
                            // ...
                            $somme_credits = 0;
                            $nomCours = $row["nom"];
                            $indice = $indice + 1;                        
    
                            if(isset($releve["$nomCours"])){
                                $coteCours =  $releve["$nomCours"];
                                $id_cours = $row['id'];
                                $credit = $row['credit'];
                                
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$coteCours));
    
                                $somme_produits = ($coteCours*$credit);
                                $somme_credits = $somme_credits + $credit;
                                if($coteCours < 10){
                                    $echecs = 1;
                                    $credit = $credit - $credit;
                                }else{
                                    $credits_valides = $credit;
                                }
                                
                                $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                                $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
    
                                $success = 1;
                                $msg = "Succès !";
                            }else{
                                $success = 1;
                                $msg = "Requete aboutie avec succès !$";
                            } 
                        }
    
                        // mise à jour de la decision du jury
                        $select_decision_jury = $bdd->prepare('SELECT * FROM releves WHERE id_jury = ? AND id_etudiant = ?'); 
                        $select_decision_jury->execute(array($id_jury,$id_etudiant));
                        if($select_decision_jury->rowCount() == 1){
                            $I_exist = $select_decision_jury->fetch();
                            $id_cours = $I_exist['id'];
                            $credits = $I_exist['credits'];
                            $echecs = $I_exist['echecs'];
                            $credits_valides = $I_exist['credits_valides'];
    
                            if($echecs <= 3 && $echecs >= 1){
                                $decision_jury = 'P'.$echecs;
                                $reussite = $reussite + 1;
                            }
                            if($echecs == 0){
                                $decision_jury = 'PS';
                                $reussite = $reussite + 1;
                            }
                            $update_mp = $bdd->prepare("UPDATE releves SET decision_jury = ? WHERE id_jury = ? AND id_etudiant = ?");
                            $update_mp->execute(array($decision_jury,$id_jury,$id_etudiant));
                        }
    
                        $n_etudiants = $n_etudiants + 1;
                        // creation des statistiques
                        $trait_stats = $bdd->prepare('SELECT * FROM statists WHERE id_jury = ? AND promotion = ? AND filiere = ? AND annee = ?'); 
                        $trait_stats->execute(array($id_jury,$promotion,$filiere,$annee));
                        if($trait_stats->rowCount() == 1){
                            $stat_exist = $trait_stats->fetch();
                            $id_stat = $stat_exist['id'];                                        
                            
                            $update_stat = $bdd->prepare("UPDATE statists SET n_etudiants = ?, n_consultation = ?, reussite = ? WHERE id_jury = ? AND promotion = ? AND filiere = ?");
                            $update_stat->execute(array($n_etudiants,$n_consultation,$reussite,$id_jury,$promotion,$filiere));
                        }else{
                            $creat_stat = $bdd->prepare('INSERT INTO statists(id_jury,promotion,filiere,annee,n_etudiants,n_consultation,reussite)VALUES(?,?,?,?,?,?,?) ');
                            $creat_stat->execute(array($id_jury,$promotion,$filiere,$annee,$n_etudiants,$n_consultation,$reussite));
                        }                    
    
                        // creation des notifiations
                        $titre = "Téléversement de grille".$promotion." ".$filiere;
                        $contenu = "téléversement de la grille de deliberation de bac ".$promotion." ".$filiere." ".$annee;
                        $not_pour = $id_utilisateur;
                        
                        $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?'); 
                        $trait_notifs->execute(array($id_utilisateur_jury,$titre,$contenu,$id_jury,$annee));
                        if($trait_notifs->rowCount() == 0){                        
                            $creat_not = $bdd->prepare("INSERT INTO notifications(id_author,titre,contenu,type_u,annee)VALUES(?,?,?,?,?) ");
                            $creat_not->execute(array($id_utilisateur_jury,$titre,$contenu,$id_jury,$annee));
                        }
                        $success = 1;
                        $msg = "Grille téléversée avec succès !";
    
                    }
                }else{
                    $success = 0;
                    $msg = "Erreur verification ou insertion.".$mdp;
                }
            }else{
                $success = 0;
                $msg = "La grille a été supprimée avec succès.";
            }
        }else{
            $success = 0;
            $msg = "Veillez envoyer les cours de cette promotion à votre administrateur avant de televerser cette grille.";
        }
    }else{
        $success = 0;
        $msg = "Aucune grille existante pour l'année ".$annee;
    }


    $appel = $appel + 1;
    if($appel == 1){
        responseServer($success,$msg);
    }   
}
// le televersement de grilles de deliberation
function televersement($success,$msg,$releve,$id_jury,$promotion,$filiere,$noms,$annee,$id_utilisateur_jury){
    global $bdd, $reussite, $n_etudiants, $n_consultation, $reussite, $appel;
    $noms =  $releve["NOM, POST-NOM et PRENOM"];
    $jury =  $releve["jury"];
    $indice = 0;
    $type_u = "etudiant";
    $etudiantExistant = 0;
    $indice = $indice + 1;
    $email = "idris".$indice."@gmail.com";
    $mdp = $noms;
    $echecs = 0;
    
    $verifMembresJury = $bdd->prepare('SELECT * FROM membres_jury WHERE id_jury = ? AND annee = ?'); 
    $verifMembresJury->execute(array($id_jury,$annee));
    if($verifMembresJury->rowCount() != 0){

        $verif_central = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND filiere = ? AND promotion = ?'); 
        $verif_central->execute(array($id_jury,$filiere,$promotion));
        if($verif_central->rowCount() >= 1){
    
            // annee courante
            $student_exist = $bdd->prepare('SELECT * FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?'); 
            $student_exist->execute(array($noms,$promotion,$annee,$filiere));
            if($student_exist->rowCount() == 0){
                // annee passée
                $student_exist2 = $bdd->prepare('SELECT * FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?'); 
                $student_exist2->execute(array($noms,$promotion,$annee-1,$filiere));
                if($student_exist2->rowCount() == 0){
                    $student_exist_v = $bdd->prepare('SELECT * FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?'); 
                    $student_exist_v->execute(array($noms,$promotion-1,$annee-1,$filiere));
                    if($student_exist_v->rowCount() == 0){
                        $inserUtilisateur = $bdd->prepare('INSERT INTO utilisateurs(type_u,email,mdp,mdp2)VALUES(?,?,?,?) ');
                        $inserUtilisateur->execute(array($type_u,$email,$mdp,$mdp));
                    }
                }
    
                $id_sutilisateur_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND mdp = ?'); 
                $id_sutilisateur_exist->execute(array($type_u,$mdp));
                if($id_sutilisateur_exist->rowCount() > 0){
                    $utilisateur_exist = $id_sutilisateur_exist->fetch();
                    $id_utilisateur = $utilisateur_exist['id'];
                    
                    $inserEtudiant = $bdd->prepare('INSERT INTO etudiants(id_utilisateur,id_jury,noms,filiere,promotion,annee)VALUES(?,?,?,?,?,?) ');
                    $inserEtudiant->execute(array($id_utilisateur,$id_jury,$noms,$filiere,$promotion,$annee));
                    
                    $select_etudiant_id = $bdd->prepare("SELECT * FROM etudiants WHERE id_utilisateur = ? AND filiere = ? AND annee = ?");
                    $select_etudiant_id->execute(array($id_utilisateur,$filiere,$annee));
                    if($select_etudiant_id->rowCount() == 1){
                        $id_trouve = $select_etudiant_id->fetch();
                        $id_etudiant = $id_trouve['id'];
    
                        // enregistrement d'un relevé
                        $inserReleve = $bdd->prepare('INSERT INTO releves(id_etudiant,id_jury,filiere,promotion,somme_produits,somme_credits,credits,credits_valides,echecs,decision_jury,diffuse,annee)VALUES(?,?,?,?,?,?,?,?,?,?,?,?) ');
                        $inserReleve->execute(array($id_etudiant,$id_jury,$filiere,$promotion,0,0,0,0,0,'R',1,$annee));
                        // enregistrement d'un relevé  
    
                        // inserer les notes de manière dynamique
                        $boucleCours = $bdd->query("SELECT * FROM cours WHERE id_jury = '$id_jury' AND promotion = '$promotion' AND filiere = '$filiere' ");
                        while($row = $boucleCours->fetch(PDO::FETCH_ASSOC)){
                            // ...
                            $somme_credits = 0;
                            $nomCours = $row["nom"];
                            $indice = $indice + 1;                        
    
                            if(isset($releve["$nomCours"])){
                                $coteCours =  $releve["$nomCours"];
                                $id_cours = $row['id'];
                                $credit = $row['credit'];
                                
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$coteCours));
    
                                $somme_produits = ($coteCours*$credit);
                                $somme_credits = $somme_credits + $credit;
                                if($coteCours < 10){
                                    $echecs = 1;
                                    $credit = $credit - $credit;
                                }else{
                                    $credits_valides = $credit;
                                }
                                
                                $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                                $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
    
                                $success = 1;
                                $msg = "Succès !";
                            }else{
                                $success = 1;
                                $msg = "Requete aboutie avec succès !$";
                            } 
                        }
    
                        // mise à jour de la decision du jury
                        $select_decision_jury = $bdd->prepare('SELECT * FROM releves WHERE id_jury = ? AND id_etudiant = ?'); 
                        $select_decision_jury->execute(array($id_jury,$id_etudiant));
                        if($select_decision_jury->rowCount() == 1){
                            $I_exist = $select_decision_jury->fetch();
                            $id_cours = $I_exist['id'];
                            $credits = $I_exist['credits'];
                            $echecs = $I_exist['echecs'];
                            $credits_valides = $I_exist['credits_valides'];
    
                            if($echecs <= 3 && $echecs >= 1){
                                $decision_jury = 'P'.$echecs;
                                $reussite = $reussite + 1;
                            }
                            if($echecs == 0){
                                $decision_jury = 'PS';
                                $reussite = $reussite + 1;
                            }
                            $update_mp = $bdd->prepare("UPDATE releves SET decision_jury = ? WHERE id_jury = ? AND id_etudiant = ?");
                            $update_mp->execute(array($decision_jury,$id_jury,$id_etudiant));
                        }
    
                        $n_etudiants = $n_etudiants + 1;
                        // creation des statistiques
                        $trait_stats = $bdd->prepare('SELECT * FROM statists WHERE id_jury = ? AND promotion = ? AND filiere = ? AND annee = ?'); 
                        $trait_stats->execute(array($id_jury,$promotion,$filiere,$annee));
                        if($trait_stats->rowCount() == 1){
                            $stat_exist = $trait_stats->fetch();
                            $id_stat = $stat_exist['id'];                                        
                            
                            $update_stat = $bdd->prepare("UPDATE statists SET n_etudiants = ?, n_consultation = ?, reussite = ? WHERE id_jury = ? AND promotion = ? AND filiere = ?");
                            $update_stat->execute(array($n_etudiants,$n_consultation,$reussite,$id_jury,$promotion,$filiere));
                        }else{
                            $creat_stat = $bdd->prepare('INSERT INTO statists(id_jury,promotion,filiere,annee,n_etudiants,n_consultation,reussite)VALUES(?,?,?,?,?,?,?) ');
                            $creat_stat->execute(array($id_jury,$promotion,$filiere,$annee,$n_etudiants,$n_consultation,$reussite));
                        }                    
    
                        // creation des notifiations
                        $titre = "Téléversement de grille".$promotion." ".$filiere;
                        $contenu = "téléversement de la grille de deliberation de bac ".$promotion." ".$filiere." ".$annee;
                        $not_pour = $id_utilisateur;
                        
                        $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?'); 
                        $trait_notifs->execute(array($id_utilisateur_jury,$titre,$contenu,$id_jury,$annee));
                        if($trait_notifs->rowCount() == 0){                        
                            $creat_not = $bdd->prepare("INSERT INTO notifications(id_author,titre,contenu,type_u,annee)VALUES(?,?,?,?,?) ");
                            $creat_not->execute(array($id_utilisateur_jury,$titre,$contenu,$id_jury,$annee));
                        }
                        $success = 1;
                        $msg = "Grille téléversée avec succès !";
    
                    }
                }else{
                    $success = 0;
                    $msg = "Erreur verification ou insertion.".$mdp;
                }
            }else{
                $success = 1;
                $msg = "Grille existante sur le serveur.";
            }
        }else{
            $success = 0;
            $msg = "Veillez envoyer les cours de cette promotion à votre administrateur avant de televerser cette grille.";
        }
    }else{
        $success = 0;
        $msg = "Veillez demander à l'admin d'ajouter les membres du jury pour l'année ".$annee." avant de vouloir televerser cette grille";
    }


    $appel = $appel + 1;
    if($appel == 1){
        responseServer($success,$msg);
    }   
}

// archivage BAC1
function archivageBac1($success,$msg,$releve,$id_jury,$id_utilisateur,$filiere,$noms,$annee,$id_utilisateur_jury){
    $promotion = 1;
    echo televersement($success,$msg,$releve,$id_jury,$promotion,$filiere,$noms,$annee,$id_utilisateur_jury);
}
// archivage BAC2
function archivageBac2($success,$msg,$releve,$id_jury,$id_utilisateur,$filiere,$noms,$annee,$id_utilisateur_jury){
    $promotion = 2;                    
    echo televersement($success,$msg,$releve,$id_jury,$promotion,$filiere,$noms,$annee,$id_utilisateur_jury);
}
// archivage BAC3
function archivageBac3($success,$msg,$releve,$id_jury,$id_utilisateur,$filiere,$noms,$annee,$id_utilisateur_jury){
    $promotion = 3;                    
    echo televersement($success,$msg,$releve,$id_jury,$promotion,$filiere,$noms,$annee,$id_utilisateur_jury);
}                
// les fonctions

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // ARCHIVAGE DES RELEVES
    if($param == 21 && isset($_GET['id_jury']) && isset($_GET['id_utilisateur']) && isset($_GET['nom_faculte']) && isset($_GET['whatToDo'])){
        $id_jury_source = $_GET['id_jury'];
        $id_utilisateur = $_GET['id_utilisateur'];
        $id_utilisateur_jury = $id_utilisateur;
        $nom_faculte = $_GET['nom_faculte'];

        $whatToDo = $_GET['whatToDo'];
        $verif_etat_jury = $bdd->prepare('SELECT * FROM jurys WHERE id = ?'); 
        $verif_etat_jury->execute(array($id_jury_source));
        if($verif_etat_jury->rowCount() == 1){
            $etat_exist = $verif_etat_jury->fetch();
            $etat_j = $etat_exist['etat'];
            $id_jury = $etat_exist['id'];
            $nom_du_jury = $etat_exist['nom'];

            if($id_jury == $id_jury_source){
                if($etat_j == "0"){
                    foreach ($dataReques as $releve) {    
                        // verification de colonnes obligatoires
                        if(isset($releve["NOM, POST-NOM et PRENOM"]) 
                        && isset($releve["annee"]) 
                    && isset($releve["promotion"]) 
                    && isset($releve["jury"]) 
                    && isset($releve["filiere"])
                    && !isset($releve["montant"])
                    && !isset($releve["paye"])
                    ){
                        
                        $noms =  $releve["NOM, POST-NOM et PRENOM"];
                        $annee = $releve["annee"];
                        $promotion = $releve["promotion"];
                        $nom_du_jury_grille = $releve["jury"];
                        $filiere = $releve["filiere"];
                        if($whatToDo == "delete"){
                            $success = 1;
                            $msg = "Grille supprimée avec succès !";
                            echo deleteGrid($success,$msg,$id_jury_source,$id_utilisateur,$id_utilisateur_jury,$nom_faculte,$releve);
                        }                    
                        if($whatToDo == "televerse"){
                            if($nom_faculte == $nom_du_jury_grille){
                                if($promotion == "Bac1" || $promotion == "bac1" || $promotion == "Bac 1" || $promotion == "bac 1" || $promotion == 1){
                                    echo archivageBac1($success,$msg,$releve,$id_jury,$id_utilisateur,$filiere,$noms,$annee,$id_utilisateur_jury);
                                }
                                if($promotion == "Bac2" || $promotion == "bac2" || $promotion == "Bac 2" || $promotion == "bac 2" || $promotion == 2){
                                    echo archivageBac2($success,$msg,$releve,$id_jury,$id_utilisateur,$filiere,$noms,$annee,$id_utilisateur_jury);
                                }
                                if($promotion == "Bac3" || $promotion == "bac3" || $promotion == "Bac 3" || $promotion == "bac 3" || $promotion == 3){
                                    echo archivageBac3($success,$msg,$releve,$id_jury,$id_utilisateur,$filiere,$noms,$annee,$id_utilisateur_jury);
                                }
                            }else{
                                $success = 0;
                                $msg = "Erreur : vous n'etes pas autorisé de televerser les grilles de la faculté des ".$nom_du_jury_grille;
                                $appel = $appel + 1;
                                if($appel == 1){
                                    responseServer($success,$msg);
                                }
                            }
                        }
    
                        }else{
                            // blockage du jury encours...                            
                            $success = 0;
                            $appel = $appel + 1;
                            if($appel == 1){
                                $verifTantative = $bdd->prepare('SELECT * FROM jurys_bloques WHERE id_jury = ?'); 
                                $verifTantative->execute(array($id_jury));
                                if($verifTantative->rowCount() == 1){    
                                    $resFetch = $verifTantative->fetch();
                                    $tantat = $resFetch['tantative'];
                                    $etat = ($tantat == 2) ? 1 : 0;
    
                                    if($tantat < 4){
                                        $updateBloqueJury = $bdd->prepare("UPDATE jurys SET etat = ? WHERE id = ?");
                                        $updateBloqueJury->execute(array($etat,$id_jury));
                                        $updateTantative = $bdd->prepare("UPDATE jurys_bloques SET tantative = tantative + ?, etat = ? WHERE id_jury = ?");
                                        $updateTantative->execute(array(1,$etat,$id_jury));
                                    }
                                    $msg = ($tantat == 2) ? "Ce compte vient d'etre bloqué car vous essayez de televerser des fichiers invalides." : "Ce fichier est invalide : ".$tantat." tantative(s) restante(s)";
                                }else{
                                    $tantat = 1;
                                    $isertTantative = $bdd->prepare('INSERT INTO jurys_bloques(id_jury,tantative,etat)VALUES(?,?,?) ');
                                    $isertTantative->execute(array($id_jury,$tantat,1));    
                                    
                                    $msg = "Ce fichier est invalide : 2 tantatives restantes";
                                }
                                responseServer($success,$msg);
                            }
                        }
                    }
                }else{
                    $success = 0;
                    $msg = "L'opération a échoué ! Demandez à votre administrateur de vous debloquer pour pouvoir éffectuer cette operation.";
                    responseServer($success,$msg);
                }
            }else{
                $success = 0;
                $msg = "Vous ne pouvez ni televerser ni supprimer les grilles d'une autre faculté.".$id_jury_source." != ".$id_jury;
                responseServer($success,$msg);
            }
        }

    }

    if($param == 'tantative' && isset($_GET['id_jury'])){
        $success = 0;
        $msg = "Tantative";
        responseServer($success,$msg);
    }
}
?>