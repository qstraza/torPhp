<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 18:16
 */

namespace qstraza\torphp\Realizacija;


use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

class TorRealizacijaStreliva extends TorRealizacija
{
    private $vrstaDovoljenjaDrugo;
    private $prodanaKolicina;

    /**
     * TorRealizacijaStreliva constructor.
     */
    public function __construct($clientName = null, $seleniumDriver = null)
    {
        parent::__construct($clientName, $seleniumDriver);
    }

    /**
     * @return mixed
     */
    public function getVrstaDovoljenjaDrugo()
    {
        return $this->vrstaDovoljenjaDrugo;
    }

    /**
     * @param mixed $vrstaDovoljenjaDrugo
     * @return TorRealizacijaStreliva
     */
    public function setVrstaDovoljenjaDrugo($vrstaDovoljenjaDrugo)
    {
        // To get valid options, execute following jQuery onpage
        // var output = "";jQuery("select[id$=rel_w13_id_vrs_vlg_reg] option").each(function() {var name = /.*? - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
        $validOptions = [
            'Orožni list' => 'OL',
            'Orožni posestni list' => 'OP',
            'Druga listina' => 'DL',
            'Listina ni potrebna' => 'NP',
        ];
        if (isset($validOptions[$vrstaDovoljenjaDrugo])) {
            $this->vrstaDovoljenjaDrugo = $validOptions[$vrstaDovoljenjaDrugo];
            $this->selectOption('FM:rel_w13_id_vrs_vlg_reg', $this->vrstaDovoljenjaDrugo);
            return $this;
        }
        throw new \Exception('Vrsta dovoljenja - Drugo "' . $vrstaDovoljenjaDrugo . '", ni pravilna!');
    }

    /**
     * @return mixed
     */
    public function getProdanaKolicina()
    {
        return $this->prodanaKolicina;
    }

    /**
     * @param int $prodanaKolicina
     * @return TorRealizacijaStreliva
     */
    public function setProdanaKolicina($prodanaKolicina)
    {
        $this->prodanaKolicina = $prodanaKolicina;
        $this->writeById('contentForm:rel_kolicina', $prodanaKolicina);
        $this->getSeleniumDriver()->getKeyboard()->pressKey(WebDriverKeys::TAB);
        sleep(1);
        return $this;
    }

    public function searchStrelivoDelStrelivaByNameCal($cal, $strelivoDelStreliva, $proizvajalec, $nerealizirane = false, $vrstaEvidence = false)
    {
        $this->menuClick('iskanje');
        $this->writeById('contentForm:vno_proizvajalec', $proizvajalec);
        $this->writeById('contentForm:vno_kaliber', $cal);

        $this->setStrelivoDelStreliva($strelivoDelStreliva);
        if ($nerealizirane !== false) {
            $this->setRealizacija($nerealizirane);
        }
        if ($vrstaEvidence !== false) {
            $this->setVrstaEvidence($vrstaEvidence);
        }
        $this->clickById('contentForm:searchBtn');
        sleep(1);
        $error = $this->getErrorStatus();
        if ($error !== null) {
            return ["error" => $error];
        }
        return null;

    }

    private function setStrelivoDelStreliva($strelivoDelStreliva)
    {
        $strelivoDelStrelivaCode = substr($strelivoDelStreliva, 0, 1);
        $this->clickById("contentForm:vno_w61_id_streliva_dela");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\:vno_w61_id_streliva_dela_items li'));
        foreach ($options as $option) {
            if (substr($option->getText(), 0, 1) == $strelivoDelStrelivaCode) {
                $this->clickById($option->getAttribute(("id")));
                return $this;
            }
        }
        throw new \Exception("Vrednost za vrsto streliva/del streliva ni prava: {$strelivoDelStreliva}");
    }

}