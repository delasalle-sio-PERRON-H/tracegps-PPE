<?php
// Projet TraceGPS - services web
// fichier : services/RetirerUneAutorisation.php
// Dernière mise à jour : 25/4/2018 par Jim

// Rôle : ce service permet à un utilisateur de supprimer une autorisation qu'il avait accordée à un autre utilisateur
// Le service web doit recevoir 4 paramètres :
//     pseudo : le pseudo de l'utilisateur qui retire l'autorisation
//     mdpSha1 : le mot de passe hashé en sha1 de l'utilisateur qui retire l'autorisation
//     pseudoARetirer : le pseudo de l'utilisateur à qui on veut retirer l'autorisation
//     texteMessage : le texte d'un message accompagnant la suppression
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/RetirerUneAutorisation.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&pseudoARetirer=oxygen&texteMessage=C'est fini

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/RetirerUneAutorisation.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["pseudoARetirer"]) == true)  $pseudoDestinataire = "";  else $pseudoDestinataire = $_REQUEST ["pseudoARetirer"];
if ( empty ($_REQUEST ["texteMessage"]) == true) $texteMessage = "";  else $texteMessage = $_REQUEST ["texteMessage"];

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $pseudoDestinataire == "" )
{	$msg = "Erreur : données incomplètes !";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    {   $msg = "Erreur : authentification incorrecte !";
    }
	else 
	{	// contrôle d'existence de $pseudoAretirer
	    $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoDestinataire);
	    if ($utilisateurDestinataire == null)
	    {  $msg = "Erreur : utilisateur inexistant !";
	    }
	    else
	    {   $utilisateurAutorisant = $dao->getUnUtilisateur($pseudo);
    	    $idAutorisant = $utilisateurAutorisant->getId();
    	    $idAutorise = $utilisateurDestinataire->getId();
    	    
    	    if ( $dao->autoriseAConsulter($idAutorisant, $idAutorise) == false ) {
	            $msg = "Erreur : l'autorisation n'était pas accordée !";
	        }
	        else {
	            // suppression de l'autorisation
	            $ok = $dao->supprimerUneAutorisation($idAutorisant, $idAutorise);
	            if ( ! $ok ) {
                    $msg = "Erreur : problème lors de la suppression de l'autorisation !";
                }
                else {
                    // la suppression a fonctionné
                    if ($texteMessage == '') {
                        // l'utilisateur ne souhaite pas envoyer de courriel
                        $msg = "Autorisation supprimée.";
                    }
                    else {
                        // l'utilisateur souhaite envoyer un courriel
                        $adrMail = $utilisateurDestinataire->getAdrMail();
                        $sujetMail = "Suppression d'autorisation de la part d'un utilisateur du système TraceGPS";
                        $contenuMail = "Cher ou chère " . $pseudoDestinataire . "\n\n";
                        $contenuMail .= "L'utilisateur " . $pseudo . " du système TraceGPS vous retire l'autorisation de suivre ses parcours.\n\n";
                        $contenuMail .= "Son message : " . $texteMessage . "\n\n";
                        $contenuMail .= "Cordialement.\n";
                        $contenuMail .= "L'administrateur du système TraceGPS";
                        
                        $ok = Outils::envoyerMail($adrMail, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
                        if ( ! $ok ) {
                            $msg = "Erreur : autorisation supprimée. L'envoi du courriel de notification a rencontré un problème  !";
                        }
                        else {
                            // tout a fonctionné
                            $msg = "Autorisation supprimée. L'intéressé va recevoir un courriel de notification.";
                        }
                    }

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
	$elt_commentaire = $doc->createComment('Service web SupprimerUneAutorisation - BTS SIO - Lycée De La Salle - Rennes');
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
