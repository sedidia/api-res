<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";

// variables de statistiques
$n_etudiants = 0;
$n_consultation = 0;
$reussite = 0;
// variables de statistiques

// les fonctions
// archivage BAC1
function archivageBac1($success,$msg,$releve,$id_jury){
    $promotion = 1;
    global $bdd, $reussite, $n_etudiants, $n_consultation, $reussite;

    // traitement de l'operation d'archivage
    $noms =  $releve["NOM, POST-NOM et PRENOM"];
    // liste des cours
    $informatique = $releve["informatique"];
    $language_c = $releve["language c"];
    $algorithmique = $releve["algorithmique"];
    $algebre = $releve["algebre"];
    $analyse = $releve["analyse"];
    $logique_mathematique = $releve["logique mathematique"];
    $logique_formelle = $releve["logique formelle"];
    $theorie_des_graphes = $releve["theorie des graphes"];
    $element_electronique = $releve["element electronique"];
    $anglais = $releve["anglais"];

    // liste des cours
            
    $annee = $releve["annee"];
    $jury = $releve["jury"];
    $filiere = $releve["filiere"];

    $type_u = 'etudiant';
    $mdp = $noms.'_'.$type_u.'_'.$annee;

    // EMAIL PAR DEFAUT
    $email = "unilu.archive.dev@gmail.com";
    $mdp = $noms;

    $somme_produits = 0;
    $somme_credits = 0;
    $echecs = 0;
    $credits_valides = 0;

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

            $id_sutilisateur_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
            $id_sutilisateur_exist->execute(array($type_u,$email,$mdp));
            if($id_sutilisateur_exist->rowCount() == 1){
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
                    $inserReleve->execute(array($id_etudiant,$id_jury,$filiere,$promotion,0,0,0,0,0,'R',0,$annee));
                    // enregistrement d'un relevé  

                    $select_1 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_1->execute(array($id_jury,"informatique",$filiere,$promotion));
                    if($select_1->rowCount() == 1){
                        $I_exist = $select_1->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
                        
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$informatique));

                        $somme_produits = ($informatique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($informatique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));

                        $success = 1;
                        $msg = "Succès !";
                    }                            
                    $select_2 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_2->execute(array($id_jury,"language c",$filiere,$promotion));
                    if($select_2->rowCount() == 1){
                        $I_exist = $select_2->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$language_c));

                        $somme_produits = ($language_c*$credit);
                        $somme_credits = $somme_credits + $credit;                                
                        if($language_c < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_3 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_3->execute(array($id_jury,"algorithmique",$filiere,$promotion));
                    if($select_3->rowCount() == 1){
                        $I_exist = $select_3->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$algorithmique));

                        $somme_produits = ($algorithmique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($algorithmique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_4 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_4->execute(array($id_jury,"algebre",$filiere,$promotion));
                    if($select_4->rowCount() == 1){
                        $I_exist = $select_4->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$algebre));

                        $somme_produits = ($algebre*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($algebre < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_5 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_5->execute(array($id_jury,"analyse",$filiere,$promotion));
                    if($select_5->rowCount() == 1){
                        $I_exist = $select_5->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$analyse));

                        $somme_produits = ($analyse*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($analyse < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_6 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_6->execute(array($id_jury,"logique mathematique",$filiere,$promotion));
                    if($select_6->rowCount() == 1){
                        $I_exist = $select_6->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$logique_mathematique));

                        $somme_produits = ($logique_mathematique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($logique_mathematique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_7 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_7->execute(array($id_jury,"logique formelle",$filiere,$promotion));
                    if($select_7->rowCount() == 1){
                        $I_exist = $select_7->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$logique_formelle));

                        $somme_produits = ($logique_formelle*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($logique_formelle < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_8 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_8->execute(array($id_jury,"theorie des graphes",$filiere,$promotion));
                    if($select_8->rowCount() == 1){
                        $I_exist = $select_8->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$theorie_des_graphes));

                        $somme_produits = ($theorie_des_graphes*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($theorie_des_graphes < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_9 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_9->execute(array($id_jury,"element electronique",$filiere,$promotion));
                    if($select_9->rowCount() == 1){
                        $I_exist = $select_9->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$element_electronique));

                        $somme_produits = ($element_electronique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($element_electronique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_10 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_10->execute(array($id_jury,"anglais",$filiere,$promotion));
                    if($select_10->rowCount() == 1){
                        $I_exist = $select_10->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$anglais));

                        $somme_produits = ($anglais*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($anglais < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
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
                    $titre = "Archivage : ".$jury;
                    $contenu = "Vous avez éffectués l'archivage des relevés de bac ".$promotion." ".$filiere." avec succès. Vous pouvez les consulter, les imprimer voir même les diffuser quand vous voudrez et où vous voudrez.";
                    $not_pour = $id_jury;


                    $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?'); 
                    $trait_notifs->execute(array($id_jury,$titre,$contenu,$not_pour,$annee));
                    if($trait_notifs->rowCount() == 0){
                        
                        $creat_not = $bdd->prepare("INSERT INTO notifications(id_author,titre,contenu,type_u,annee)VALUES(?,?,?,?,?) ");
                        $creat_not->execute(array($id_jury,$titre,$contenu,$not_pour,$annee));
                    }
                }
            }
            $success = 1;
            $msg = "Grille téléversée avec succès !";
        }else{
            $success = 1;
            $msg = "Grille existante sur le serveur.";
        }
    }else{
        $success = 0;
        $msg = "En attente d'enregistrement de cours pour cette promotion; par l'administrateur.";
    }                
    // traitement de l'operation d'archivage

    $res = array(
        'success' => $success,
        'msg' => $msg
    );
    header('Content-Type: application/json');
    return json_encode($res);                 
}
// archivage BAC2
function archivageBac2($success,$msg,$releve,$id_jury){
    $promotion = 2;
    global $bdd, $reussite, $n_etudiants, $n_consultation, $reussite;

    // traitement de l'operation d'archivage
    $noms =  $releve["NOM, POST-NOM et PRENOM"];
    // liste des cours
    $informatique = $releve["informatique"];
    $language_c = $releve["language c"];
    $algorithmique = $releve["algorithmique"];
    $algebre = $releve["algebre"];
    $analyse = $releve["analyse"];
    $logique_mathematique = $releve["logique mathematique"];
    $logique_formelle = $releve["logique formelle"];
    $theorie_des_graphes = $releve["theorie des graphes"];
    $element_electronique = $releve["element electronique"];
    $anglais = $releve["anglais"];

    // liste des cours
            
    $annee = $releve["annee"];
    $jury = $releve["jury"];
    $filiere = $releve["filiere"];

    $type_u = 'etudiant';
    $mdp = $noms.'_'.$type_u.'_'.$annee;

    // EMAIL PAR DEFAUT
    $email = "unilu.archive.dev@gmail.com";
    $mdp = $noms;

    $somme_produits = 0;
    $somme_credits = 0;
    $echecs = 0;
    $credits_valides = 0;

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

            $id_sutilisateur_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
            $id_sutilisateur_exist->execute(array($type_u,$email,$mdp));
            if($id_sutilisateur_exist->rowCount() == 1){
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
                    $inserReleve->execute(array($id_etudiant,$id_jury,$filiere,$promotion,0,0,0,0,0,'R',0,$annee));
                    // enregistrement d'un relevé  

                    $select_1 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_1->execute(array($id_jury,"informatique",$filiere,$promotion));
                    if($select_1->rowCount() == 1){
                        $I_exist = $select_1->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
                        
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$informatique));

                        $somme_produits = ($informatique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($informatique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));

                        $success = 1;
                        $msg = "Succès !";
                    }                            
                    $select_2 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_2->execute(array($id_jury,"language c",$filiere,$promotion));
                    if($select_2->rowCount() == 1){
                        $I_exist = $select_2->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$language_c));

                        $somme_produits = ($language_c*$credit);
                        $somme_credits = $somme_credits + $credit;                                
                        if($language_c < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_3 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_3->execute(array($id_jury,"algorithmique",$filiere,$promotion));
                    if($select_3->rowCount() == 1){
                        $I_exist = $select_3->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$algorithmique));

                        $somme_produits = ($algorithmique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($algorithmique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_4 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_4->execute(array($id_jury,"algebre",$filiere,$promotion));
                    if($select_4->rowCount() == 1){
                        $I_exist = $select_4->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$algebre));

                        $somme_produits = ($algebre*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($algebre < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_5 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_5->execute(array($id_jury,"analyse",$filiere,$promotion));
                    if($select_5->rowCount() == 1){
                        $I_exist = $select_5->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$analyse));

                        $somme_produits = ($analyse*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($analyse < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_6 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_6->execute(array($id_jury,"logique mathematique",$filiere,$promotion));
                    if($select_6->rowCount() == 1){
                        $I_exist = $select_6->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$logique_mathematique));

                        $somme_produits = ($logique_mathematique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($logique_mathematique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_7 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_7->execute(array($id_jury,"logique formelle",$filiere,$promotion));
                    if($select_7->rowCount() == 1){
                        $I_exist = $select_7->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$logique_formelle));

                        $somme_produits = ($logique_formelle*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($logique_formelle < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_8 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_8->execute(array($id_jury,"theorie des graphes",$filiere,$promotion));
                    if($select_8->rowCount() == 1){
                        $I_exist = $select_8->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$theorie_des_graphes));

                        $somme_produits = ($theorie_des_graphes*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($theorie_des_graphes < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_9 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_9->execute(array($id_jury,"element electronique",$filiere,$promotion));
                    if($select_9->rowCount() == 1){
                        $I_exist = $select_9->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$element_electronique));

                        $somme_produits = ($element_electronique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($element_electronique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_10 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_10->execute(array($id_jury,"anglais",$filiere,$promotion));
                    if($select_10->rowCount() == 1){
                        $I_exist = $select_10->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$anglais));

                        $somme_produits = ($anglais*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($anglais < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
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
                    $titre = "Archivage : ".$jury;
                    $contenu = "Vous avez éffectués l'archivage des relevés de bac ".$promotion." ".$filiere." avec succès. Vous pouvez les consulter, les imprimer voir même les diffuser quand vous voudrez et où vous voudrez.";
                    $not_pour = $id_jury;


                    $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?'); 
                    $trait_notifs->execute(array($id_jury,$titre,$contenu,$not_pour,$annee));
                    if($trait_notifs->rowCount() == 0){
                        
                        $creat_not = $bdd->prepare("INSERT INTO notifications(id_author,titre,contenu,type_u,annee)VALUES(?,?,?,?,?) ");
                        $creat_not->execute(array($id_jury,$titre,$contenu,$not_pour,$annee));
                    }
                }
            }

            $success = 1;
            $msg = "Grille téléversée avec succès !";
        }else{
            $success = 1;
            $msg = "Grille existante sur le serveur.";
        }
    }else{
        $success = 0;
        $msg = "En attente d'enregistrement de cours pour cette promotion; par l'administrateur.";
    }                
    // traitement de l'operation d'archivage
    

    $res = array(
        'success' => $success,
        'msg' => $msg
    );
    header('Content-Type: application/json');
    return json_encode($res);                 
}
// archivage BAC3
function archivageBac3($success,$msg,$releve,$id_jury){
    $promotion = 3;
    global $bdd, $reussite, $n_etudiants, $n_consultation, $reussite;

    // traitement de l'operation d'archivage
    $noms =  $releve["NOM, POST-NOM et PRENOM"];
    // liste des cours
    $informatique = $releve["informatique"];
    $language_c = $releve["language c"];
    $algorithmique = $releve["algorithmique"];
    $algebre = $releve["algebre"];
    $analyse = $releve["analyse"];
    $logique_mathematique = $releve["logique mathematique"];
    $logique_formelle = $releve["logique formelle"];
    $theorie_des_graphes = $releve["theorie des graphes"];
    $element_electronique = $releve["element electronique"];
    $anglais = $releve["anglais"];

    // liste des cours
            
    $annee = $releve["annee"];
    $jury = $releve["jury"];
    $filiere = $releve["filiere"];

    $type_u = 'etudiant';
    $mdp = $noms.'_'.$type_u.'_'.$annee;

    // EMAIL PAR DEFAUT
    $email = "unilu.archive.dev@gmail.com";
    $mdp = $noms;

    $somme_produits = 0;
    $somme_credits = 0;
    $echecs = 0;
    $credits_valides = 0;

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

            $id_sutilisateur_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
            $id_sutilisateur_exist->execute(array($type_u,$email,$mdp));
            if($id_sutilisateur_exist->rowCount() == 1){
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
                    $inserReleve->execute(array($id_etudiant,$id_jury,$filiere,$promotion,0,0,0,0,0,'R',0,$annee));
                    // enregistrement d'un relevé  

                    $select_1 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_1->execute(array($id_jury,"informatique",$filiere,$promotion));
                    if($select_1->rowCount() == 1){
                        $I_exist = $select_1->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
                        
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$informatique));

                        $somme_produits = ($informatique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($informatique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));

                        $success = 1;
                        $msg = "Succès !";
                    }                            
                    $select_2 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_2->execute(array($id_jury,"language c",$filiere,$promotion));
                    if($select_2->rowCount() == 1){
                        $I_exist = $select_2->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$language_c));

                        $somme_produits = ($language_c*$credit);
                        $somme_credits = $somme_credits + $credit;                                
                        if($language_c < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_3 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_3->execute(array($id_jury,"algorithmique",$filiere,$promotion));
                    if($select_3->rowCount() == 1){
                        $I_exist = $select_3->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$algorithmique));

                        $somme_produits = ($algorithmique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($algorithmique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_4 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_4->execute(array($id_jury,"algebre",$filiere,$promotion));
                    if($select_4->rowCount() == 1){
                        $I_exist = $select_4->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$algebre));

                        $somme_produits = ($algebre*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($algebre < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_5 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_5->execute(array($id_jury,"analyse",$filiere,$promotion));
                    if($select_5->rowCount() == 1){
                        $I_exist = $select_5->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$analyse));

                        $somme_produits = ($analyse*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($analyse < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_6 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_6->execute(array($id_jury,"logique mathematique",$filiere,$promotion));
                    if($select_6->rowCount() == 1){
                        $I_exist = $select_6->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$logique_mathematique));

                        $somme_produits = ($logique_mathematique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($logique_mathematique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_7 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_7->execute(array($id_jury,"logique formelle",$filiere,$promotion));
                    if($select_7->rowCount() == 1){
                        $I_exist = $select_7->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$logique_formelle));

                        $somme_produits = ($logique_formelle*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($logique_formelle < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_8 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_8->execute(array($id_jury,"theorie des graphes",$filiere,$promotion));
                    if($select_8->rowCount() == 1){
                        $I_exist = $select_8->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$theorie_des_graphes));

                        $somme_produits = ($theorie_des_graphes*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($theorie_des_graphes < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_9 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_9->execute(array($id_jury,"element electronique",$filiere,$promotion));
                    if($select_9->rowCount() == 1){
                        $I_exist = $select_9->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$element_electronique));

                        $somme_produits = ($element_electronique*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($element_electronique < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
                    }                            
                    $select_10 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ?'); 
                    $select_10->execute(array($id_jury,"anglais",$filiere,$promotion));
                    if($select_10->rowCount() == 1){
                        $I_exist = $select_10->fetch();
                        $id_cours = $I_exist['id'];
                        $credit = $I_exist['credit'];
            
                        $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                        $inserCotes->execute(array($id_etudiant,$id_cours,$anglais));

                        $somme_produits = ($anglais*$credit);
                        $somme_credits = $somme_credits + $credit;
                        if($anglais < 10){
                            $echecs = 1;
                            $credit = $credit - $credit;
                        }else{
                            $credits_valides = $credit;
                        }
                        
                        $update_mp = $bdd->prepare("UPDATE releves SET somme_produits = somme_produits + ?, somme_credits = ?, credits = credits + ?, credits_valides = credits_valides + ?, echecs = echecs + ? WHERE id_etudiant = ?");
                        $update_mp->execute(array($somme_produits,$somme_credits,$credit,$credits_valides,$echecs,$id_etudiant));
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
                    $titre = "Archivage : ".$jury;
                    $contenu = "Vous avez éffectués l'archivage des relevés de bac ".$promotion." ".$filiere." avec succès. Vous pouvez les consulter, les imprimer voir même les diffuser quand vous voudrez et où vous voudrez.";
                    $not_pour = $id_jury;


                    $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?'); 
                    $trait_notifs->execute(array($id_jury,$titre,$contenu,$not_pour,$annee));
                    if($trait_notifs->rowCount() == 0){
                        
                        $creat_not = $bdd->prepare("INSERT INTO notifications(id_author,titre,contenu,type_u,annee)VALUES(?,?,?,?,?) ");
                        $creat_not->execute(array($id_jury,$titre,$contenu,$not_pour,$annee));
                    }
                }
            }

            $success = 1;
            $msg = "Grille téléversée avec succès !";
        }else{
            $success = 1;
            $msg = "Grille existante sur le serveur.";
        }
    }else{
        $success = 0;
        $msg = "En attente d'enregistrement de cours pour cette promotion; par l'administrateur.";
    }                
    // traitement de l'operation d'archivage
    

    $res = array(
        'success' => $success,
        'msg' => $msg
    );
    header('Content-Type: application/json');
    return json_encode($res);                 
}         
// EN CAS D'ERREUR
function ErreurArchivage($success,$msg){
    $res = array(
        'success' => $success,
        'msg' => $msg
    );
    header('Content-Type: application/json');
    return json_encode($res);                 
}                
// les fonctions

// appel
$appel = 0;
// appel

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // ARCHIVAGE DES RELEVES
    // ARCHIVAGE DES RELEVES
    if($param == 21 && isset($_GET['id_jury'])){
        $id_jury_source = $_GET['id_jury'];

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
                        $promotion = $releve["promotion"];
                        $nom_du_jury_grille = $releve["jury"];

                        if($nom_du_jury == $nom_du_jury_grille){
                            if($promotion == "Bac1" || $promotion == "bac1" || $promotion == "Bac 1" || $promotion == "bac 1" || $promotion == 1){
                                archivageBac1($success,$msg,$releve,$id_jury);
                                $appel = $appel + 1;
                                if($appel == 1){
                                    echo archivageBac1($success,$msg,$releve,$id_jury);
                                }
                            }
                            if($promotion == "Bac2" || $promotion == "bac2" || $promotion == "Bac 2" || $promotion == "bac 2" || $promotion == 2){
                                archivageBac2($success,$msg,$releve,$id_jury);
                                $appel = $appel + 1;
                                if($appel == 1){
                                    echo archivageBac2($success,$msg,$releve,$id_jury);
                                }
                            }
                            if($promotion == "Bac3" || $promotion == "bac3" || $promotion == "Bac 3" || $promotion == "bac 3" || $promotion == 3){
                                archivageBac3($success,$msg,$releve,$id_jury);
                                $appel = $appel + 1;
                                if($appel == 1){
                                    echo archivageBac3($success,$msg,$releve,$id_jury);
                                }
                            }
                        }else{
                            $success = 0;
                            $msg = "Erreur : vous n'etes pas autorisé d'archiver des relevés de la faculté des ".$nom_du_jury_grille;
                            ErreurArchivage($success,$msg);
                            $appel = $appel + 1;
                            if($appel == 1){
                                echo ErreurArchivage($success,$msg);
                            }
                        }

                    }
                }else{
                    $success = 0;
                    $msg = "L'opération a échoué ! Demandez à votre administrateur de vous debloquer pour pouvoir continuer avec l'archivage.";
                }
            }else{
                $success = 0;
                $msg = "Erreur : vous n'etes pas autorisé à archiver les relevés d'une autre faculté.".$id_jury_source." != ".$id_jury;
                ErreurArchivage($success,$msg);
                echo ErreurArchivage($success,$msg);
            }

        }
    }
}
?>