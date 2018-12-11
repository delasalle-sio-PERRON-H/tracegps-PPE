<?php
// Projet TraceGPS - services web
// fichier : services/GetLesUtilisateursQueJautorise.php
// Dernière mise à jour : 25/1/2018 par Jim

// Rôle : ce service permet à un utilisateur d'obtenir la liste des utilisateurs qu'il autorise à consulter ses parcours
// Le service web doit recevoir 2 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdpSha1 : le mot de passe hashé en sha1
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution ainsi que la liste des utilisateurs

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/GetLesUtilisateursQueJautorise.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/GetLesUtilisateursQueJautorise.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];

// initialisation du nombre de réponses
$nbReponses = 0;
$lesUtilisateurs = array();

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" )
{	$msg = "Erreur : données incomplètes !";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
		$msg = "Erreur : authentification incorrecte !";
	else 
	{	// récupération de l'id de l'utilisateur
	    $idUtilisateurConsulte = $dao->getUnUtilisateur($pseudo)->getId();
	    
	    // récupération de la liste des utilisateurs autorises à l'aide de la méthode getLesUtilisateursAutorises de la classe DAO
	    $lesUtilisateurs = $dao->getLesUtilisateursAutorises($idUtilisateurConsulte);
	    
	    // mémorisation du nombre d'utilisateurs autorisés
	    $nbReponses = sizeof($lesUtilisateurs);
	
		if ($nbReponses == 0)
			$msg = "Aucune autorisation accordée par " . $pseudo . " !";
		else
			$msg = $nbReponses . " autorisation(s) accordée(s) par " . $pseudo . ".";
	}
}
// ferme la connexion à MySQL
unset($dao);

// création du flux XML en sortie
creerFluxXML ($msg, $lesUtilisateurs);

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
 


// création du flux XML en sortie
function creerFluxXML($msg, $lesUtilisateurs)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();
	
	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en UTF-8
	$elt_commentaire = $doc->createComment('Service web GetLesUtilisateursQueJautorise - BTS SIO - Lycée De La Salle - Rennes');
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
	
	// traitement des utilisateurs
	if (sizeof($lesUtilisateurs) > 0) {
	    foreach ($lesUtilisateurs as $unUtilisateur)
	    {
	        // crée un élément vide 'reservation'
	        $elt_utilisateur = $doc->createElement('utilisateur');
	        // place l'élément 'utilisateur' dans l'élément 'donnees'
	        $elt_donnees->appendChild($elt_utilisateur);
	        
	        // crée les éléments enfants de l'élément 'utilisateur'
	        $elt_id         = $doc->createElement('id', $unUtilisateur->getId());
	        $elt_utilisateur->appendChild($elt_id);
	        
	        $elt_pseudo     = $doc->createElement('pseudo', $unUtilisateur->getPseudo());
	        $elt_utilisateur->appendChild($elt_pseudo);
	        
	        $elt_adrMail    = $doc->createElement('adrMail', $unUtilisateur->getAdrMail());
	        $elt_utilisateur->appendChild($elt_adrMail);
	        
	        $elt_numTel     = $doc->createElement('numTel', $unUtilisateur->getNumTel());
	        $elt_utilisateur->appendChild($elt_numTel);
	        
	        $elt_niveau     = $doc->createElement('niveau', $unUtilisateur->getNiveau());
	        $elt_utilisateur->appendChild($elt_niveau);
	        
	        $elt_dateCreation = $doc->createElement('dateCreation', $unUtilisateur->getDateCreation());
	        $elt_utilisateur->appendChild($elt_dateCreation);
	        
	        $elt_nbTraces   = $doc->createElement('nbTraces', $unUtilisateur->getNbTraces());
	        $elt_utilisateur->appendChild($elt_nbTraces);
	        
	        if ($unUtilisateur->getNbTraces() > 0)
	        {   $elt_dateDerniereTrace = $doc->createElement('dateDerniereTrace', $unUtilisateur->getDateDerniereTrace());
	        $elt_utilisateur->appendChild($elt_dateDerniereTrace);
	        }
	    }
	}
	
	// Mise en forme finale
	$doc->formatOutput = true;
	
	// renvoie le contenu XML
	echo $doc->saveXML();
	return;
}
?>
