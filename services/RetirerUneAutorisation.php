<?php
// Projet TraceGPS - services web
// fichier :  services/ValiderDemandeAutorisation.php
// Dernière mise à jour : 8/11/2018 par Jim

// Rôle : ce service web permet à un utilisateur destinataire d'accepter ou de rejeter une demande d'autorisation provenant d'un utilisateur demandeur
// il envoie un mail au demandeur avec la décision de l'utilisateur destinataire

// Le service web doit être appelé avec 4 paramètres obligatoires dont les noms sont volontairement non significatifs :
//      pseudo : le pseudo de l'utilisateur qui retire l'autorisation
//      mdpSha1 : le mot de passe hashé en sha1 de l'utilisateur qui retire l&#39;autorisation
//      pseudoARetirer : le pseudo de l'utilisateur à qui on veut retirer l&#39;autorisation
//      texteMessage : le texte d'un message accompagnant la suppression
//      lang : le langage utilisé pour le flux de données (xml ou json)


// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ValiderDemandeAutorisation.php?a=13e3668bbee30b004380052b086457b014504b3e&b=oxygen&c=europa&d=1

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ValiderDemandeAutorisation.php

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if (empty ($_REQUEST ["mdpSha1"]) == true) $mdpSha1 = ""; else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if (empty ($_REQUEST ["pseudo"]) == true) $pseudoAutorisant = ""; else   $pseudoAutorisant = $_REQUEST ["pseudo"];
if (empty ($_REQUEST ["pseudoARetirer"]) == true) $pseudoARetirer = ""; else   $pseudoARetirer = $_REQUEST ["pseudoARetirer"];
if (empty ($_REQUEST ["textMessage"]) == true) $textMessage = ""; else   $textMessage = $_REQUEST ["textMessage"];

// Contrôle de la présence et de la correction des paramètres
if ($mdpSha1 == "" || $pseudoAutorisant == "" || $pseudoARetirer == "" || $textMessage = "") {
    $message = "Erreur : données incomplètes ou incorrectes.";
} else {    // connexion du serveur web à la base MySQL
    include_once('../modele/DAO/DAO.class.php');
    $dao = new DAO();

// test de l'authentification de l'utilisateur
// la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
    $niveauConnexion = $dao->getNiveauConnexion($pseudoAutorisant, $mdpSha1);

    if ($niveauConnexion == 0) {
        $message = "Erreur : authentification incorrecte.";
    } else {
        $utilisateurRetirer = $dao->getUnUtilisateur($pseudoARetirer);
        $utilisateurAutorisant = $dao->getUnUtilisateur($pseudoAutorisant);
        $idAutorisant = $utilisateurAutorisant->getId();
        $idRetirer = $utilisateurRetirer->getId();
        $adrMailRetirer = $utilisateurRetirer->getAdrMail();

        if (!$dao->autoriseAConsulter($idAutorisant, $idRetirer)) {
            $message = "Erreur : autorisation non accordée.";
        } else {// enregistrement de l'autorisation dans la bdd
            $ok = $dao->supprimerUneAutorisation($idAutorisant, $idRetirer);
            if (!$ok) {
                $message = "Erreur : problème lors de l'enregistrement.";
            } else {   // envoi d'un mail d'alerte à l'intéressé
                $sujetMail = "Autorisation révoquée";
                $contenuMail = "Cher ou chère " . $pseudoARetirer . "\n\n";
                $contenuMail .= "l'utilisateur " . $pseudoAutorisant . " vient de révoquer votre autorisation de consulter ses parcours.\n";
                $contenuMail .= "Cordialement.\n";
                $contenuMail .= "L'administrateur du système TraceGPS";
                $ok = Outils::envoyerMail($adrMailRetirer, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
                if (!$ok)
                    $message = "Erreur : l'envoi du courriel au demandeur a rencontré un problème.";
                else
                    $message = "Supression confirmée.";
            }
        }
    }
    unset($dao);   // ferme la connexion à MySQL
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Validation TraceGPS</title>
    <style type="text/css">body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: small;
        }</style>
</head>
<body>
<p><?php echo $message; ?></p>
<p><a href="Javascript:window.close();">Fermer</a></p>
</body>
</html>