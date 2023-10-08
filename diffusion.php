<?php
$bdd = new PDO('mysql:host=localhost;dbname=unilu_archive;charset=utf8;', 'root', '');
$success = 0;
$msg = "Erreur survenue #";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['param'])) {
    $dataReques = json_decode(file_get_contents('php://input'), true);

    $raw_data = file_get_contents('php://input');
    $myData = json_decode($raw_data);

    // Traitement des données reçues
    $param = $_GET['param'];

    // diffusion
    if ($param == 21 && isset($_GET['diffuser']) && isset($_GET['nom_user']) && !empty($_GET['filiere']) && !empty($_GET['promotion']) && !empty($_GET['annee'])) {
        $id_jury = $_GET['diffuser'];
        $nom_user = $_GET['nom_user'];
        $filiere = $_GET['filiere'];
        $promotion = $_GET['promotion'];
        $annee = $_GET['annee'];

        $diffusion_releve = $bdd->prepare('SELECT * FROM releves WHERE id_jury = ? AND filiere = ? AND promotion = ? AND diffuse = ? AND annee = ?');
        $diffusion_releve->execute(array($id_jury, $filiere, $promotion, 0, $annee));

        if ($diffusion_releve->rowCount() > 0) {
            while ($row = $diffusion_releve->fetch(PDO::FETCH_ASSOC)) {
                $select_and_update = $bdd->prepare("UPDATE releves SET diffuse = ? WHERE id_jury = ? AND filiere = ? AND promotion = ? AND annee = ?");
                $select_and_update->execute(array(1, $id_jury, $filiere, $promotion, $annee));
                $success = 1;
                $msg = "Succès !";
            }
        } else {
            $diffusion_releve = $bdd->prepare('SELECT * FROM releves WHERE id_jury = ? AND filiere = ? AND promotion = ? AND diffuse = ? AND annee = ?');
            $diffusion_releve->execute(array($id_jury, $filiere, $promotion, 1, $annee));

            if ($diffusion_releve->rowCount() > 0) {
                $success = 1;
                $msg = "La faculté des " . $nom_user . " de " . $annee . " a déjà diffusé les résultats de cette promotion";
            } else {
                $success = 0;
                $msg = "Il se trouve que vous n'avez pas encore archivé les relevés de cette promotion.";
            }
        }

        // ajout de la notification de la diffusion
        // creation des notifications
        $titre = "Resultats " . $nom_user;
        $contenu = "Les résultats de Bac" . $promotion . " " . $filiere . "/faculté des " . $nom_user . " sont disponibles pour l'année " . $annee . ". Dès lors, vous pouvez les consulter, télécharger vos relevés et les partager avec vos proches si vous êtes étudiant.";
        $not_pour = 'all';

        $trait_notifs = $bdd->prepare('SELECT * FROM notifications WHERE id_author = ? AND titre = ? AND contenu = ? AND type_u = ? AND annee = ?');
        $trait_notifs->execute(array($id_jury, $titre, $contenu, $not_pour, $annee));

        if ($trait_notifs->rowCount() == 0) {
            $creat_not = $bdd->prepare("INSERT INTO notifications(id_author, titre, contenu, type_u, annee) VALUES (?, ?, ?, ?, ?)");
            $creat_not->execute(array($id_jury, $titre, $contenu, $not_pour, $annee));
        }
        // ajout de la notification de la diffusion
    }
}

$res = [
    "success" => $success,
    "msg" => $msg
];
echo json_encode($res);
?>