<?php
// Projet TraceGPS - services web
// fichier :  services/ValiderDemandeAutorisation.php
// Dernière mise à jour : 8/11/2018 par Jim

// Rôle : ce service web permet à un utilisateur destinataire d'accepter ou de rejeter une demande d'autorisation provenant d'un utilisateur demandeur
// il envoie un mail au demandeur avec la décision de l'utilisateur destinataire

// Le service web doit être appelé avec 4 paramètres obligatoires dont les noms sont volontairement non significatifs :
//    a : le mot de passe (hashé) de l'utilisateur destinataire de la demande ($mdpSha1)
//    b : le pseudo de l'utilisateur destinataire de la demande ($pseudoAutorisant)
//    c : le pseudo de l'utilisateur source de la demande ($pseudoAutorise)
//    d : la decision 1=oui, 0=non ($decision)

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ValiderDemandeAutorisation.php?a=13e3668bbee30b004380052b086457b014504b3e&b=oxygen&c=europa&d=1

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ValiderDemandeAutorisation.php
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["a"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["a"];
if ( empty ($_REQUEST ["b"]) == true)  $pseudoAutorisant = "";  else   $pseudoAutorisant = $_REQUEST ["b"];
if ( empty ($_REQUEST ["c"]) == true)  $pseudoAutorise = "";  else   $pseudoAutorise = $_REQUEST ["c"];
if ( empty ($_REQUEST ["d"]) == true)  $decision = "";  else   $decision = $_REQUEST ["d"];
			 
// Contrôle de la présence et de la correction des paramètres
if ( $mdpSha1 == "" || $pseudoAutorisant == "" || $pseudoAutorise == "" || ( $decision != 0 && $decision != 1 ) )
{	$message = "Erreur : données incomplètes ou incorrectes.";
}
else
{	// connexion du serveur web à la base MySQL
	include_once ('../modele/DAO/DAO.class.php');
	$dao = new DAO();
	
	// test de l'authentification de l'utilisateur
	// la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
	$niveauConnexion = $dao->getNiveauConnexion($pseudoAutorisant, $mdpSha1);

	if ( $niveauConnexion == 0 )
	{	$message = "Erreur : authentification incorrecte.";
	}
	else
	{	$utilisateurDemandeur = $dao->getUnUtilisateur($pseudoAutorise);
        $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoAutorisant);
        $idAutorisant = $utilisateurDestinataire->getId();
        $idAutorise = $utilisateurDemandeur->getId();
        $adrMailDemandeur = $utilisateurDemandeur->getAdrMail();
        
        if ($dao->autoriseAConsulter($idAutorisant, $idAutorise))
        {	$message = "Erreur : autorisation déjà accordée.";
        }
        else 
        {
    		if ( $decision == "1" )   // acceptation de la demande
    		{   // enregistrement de l'autorisation dans la bdd
    		    $ok = $dao->creerUneAutorisation($idAutorisant, $idAutorise);
    		    if ( ! $ok ) 
    		    {   $message = "Erreur : problème lors de l'enregistrement.";
    		    }
    		    else 
    		    {   // envoi d'un mail d'acceptation à l'intéressé
        			$sujetMail = "Votre demande d'autorisation à un utilisateur du système TraceGPS";
        			$contenuMail = "Cher ou chère " . $pseudoAutorise . "\n\n";
        			$contenuMail .= "Vous avez demandé à " . $pseudoAutorisant . " l'autorisation de consulter ses parcours.\n";
        			$contenuMail .= "Votre demande a été acceptée.\n\n";
        			$contenuMail .= "Cordialement.\n";
        			$contenuMail .= "L'administrateur du système TraceGPS";
        			$ok = Outils::envoyerMail($adrMailDemandeur, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
        			if ( ! $ok )
        			    $message = "Erreur : l'envoi du courriel au demandeur a rencontré un problème.";
        			else
        			    $message = "Autorisation enregistrée.<br>Le demandeur va recevoir un courriel de confirmation.";
        		}
    		}
    		else {    // refus de la demande
    			// envoi d'un mail de rejet à l'intéressé
    		    $sujetMail = "Votre demande d'autorisation à un utilisateur du système TraceGPS";
    		    $contenuMail = "Cher ou chère " . $pseudoAutorise . "\n\n";
    		    $contenuMail .= "Vous avez demandé à " . $pseudoAutorisant . " l'autorisation de consulter ses parcours.\n";
    		    $contenuMail .= "Votre demande a été refusée.\n\n";
    		    $contenuMail .= "Cordialement.\n";
    		    $contenuMail .= "L'administrateur du système TraceGPS";
    		    $ok = Outils::envoyerMail($adrMailDemandeur, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
    			if ( ! $ok )
    			    $message = "Erreur : l'envoi du courriel au demandeur a rencontré un problème.";
			    else
			        $message = "Autorisation refusée.<br>Le demandeur va recevoir un courriel de confirmation.";
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
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>
	<p><?php echo $message; ?></p>
	<p><a href="Javascript:window.close();">Fermer</a></p>
</body>
</html>