<?php
// Projet TraceGPS - services web
// fichier : services/GetTousLesUtilisateurs.php
// Dernière mise à  jour : 27/11/2018 par Coubrun

//Rôle : ce service web permet à un utilisateur d'obtenir le détail d'un de ses parcours ou d'un parcours d'un membre qui l'autorise.

//Paramètres à fournir :
//	pseudo : le pseudo de l'utilisateur
//	mdpSha1 : le mot de passe de l'utilisateur hashé en sha1
//	idTrace : l'id de la trace à consulter
//	dateHeure : la date et l'heure au point de passage (format 'Y-m-d H:i:s')
//	latitude : latitude du point de passage
//	longitude : longitude du point de passage
//	altitude : altitude du point de passage
//	rythmeCardio : rythme cardiaque au point de passage (ou 0 si le rythme n'est pas mesurable)
//	lang : le langage utilisé pour le flux de données ("xml" ou "json")

// Le service retourne un flux de données XML contenant un compte-rendu d'exécution ainsi que la synthèse et la liste des points du parcours

// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/GetUnParcoursEtSesPoints.php?pseudo=europa&mdpSha1=13e3668bbee30b004380052b086457b014504b3e&idTrace=2

// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/GetUnParcoursEtSesPoints.php

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
if ( empty ($_REQUEST ["dateHeure"]) == true)  $dateHeure = "";  else   $dateHeure = $_REQUEST ["dateHeure"];
if ( empty ($_REQUEST ["latitude"]) == true)  $latitude = "";  else   $latitude = $_REQUEST ["latitude"];
if ( empty ($_REQUEST ["longitude"]) == true)  $longitude = "";  else   $longitude = $_REQUEST ["longitude"];
if ( empty ($_REQUEST ["altitude"]) == true)  $altitude = "";  else   $altitude = $_REQUEST ["altitude"];
if ( empty ($_REQUEST ["rythmeCardio"]) == true)  $rythmeCardio = "0";  else   $rythmeCardio = $_REQUEST ["rythmeCardio"];

// initialisation
$laTrace = null;

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "" || $dateHeure == "" || $latitude == "" || $longitude == "" || $altitude == "" || $rythmeCardio == ""  )
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
            $msg = "Erreur : le numéro de trace n'existe pas !";
        }
        else
        {
            // récupération de l'id de l'utilisateur
            $idUtilisateur = $dao->getUnUtilisateur($pseudo)->getId();
            if ( $idUtilisateur != $laTrace->getIdUtilisateur() )
            {
                $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur !";
            }
            else
            {
                // calcul du numéro du point
                $idPoint = $laTrace->getNombrePoints() + 1;
                
                // création du point
                $tempsCumule = 0;
                $distanceCumulee = 0;
                $vitesse = 0;
                $unPoint = new PointDeTrace($idTrace, $idPoint, $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio, $tempsCumule, $distanceCumulee, $vitesse);
                
                // enregistrement du point
                $ok = $dao->creerUnPointDeTrace($unPoint);
                if (! $ok)
                {
                    $msg = "Erreur : problème lors de l'enregistrement du point !";
                }// fin if
                else 
                {
                    $msg = "Point créé.";
                }// fin else 5
                
            }//fin else 4
        }//fin else 3
    }//fin else 2
}//fin else 1

// ferme la connexion à MySQL
unset($dao);

// création du flux XML en sortie
creerFluxXML ($msg, $unPoint);
// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;
// création du flux XML en sortie
function creerFluxXML($msg, $unPoint)
{
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web EnvoyerPosition - BTS SIO - Lycée De La Salle - Rennes');
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
    
    if ($unPoint != null)
    {
        // place l'id du point dans l'élément 'donnees'
        $elt_id = $doc->createElement('id', $unPoint->getId());
        $elt_donnees->appendChild($elt_id);
    }// fin if
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
    
}// fin function

?>