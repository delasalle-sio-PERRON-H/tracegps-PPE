<?php
// Projet TraceGPS
// fichier : modele/DAO.class.php   (DAO : Data Access Object)
// Rôle : fournit des méthodes d'accès à la bdd tracegps (projet TraceGPS) au moyen de l'objet PDO
// modifié par Jim le 12/8/2018

// liste des méthodes déjà développées (dans l'ordre d'apparition dans le fichier) :

// __construct() : le constructeur crée la connexion $cnx à la base de données
// __destruct() : le destructeur ferme la connexion $cnx à la base de données
// getNiveauConnexion($login, $mdp) : fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $login et $mdp
// function existePseudoUtilisateur($pseudo) : fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
// getUnUtilisateur($login) : fournit un objet Utilisateur à partir de $login (son pseudo ou son adresse mail)
// getTousLesUtilisateurs() : fournit la collection de tous les utilisateurs (de niveau 1)
// creerUnUtilisateur($unUtilisateur) : enregistre l'utilisateur $unUtilisateur dans la bdd
// modifierMdpUtilisateur($login, $nouveauMdp) : enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $login daprès l'avoir hashé en SHA1
// supprimerUnUtilisateur($login) : supprime l'utilisateur $login (son pseudo ou son adresse mail) dans la bdd, ainsi que ses traces et ses autorisations
// envoyerMdp($login, $nouveauMdp) : envoie un mail à l'utilisateur $login avec son nouveau mot de passe $nouveauMdp

// liste des méthodes restant à développer :

// existeAdrMailUtilisateur($adrmail) : fournit true si l'adresse mail $adrMail existe dans la table tracegps_utilisateurs, false sinon
// getLesUtilisateursAutorises($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisés à suivre l'utilisateur $idUtilisateur
// getLesUtilisateursAutorisant($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisant l'utilisateur $idUtilisateur à voir leurs parcours
// autoriseAConsulter($idAutorisant, $idAutorise) : vérifie que l'utilisateur $idAutorisant) autorise l'utilisateur $idAutorise à consulter ses traces
// creerUneAutorisation($idAutorisant, $idAutorise) : enregistre l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// supprimerUneAutorisation($idAutorisant, $idAutorise) : supprime l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// getLesPointsDeTrace($idTrace) : fournit la collection des points de la trace $idTrace
// getUneTrace($idTrace) : fournit un objet Trace à partir de identifiant $idTrace
// getToutesLesTraces() : fournit la collection de toutes les traces
// getMesTraces($idUtilisateur) : fournit la collection des traces de l'utilisateur $idUtilisateur
// getLesTracesAutorisees($idUtilisateur) : fournit la collection des traces que l'utilisateur $idUtilisateur a le droit de consulter
// creerUneTrace(Trace $uneTrace) : enregistre la trace $uneTrace dans la bdd
// terminerUneTrace($idTrace) : enregistre la fin de la trace d'identifiant $idTrace dans la bdd ainsi que la date de fin
// supprimerUneTrace($idTrace) : supprime la trace d'identifiant $idTrace dans la bdd, ainsi que tous ses points
// creerUnPointDeTrace(PointDeTrace $unPointDeTrace) : enregistre le point $unPointDeTrace dans la bdd


// certaines méthodes nécessitent les classes suivantes :
include_once(__DIR__ . '/../Utilisateur.class.php');
include_once(__DIR__ . '/../Trace.class.php');
include_once(__DIR__ . '/../PointDeTrace.class.php');
include_once(__DIR__ . '/../Point.class.php');
include_once(__DIR__ . '/../Outils.class.php');

// inclusion des paramètres de l'application
include_once('parametres.php');

