<?php
// Projet TraceGPS - services web
// fichier : services/GetTousLesUtilisateurs.php
// Dernière mise à jour : 27/11/2018 par Coubrun

//Rôle : ce service web permet à un utilisateur d'obtenir le détail d'un de ses parcours ou d'un parcours d'un membre qui l'autorise.
    
//Paramètres a fournir :
//	pseudo : le pseudo de l'utilisateur
//	mdpSha1 : le mot de passe de l'utilisateur hashé en sha1
//	idTrace : l'id de la trace à consulter
//	lang : le langage utilisé pour le flux de données ("xml" ou "json")

// Le service retourne un flux de données XML contenant un compte-rendu d'exécution ainsi que la synthèse et la liste des points du parcours
// Les paramétres peuvent être passes par la methode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/GetUnParcoursEtSesPoints.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&idTrace=2
// Les paramètres peuvent être passés par la méthode POST (à privilègier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/GetUnParcoursEtSesPoints.php

// connexion du serveur web è la base MySQL
include_once ('../modele/DAO/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramétre dans l'URL par la méthode GET
// la fonction $_POST récupére une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["idTrace"]) == true)  $idTrace = "";  else   $idTrace = $_REQUEST ["idTrace"];
// initialisation
$laTrace = null;
// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "" )
{	
    $msg = "Erreur : données incomplètes !";
}
else
{	
    if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
{   
    $msg = "Erreur : authentification incorrecte !";
}
else
{	// contrôle d'existence de idTrace
    $laTrace = $dao->getUneTrace($idTrace);
    if ($laTrace == null)
    {  
        $msg = "Erreur : parcours inexistant !";
    }
    else
    {   
        // récupèration de l'id de l'utilisateur demandeur et du propriétaire du parcours
        $idDemandeur = $dao->getUnUtilisateur($pseudo)->getId();
        $idProprietaire = $laTrace->getIdUtilisateur();
        
        // vérification de l'autorisation
        if ( $idDemandeur != $idProprietaire && $dao->autoriseAConsulter($idProprietaire, $idDemandeur) == false )
        {   
            $msg = "Erreur : vous n'êtes pas autorisé par le propriétaire du parcours !";
        }
        else
        {   
            $msg = "Données de la trace demandée.";
        }
    }
}
}
// ferme la connexion à MySQL
unset($dao);

// création du flux XML en sortie
creerFluxXML ($msg, $laTrace);
// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

// création du flux XML en sortie
function creerFluxXML($msg, $laTrace)
{
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    //specifie la version et le type d'encodage
    $doc->xmlVersion = '1.0';
    $doc->encoding = "UTF-8";
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web GetUnParcoursEtSesPoints - BTS SIO - Lycée De La Salle - Rennes');
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
    
    if ($laTrace != null)
    {
        // place l'élément 'trace' dans l'élément 'donnees'
        $elt_trace = $doc->createElement('trace');
        $elt_donnees->appendChild($elt_trace);
        
        // place la description de la trace dans l'élément 'trace'
        $elt_id = $doc->createElement('id', $laTrace->getId());
        $elt_trace->appendChild($elt_id);
        
        $elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $laTrace->getId());
        $elt_trace->appendChild($elt_dateHeureDebut);
        
        $elt_terminee = $doc->createElement('terminee', $laTrace->getId());
        $elt_trace->appendChild($elt_terminee);
        
        if ($laTrace->getTerminee() == true)
        {
            $elt_dateHeureFin = $doc->createElement('dateHeureFin', $laTrace->getDateHeureFin());
            $elt_trace->appendChild($elt_dateHeureFin);
        } // fin if
        
        $elt_idUtilisateur = $doc->createElement('idUtilisateur', $laTrace->getIdUtilisateur());
        $elt_trace->appendChild($elt_idUtilisateur);
        
        // place l'élément 'lespoints' dans l'élément 'donnees'
        $elt_lespoints = $doc->createElement('lespoints');
        $elt_donnees->appendChild($elt_lespoints);
        
        // traitement des points
        if (sizeof($laTrace->getLesPointsDeTrace()) > 0)
        {
            foreach ($laTrace->getLesPointsDeTrace() as $unPointDeTrace)
            {
                // crée un élément vide 'point'
                $elt_point = $doc->createElement('point');
                // place l'élément 'point' dans l'élément 'lespoints'
                $elt_lespoints->appendChild($elt_point);
                
                // crée les éléments enfants de l'élément 'point'
                $elt_id             = $doc->createElement('id', $unPointDeTrace->getId());
                $elt_point->appendChild($elt_id);
                
                $elt_latitude       = $doc->createElement('latitude', $unPointDeTrace->getLatitude());
                $elt_point->appendChild($elt_latitude);
                
                $elt_longitude      = $doc->createElement('longitude', $unPointDeTrace->getLongitude());
                $elt_point->appendChild($elt_longitude);
                
                $elt_altitude       = $doc->createElement('altitude', $unPointDeTrace->getAltitude());
                $elt_point->appendChild($elt_altitude);
                
                $elt_dateHeure      = $doc->createElement('dateHeure', $unPointDeTrace->getDateHeure());
                $elt_point->appendChild($elt_dateHeure);
                
                $elt_rythmeCardio       = $doc->createElement('rythmeCardio', $unPointDeTrace->getRythmeCardio());
                $elt_point->appendChild($elt_rythmeCardio);
                
            }// fin foreach
        }// fin if
        
    } // fin if
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
    
} // fin function



?>