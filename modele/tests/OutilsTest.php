<?php
/**
 * Created by PhpStorm.
 * User: lelu.a
 * Date: 09/10/2018
 * Time: 14:54
 */

include_once ('../Outils.class.php');

class OutilsTest extends PHPUnit_Framework_TestCase
{

    public function testConvertirEnDateFr()
    {
        $uneDateUS = '2007-05-16';
        $uneDateFR = Outils::convertirEnDateFR($uneDateUS);
        self::assertEquals($uneDateFR, '16/05/2007');
    }

    public function testConvertirEnDateUS()
    {
        $uneDateFR = '16/05/2007';
        $uneDateUS = Outils::convertirEnDateUS($uneDateFR);
        self::assertEquals($uneDateUS, '2007-05-16');
    }

    public function testCorrigerDate()
    {
        $uneDateAvant = '16-05-2007';
        $uneDateApres = Outils::corrigerDate($uneDateAvant);
        self::assertEquals($uneDateApres, '16/05/2007');
    }

    public function testCorrigerPrenom()
    {
        $unPrenomAvant = 'charles';
        $unPrenomApres = Outils::corrigerPrenom($unPrenomAvant);
        self::assertEquals($unPrenomApres, 'Charles');

        $unPrenomAvant = 'charles-edouard';
        $unPrenomApres = Outils::corrigerPrenom($unPrenomAvant);
        self::assertEquals($unPrenomApres, 'Charles-Edouard');
    }

    public function testCorrigerTelephone()
    {
        $unNumeroAvant = '1122334455';
        $unNumeroApres = Outils::corrigerTelephone($unNumeroAvant);
        self::assertEquals($unNumeroApres, '11.22.33.44.55');

        $unNumeroAvant = '11 22 33 44 55';
        $unNumeroApres = Outils::corrigerTelephone($unNumeroAvant);
        self::assertEquals($unNumeroApres, '11.22.33.44.55');
    }

    public function testCorrigerVille()
    {
        $uneVilleAvant = 'rennes';
        $uneVilleApres = Outils::corrigerVille($uneVilleAvant);
        self::assertEquals($uneVilleApres, 'RENNES');

        $uneVilleAvant = 'saint malo';
        $uneVilleApres = Outils::corrigerVille($uneVilleAvant);
        self::assertEquals($uneVilleApres, 'St MALO');

        $uneVilleAvant = 'saint-malo';
        $uneVilleApres = Outils::corrigerVille($uneVilleAvant);
        self::assertEquals($uneVilleApres, 'St MALO');
    }

    public function testEstUneDateValide()
    {
        $unCP = '35000';
        assertTrue(Outils::estUnCodePostalValide($unCP));
        $unCP = '3500';
        self::assertFalse(Outils::estUnCodePostalValide($unCP));
    }

    public function testEstUneAdrMailValide()
    {

    }

    public function testEnvoyerMail()
    {

    }

    public function testEstUnCodePostalValide()
    {

    }

    public function testEstUnNumTelValide()
    {

    }

}
