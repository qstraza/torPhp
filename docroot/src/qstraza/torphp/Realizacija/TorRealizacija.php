<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 16:44
 */

namespace qstraza\torphp\Realizacija;


use Facebook\WebDriver\WebDriverBy;
use qstraza\torphp\Data\OrozjeItem;
use qstraza\torphp\Data\User;
use qstraza\torphp\TorProxy;

class TorRealizacija extends TorProxy
{
    private $vrnitev;
    private $vrstaKupca;
    private $drzavaProdaje;
    private $drzava;
    private $kupecNazivPriimekIme;
    private $maticnaDavcnaPoslovnegaSubjekta;
    private $datumProdaje;
    private $naselje;
    private $ulica;
    private $hst;
    private $vrstaDovoljenja;
    private $organIzdaje;
    private $stevilkaListine;
    private $datumIzdajeListine;

    /**
     * TorRealizacija constructor.
     */
    public function __construct($clientName = null, $seleniumDriver = null)
    {
        // If there is not ClientName set yet, set it.
        if ($clientName) {
            $this->setClientName($clientName);
        }
        // If seleniumDriver has been passed upon creation, that means that browser
        // is already initialized and is opened, so we need to just set the driver
        // which was passed upon creation. If there is no seleniumDriver passed,
        // it means that browser is closed so we need to init it.
        if ($seleniumDriver) {
            $this->setSeleniumDriver($seleniumDriver);
        } else {
            $this->initBrowser();
        }
    }

    /**
     * @return mixed
     */
    public function getVrnitev()
    {
        return $this->vrnitev;
    }

    /**
     * @param mixed $vrnitev
     * @return TorRealizacija
     */
    public function setVrnitev($vrnitev)
    {
        // To get valid options, execute following jQuery onpage
        // var output = "";jQuery("select[id$=rel_w66_id_vrnitve] option").each(function(index) {if (index==0) return; var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
        $validOptions = [
            'Vrnitev dobavitelju' => '1',
            'Vrnitev lastniku' => '2',
        ];
        if (isset($validOptions[$vrnitev])) {
            $this->vrnitev = $validOptions[$vrnitev];
            $this->selectOption('FM:rel_w66_id_vrnitve', $this->vrnitev);
            return $this;
        }
        throw new \Exception('Vrnitev "' . $vrnitev . '", ni pravilna!');
    }

    /**
     * @return mixed
     */
    public function getVrstaKupca()
    {
        return $this->vrstaKupca;
    }

    /**
     * @return mixed
     */
    public function getDrzavaProdaje()
    {
        return $this->drzavaProdaje;
    }

