<?php
// Projet TraceGPS
// fichier : modele/DAO.test.php
// RÃ´le : test de la classe DAO.class.php
// DerniÃ¨re mise Ã  jour : 15/8/2018 par JM CARTRON
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// connexion du serveur web Ã  la base MySQL
include_once ('DAO.class.php');
//include_once ('_DAO.mysql.class.php');
$dao = new DAO();

/*
// test de la mÃ©thode getNiveauConnexion ----------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de getNiveauConnexion : </h3>";
$niveau = $dao->getNiveauConnexion("admin", sha1("mdpadmin"));
echo "<p>Niveau de ('admin', 'mdpadmin') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("europa", sha1("mdputilisateur"));
echo "<p>Niveau de ('europa', 'mdputilisateur') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("europa", sha1("123456"));
echo "<p>Niveau de ('europa', '123456') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("toto", sha1("mdputilisateur"));
echo "<p>Niveau de ('toto', 'mdputilisateur') : " . $niveau . "</br>";
*/


/*
// test de la mÃ©thode existePseudoUtilisateur -----------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de existePseudoUtilisateur : </h3>";
if ($dao->existePseudoUtilisateur("admin")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin' : <b>" . $existe . "</b><br>";
if ($dao->existePseudoUtilisateur("europa")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'europa' : <b>" . $existe . "</b></br>";
if ($dao->existePseudoUtilisateur("toto")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'toto' : <b>" . $existe . "</b></p>";
*/


/*
// test de la mÃ©thode getUnUtilisateur -----------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de getUnUtilisateur : </h3>";
$unUtilisateur = $dao->getUnUtilisateur("admin");
if ($unUtilisateur) {
    echo "<p>L'utilisateur admin existe : <br>" . $unUtilisateur->toString() . "</p>";
}
else {
    echo "<p>L'utilisateur admin n'existe pas !</p>";
}
$unUtilisateur = $dao->getUnUtilisateur("europa");
if ($unUtilisateur) {
    echo "<p>L'utilisateur europa existe : <br>" . $unUtilisateur->toString() . "</p>";
}
else {
    echo "<p>L'utilisateur europa n'existe pas !</p>";
}
$unUtilisateur = $dao->getUnUtilisateur("admon");
if ($unUtilisateur) {
    echo "<p>L'utilisateur admon existe : <br>" . $unUtilisateur->toString() . "</p>";
}
else {
    echo "<p>L'utilisateur admon n'existe pas !</p>";
}
*/  


/*
// test de la mÃ©thode getTousLesUtilisateurs ------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de getTousLesUtilisateurs : </h3>";
$lesUtilisateurs = $dao->getTousLesUtilisateurs();
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{	echo ($unUtilisateur->toString());
    echo ('<br>');
}
*/


/*
// test de la mÃ©thode creerUnUtilisateur ----------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de creerUnUtilisateur : </h3>";
$unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "toto@gmail.com", "5566778899", 1, date('Y-m-d H:i:s', time()), 0, null);
$ok = $dao->creerUnUtilisateur($unUtilisateur);
if ($ok)
{   echo "<p>Utilisateur bien enregistrÃ© !</p>";
    echo $unUtilisateur->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de l'utilisateur !</p>";
}
*/


/*
// test de la mÃ©thode modifierMdpUtilisateur ------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de modifierMdpUtilisateur : </h3>";
$unUtilisateur = $dao->getUnUtilisateur("toto");
if ($unUtilisateur) {
    echo "<p>Ancien mot de passe de l'utilisateur toto : <b>" . $unUtilisateur->getMdpSha1() . "</b><br>";
    $dao->modifierMdpUtilisateur("toto", "mdpadmin");
    $unUtilisateur = $dao->getUnUtilisateur("toto");
    echo "Nouveau mot de passe de l'utilisateur toto : <b>" . $unUtilisateur->getMdpSha1() . "</b><br>";
    
    $niveauDeConnexion = $dao->getNiveauConnexion('toto', sha1('mdputilisateur'));
    echo "Niveau de connexion de ('toto', 'mdputilisateur') : <b>" . $niveauDeConnexion . "</b><br>";
    
    $niveauDeConnexion = $dao->getNiveauConnexion('toto', sha1('mdpadmin'));
    echo "Niveau de connexion de ('toto', 'mdpadmin') : <b>" . $niveauDeConnexion . "</b></p>";
}
else {
    echo "<p>L'utilisateur toto n'existe pas !</p>";
}
*/


/*
// test de la mÃ©thode supprimerUnUtilisateur ------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de supprimerUnUtilisateur : </h3>";
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimÃ© !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimÃ© !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
*/


/*
// test de la mÃ©thode envoyerMdp ------------------------------------------------------------------
// modifiÃ© par Jim le 12/8/2018
echo "<h3>Test de envoyerMdp : </h3>";
// pour ce test, une adresse mail que vous pouvez consulter
$unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "jean.michel.cartron@gmail.com", "5566778899", 2, date('Y-m-d H:i:s', time()), 0, null);
$ok = $dao->creerUnUtilisateur($unUtilisateur);
$dao->modifierMdpUtilisateur("toto", "mdpadmin");
$ok = $dao->envoyerMdp("toto", "mdpadmin");
if ($ok) {
    echo "<p>Mail bien envoyÃ© !</p>";
}
else {
    echo "<p>Echec lors de l'envoi du mail !</p>";
}
// supprimer le compte crÃ©Ã©
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimÃ© !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
*/





// Le code restant Ã  dÃ©velopper va Ãªtre rÃ©parti entre les membres de l'Ã©quipe de dÃ©veloppement.
// Afin de limiter les conflits avec GitHub, il est dÃ©cidÃ© d'attribuer une zone de ce fichier Ã  chaque dÃ©veloppeur.
// DÃ©veloppeur 1 : lignes 200 Ã  299
// DÃ©veloppeur 2 : lignes 300 Ã  399
// DÃ©veloppeur 3 : lignes 400 Ã  500

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de dÃ©veloppement (dÃ©but de sÃ©ance, nouvelle mÃ©thode, ...), faites un Pull pour rÃ©cupÃ©rer
// la derniÃ¨re version du fichier.
// AprÃ¨s avoir testÃ© et validÃ© une mÃ©thode, faites un commit et un push pour transmettre cette version aux autres dÃ©veloppeurs.





// --------------------------------------------------------------------------------------
// dÃ©but de la zone attribuÃ©e au dÃ©veloppeur 1 (LELU AWEN) : lignes 200 Ã  299
// --------------------------------------------------------------------------------------

































































































// --------------------------------------------------------------------------------------
// dÃ©but de la zone attribuÃ©e au dÃ©veloppeur 2 (xxxxxxxxxxxxxxxxxxxx) : lignes 300 Ã  399
// --------------------------------------------------------------------------------------

































































































// --------------------------------------------------------------------------------------
// dÃ©but de la zone attribuÃ©e au dÃ©veloppeur 3 (Coubrun Mickaël) : lignes 400 Ã  499
// --------------------------------------------------------------------------------------


































































































// ferme la connexion Ã  MySQL :
unset($dao);
?>

</body>
</html>
