<?php
// Projet TraceGPS
// fichier : modele/Trace.class.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 17/7/2018 par JM CARTRON

include_once ('PointDeTrace.class.php');

class Trace
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $id;				// identifiant de la trace
    private $dateHeureDebut;		// date et heure de début
    private $dateHeureFin;		// date et heure de fin
    private $terminee;			// true si la trace est terminée, false sinon
    private $idUtilisateur;		// identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace;		// la collection (array) des objets PointDeTrace formant la trace
    
    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function Trace($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur) {
        $this->id = $unId;
        $this->dateHeureDebut = $uneDateHeureDebut;
        $this->dateHeureFin = $uneDateHeureFin;
        $this->terminee = $terminee;
        $this->idUtilisateur = $unIdUtilisateur;
        
        $this->lesPointsDeTrace = array();
    }
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function getId() {return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getDateHeureDebut() {return $this->dateHeureDebut;}
    public function setDateHeureDebut($uneDateHeureDebut) {$this->dateHeureDebut = $uneDateHeureDebut;}
    
    public function getDateHeureFin() {return $this->dateHeureFin;}
    public function setDateHeureFin($uneDateHeureFin) {$this->dateHeureFin= $uneDateHeureFin;}
    
    public function getTerminee() {return $this->terminee;}
    public function setTerminee($terminee) {$this->terminee = $terminee;}
    
    public function getIdUtilisateur() {return $this->idUtilisateur;}
    public function setIdUtilisateur($unIdUtilisateur) {$this->idUtilisateur = $unIdUtilisateur;}
    
    public function getLesPointsDeTrace() {return $this->lesPointsDeTrace;}
    public function setLesPointsDeTrace($lesPointsDeTrace) {$this->lesPointsDeTrace = $lesPointsDeTrace;}
    
    // Fournit une chaine contenant toutes les données de l'objet
    public function toString() {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui  <br>";
        }
        else {
            $msg .= "Terminée : Non  <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= "   - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= "   - Longitude : "  . $this->getCentre()->getLongitude() . "<br>";
            $msg .= "   - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }
    
    //Fournit le nombre de points de passage
    public function getNombrePoints() {
        return sizeof($this->lesPointsDeTrace);
    }
    
    //Fournit le point central du parcours : un objet Point (ou null si collection vide)
    public function getCentre() {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return null;
            else {
                // au départ, les valeurs extrêmes sont celles du premier point
                $premierPoint = $this->lesPointsDeTrace[0];
                $latMini = $premierPoint->getLatitude();
                $latMaxi = $premierPoint->getLatitude();
                $longMini = $premierPoint->getLongitude();
                $longMaxi = $premierPoint->getLongitude();
                // parcours des autres points (à partir de la position 1)
                for ($i = 1 ; $i < sizeof($this->lesPointsDeTrace) ; $i++) {
                    $lePoint = $this->lesPointsDeTrace[$i];
                    if ($lePoint->getLatitude() < $latMini) $latMini = $lePoint->getLatitude();
                    if ($lePoint->getLatitude() > $latMaxi) $latMaxi = $lePoint->getLatitude();
                    if ($lePoint->getLongitude() < $longMini) $longMini = $lePoint->getLongitude();
                    if ($lePoint->getLongitude() > $longMaxi) $longMaxi = $lePoint->getLongitude();
                }
                $latCentre = ($latMini + $latMaxi) / 2;
                $longCentre = ($longMini + $longMaxi) / 2;
                $leCentre = new Point($latCentre, $longCentre, 0);
                return $leCentre;
            }
    }
    
    // Fournit le dénivelé (en m) entre le point le plus bas et le point le plus haut du parcours (ou 0 si la collection est vide)
    public function getDenivele() {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else {
                // au départ, les valeurs extrêmes sont celles du premier point
                $premierPoint = $this->lesPointsDeTrace[0];
                $altMini = $premierPoint->getAltitude();
                $altMaxi = $premierPoint->getAltitude();
                // parcours des autres points (à partir de la position 1)
                for ($i = 1 ; $i < sizeof($this->lesPointsDeTrace) ; $i++) {
                    $lePoint = $this->lesPointsDeTrace[$i];
                    if ($lePoint->getAltitude() < $altMini) $altMini = $lePoint->getAltitude();
                    if ($lePoint->getAltitude() > $altMaxi) $altMaxi = $lePoint->getAltitude();
                }
                $denivele = $altMaxi - $altMini;
                return $denivele;
            }
    }
    
    // Fournit la durée totale du parcours en secondes (ou 0 si la collection est vide)
    public function getDureeEnSecondes() {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else {
                $positionDernierPoint = sizeof($this->lesPointsDeTrace) - 1;
                $dernierPoint = $this->lesPointsDeTrace[$positionDernierPoint];
                return $dernierPoint->getTempsCumule();
            }
    }
    
    // Fournit la durée totale du parcours sous forme d'une chaine "hh:mm:ss" (ou "00:00:00" si la collection est vide)
    public function getDureeTotale() {
        $duree = $this->getDureeEnSecondes();
        if ($duree == 0) {
            return "00:00:00";
        }
        else {
            $heures = $duree / 3600;
            $duree = $duree % 3600;
            $minutes = $duree / 60;
            $secondes = $duree % 60;
            return sprintf("%02d",$heures) . ":" . sprintf("%02d",$minutes) . ":" . sprintf("%02d",$secondes);
        }
    }
    
    // Fournit la distance totale du parcours (en Km)
    public function getDistanceTotale() {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else {
                $positionDernierPoint = sizeof($this->lesPointsDeTrace) - 1;
                $dernierPoint = $this->lesPointsDeTrace[$positionDernierPoint];
                return $dernierPoint->getDistanceCumulee();
            }
    }
    
    // Fournit le dénivelé positif (en m)
    public function getDenivelePositif()
    {
        $denivele = 0;
        // parcours de tous les couples de points
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace) - 1; $i++)
        {
            $lePoint1 = $this->lesPointsDeTrace[$i];
            $lePoint2 = $this->lesPointsDeTrace[$i + 1];
            // on teste si ça monte
            if ( $lePoint2->getAltitude() > $lePoint1->getAltitude() )
                $denivele += $lePoint2->getAltitude() - $lePoint1->getAltitude();
        }
        return $denivele;
    }
    
    // Fournit le dénivelé négatif (en m)
    public function getDeniveleNegatif()
    {
        $denivele = 0;
        // parcours de tous les couples de points
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace) - 1; $i++)
        {
            $lePoint1 = $this->lesPointsDeTrace[$i];
            $lePoint2 = $this->lesPointsDeTrace[$i + 1];
            // on teste si ça descend
            if ( $lePoint2->getAltitude() < $lePoint1->getAltitude() )
                $denivele += $lePoint1->getAltitude() - $lePoint2->getAltitude();
        }
        return $denivele;
    }
    
    // Fournit la vitesse moyenne du parcours en km/h (ou 0 si la distance est nulle)
    public function getVitesseMoyenne() {
        if ($this->getDistanceTotale() == 0)
            return 0;
            else {
                $vitesseEnKmH = $this->getDistanceTotale() / $this->getDureeEnSecondes() * 3600;
                return $vitesseEnKmH;
            }
    } 
    
    // ajoute un objet PointDeTrace à la collection $lesPointsDeTrace
    public function ajouterPoint(PointDeTrace $unPoint) {
        if (sizeof($this->lesPointsDeTrace) == 0)
        {   // si premier point de la trace, mise à zéro des données cumulées et de la vitesse
            $unPoint->setTempsCumule(0);
            $unPoint->setDistanceCumulee(0);
            $unPoint->setVitesse(0);
        }
        else
        {   // si déjà d'autres points dans la trace, on cumule la durée et la distance avec celle du dernier point stocké
            $dernierPoint = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1];
            
            $duree = strtotime($unPoint->getDateHeure()) - strtotime($dernierPoint->getDateHeure());		// en secondes
            $unPoint->setTempsCumule($dernierPoint->getTempsCumule() + $duree);
            
            $distance = Point::getDistance($dernierPoint, $unPoint);
            $unPoint->setDistanceCumulee($dernierPoint->getDistanceCumulee() + $distance);
            
            // calcul de la vitesse entre l'avant-dernier point et le point à ajouter
            if ($duree > 0)
                $vitesse = $distance / $duree * 3600;
                else
                    $vitesse = 0;
                    // on affecte la vitesse calculée au nouveau point
                    $unPoint->setVitesse($vitesse);
        }
        $this->lesPointsDeTrace[] = $unPoint;
    }
    
    // vide la collection
    public function viderListePoints() {
        // on recrée un nouveau tableau vide
        $this->lesPointsDeTrace = array();
    }
    
} // fin de la classe Trace

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
