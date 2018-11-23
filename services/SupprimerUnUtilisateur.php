<?php
// Projet TraceGPS - services web
// fichier : services/SupprimerUnUtilisateur.php
// Dernière mise à jour : 15/11/2018 par Jim

// Rôle : ce service permet à un administrateur de supprimer un utilisateur (à condition qu'il ne possède aucune trace enregistrée)
// Le service web doit recevoir 4 paramètres :
//     pseudo : le pseudo de l'administrateur
//     mdpSha1 : le mot de passe hashé en sha1 de l'administrateur
//     pseudoAsupprimer : le pseudo de l'utilisateur à supprimer
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/SupprimerUnUtilisateur.php?pseudo=admin&mdpSha1=ff9fff929a1292db1c00e3142139b22ee4925177&pseudoAsupprimer=oxygen&lang=xml

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/SupprimerUnUtilisateur.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["pseudoAsupprimer"]) == true)  $pseudoAsupprimer = "";  else   $pseudoAsupprimer = $_REQUEST ["pseudoAsupprimer"];
if ( empty ($_REQUEST ["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $pseudoAsupprimer == "" )
{	$msg = "Erreur : données incomplètes.";
}
else
{	// il faut être administrateur pour supprimer un utilisateur
    if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) != 2 )
    {   $msg = "Erreur : authentification incorrecte.";
    }
	else 
	{	// contrôle d'existence de pseudoAsupprimer
	    $unUtilisateur = $dao->getUnUtilisateur($pseudoAsupprimer);
	    if ($unUtilisateur == null)
	    {  $msg = "Erreur : pseudo utilisateur inexistant.";
	    }
	    else
	    {   // si cet utilisateur possède encore des traces, sa suppression est refusée
	        if ( $unUtilisateur->getNbTraces() > 0 ) {
	            $msg = "Erreur : suppression impossible ; cet utilisateur possède encore des traces.";
	        }
	        else {
	            // suppression de l'utilisateur dans la BDD
	            $ok = $dao->supprimerUnUtilisateur($pseudoAsupprimer);
	            if ( ! $ok ) {
                    $msg = "Erreur : problème lors de la suppression de l'utilisateur.";
                }
                else {
                    // envoi d'un mail de confirmation de la suppression
                    $adrMail = $unUtilisateur->getAdrMail();
                    $sujet = "Suppression de votre compte dans le système TraceGPS";
                    $contenuMail = "Bonjour " . $pseudoAsupprimer . "\n\nL'administrateur du service TraceGPS vient de supprimer votre compte utilisateur.";
                    
                    $ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
                    if ( ! $ok ) {
                        // si l'envoi de mail a échoué, réaffichage de la vue avec un message explicatif
                        $msg = "Suppression effectuée ; l'envoi du courriel à l'utilisateur a rencontré un problème.";
                    }
                    else {
                        // tout a fonctionné
                        $msg = "Suppression effectuée ; un courriel va être envoyé à l'utilisateur.";
                    }
                }
            }
	    }
	}
}
// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
}
else {
    creerFluxJSON($msg);
}

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
	$elt_commentaire = $doc->createComment('Service web SupprimerUnUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
	// place ce commentaire à la racine du document XML
	$doc->appendChild($elt_commentaire);
	
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	
	// place l'élément 'reponse' dans l'élément 'data'
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
