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

    public function testEstUnCodePostalValide()
    {
        $unCP = '35000';
        self::assertTrue(Outils::estUnCodePostalValide($unCP));
        $unCP = '3500';
        self::assertFalse(Outils::estUnCodePostalValide($unCP));
    }

    public function testEstUneAdrMailValide()
    {
        $uneAdrMail = 'sophie.fonfec@gmail.com';
        self::assertTrue(Outils::estUneAdrMailValide($uneAdrMail));
        $uneAdrMail = 'sophie.fonfec@gmailcom';
        self::assertFalse(Outils::estUneAdrMailValide($uneAdrMail));
        $uneAdrMail = 'sophie.fonfecgmail.com';
        self::assertFalse(Outils::estUneAdrMailValide($uneAdrMail));
    }

    public function testEstUneDateValide()
    {
        $uneDate = '31/13/2016';
        self::assertFalse(Outils::estUneDateValide($uneDate));
        $uneDate = '31/12/2016';
        self::assertTrue(Outils::estUneDateValide($uneDate));
        $uneDate = '29/02/2015';
        self::assertFalse(Outils::estUneDateValide($uneDate));
        $uneDate = '29/02/2016';
        self::assertTrue(Outils::estUneDateValide($uneDate));
    }

    public function testEstUnNumTelValide()
    {

        $unNumero = '1122334455';
        self::assertTrue(Outils::estUnNumTelValide($unNumero));
        $unNumero = '112233445';
        self::assertFalse(Outils::estUnNumTelValide($unNumero));
        $unNumero = '11.22.33.44.55';
        self::assertTrue(Outils::estUnNumTelValide($unNumero));
        $unNumero = '11,22,33,44,55';
        self::assertFalse(Outils::estUnNumTelValide($unNumero));
        $unNumero = '11-22-33-44-55';
        self::assertTrue(Outils::estUnNumTelValide($unNumero));
        $unNumero = '11/22/33/44/55';
        self::assertTrue(Outils::estUnNumTelValide($unNumero));
    }

}
