<?php
require_once("bdd.php");
$success = 0;
$msg = "Erreur survenue #";

// REQUETES par la methode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);
    // Traitement des données reçues
    $param = $_GET['param'];

    // ARCHIVAGE DES RELEVES
    if($param == 21){

        foreach ($dataReques as $releve) {
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
            $promotion = $releve["promotion"];
        
            $annee = $releve["annee"];
            $jury = $releve["jury"];
            $filiere = $releve["filiere"];
    
            $type_u = 'etudiant';
            $mdp = $noms.'_'.$type_u.'_'.$annee;

            // EMAIL PAR DEFAUT
            $email = $noms."@gmail.com";
        
            $mdp = $noms;

            // annee courante   
            $student_exist = $bdd->prepare('SELECT * FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?'); 
            $student_exist->execute(array($noms,$promotion,$annee,$filiere));
            if($student_exist->rowCount() == 0){
                // annee passée
                $student_exist2 = $bdd->prepare('SELECT * FROM etudiants WHERE noms = ? AND promotion = ? AND annee = ? AND filiere = ?'); 
                $student_exist2->execute(array($noms,$promotion - 1,$annee - 1,$filiere));
                if($student_exist2->rowCount() == 0){
                    $inserUtilisateur = $bdd->prepare('INSERT INTO utilisateurs(type_u,email,mdp,mdp2)VALUES(?,?,?,?) ');
                    $inserUtilisateur->execute(array($type_u,$email,$mdp,$mdp));                  
                }


                $id_sutilisateur_exist = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
                $id_sutilisateur_exist->execute(array($type_u,$email,$mdp));
                if($id_sutilisateur_exist->rowCount() == 1){
                    $utilisateur_exist = $id_sutilisateur_exist->fetch();
                    $id_utilisateur = $utilisateur_exist['id'];
        
                    $id_jury_exist = $bdd->prepare('SELECT * FROM jurys WHERE nom = ?'); 
                    $id_jury_exist->execute(array($jury));
                    if($id_jury_exist->rowCount() == 1){
                        $jury_exist = $id_jury_exist->fetch();
                        $id_jury = $jury_exist['id'];
        
                        $inserEtudiant = $bdd->prepare('INSERT INTO etudiants(id_utilisateur,id_jury,noms,filiere,promotion,annee)VALUES(?,?,?,?,?,?) ');
                        $inserEtudiant->execute(array($id_utilisateur,$id_jury,$noms,$filiere,$promotion,$annee));

                        $fetch_id_etudiant = $bdd->prepare('SELECT * FROM etudiants WHERE id_utilisateur = ? AND id_jury = ? AND noms = ? AND filiere = ? AND promotion = ? AND annee = ?'); 
                        $fetch_id_etudiant->execute(array($id_utilisateur,$id_jury,$noms,$filiere,$promotion,$annee));
                        if($fetch_id_etudiant->rowCount() == 0){
                            $id_etudiant = $fetch_id_etudiant['id'];


                            // enregistrement d'un relevé
                            $inserReleve = $bdd->prepare('INSERT INTO releves(id_etudiant,id_jury,diffuse)VALUES(?,?,?) ');
                            $inserReleve->execute(array($id_etudiant,$id_jury,0));
                            // enregistrement d'un relevé
                             
                            $select_1 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_1->execute(array($id_jury,"informatique",$filiere,$promotion));
                            if($select_1->rowCount() == 1){
                                $I_exist = $select_1->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$informatique));
                                $success = 1;
                                $msg = "Succès !";
                            }                            
                            $select_2 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_2->execute(array($id_jury,"language c",$filiere,$promotion));
                            if($select_2->rowCount() == 1){
                                $I_exist = $select_2->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$language_c));
                            }                            
                            $select_3 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_3->execute(array($id_jury,"algorithmique",$filiere,$promotion));
                            if($select_3->rowCount() == 1){
                                $I_exist = $select_3->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$algorithmique));
                            }                            
                            $select_4 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_4->execute(array($id_jury,"algebre",$filiere,$promotion));
                            if($select_4->rowCount() == 1){
                                $I_exist = $select_4->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$algebre));
                            }                            
                            $select_5 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_5->execute(array($id_jury,"analyse",$filiere,$promotion));
                            if($select_5->rowCount() == 1){
                                $I_exist = $select_5->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$analyse));
                            }                            
                            $select_6 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_6->execute(array($id_jury,"logique mathematique",$filiere,$promotion));
                            if($select_6->rowCount() == 1){
                                $I_exist = $select_6->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$logique_mathematique));
                            }                            
                            $select_7 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_7->execute(array($id_jury,"logique formelle",$filiere,$promotion));
                            if($select_7->rowCount() == 1){
                                $I_exist = $select_7->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$logique_formelle));
                            }                            
                            $select_8 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_8->execute(array($id_jury,"theorie des graphes",$filiere,$promotion));
                            if($select_8->rowCount() == 1){
                                $I_exist = $select_8->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$theorie_des_graphes));
                            }                            
                            $select_9 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_9->execute(array($id_jury,"element electronique",$filiere,$promotion));
                            if($select_9->rowCount() == 1){
                                $I_exist = $select_9->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$element_electronique));
                            }                            
                            $select_10 = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere AND promotion = ?'); 
                            $select_10->execute(array($id_jury,"anglais",$filiere,$promotion));
                            if($select_10->rowCount() == 1){
                                $I_exist = $select_10->fetch();
                                $id_cours = $I_exist['id'];
                    
                                $inserCotes = $bdd->prepare('INSERT INTO cotes(id_etudiant,id_cours,cote)VALUES(?,?,?) ');
                                $inserCotes->execute(array($id_etudiant,$id_cours,$anglais));
                            }                            
                        }
                        
                    }                    
                }            

            }else{
                $success = 0;
                $msg = "Erreur survenue lors de l'archivage : soit certains releve ont deja ete archive, soit cest un probleme lie au serveur.";
            }
        }
    }
    
    // ENREGISTREMENT DES JURYS & COURS > INFOS : PARAMETRAGE
    if($param == 11){

        foreach ($dataReques as $cours) {
            $nom =  $cours["nom"];
            $faculte =  $cours["faculte"];
            $promotion =  $cours["promotion"];
            $credit =  $cours["credit"];
            $sur_cote =  $cours["sur_cote"];
            $unite =  $cours["unite"];
            $annee =  $cours["annee"];
            $filiere =  $cours["filiere"];
            

            // insertion de données   
            $jury_exist = $bdd->prepare('SELECT * FROM jurys WHERE nom = ?'); 
            $jury_exist->execute(array($faculte));
            if($jury_exist->rowCount() == 1){
                $jury_troune = $jury_exist->fetch();
                $id_jury = $jury_troune['id'];
                
                $id_jury_exist = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND filiere = ? AND promotion = ? AND credit = ? AND sur_cote = ? AND unite = ?'); 
                $id_jury_exist->execute(array($id_jury,$nom,$filiere,$promotion,$credit,$sur_cote,$unite));
                if($id_jury_exist->rowCount() == 0){

                    $inserCours = $bdd->prepare('INSERT INTO cours(id_jury,nom,filiere,promotion,credit,sur_cote,unite)VALUES(?,?,?,?,?,?,?) ');
                    $inserCours->execute(array($id_jury,$nom,$filiere,$promotion,$credit,$sur_cote,$unite));

                }

                $success = 1;
                $msg = "Enregistrés avec succès";                    
            }else{
                $inserCours = $bdd->prepare('INSERT INTO utilisateurs(type_u,email,mdp,mdp2)VALUES(?,?,?,?) ');
                $inserCours->execute(array("jury",$faculte."@gmail.com",$faculte,$faculte));
                
                $rec_user = $bdd->prepare('SELECT * FROM utilisateurs WHERE type_u = ? AND email = ? AND mdp = ?'); 
                $rec_user->execute(array("jury",$faculte."@gmail.com",$faculte));
                if($rec_user->rowCount() == 1){
                    $user_troune = $rec_user->fetch();
                    $id_user = $user_troune['id'];
                    $etat = 1;
                    
                    $inserJury = $bdd->prepare('INSERT INTO jurys(id_utilisateur,nom,etat)VALUES(?,?,?) ');
                    $inserJury->execute(array($id_user,$faculte,$etat));

                    $jury2_exist = $bdd->prepare('SELECT * FROM jurys WHERE id_utilisateur = ? AND nom = ?'); 
                    $jury2_exist->execute(array($id_user,$faculte));
                    if($jury2_exist->rowCount() == 1){
                        $jury2_troune = $jury2_exist->fetch();
                        $id_jury2 = $jury2_troune['id'];


                        $id_jury_exist = $bdd->prepare('SELECT * FROM cours WHERE id_jury = ? AND nom = ? AND promotion = ? AND credit = ? AND sur_cote = ? AND unite = ?'); 
                        $id_jury_exist->execute(array($id_jury2,$nom,$promotion,$credit,$sur_cote,$unite));
                        if($id_jury_exist->rowCount() == 0){

                            $inserCours = $bdd->prepare('INSERT INTO cours(id_jury,nom,filiere,promotion,credit,sur_cote,unite)VALUES(?,?,?,?,?,?,?) ');
                            $inserCours->execute(array($id_jury2,$nom,$filiere,$promotion,$credit,$sur_cote,$unite));                            
                        }
                    }

                }

                $success = 1;
                $msg = "Enregistrés avec succès";
                
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
        }
    }

    // AUTHENTIFICATION
    if($param == 3){
        if(!empty($_GET['email']) AND !empty($_GET['password'])){
            $password = $_GET['password'];
            $email = $_GET['email'];
            
            $verif_user = $bdd->prepare('SELECT * FROM utilisateurs WHERE mdp = ?');
            $verif_user->execute(array($password));
            if($verif_user->rowCount() == 1){
                $user_exist = $verif_user->fetch();

                $id_utilisateur = $user_exist['id'];
                $type_user = $user_exist['type_u'];
                $email_user = $user_exist['email'];
                $n_connect = $user_exist['n_connect'];
                
                if($type_user == "jury"){
                    if($email == $email_user){

                        $recJury = $bdd->prepare('SELECT * FROM jurys WHERE id_utilisateur = ?');
                        $recJury->execute(array($id_utilisateur));
                        if($recJury->rowCount() == 1){
                            $j_exist = $recJury->fetch();
                            $nom_user = $j_exist['nom'];
                            $id = $j_exist['id'];
    
                            $success = 1;
                            $msg = "Succès";
                        }else{                            
                            $success = 0;
                            $msg = "Votre compte n'avais pas été bien créé !";
                        }  
                    }else{
                        $success = 0;
                        $msg = "L'Email fourni est incorrect !";
                    }
                }
                if($type_user == "etudiant"){
                    $recEtudiant = $bdd->prepare('SELECT * FROM etudiants WHERE id_utilisateur = ?');
                    $recEtudiant->execute(array($id_utilisateur));
                    if($recEtudiant->rowCount() >= 1){
                        $e_exist = $recEtudiant->fetch();
                        $nom_user = $e_exist['noms'];
                        $id = $e_exist['id'];
                        

                        if($user_exist == 0){
                            $update_jr = $bdd->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
                            $update_jr->execute(array($email,$id_utilisateur));
                        }
    
                        $success = 1;
                        $msg = "Succès";
                    }else{                        
                        $success = 0;
                        $msg = "Votre compte n'avais pas été bien créé !";
                    }
                }
                if($type_user == "admin"){
                    if($email == $email_user){
                        
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
                        $msg = "L'Email fourni est incorrect !";
                    }
                }

            }else{
                $success = 0;
                $msg = "Email ou Mot de passe incorrect !";
            }
        }else{
            $success = 0;
            $msg = "Tous les champs sont obligatoire !";
        }
    }
}

