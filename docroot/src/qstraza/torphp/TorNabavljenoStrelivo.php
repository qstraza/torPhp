<?php


namespace qstraza\torphp;


use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use qstraza\torphp\Data\OrozjeItem;
use qstraza\torphp\Data\User;

class TorNabavljenoStrelivo extends TorNabava
{

    /**
     * TorNabavljenoStrelivo constructor.
     * @param mixed $clientName
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
        }
        else {
            $this->initBrowser();
        }
    }
    public function setVrstaDovoljenja($vrstaDovoljenja, $vstaDovoljenjaBtnId1 = null, $elementPrefix = null)
    {
        parent::setVrstaDovoljenja($vrstaDovoljenja, "contentForm:vno_w67_id_vrs_dovoljenja", "vno");
        return $this;
    }

    public function setDrzavaProizvajalka($drzavaProizvajalka)
    {
        $this->writeById("contentForm:vno_drzava_pro", $drzavaProizvajalka);
        return $this;
    }
    public function getDrzavaProizvajalka()
    {
        return $this->getElementById("contentForm:vno_drzava_pro")->getAttribute('value');
    }

    public function setStrelivoDelStreliva($strelivoDelStreliva)
    {
        $strelivoDelStrelivaCode = substr(trim($strelivoDelStreliva), 0, 1);

        $this->clickById("contentForm:vno_w61_id_streliva_dela");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\:vno_w61_id_streliva_dela_items li'));
        foreach ($options as $option) {
            $optionText = substr(trim($option->getText()), 0, 1);
            if ($optionText == $strelivoDelStrelivaCode) {
                $this->clickById($option->getAttribute("id"));
                return $this;
            }
        }

        throw new \Exception("Strleivo/Del streliva je napačna: {$strelivoDelStreliva}");
    }
    public function getStrelivoDelStreliva()
    {
        $selectElement = $this->getElementById("contentForm:vno_w61_id_streliva_dela_input");
        // Now pass it to WebDriverSelect constructor
        $select = new WebDriverSelect($selectElement);
        // Get value of first selected option:
        return $select->getFirstSelectedOption()->getAttribute('value');
    }

    public function setVrstaStreliva($tipVrstaStreliva)
    {
        $this->writeById("contentForm:vno_tov_stevilka", $tipVrstaStreliva);
        return $this;
    }

    public function getVrstaStreliva()
    {
        return $this->getElementById("contentForm:vno_tov_stevilka")->getAttribute('value');
    }

    public function setEnota($enota)
    {
        $enota = strtolower(trim($enota));

        $this->clickById("contentForm:vno_w49_id_pe_en_mere");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\:vno_w49_id_pe_en_mere_items li'));
        foreach ($options as $option) {
            $optionText = strtolower(trim($option->getText()));
            if ($optionText == $enota) {
                $this->clickById($option->getAttribute("id"));
                return $this;
            }
        }

        throw new \Exception("Izbrana napačna enota: {$enota}");
    }

    public function confirmPage($potrdiButtonSelector = null, $confirmButtonSelector = null)
    {
        $potrdiButtonSelector = "#main_content > div.card.main-frame > div > div:nth-child(5) > div > div > button:nth-child(2)";
        $confirmButtonSelector = "#contentForm\\:confirmDialog button.ui-confirmdialog-yes";
        $error = parent::confirmPage($potrdiButtonSelector, $confirmButtonSelector);

        // Če TOR vidi, da si že takšno trelivo prevzel, javi napako in moraš še enkrat potrdit.
        // Napaka: TOR-00460 Zapis s takšnim kalibrom, proizvajalcem in znamko že obstaja!
        // Za potrditev vnosa pritisni Potrdi.
        if (strpos($error, "TOR-00460") !== false) {
            return parent::confirmPage($potrdiButtonSelector, $confirmButtonSelector);
        }
        return $error;
    }




}