    /**
     * @param mixed $drzavaProdaje
     * @return TorRealizacija
     */
    public function setDrzavaProdaje($drzavaProdaje, $isEU = false)
    {
        $this->clickById("contentForm:rel_w63_id_drzave_prodaje");
        sleep(0.2);
        if ($drzavaProdaje == "Slovenia") {
            $this->clickById("contentForm:rel_w63_id_drzave_prodaje_0");
        } elseif ($isEU) {
            $this->clickById("contentForm:rel_w63_id_drzave_prodaje_2");
        } else {
            $this->clickById("contentForm:rel_w63_id_drzave_prodaje_1");
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrzava()
    {
        return $this->drzava;
    }

    /**
     * @param mixed $drzava
     * @return TorRealizacija
     */
    public function setDrzava($drzava)
    {
        $this->drzava = $drzava;
        $this->enableElementById("FM:rel_drzava_prod");
        $this->writeById('FM:rel_drzava_prod', $drzava);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKupecNazivPriimekIme()
    {
        return $this->kupecNazivPriimekIme;
    }

    /**
     * @param mixed $kupecNazivPriimekIme
     * @return TorRealizacija
     */
    public function setKupecNazivPriimekIme($kupecNazivPriimekIme)
    {
        $this->kupecNazivPriimekIme = $kupecNazivPriimekIme;
        $this->writeById('FM:rel_subjekt', $kupecNazivPriimekIme);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaticnaDavcnaPoslovnegaSubjekta()
    {
        return $this->maticnaDavcnaPoslovnegaSubjekta;
    }

    /**
     * @param mixed $maticnaDavcnaPoslovnegaSubjekta
     * @return TorRealizacija
     */
    public function setMaticnaDavcnaPoslovnegaSubjekta($maticnaDavcnaPoslovnegaSubjekta)
    {
        $this->maticnaDavcnaPoslovnegaSubjekta = $maticnaDavcnaPoslovnegaSubjekta;
        // $this->writeById('FM:rel_pos_id_mat_stv_kup', $maticnaDavcnaPoslovnegaSubjekta);
        $this->writeById('FM:rel_ds_ms', $maticnaDavcnaPoslovnegaSubjekta);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatumProdaje()
    {
        return $this->datumProdaje;
    }

    /**
     * @param mixed $datumProdaje
     * @return TorRealizacija
     */
    public function setDatumProdaje($datumProdaje)
    {
        $this->datumProdaje = $this->transformDate($datumProdaje);
        $this->writeById('contentForm:rel_dtm_dog_input', $this->datumProdaje);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNaselje()
    {
        return $this->naselje;
    }

    /**
     * @param mixed $naselje
     * @return TorRealizacija
     */
    public function setNaselje($naselje)
    {
        $this->naselje = $naselje;
        $this->enableElementById("FM:rel_obcina");
        $this->writeById('FM:rel_obcina', $naselje);
        return $this;
    }

    public function setOpomba($opomba)
    {
        $this->getElementById('contentForm:rel_opombe')->clear();
        $this->writeById('contentForm:rel_opombe', $opomba);
        return $this;
    }
    /**
     * @return mixed
     */
    public function getUlica()
    {
        return $this->ulica;
    }

    /**
     * @param mixed $ulica
     * @return TorRealizacija
     */
    public function setUlica($ulica)
    {
        $this->ulica = $ulica;
        $this->enableElementById("FM:rel_naselje");
        $this->writeById('FM:rel_naselje', $ulica);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHst()
    {
        return $this->hst;
    }

    /**
     * @param mixed $hst
     * @return TorRealizacija
     */
    public function setHst($hst)
    {
        $this->hst = $hst;
        $this->enableElementById("FM:rel_ulc_hst");
        $this->writeById('FM:rel_ulc_hst', $hst);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVrstaDovoljenja()
    {
        return $this->vrstaDovoljenja;
    }

    /**
     * @param mixed $vrstaDovoljenja
     * @return TorRealizacija
     */
    public function setVrstaDovoljenja($vrstaDovoljenja, $vstaDovoljenjaBtnId1 = null, $elementPrefix = null)
    {
        parent::setVrstaDovoljenja($vrstaDovoljenja, "contentForm:rel_w67_id_vrs_dovoljenja", "rel");
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganIzdaje()
    {
        return $this->organIzdaje;
    }

    /**
     * @param mixed $organIzdaje
     * @return TorRealizacija
     */
    public function setOrganIzdaje($organIzdaje)
    {
        $this->organIzdaje = $organIzdaje;
        $this->writeById('contentForm:rel_organ_izdaje', $organIzdaje);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStevilkaListine()
    {
        return $this->stevilkaListine;
    }

    /**
     * @param mixed $stevilkaListine
     * @return TorRealizacija
     */
    public function setStevilkaListine($stevilkaListine)
    {
        $this->stevilkaListine = $stevilkaListine;
        $this->writeById('contentForm:rel_stv_listine', $stevilkaListine);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatumIzdajeListine()
    {
        return $this->datumIzdajeListine;
    }

    /**
     * @param mixed $datumIzdajeListine
     * @return TorRealizacija
     */
    public function setDatumIzdajeListine($datumIzdajeListine)
    {
        $this->datumIzdajeListine = $datumIzdajeListine;
        $this->writeById('contentForm:rel_dtm_izdaje_lst_input', $datumIzdajeListine);
        return $this;
    }

    public function selectBuyer($buttonId, $vrstaEvidence, User $user)
    {
        switch (substr($vrstaEvidence, 0, 1)){
            case "1": // Izdelano orožje.
                $isciButtonId = 189;
                $izberiUserButtonId = 194;
                $dodajUserButtonId = 195;
                $potrdiAddingNewUserButtonId = 231;
                $potrdiAddingNewUserConfirmButtonId = 179;
                break;

            case "3": // Nabavljeno in prodano orožje in priglasitveni list.
                $isciButtonId = 246;
                $izberiUserButtonId = 251;
                $dodajUserButtonId = 252;
                $potrdiAddingNewUserButtonId = 288;
                $potrdiAddingNewUserConfirmButtonId = 236;
                break;

            case "4": //4 - Nabavljeno in prodano strelivo.
                $isciButtonId = 236;
                $izberiUserButtonId = 241;
                $dodajUserButtonId = 242;
                $potrdiAddingNewUserButtonId = 278;
                $potrdiAddingNewUserConfirmButtonId = 226;
                break;
        }

        $this->clickById("contentForm:j_idt" . $buttonId);
        sleep(2);
        $this->selectUser($user, $isciButtonId, $izberiUserButtonId, $dodajUserButtonId, $potrdiAddingNewUserButtonId, $potrdiAddingNewUserConfirmButtonId);
        return $this;

    }

    /**
     * @param mixed $vrstaEvidence
     */
    public function setVrstaEvidence($vrstaEvidence)
    {
        $vrstaEvidenceCode = substr($vrstaEvidence, 0, 1);
        $this->clickById("contentForm:vno_w62_id_knjige_tor");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\\:vno_w62_id_knjige_tor_items li'));
        foreach ($options as $option) {
            if (substr($option->getText(), 0, 1) == $vrstaEvidenceCode) {
                $this->clickById($option->getAttribute(("id")));
                return $this;
            }
        }
        throw new \Exception("Vrednost za vrsto evidence ni prava: {$vrstaEvidence}");
    }
    protected function setRealizacija($nerealizirane)
    {
        $this->clickById("contentForm:ui_ind_realizacija");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\\:ui_ind_realizacija_items li'));
        foreach ($options as $option) {
            if (strtolower(trim($option->getText())) == strtolower(trim($nerealizirane))) {
                $this->clickById($option->getAttribute(("id")));
                return $this;
            }
        }
        throw new \Exception("Vrednost za realizacijo ni prava: {$nerealizirane}");
    }

    public function selectMoreResultsPerPage($n) {
        // Selecting 50 hits per page.
        $this->getSeleniumDriver()
            ->findElement(WebDriverBy::cssSelector('select[name="contentForm\:to11DataTable_rppDD"] option[value="'.$n.'"]'))
            ->click();
    }
}
