<?php
// Projet TraceGPS - services web
// fichier :  services/DemarerEnregistrementParcours.php
// Dernière mise à jour : 7/5/2018 par Jim

// Rôle : ce service permet à un utilisateur de démarrer l'enregistrement d'un parcours
// Le service web doit recevoir 2 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdpSha1 : le mot de passe hashé en sha1
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution (avec l'id de la trace créée)

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/DemarerEnregistrementParcours.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/DemarerEnregistrementParcours.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];

// initialisation
$laTrace = null;

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" )
{	$msg = "Erreur : données incomplètes !";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    {   $msg = "Erreur : authentification incorrecte !";
    }
    else
    {   // récupération de l'id de l'utilisateur
        $idUtilisateur = $dao->getUnUtilisateur($pseudo)->getId();
        
        // créer et enregistrer la trace
        $laTrace = new Trace(0, date('Y-m-d H:i:s', time()), null, false, $idUtilisateur);
        $ok = $dao->creerUneTrace($laTrace);
        // récupération de l'id de la trace
        $idTrace = $laTrace->getId();
        
        $msg = "Trace créée.";
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux XML en sortie
creerFluxXML ($msg, $laTrace);

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;


// création du flux XML en sortie
function creerFluxXML($msg, $laTrace)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();	

	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en UTF-8
	$elt_commentaire = $doc->createComment('Service web DemarerEnregistrementParcours - BTS SIO - Lycée De La Salle - Rennes');
	// place ce commentaire à la racine du document XML
	$doc->appendChild($elt_commentaire);
		
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	
	// place l'élément 'donnees' dans l'élément 'data'
	$elt_donnees = $doc->createElement('donnees');
	$elt_data->appendChild($elt_donnees);
	
	if ($laTrace != null)
	{
	    // crée un élément vide 'trace'
	    $elt_trace = $doc->createElement('trace');
	    // place l'élément 'trace' dans l'élément 'donnees'
	    $elt_donnees->appendChild($elt_trace);
	    
	    // crée les éléments enfants de l'élément 'trace'
    	$elt_idTrace = $doc->createElement('id', $laTrace->getId());
    	$elt_trace->appendChild($elt_idTrace);
    	
    	$elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $laTrace->getDateHeureDebut());
    	$elt_trace->appendChild($elt_dateHeureDebut);
    	
    	$elt_dateHeureFin = $doc->createElement('dateHeureFin', $laTrace->getDateHeureFin());
    	$elt_trace->appendChild($elt_dateHeureFin);
    	
    	$elt_terminee = $doc->createElement('terminee', $laTrace->getTerminee());
    	$elt_trace->appendChild($elt_terminee);
    	
    	$elt_idUtilisateur = $doc->createElement('idUtilisateur', $laTrace->getIdUtilisateur());
    	$elt_trace->appendChild($elt_idUtilisateur);
	}
	
	// Mise en forme finale
	$doc->formatOutput = true;
	
	// renvoie le contenu XML
	echo $doc->saveXML();
	return;
}
?>