<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 16:45
 */

namespace qstraza\torphp\Realizacija;


use Facebook\WebDriver\WebDriverBy;

class TorRealizacijaOrozja extends TorRealizacija
{
    private $prevzemnikOrozja;
    private $stPriglasitvenegaLista;
    private $maticnaStPoslovalnice;
    private $datumPrevzemaVrnitveOrozja;
    private $datumIzdajePriglasitvenegaLista;
    private $vrstaDovoljenjaDrugo;

    /**
     * TorRealizacijaOrozja constructor.
     */
    public function __construct($clientName = null, $seleniumDriver = null)
    {
        parent::__construct($clientName, $seleniumDriver);
    }

    /**
     * @return mixed
     */
    public function getPrevzemnikOrozja()
    {
        return $this->prevzemnikOrozja;
    }

    /**
     * @param mixed $prevzemnikOrozja
     * @return TorRealizacijaOrozja
     */
    public function setPrevzemnikOrozja($prevzemnikOrozja)
    {
        $this->prevzemnikOrozja = $prevzemnikOrozja;
        $this->writeById('contentForm:rel_prevzemnik', $prevzemnikOrozja);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStPriglasitvenegaLista()
    {
        return $this->stPriglasitvenegaLista;
    }

    /**
     * @param mixed $stPriglasitvenegaLista
     * @return TorRealizacijaOrozja
     */
    public function setStPriglasitvenegaLista($stPriglasitvenegaLista)
    {
        $this->stPriglasitvenegaLista = $stPriglasitvenegaLista;
        $this->writeById('FM:rel_ser_stv_prig_lista', $stPriglasitvenegaLista);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaticnaStPoslovalnice()
    {
        return $this->maticnaStPoslovalnice;
    }

    /**
     * @param mixed $maticnaStPoslovalnice
     * @return TorRealizacijaOrozja
     */
    public function setMaticnaStPoslovalnice($maticnaStPoslovalnice)
    {
        $this->maticnaStPoslovalnice = $maticnaStPoslovalnice;
        $this->writeById('FM:rel_pos_id_mat_stv', $maticnaStPoslovalnice);
        $this->writeById('FM:rel_ds_ms', $maticnaStPoslovalnice);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatumPrevzemaVrnitveOrozja()
    {
        return $this->datumPrevzemaVrnitveOrozja;
    }

    /**
     * @param mixed $datumPrevzemaVrnitveOrozja
     * @return TorRealizacijaOrozja
     */
    public function setDatumPrevzemaVrnitveOrozja($datumPrevzemaVrnitveOrozja)
    {
        $this->datumPrevzemaVrnitveOrozja = $this->transformDate($datumPrevzemaVrnitveOrozja);
        $this->writeById('contentForm:rel_dtm_prevzema_input', $this->datumPrevzemaVrnitveOrozja);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatumIzdajePriglasitvenegaLista()
    {
        return $this->datumIzdajePriglasitvenegaLista;
    }

    /**
     * @param mixed $datumIzdajePriglasitvenegaLista
     * @return TorRealizacijaOrozja
     */
    public function setDatumIzdajePriglasitvenegaLista($datumIzdajePriglasitvenegaLista)
    {
        $this->datumIzdajePriglasitvenegaLista = $datumIzdajePriglasitvenegaLista;
        $this->writeById('FM:rel_dtm_izdaje_prig_lista', $datumIzdajePriglasitvenegaLista);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVrstaDovoljenjaDrugo()
    {
        return $this->vrstaDovoljenjaDrugo;
    }


    public function openItemBySerial($serijska)
    {
        $this->menuClick('iskanje');
        $this->writeById('contentForm:vno_tov_stevilka', $serijska);
        $this->clickById('contentForm:searchBtn');
        sleep(1);
        $error = $this->getErrorStatus();
        if ($error !== null) {
            return ["error" => $error];
        }
        $return = null;
        $elements = $this->getSeleniumDriver()->findElements(WebDriverBy::xpath('//tbody[@id="contentForm:to11DataTable_data"]/tr'));

        foreach ($elements as $element) {
            if (strpos($element->getText(), 'Realizacija') !== false) {
                return ["error" => "Orožja je že realizirano!"];
            }
            elseif (strpos($element->getText(), 'Vpis') === false) {
                return ["error" => "Orožja ne morem realizirati!"];
            }
            else {
                // TODO: testiraj, če je več zadetkov, ali bo vse obklukal, ali samo prvega...
                if (strpos($element->getText(), "Nabavljeno in prodano orožje in priglasitveni list") !== false) {
                    $return["vrsta evidence"] = "3 - Nabavljeno in prodano orožje in priglasitveni list";
                }
                elseif (strpos($element->getText(), "Izdelano orožje") !== false) {
                    $return["vrsta evidence"] = "1 - Izdelano orožje";
                }
                else {
                    throw new \Exception("Neznana vrsta evidence!");
                }
                $element->findElement(WebDriverBy::cssSelector("td"))->click();
                $this->getElementByCssSelector("#main_content > div.card.main-frame > div > div:nth-child(1) > div > div:nth-child(2) > div > button:nth-child(3)")->click();
            }
            break;
        }
        return $return;
    }

    public function openItemByCatBrandModel ($kategorija, $proizvajalec, $model, $nerealizirane = false, $vrstaEvidence = false) {
        $this->menuClick('iskanje');
        $this->writeById('contentForm:vno_proizvajalec', $proizvajalec);
        $this->writeById('contentForm:vno_model', $model);

        $this->setKategorijaOrozja($kategorija);
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
        $elements = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\:to11DataTable_data tr'));
        foreach ($elements as $element) {
            $element->findElement(WebDriverBy::cssSelector("td"))->click();
            $this->getElementByCssSelector("#main_content > div.card.main-frame > div > div:nth-child(1) > div > div:nth-child(2) > div > button:nth-child(3)")->click();

            break;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getVrstaEvidence()
    {
        return $this->vrstaEvidence;
    }

    public function setProdanaKolicina($kolicina)
    {
        if (!ctype_digit($kolicina)) {
            throw new \Exception("Količina mora biti številka in ne: {$kolicina}");
        }
        $trenutnaZaloga = $this->getElementById("contentForm:ui_zaloga")->getAttribute("value");
        if ($kolicina > $trenutnaZaloga) {
            throw new \Exception("Ni dovolj zaloge! Zaloga: {$trenutnaZaloga}");
        }

        $this->writeById("contentForm:rel_kolicina", $kolicina);
    }

}