// REQUETES par la methode GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' AND isset($_GET['param'])) {
    // Récupérer les paramètres de l'URL
    $param = $_GET['param'];

    // CONSULTATION DES RELEVES : JURY/ETUDIANT
    if($param == 22){
        if(!empty($_GET['promotion']) AND !empty($_GET['annee'])){
            $promotion = $_GET['promotion'];
            $annee = $_GET['annee'];
            
            // Traiter les données reçues
            $rec_releves = $bdd->prepare("SELECT etudiants.id, jurys.id as id_jury, jurys.nom as nom_jury, etudiants.noms, etudiants.promotion, etudiants.annee FROM jurys, etudiants WHERE jurys.id = etudiants.id_jury AND etudiants.promotion = ? AND etudiants.annee = ?");
            $rec_releves->execute(array($promotion,$annee));
            $data = $rec_releves->fetchAll(PDO::FETCH_ASSOC);
            $json = json_encode($data);
            echo $json;
        }
    }
    // CONSULTATION DES RELEVES : JURY/ETUDIANT
    if($param == 221 AND isset($_GET['id_releve'])){
        $id_releve = $_GET['id_releve'];
        $rec_releves = $bdd->prepare("SELECT etudiants.id, jurys.id as id_jury, jurys.nom as nom_jury, etudiants.noms, etudiants.promotion, etudiants.annee, cours.unite, cours.nom as nom_cours, cours.credit, cours.sur_cote, cotes.cote FROM releves, jurys, cours, etudiants, cotes WHERE releves.id_jury = jurys.id AND releves.id_etudiant = etudiants.id AND jurys.id = etudiants.id_jury AND jurys.id = cours.id_jury AND cours.id = cotes.id_cours AND etudiants.id = cotes.id_etudiant AND etudiants.id = ?");
        $rec_releves->execute(array($id_releve));
        $data = $rec_releves->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($data);
        echo $json;
    }
}

// table de cles
// 0    = échec
// 1    = succès
// 11   = enregistrement de cours           : Admin
// 12   = enregistre jurys                  : Admin
// 13   = consultation jurys                : Admin
// 131   = consultation jury                 : Admin
// 21   = archivage relevés                 : jury
// 22   = consultation releves              : jury
// 221  = consultation releves              : jury
// 22/3 = consultation resultat             : etudiant
// 231  = consultation resultats publiés    : jury
// 3    = connexion                         : utilisateur
// 30    = consultation de notification     : utilisateur


// Envoi d'une réponse JSON
header('Content-Type: application/json');
if(isset($type_user)){
    $res = [
        "id" => $id,
        "id_utilisateur" => $_utilisateur,
        "type_user" => $type_user,
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

