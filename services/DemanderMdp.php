<?php
// Projet TraceGPS - services web
// fichier :  services/DemanderMdp.php
// Dernière mise à jour : 15/1/2018 par Jim

// Rôle : ce service génère un nouveau mot de passe, l'enregistre en sha1 et l'envoie par mail à l'utilisateur
// Le service web doit recevoir 1 paramètre :
//     pseudo : le pseudo de l'utilisateur
// Le service retourne un flux de données XML contenant un compte-rendu d'exécution

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/DemanderMdp.php?pseudo=europa

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/DemanderMdp.php
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true) $pseudo = "";  else $pseudo = $_REQUEST ["pseudo"];

// Contrôle de la présence des paramètres
if ( $pseudo == "")
{	$msg = "Erreur : données incomplètes !";
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO/DAO.class.php');
	$dao = new DAO();
	
	if ( ! $dao->existePseudoUtilisateur($pseudo) ) 
		$msg = "Erreur : pseudo inexistant !";
	else {
		// génération d'un nouveau mot de passe
		$nouveauMdp = Outils::creerMdp();
		// enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir codé en MD5
		$ok = $dao->modifierMdpUtilisateur ($pseudo, $nouveauMdp);
	
		if ( ! $ok ) {
		    $msg = "Erreur : problème lors de l'enregistrement du mot de passe !";
		}
		else {
    		// envoie un mail à l'utilisateur avec son nouveau mot de passe 
		    $ok = $dao->envoyerMdp($pseudo, $nouveauMdp);
    		if (! $ok)
    		    $msg = "Erreur : enregistrement effectué. L'envoi du mail de confirmation a rencontré un problème !";
    		
    		else
    		    $msg = 'Vous allez recevoir un courriel avec votre nouveau mot de passe.';
		}
	}
	
	// ferme la connexion à MySQL :
	unset($dao);
}
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
	$elt_commentaire = $doc->createComment('Service web DemanderMdp - BTS SIO - Lycée De La Salle - Rennes');
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
?>
