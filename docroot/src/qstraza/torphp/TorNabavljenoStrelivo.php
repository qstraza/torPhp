<?php


namespace qstraza\torphp;


use Facebook\WebDriver\WebDriverBy;
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

    public function selectSeller(User $user)
    {
        $isciButtonId = 174;
        $izberiUserButtonId = 179;
        $dodajUserButtonId = 180;
        $potrdiAddingNewUserButtonId = 216;
        $potrdiAddingNewUserConfirmButtonId = 164;

        $this->clickById("contentForm:j_idt107");
        sleep(2);
        $this->selectUser($user, $isciButtonId, $izberiUserButtonId, $dodajUserButtonId, $potrdiAddingNewUserButtonId, $potrdiAddingNewUserConfirmButtonId);
        return $this;

    }

    public function setDrzavaProizvajalka($drzavaProizvajalka)
    {
        $this->writeById("contentForm:vno_drzava_pro", $drzavaProizvajalka);
        return $this;
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

    public function setVrstaStreliva($tipVrstaStreliva)
    {
        $this->writeById("contentForm:vno_tov_stevilka", $tipVrstaStreliva);
        return $this;
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

    public function confirmPage($potrdiButtonId, $confirmButtonId)
    {
        $error = parent::confirmPage($potrdiButtonId, $confirmButtonId);
        if (!$error) {
            return $error;
        }
        // Če TOR vidi, da si že takšno trelivo prevzel, javi napako in moraš še enkrat potrdit.
        // Napaka: TOR-00460 Zapis s takšnim kalibrom, proizvajalcem in znamko že obstaja!
        // Za potrditev vnosa pritisni Potrdi.
        if (strpos($error, "TOR-00460") !== false) {
            return parent::confirmPage($potrdiButtonId, $confirmButtonId);
        }
    }
}