<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 25/09/2022
 * Time: 10:40
 */

namespace qstraza\torphp;


use qstraza\torphp\Data\OrozjeItem;
use qstraza\torphp\Data\User;

class TorNabavljenoOrozje extends TorNabava
{
    private $datumIzdelave;

    /**
     * TorNabavljenoOrozje constructor.
     *
     * @param null|String $clientName
     *   Holds client's name.
     * @param null|\Facebook\WebDriver\Remote\RemoteWebDriver $seleniumDriver
     *   Holds selenium Driver.
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
     * @param mixed $datumIzdelave
     * @return TorProxy
     */
    public function setDatumIzdelave($datumIzdelave)
    {
        $this->datumIzdelave = $this->transformDate($datumIzdelave);
        $this->writeById('contentForm:vno_dtm_dog_input', $this->datumIzdelave);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatumIzdelave()
    {
        return $this->datumIzdelave;
    }

    public function setVrstaDovoljenja($vrstaDovoljenja, $vstaDovoljenjaBtnId1 = null, $elementPrefix = null)
    {
        parent::setVrstaDovoljenja($vrstaDovoljenja, "contentForm:vno_w34_id_vrs_privolitve", "vno");
        return $this;
    }

    public function selectSeller(User $user)
    {
        $isciButtonId = 179;
        $izberiUserButtonId = 184;
        $dodajUserButtonId = 185;
        $potrdiAddingNewUserButtonId = 221;
        $potrdiAddingNewUserConfirmButtonId = 169;

        $this->clickById("contentForm:j_idt107");
        sleep(2);
        $this->selectUser($user, $isciButtonId, $izberiUserButtonId, $dodajUserButtonId, $potrdiAddingNewUserButtonId, $potrdiAddingNewUserConfirmButtonId);
        return $this;

    }

    public function setLetoIzdelave($letoIzdelave)
    {
        $this->writeById("contentForm:leto_izdelave", $letoIzdelave);
        return $this;
    }

    public function setDrzavaProizvajalka($drzavaProizvajalka)
    {
        $this->writeById("contentForm:vno_drz_id_n3_pro", $drzavaProizvajalka);
        return $this;
    }
}
