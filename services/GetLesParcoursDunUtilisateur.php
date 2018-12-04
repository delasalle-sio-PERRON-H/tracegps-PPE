<?php
// Projet TraceGPS - services web
// fichier : services/GetLesParcoursDunUtilisateur.php
// Dernière mise à jour : 29/4/2018 par Jim
// Rôle : ce service permet à un utilisateur d'obtenir la liste de ses parcours
// ou la liste des parcours d'un utilisateur qui l'autorise
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur qui demande à consulter
//     mdpSha1 : le mot de passe hashé en sha1 de l'utilisateur qui demande à consulter
//     pseudoConsulte : le pseudo de l'utilisateur dont on veut consulter la liste des parcours
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution ainsi que la liste des parcours
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/GetLesParcoursDunUtilisateur.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&pseudoConsulte=callisto
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/GetLesParcoursDunUtilisateur.php
// connexion du serveur web à la base MySQL
include_once ('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["pseudoConsulte"]) == true) $pseudoConsulte = "";  else $pseudoConsulte = $_REQUEST ["pseudoConsulte"];
// initialisation du nombre de réponses
$nbReponses = 0;
$lesTraces = array();
// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $pseudoConsulte == "" )
{	$msg = "Erreur : données incomplètes !";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
{   $msg = "Erreur : authentification incorrecte !";
}
else
{	// récupération de l'id de l'utilisateur demandeur et de l'utilisateur consulté
    $idDemandeur = $dao->getUnUtilisateur($pseudo)->getId();
    $idUtilisateurConsulte = $dao->getUnUtilisateur($pseudoConsulte)->getId();

    // vérification de l'autorisation
    if ( $idUtilisateurConsulte != $idDemandeur && $dao->autoriseAConsulter($idUtilisateurConsulte, $idDemandeur) == false )
    {   $msg = "Erreur : vous n'êtes pas autorisé par cet utilisateur !";
    }
    else
    {   // récupération de la liste des traces de l'utilisateur à l'aide de la méthode getLesTraces de la classe DAO
        $lesTraces = $dao->getLesTraces($idUtilisateurConsulte);

        // mémorisation du nombre de traces
        $nbReponses = sizeof($lesTraces);

        if ($nbReponses == 0)
            $msg = "Aucune trace pour cet utilisateur !";
        else
            $msg = $nbReponses . " trace(s) pour l'utilisateur " . $idUtilisateurConsulte;
    }
}
}
// ferme la connexion à MySQL
unset($dao);
// création du flux XML en sortie
creerFluxXML ($msg, $lesTraces);
// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

// création du flux XML en sortie
function creerFluxXML($msg, $lesTraces)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();

    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web GetLesParcoursDunUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);

    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    // place l'élément 'reponse' dans l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    // place l'élément 'donnees' dans l'élément 'data'
    $elt_donnees = $doc->createElement('donnees');
    $elt_data->appendChild($elt_donnees);

    // traitement des traces
    if (sizeof($lesTraces) > 0) {
        foreach ($lesTraces as $uneTrace)
        {
            // crée un élément vide 'trace'
            $elt_trace = $doc->createElement('trace');
            // place l'élément 'trace' dans l'élément 'donnees'
            $elt_donnees->appendChild($elt_trace);

            // crée les éléments enfants de l'élément 'trace'
            $elt_id             = $doc->createElement('id', $uneTrace->getId());
            $elt_trace->appendChild($elt_id);

            $elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $uneTrace->getDateHeureDebut());
            $elt_trace->appendChild($elt_dateHeureDebut);

            $elt_terminee       = $doc->createElement('terminee', $uneTrace->getTerminee());
            $elt_trace->appendChild($elt_terminee);

            if ($uneTrace->getTerminee() == true)
            {
                $elt_dateHeureFin = $doc->createElement('dateHeureFin', $uneTrace->getDateHeureFin());
                $elt_trace->appendChild($elt_dateHeureFin);
            }

            $elt_distance = $doc->createElement('distance', number_format($uneTrace->getDistanceTotale(), 1));
            $elt_trace->appendChild($elt_distance);

            $elt_idUtilisateur = $doc->createElement('idUtilisateur', $uneTrace->getIdUtilisateur());
            $elt_trace->appendChild($elt_idUtilisateur);
        }
    }

    // Mise en forme finale
    $doc->formatOutput = true;

    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}
?>