// début de la classe DAO (Data Access Object)
class DAO
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Membres privés de la classe ---------------------------------------
    // ------------------------------------------------------------------------------------------------------

    private $cnx;                // la connexion à la base de données

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Constructeur et destructeur ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
        try {
            $this->cnx = new PDO ("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
                $PARAM_USER,
                $PARAM_PWD);
            return true;
        } catch (Exception $ex) {
            echo("Echec de la connexion a la base de donnees <br>");
            echo("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
            echo("PARAM_HOTE = " . $PARAM_HOTE);
            return false;
        }
    }

    public function __destruct()
    {
        // ferme la connexion à MySQL :
        unset($this->cnx);
    }

    // ------------------------------------------------------------------------------------------------------
    // -------------------------------------- Méthodes d'instances ------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    // fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $pseudo et $mdpSha1
    // cette fonction renvoie un entier :
    //     0 : authentification incorrecte
    //     1 : authentification correcte d'un utilisateur (pratiquant ou personne autorisée)
    //     2 : authentification correcte d'un administrateur
    // modifié par Jim le 11/1/2018
    public function getNiveauConnexion($pseudo, $mdpSha1)
    {
        // préparation de la requête de recherche
        $txt_req = "Select niveau from tracegps_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $txt_req .= " and mdpSha1 = :mdpSha1";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        $req->bindValue("mdpSha1", $mdpSha1, PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // traitement de la réponse
        $reponse = 0;
        if ($uneLigne) {
            $reponse = $uneLigne->niveau;
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la réponse
        return $reponse;
    }


    // fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
    // modifié par Jim le 27/12/2017
    public function existePseudoUtilisateur($pseudo)
    {
        // préparation de la requête de recherche
        $txt_req = "Select count(*) from tracegps_utilisateurs where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // exécution de la requête
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libère les ressources du jeu de données
        $req->closeCursor();

        // fourniture de la réponse
        if ($nbReponses == 0) {
            return false;
        } else {
            return true;
        }
    }


    // fournit un objet Utilisateur à partir de son pseudo $pseudo
    // fournit la valeur null si le pseudo n'existe pas
    // modifié par Jim le 9/1/2018
    public function getUnUtilisateur($pseudo)
    {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // libère les ressources du jeu de données
        $req->closeCursor();

        // traitement de la réponse
        if (!$uneLigne) {
            return null;
        } else {
            // création d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);

            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            return $unUtilisateur;
        }
    }


    // fournit la collection  de tous les utilisateurs (de niveau 1)
    // le résultat est fourni sous forme d'une collection d'objets Utilisateur
    // modifié par Jim le 27/12/2017
    public function getTousLesUtilisateurs()
    {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where niveau = 1";
        $txt_req .= " order by pseudo";

        $req = $this->cnx->prepare($txt_req);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);

            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur à la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }


    // enregistre l'utilisateur $unUtilisateur dans la bdd
    // fournit true si l'enregistrement s'est bien effectué, false sinon
    // met à jour l'objet $unUtilisateur avec l'id (auto_increment) attribué par le SGBD
    // modifié par Jim le 9/1/2018
    /**
     * @param $unUtilisateur
     * @return bool
     */
    public function creerUnUtilisateur($unUtilisateur)
    {
        /**
         * @var Utilisateur $unUtilisateur
         */
        // on teste si l'utilisateur existe déjà
        if ($this->existePseudoUtilisateur($unUtilisateur->getPseudo())) return false;

        // préparation de la requête
        $txt_req1 = "insert into tracegps_utilisateurs (pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation)
                      values (:pseudo, :mdpSha1, :adrMail, :numTel, :niveau, :dateCreation)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("pseudo", utf8_decode($unUtilisateur->getPseudo()), PDO::PARAM_STR);
        $req1->bindValue("mdpSha1", utf8_decode(sha1($unUtilisateur->getMdpsha1())), PDO::PARAM_STR);
        $req1->bindValue("adrMail", utf8_decode($unUtilisateur->getAdrmail()), PDO::PARAM_STR);
        $req1->bindValue("numTel", utf8_decode($unUtilisateur->getNumTel()), PDO::PARAM_STR);
        $req1->bindValue("niveau", utf8_decode($unUtilisateur->getNiveau()), PDO::PARAM_INT);
        $req1->bindValue("dateCreation", utf8_decode($unUtilisateur->getDateCreation()), PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req1->execute();
        // sortir en cas d'échec
        if (!$ok) {
            return false;
        }

        // recherche de l'identifiant (auto_increment) qui a été attribué à la trace
        $txt_req2 = "Select max(id) as idMax from tracegps_utilisateurs";
        $req2 = $this->cnx->prepare($txt_req2);
        // extraction des données
        $req2->execute();
        $uneLigne = $req2->fetch(PDO::FETCH_OBJ);
        $unId = $uneLigne->idMax;
        $unUtilisateur->setId($unId);
        return true;
    }


    // enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $pseudo daprès l'avoir hashé en SHA1
    // fournit true si la modification s'est bien effectuée, false sinon
    // modifié par Jim le 9/1/2018
    public function modifierMdpUtilisateur($pseudo, $nouveauMdp)
    {
        // préparation de la requête
        $txt_req = "update tracegps_utilisateurs set mdpSha1 = :nouveauMdp";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("nouveauMdp", sha1($nouveauMdp), PDO::PARAM_STR);
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req->execute();
        return $ok;
    }


    // supprime l'utilisateur $pseudo dans la bdd, ainsi que ses traces et ses autorisations
    // fournit true si l'effacement s'est bien effectué, false sinon
    // modifié par Jim le 9/1/2018
    public function supprimerUnUtilisateur($pseudo)
    {
        $unUtilisateur = $this->getUnUtilisateur($pseudo);
        if ($unUtilisateur == null) {
            return false;
        } else {
            $idUtilisateur = $unUtilisateur->getId();

            // suppression des traces de l'utilisateur (et des points correspondants)
            $lesTraces = $this->getLesTraces($idUtilisateur);
            foreach ($lesTraces as $uneTrace) {
                $this->supprimerUneTrace($uneTrace->getId());
            }

            // préparation de la requête de suppression des autorisations
            $txt_req1 = "delete from tracegps_autorisations";
            $txt_req1 .= " where idAutorisant = :idUtilisateur or idAutorise = :idUtilisateur";
            $req1 = $this->cnx->prepare($txt_req1);
            // liaison de la requête et de ses paramètres
            $req1->bindValue("idUtilisateur", utf8_decode($idUtilisateur), PDO::PARAM_INT);
            // exécution de la requête
            $req1->execute();

            // préparation de la requête de suppression de l'utilisateur
            $txt_req2 = "delete from tracegps_utilisateurs";
            $txt_req2 .= " where pseudo = :pseudo";
            $req2 = $this->cnx->prepare($txt_req2);
            // liaison de la requête et de ses paramètres
            $req2->bindValue("pseudo", utf8_decode($pseudo), PDO::PARAM_STR);
            // exécution de la requête
            $ok = $req2->execute();
            return $ok;
        }
    }


    // envoie un mail à l'utilisateur $pseudo avec son nouveau mot de passe $nouveauMdp
    // retourne true si envoi correct, false en cas de problème d'envoi
    // modifié par Jim le 9/1/2018
    public function envoyerMdp($pseudo, $nouveauMdp)
    {
        global $ADR_MAIL_EMETTEUR;
        // si le pseudo n'est pas dans la table tracegps_utilisateurs :
        if ($this->existePseudoUtilisateur($pseudo) == false) return false;

        // recherche de l'adresse mail
        $adrMail = $this->getUnUtilisateur($pseudo)->getAdrMail();

        // envoie un mail à l'utilisateur avec son nouveau mot de passe
        $sujet = "Modification de votre mot de passe d'accès au service TraceGPS";
        $message = "Cher(chère) " . $pseudo . "\n\n";
        $message .= "Votre mot de passe d'accès au service service TraceGPS a été modifié.\n\n";
        $message .= "Votre nouveau mot de passe est : " . $nouveauMdp;
        $ok = Outils::envoyerMail($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
        return $ok;
    }


    // Le code restant à développer va être réparti entre les membres de l'équipe de développement.
    // Afin de limiter les conflits avec GitHub, il est décidé d'attribuer une zone de ce fichier à chaque développeur.
    // Développeur 1 : lignes 350 à 549
    // Développeur 2 : lignes 550 à 749
    // Développeur 3 : lignes 750 à 950

    // Quelques conseils pour le travail collaboratif :
    // avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer 
    // la dernière version du fichier.
    // Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.


    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 1 (LELU AWEN) : lignes 350 à 549
    // --------------------------------------------------------------------------------------


    /**
     * @param $idTrace
     * @return array
     */
    public function getLesPointsDeTrace($idTrace)
    {
        /** @var PointDeTrace $lastPointDeTrace */
        $pointsDeTrace = [];

        $txt_req =
            "
            select idTrace, id, latitude, longitude, altitude, dateHeure, rythmeCardio
            from tracegps_points
            where idTrace = $idTrace;
            ";

        $req = $this->cnx->prepare($txt_req);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            $unIdTrace = utf8_encode($uneLigne->idTrace);
            $unId = utf8_encode($uneLigne->id);
            $uneLatitude = utf8_encode($uneLigne->latitude);
            $uneLongitude = utf8_encode($uneLigne->longitude);
            $uneAltitude = utf8_encode($uneLigne->altitude);
            $uneDateHeure = utf8_encode($uneLigne->dateHeure);
            $unRythmeCardio = utf8_encode($uneLigne->rythmeCardio);

            if (sizeof($pointsDeTrace) == 0) {
                $unPointDeTrace = new PointDeTrace(
                    $unIdTrace,
                    $unId,
                    $uneLatitude,
                    $uneLongitude,
                    $uneAltitude,
                    $uneDateHeure,
                    $unRythmeCardio,
                    0,
                    0,
                    0
                );
            } else {
                $lastPointDeTrace = $pointsDeTrace[sizeof($pointsDeTrace) - 1];
                $unPointDeTrace = new PointDeTrace(
                    $unIdTrace,
                    $unId,
                    $uneLatitude,
                    $uneLongitude,
                    $uneAltitude,
                    $uneDateHeure,
                    $unRythmeCardio,
                    0,
                    0,
                    0
                );

                $distance = Point::getDistance($lastPointDeTrace, $unPointDeTrace);
                $unPointDeTrace->setDistanceCumulee($lastPointDeTrace->getDistanceCumulee() + $distance);
                $timeLastPoint = DateTime::createFromFormat("Y-m-d H:i:s", $lastPointDeTrace->getDateHeure());
                $timePoint = DateTime::createFromFormat("Y-m-d H:i:s", $unPointDeTrace->getDateHeure());
                $tempsCumulee = $lastPointDeTrace->getTempsCumule() + $timePoint->diff($timeLastPoint)->s;
                $unPointDeTrace->setTempsCumule($tempsCumulee);

                $vitesse = $distance / ($timePoint->diff($timeLastPoint)->s / 3600);

                $unPointDeTrace->setVitesse($vitesse);
            }

            // ajout de l'utilisateur à la collection
            $pointsDeTrace[] = $unPointDeTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $pointsDeTrace;
    }

    /**
     * @param $unPointDeTrace
     */
    public function creerUnPointDeTrace($unPointDeTrace)
    {
        /** @var PointDeTrace $unPointDeTrace */

        $idTrace = $unPointDeTrace->getIdTrace();
        $id = $unPointDeTrace->getId();
        $latitude = $unPointDeTrace->getLatitude();
        $longitude = $unPointDeTrace->getLongitude();
        $altitude = $unPointDeTrace->getAltitude();
        $dateHeure = $unPointDeTrace->getDateHeure();
        $rythmeCardio = $unPointDeTrace->getRythmeCardio();

        $txt_req =
            "
            insert into tracegps_points (idTrace, id, latitude, longitude, altitude, dateHeure, rythmeCardio) 
            values ($idTrace, $id, $latitude, $longitude, $altitude, '$dateHeure', $rythmeCardio)
            ";

        $req = $this->cnx->prepare($txt_req);
        // extraction des données
        $req->execute();

    }

    /**
     * @param $id
     * @return null|Trace
     */
    public function getUneTrace($id)
    {
        $txt_req =
            "
            select *
            from tracegps_traces
            where id = $id
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->execute();

        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        if ($uneLigne) {
            // si une ligne est trouvée :
            $dateDebut = $uneLigne->dateDebut;
            $dateFin = $uneLigne->dateFin;
            $terminee = $uneLigne->terminee;
            $idUtilisateur = $uneLigne->idUtilisateur;

            $uneTrace = new Trace($id, $dateDebut, $dateFin, $terminee, $idUtilisateur);

            $uneTrace->setLesPointsDeTrace($this->getLesPointsDeTrace($id));

            return $uneTrace;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getToutesLesTraces()
    {
        $lesTraces = [];

        $txt_req =
            "
            select id
            from tracegps_traces
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->execute();

        $uneLigne = $req->fetch(5);

        while ($uneLigne) {
            $lesTraces[] = $this->getUneTrace($uneLigne->id);
            $uneLigne = $req->fetch(5);
        }

        return $lesTraces;
    }

    /**
     * @param $idUtilisateur
     * @return array
     */
    public function getLesTraces($idUtilisateur)
    {
        $lesTraces = [];

        $txt_req =
            "
            select id
            from tracegps_traces
            where idUtilisateur = :idUser
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(':idUser', $idUtilisateur, PDO::PARAM_INT);
        $req->execute();

        $uneLigne = $req->fetch(5);

        while ($uneLigne) {
            $lesTraces[] = $this->getUneTrace($uneLigne->id);
            $uneLigne = $req->fetch(5);
        }

        return $lesTraces;
    }

    /**
     * @param $idUtilisateur
     * @return array
     */
    public function getLesTracesAutorisees($idUtilisateur)
    {
        $lesTraces = [];

        $txt_req =
            "
            select id
            from tracegps_traces, tracegps_autorisations
            where idUtilisateur = idAutorisant and idAutorise = :idUser
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(':idUser', $idUtilisateur, PDO::PARAM_INT);
        $req->execute();

        $uneLigne = $req->fetch(5);

        while ($uneLigne) {
            $lesTraces[] = $this->getUneTrace($uneLigne->id);
            $uneLigne = $req->fetch(5);
        }

        return $lesTraces;
    }

    /**
     * @param $uneTrace
     * @return bool
     */
    public function creerUneTrace($uneTrace)
    {
        /** @var Trace $uneTrace */

        $txt_req =
            "
            insert into tracegps_traces (dateDebut, dateFin, terminee, idUtilisateur)
            values (:dateDebut, :dateFin, :terminee, :idUtilisateur)
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(':dateDebut', $uneTrace->getDateHeureDebut(), PDO::PARAM_STR);
        $req->bindValue(':dateFin', $uneTrace->getDateHeureFin(), PDO::PARAM_STR);
        $req->bindValue(':terminee', $uneTrace->getTerminee(), PDO::PARAM_BOOL);
        $req->bindValue(':idUtilisateur', $uneTrace->getIdUtilisateur(), PDO::PARAM_INT);

        $req->execute();

        return true;
    }

    /**
     * @param $uneTrace
     * @return bool
     */
    public function supprimerUneTrace($uneTrace)
    {
        /**
         * @var PointDeTrace $PointDeTrace
         */

        $txt_req =
            "
            delete from tracegps_points
            where idTrace = :idPoint
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(':idPoint', $uneTrace, PDO::PARAM_INT);
        $req->execute();

        $txt_req =
            "
            delete from tracegps_traces
            where id = :idTrace
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(':idTrace', $uneTrace, PDO::PARAM_INT);
        $req->execute();

        return true;
    }

    /**
     * @param $uneTrace
     */
    public function terminerUneTrace($uneTrace)
    {

        $txt_req =
            "
            update tracegps_traces 
            set dateFin = :dateFin, terminee = true
            where id = :traceId
            ";

        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(':dateFin', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $req->bindValue(':traceId', $uneTrace, PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * @param $idUtilisateur
     * @return array
     */
    public function getLesUtilisateursAutorises($idUtilisateur)
    {
        $users = [];

        $req_txt =
            "
            select idAutorise
            from tracegps_autorisations
            where idAutorisant = :idUser
            ";

        $req = $this->cnx->prepare($req_txt);
        $req->bindValue(':idUser', $idUtilisateur, PDO::PARAM_INT);
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        while ($uneLigne) {

            $users[] = $this->getUserById($uneLigne->idAutorise);

            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }

        return $users;
    }

    /**
     * @param $idUtilisateur
     * @return array
     */
    public function getLesUtilisateursAutorisant($idUtilisateur)
    {
        $users = [];

        $req_txt =
            "
            select idAutorisant
            from tracegps_autorisations
            where idAutorise = :idUser
            ";

        $req = $this->cnx->prepare($req_txt);
        $req->bindValue(':idUser', $idUtilisateur, PDO::PARAM_INT);
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        while ($uneLigne) {

            $users[] = $this->getUserById($uneLigne->idAutorisant);

            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }

        return $users;
    }

    /**
     * @param $userId
     * @return null|Utilisateur
     */
    public function getUserById($userId)
    {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where id = :userId";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue(":userId", $userId, PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // libère les ressources du jeu de données
        $req->closeCursor();

        // traitement de la réponse
        if (!$uneLigne) {
            return null;
        } else {
            // création d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);

            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            return $unUtilisateur;
        }
    }


    // fournit true si l'adresse mail $adrMail existe dans la table sinon false
    public function existeAdrMailUtilisateur($adrmail)

    {
        // préparation de la requête de recherche
        $txt_req = "Select count(*) from tracegps_utilisateurs where adrMail = :adrMail";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et ses paramètres
        $req->bindValue("adrMail", $adrmail, PDO::PARAM_STR);
        // exécution de la requête
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libère ls ressources du jeu de données
        $req->closeCursor();

        // fourniture de la réponse
        if ($nbReponses == 0) {
            return false;
        } else
            return true;
    } // fin méthode existeAdrMailUtilisateur


    // enregistre l'autorisation dans la BDD, true si enregistrer, false sinon
    public function creerUneAutorisation($idAutorisant, $idAutorise)
    {
        // test si l'autorisation existe déjà
        if ($this->autoriseAConsulter($idAutorisant, $idAutorise) == true) {
            return false;
        }
        // préparation de la requête
        $txt_req = "insert into tracegps_autorisations (idAutorisant, idAutorise) values (:idAutorisant, :idAutorise)";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et ses paramètres
        $req->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_INT);
        $req->bindValue("idAutorise", $idAutorise, PDO::PARAM_INT);
        // exécution de la requête
        $ok = $req->execute();
        return $ok;
    } // fin méthode creerUneAutorisation

    // vérifie que l'autorisateur autorise l'autorisé à consulter ses traces, renvoie true si l'autorisation est donnée, false sinon
    public function autoriseAConsulter($idAutorisant, $idAutorise)
    {
        // préparation de la requête de recherche
        $txt_req = "Select count(*) from tracegps_autorisations where idAutorisant = :idAutorisant and idAutorise = :idAutorise";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_INT);
        $req->bindValue("idAutorise", $idAutorise, PDO::PARAM_INT);

        // extraction des données et comptage des réponses
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libère les ressources du jeu de données
        $req->closeCursor();

        // fourniture de la réponse
        if ($nbReponses == 0)
            return false;
        else
            return true;
    } // fin méthode autoriseAConsulter

    // supprime l'autorisation ($idAutorisant, $idAutorise) dans la bdd, fournit true si l'effacement s'est bien effectué, false sinon
    public function supprimerUneAutorisation($idAutorisant, $idAutorise)
    {
        // préparation de la requête
        $txt_req = "delete from tracegps_autorisations";
        $txt_req .= " where idAutorisant = :idAutorisant and idAutorise = :idAutorise";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_INT);
        $req->bindValue("idAutorise", $idAutorise, PDO::PARAM_INT);
        // exécution de la requête
        $ok = $req->execute();
        return $ok;
    } // fin méthode supprimerUneAutorisation

} // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
