<?php
// Projet TraceGPS - services web
// fichier : services/DemanderUneAutorisation.php
// Dernière mise à jour : 25/4/2018 par Jim

// Rôle : ce service permet à un utilisateur de demander une autorisation à un autre utilisateur
// Le service web doit recevoir 5 paramètres :
//     pseudo : le pseudo de l'utilisateur qui demande l'autorisation
//     mdpSha1 : le mot de passe hashé en sha1 de l'utilisateur qui demande l'autorisation
//     pseudoDestinataire : le pseudo de l'utilisateur à qui on demande l'autorisation
//     texteMessage : le texte d'un message accompagnant la demande
//     nomPrenom : le nom et le prénom du demandeur
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/DemanderUneAutorisation.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&pseudoDestinataire=oxygen&texteMessage=coucou&nomPrenom=charles-edouard

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/DemanderUneAutorisation.php

// connexion du serveur web à la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["pseudoDestinataire"]) == true)  $pseudoDestinataire = "";  else $pseudoDestinataire = $_REQUEST ["pseudoDestinataire"];
if ( empty ($_REQUEST ["texteMessage"]) == true)  $texteMessage = "";  else   $texteMessage = $_REQUEST ["texteMessage"];
if ( empty ($_REQUEST ["nomPrenom"]) == true)  $nomPrenom = "";  else   $nomPrenom = $_REQUEST ["nomPrenom"];

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $pseudoDestinataire == "" || $texteMessage == "" || $nomPrenom == "" )
{	$msg = "Erreur : données incomplètes !";
}
else
{	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    {   $msg = "Erreur : authentification incorrecte !";
    }
    else
    {	// contrôle d'existence de $pseudoDestinataire
        $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoDestinataire);
        if ($utilisateurDestinataire == null)
        {   $msg = "Erreur : utilisateur inexistant !";
        }
        else
        {   $utilisateurDemandeur = $dao->getUnUtilisateur($pseudo);
            // envoi d'un mail de confirmation de l'enregistrement
            $adrMail = $utilisateurDestinataire->getAdrMail();
            $sujetMail = "Demande d'autorisation de la part d'un utilisateur du système TraceGPS";
            $contenuMail = "Cher ou chère " . $pseudoDestinataire . "\n\n";
            $contenuMail .= "Un utilisateur du système TraceGPS vous demande l'autorisation de suivre vos parcours.\n\n";
            $contenuMail .= "Voici les données le concernant :\n\n";
            $contenuMail .= "Son pseudo : " . $utilisateurDemandeur->getPseudo() . "\n";
            $contenuMail .= "Son adresse mail : " . $utilisateurDemandeur->getAdrMail() . "\n";
            $contenuMail .= "Son numéro de téléphone : " . $utilisateurDemandeur->getNumTel() . "\n";
            $contenuMail .= "Son nom et prénom : " . $nomPrenom . "\n";
            $contenuMail .= "Son message : " . $texteMessage . "\n\n";
            
            $contenuMail .= "Pour accepter la demande, cliquez sur ce lien :\n";
            $contenuMail .= $ADR_SERVICE_WEB . "ValiderDemandeAutorisation.php?a=" . $utilisateurDestinataire->getMdpSha1();
            $contenuMail .= "&b=" . $utilisateurDestinataire->getPseudo() . "&c=" . $utilisateurDemandeur->getPseudo() . "&d=1";
            $contenuMail .= "\n\n";
            $contenuMail .= "Pour rejeter la demande, cliquez sur ce lien :\n";
            $contenuMail .= $ADR_SERVICE_WEB . "ValiderDemandeAutorisation.php?a=" . $utilisateurDestinataire->getMdpSha1();
            $contenuMail .= "&b=" . $utilisateurDestinataire->getPseudo() . "&c=" . $utilisateurDemandeur->getPseudo() . "&d=0";
        
            $ok = Outils::envoyerMail($adrMail, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
            if ( ! $ok ) {
                $msg = "L'envoi du courriel de demande d'autorisation a rencontré un problème  !";
            }
            else {
                // tout a fonctionné
                $msg = $pseudoDestinataire . " va recevoir un courriel avec votre demande.";
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
    $elt_commentaire = $doc->createComment('Service web DemanderUneAutorisation - BTS SIO - Lycée De La Salle - Rennes');
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
