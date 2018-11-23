<?php
// Projet TraceGPS - services web
// fichier :  services/ChangerDeMdp.php
// Dernière mise à jour : 29/9/2018 par Jim

// Rôle : ce service permet à un utilisateur de récupérer un nouveau Mot de Passe
// Le service web doit recevoir 5 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/DemanderMdp.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&nouveauMdp=123&confirmationMdp=123&lang=xml

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/Mdp.php

// connexion du serveur web à la base MySQL
include_once('../modele/DAO/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if (empty ($_REQUEST ["pseudo"]) == true) $pseudo = ""; else $pseudo = $_REQUEST ["pseudo"];
if (empty ($_REQUEST ["lang"]) == true) $lang = ""; else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// Contrôle de la présence des paramètres
if ($pseudo == "") {
    $msg = "Erreur : données incomplètes.";
} else {
    if ($dao->existePseudoUtilisateur($pseudo)) {
        $mdp = Outils::creerMdp();
        $dao->modifierMdpUtilisateur($pseudo, Outils::creerMdp());
        $user = $dao->getUnUtilisateur($pseudo);
        $msg = "nouveau mot de passe : $mdp";
        Outils::envoyerMail($user->getAdrMail(), 'nouveau mdp', $msg, $ADR_MAIL_EMETTEUR);
        $msg = 'Mot de passe modifier, un mail à été envoyer à votre adresse mail.';
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
} else {
    creerFluxJSON($msg);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;


// création du flux XML en sortie
function creerFluxXML($msg)
{
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
            <reponse>Erreur : authentification incorrecte.</reponse>
        </data>
     */

    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();

    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);

    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    // place l'élément 'reponse' juste après l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    // Mise en forme finale
    $doc->formatOutput = true;

    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}

// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    /* Exemple de code JSON
         {
             "data": {
                "reponse": "Erreur : authentification incorrecte."
             }
         }
     */

    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];

    // construction de la racine
    $elt_racine = ["data" => $elt_data];

    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}

?>
