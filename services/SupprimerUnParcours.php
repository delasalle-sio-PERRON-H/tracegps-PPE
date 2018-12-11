<?php
// Projet TraceGPS - services web
// fichier : services/SupprimerUnParcours.php
// Dernière mise à jour : 25/1/2018 par Jim

// Rôle : ce service permet à un utilisateur de supprimer un de ses parcours 
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur qui demande à supprimer
//     mdpSha1 : le mot de passe hashé en sha1 de l'utilisateur qui demande à supprimer
//     idTrace : l'id de la trace à supprimer
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/SupprimerUnParcours.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&idTrace=25

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/SupprimerUnParcours.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["idTrace"]) == true)  $idTrace = "";  else   $idTrace = $_REQUEST ["idTrace"];

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "" )
{	$msg = "Erreur : données incomplètes !";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    {   $msg = "Erreur : authentification incorrecte !";
    }
	else 
	{	// contrôle d'existence de idTrace
	    $laTrace = $dao->getUneTrace($idTrace);
	    if ($laTrace == null)
	    {  $msg = "Erreur : parcours inexistant !";
	    }
	    else
	    {   // récupération de l'id de l'utilisateur demandeur et du propriétaire du parcours
    	    $idDemandeur = $dao->getUnUtilisateur($pseudo)->getId();
    	    $idProprietaire = $laTrace->getIdUtilisateur();
    	    
    	    if ( $idDemandeur != $idProprietaire )
    	    {   $msg = "Erreur : vous n'êtes pas le propriétaire du parcours !";
    	    }
            else
            {   // suppression du parcours
                $ok = $dao->supprimerUneTrace($idTrace);
                if ( ! $ok ) {
                    $msg = "Erreur : problème lors de la suppression du parcours !";
                }
                else {
                    $msg = "Parcours supprimé.";
                }
            }
	    }
	}
}
// ferme la connexion à MySQL
unset($dao);

// création du flux XML en sortie
creerFluxXML ($msg);

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
 


// création du flux XML en sortie
function creerFluxXML($msg)
{	// crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();
	
	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en UTF-8
	$elt_commentaire = $doc->createComment('Service web SupprimerUnParcours - BTS SIO - Lycée De La Salle - Rennes');
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

	// Mise en forme finale
	$doc->formatOutput = true;
	
	// renvoie le contenu XML
	echo $doc->saveXML();
	return;
}
?>
