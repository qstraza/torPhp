<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 09:29
 */

namespace qstraza\torphp;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;
use qstraza\torphp\Data\OrozjeItem;
use qstraza\torphp\Data\User;
use qstraza\torphp\Realizacija\TorRealizacija;

class TorProxy
{
    private $url = 'https://etor.mnz.gov.si';
    private $seleniumHost = 'http://selenium:4444/wd/hub';
    private $orozjeDelOrozja;
    private $kategorijaOrozja;
    private $tipVrstaOrozja;
    private $znamka;
    private $kaliber;
    private $opomba;
    private $model;
    private $tovarniskaStevilka;
    private $clientName;
    /** @var  \Facebook\WebDriver\Remote\RemoteWebDriver */
    private $seleniumDriver;

    /**
     * TorProxy constructor.
     */
    public function __construct($clientName)
    {
        $this->clientName = $clientName;
        $this->initBrowser();
    }

    protected function initBrowser()
    {
        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxDriver::PROFILE, base64_encode(file_get_contents('/root/.mozilla/firefox/' . $this->clientName . '.zip')));
        $driver = RemoteWebDriver::create($this->seleniumHost, $capabilities);
        $driver->manage()->timeouts()->implicitlyWait(10);
        $driver->manage()->window()->maximize();
        $this->seleniumDriver = $driver;
        $this->goHome();
        if (strpos($this->seleniumDriver->getPageSource(), 'Potekla vam je seja ali ste bili prisiljeno odjavljeni iz aplikacije') !== false) {
            $this->seleniumDriver->close();
            $this->initBrowser();
        }
    }

    private function goHome()
    {
        $this->seleniumDriver->get($this->url);
        try {
            if ($link = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('div.errorDetail a'))) {
                if ($link->getText() == 'Prisilna odjava uporabnika') {
                    $link->click();
                    return $this->goHome();
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    public function getSeleniumDriver()
    {
        return $this->seleniumDriver;
    }

    /**
     * @param \Facebook\WebDriver\Remote\RemoteWebDriver $seleniumDriver
     * @return TorProxy
     */
    public function setSeleniumDriver(RemoteWebDriver $seleniumDriver): TorProxy
    {
        $this->seleniumDriver = $seleniumDriver;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return TorProxy
     */
    public function setUrl(string $url): TorProxy
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrozjeDelOrozja()
    {
        $selectElement = $this->getElementById("contentForm:vno_w60_id_orozja_dela_input");
        // Now pass it to WebDriverSelect constructor
        $select = new WebDriverSelect($selectElement);
        // Get value of first selected option:
        return $select->getFirstSelectedOption()->getAttribute('value');
    }

    /**
     * @param $orozjeDelOrozja
     * @return $this
     * @throws \Exception
     */
    public function setOrozjeDelOrozja($orozjeDelOrozja)
    {
        $orozjeDelOrozjaCode = substr(trim($orozjeDelOrozja), 0, 1);

        $this->clickById("contentForm:vno_w60_id_orozja_dela");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\:vno_w60_id_orozja_dela_items li'));
        foreach ($options as $option) {
            $optionText = substr(trim($option->getText()), 0, 1);
            if ($optionText == $orozjeDelOrozjaCode) {
                $this->clickById($option->getAttribute("id"));
                return $this;
            }
        }

        throw new \Exception("Orožje / del orožja je napačen: {$orozjeDelOrozja}");

    }

    /**
     * @return mixed
     */
    public function getKategorijaOrozja()
    {
        $selectElement = $this->getElementById("contentForm:ui_w05_kategorija_orozja_input");
        // Now pass it to WebDriverSelect constructor
        $select = new WebDriverSelect($selectElement);
        // Get value of first selected option:
        $value = $select->getFirstSelectedOption()->getAttribute('value');
        $value = explode("-", $value);
        return $value[1];
    }

    /**
     * @param mixed $kategorijaOrozja
     * @param $validOptions
     * @return TorProxy
     * @throws \Exception
     */
    public function setKategorijaOrozja($kategorijaOrozja)
    {
        $kategorijaOrozjaCode = explode(" ", trim($kategorijaOrozja), 2);
        $kategorijaOrozjaCode = $kategorijaOrozjaCode[0];

        $this->clickById("contentForm:ui_w05_kategorija_orozja");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\\:ui_w05_kategorija_orozja_items li'));
        foreach ($options as $option) {
            $optionText = explode(" ", trim($option->getText()), 2);
            $optionText = $optionText[0];

            if ($optionText == $kategorijaOrozjaCode) {
                $this->clickById($option->getAttribute(("id")));
                return $this;
            }
        }
        throw new \Exception("Kategorija orožja je napačna: {$kategorijaOrozja}");
    }

    /**
     * @return mixed
     */
    public function getTipVrstaOrozja()
    {
        $selectElement = $this->getElementById("contentForm:ui_w01_vrsta_orozja_input");
        // Now pass it to WebDriverSelect constructor
        $select = new WebDriverSelect($selectElement);
        // Get value of first selected option:
        return $select->getFirstSelectedOption()->getAttribute('value');
    }

    /**
     * @param mixed $tipVrstaOrozja
     * @return TorProxy
     */
    public function setTipVrstaOrozja($tipVrstaOrozja)
    {
        $tipVrstaOrozjaCode = substr(trim($tipVrstaOrozja), 0, 3);

        $this->clickById("contentForm:ui_w01_vrsta_orozja");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#contentForm\\:ui_w01_vrsta_orozja_items li'));
        foreach ($options as $option) {
            $optionText = substr(trim($option->getText()), 0, 3);
            if ($optionText == $tipVrstaOrozjaCode) {
                $this->clickById($option->getAttribute("id"));
                return $this;
            }
        }

        throw new \Exception("Tip/Vrsta orožja je napačna: {$tipVrstaOrozja}");
    }

    /**
     * @return mixed
     */
    public function getZnamka()
    {
        return $this->getElementById("contentForm:vno_znamka")->getAttribute('value');
    }

    /**
     * @param mixed $znamka
     * @return TorProxy
     */
    public function setZnamka($znamka)
    {
        $this->znamka = $znamka;
        $this->writeById('contentForm:vno_znamka', $this->znamka);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKaliber()
    {
        return $this->getElementById("contentForm:vno_kaliber")->getAttribute('value');
    }

    /**
     * @param mixed $kaliber
     * @return TorProxy
     */
    public function setKaliber($kaliber)
    {
        $this->kaliber = $kaliber;
        $this->writeById('contentForm:vno_kaliber', $this->kaliber);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOpomba()
    {
        return $this->opomba;
    }

    /**
     * @param mixed $opomba
     * @return TorProxy
     */
    public function setOpomba($opomba)
    {
        $this->opomba = $opomba;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->getElementById("contentForm:vno_model")->getAttribute('value');
    }

    /**
     * @param mixed $model
     * @return TorProxy
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->writeById('contentForm:vno_model', $this->model);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTovarniskaStevilka()
    {
        return $this->tovarniskaStevilka;
    }

    /**
     * @param mixed $tovarniskaStevilka
     * @return TorProxy
     */
    public function setTovarniskaStevilka($tovarniskaStevilka)
    {
        $this->tovarniskaStevilka = $tovarniskaStevilka;
        $this->writeById('contentForm:vno_tov_stevilka', $this->tovarniskaStevilka);
        return $this;
    }

    private function changeFrame($frameName)
    {
        return;
        $this->seleniumDriver->switchTo()->defaultContent();
        $this->seleniumDriver->wait(2, 50)->until(
            WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($frameName)
        );
//    $frame = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('frame[name=' . $frameName . ']'));
//    $this->seleniumDriver->switchTo()->frame($frame);
    }

    protected function changeToWorkingFrame()
    {
        $this->changeFrame('workingScreen');
    }

    private function changeToMenuFrame()
    {
        $this->changeFrame('menuLevel1');
    }

    /**
     * Clicks on a menu item.
     *
     * @param $code
     *   Defines which menu item to click. Check the code for possible values.
     * @throws \Exception
     */
    public function menuClick($code)
    {
        $validOptions = [
            'izdelano orozje' => 20, // Izdelano orožje
            'popravljeno oroje' => 21, // Popravljeno, predelano in spremenljeno orožje
            'nabavljeno prodano orozje' => 22, // Nabavljeno, prodano orožje in priglasitveni listi
            'nabavljeno prodano strelivo' => 23, // Nabavljeno in prodano strelivo
            'hramba' => 24, // Skladiščenje in hramba orožja
            'izdelano strelivo' => 26, // Izdelano strelivo
            'iskanje' => 10, // Iskanje orožja in streliva
            'sifrant' => 50, // Šifrant kupcev / dobaviteljev
        ];
        if (!isset($validOptions[$code])) {
            throw new \Exception("Koda za meni {$code}, ni pravilna!");
        }
        $suffix = "create";
//        if ($validOptions[$code] == 10 or $validOptions[$code] == 50) {
//            $suffix = "index";
//        }
        if ($validOptions[$code] == 10) {
            $this->executeJS("PrimeFaces.addSubmitParam('headerForm',{'headerForm:j_idt31':'headerForm:j_idt31'}).submit('headerForm');");
        }
        else {
            $this->seleniumDriver->get("https://etor.mnz.gov.si/tor/to{$validOptions[$code]}/{$suffix}.xhtml");
        }
        sleep(2);
        return;
    }

    protected function selectOption($id, $val)
    {
        $element = $this->seleniumDriver->wait(3, 100)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id))
        );
        $select = new WebDriverSelect($element);
        $select->selectByValue($val);
    }

    /**
     * @param $id
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function clickById($id)
    {
        return $this->getElementById($id)->click();
    }

    protected function writeById($id, $value)
    {
        $this->getElementById($id)->clear();
        return $this->clickById($id)
            ->sendKeys($value);
    }

    protected function getElementById($id)
    {
        $element = $this->seleniumDriver->wait(3, 100)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id))
        );
//    $element->getLocationOnScreenOnceScrolledIntoView();
        return $element;
    }

    public function getElementByCssSelector($selector)
    {
        return $this->getSeleniumDriver()->findElement(WebDriverBy::cssSelector($selector));
    }
    public function getElementsByCssSelector($selector)
    {
        return $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector($selector));
    }
    protected function waitUntilElement($elementId)
    {
        return $this->getSeleniumDriver()->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($elementId)));
    }

    protected function wait($cssSelector)
    {
        $this->seleniumDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector($cssSelector)
            )
        );
    }

    public function logOut()
    {
        $this->executeJS("PrimeFaces.addSubmitParam('headerForm',{'headerForm:j_idt21':'headerForm:j_idt21'}).submit('headerForm');");

//        $this->getElementByCssSelector('.tor-user-dropdown')->click();
//        $this->clickById(('headerForm:j_idt21'));
        return $this->seleniumDriver->close();
    }

    public function getErrorStatus()
    {
//    $this->changeToWorkingFrame();

        try {
//      /** @var \Facebook\WebDriver\Remote\RemoteWebElement $errorStatus */
//      $errorStatus = $this->seleniumDriver->wait(1, 100)->until(
//        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('span.error-status'))
//      );
            $errorStatus = $this->seleniumDriver->findElement(WebDriverBy::id('contentForm:contentFormGrowl_container'));
        } catch (\Exception $e) {
            // No error, because no error-status element was found.
            return null;
        }
//    if ($errorStatus = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('span.error-status'))) {
        if ($errorStatus) {
            if ($errorText = $errorStatus->getText()) {
                return $errorText;
            }
        }
        return null;
    }

    protected function getWarningStatus()
    {
        $this->changeToWorkingFrame();

        try {
//            /** @var \Facebook\WebDriver\Remote\RemoteWebElement $errorStatus */
//            $errorStatus = $this->seleniumDriver->wait(1, 100)->until(
//                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('span.warning-status'))
//            );
            $errorStatus = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('span.warning-status'));
        } catch (\Exception $e) {
            // No error, because no error-status element was found.
            return null;
        }
//    if ($errorStatus = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('span.error-status'))) {
        if ($errorStatus) {
            if ($errorText = $errorStatus->getText()) {
                return $errorText;
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @param mixed $clientName
     * @return TorProxy
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function createTorIzdelanoOrozje()
    {
        return new TorIzdelanoOrozje($this->clientName, $this->seleniumDriver);
    }

    public function confirmPage($potrdiButtonSelector, $confirmButtonSelector)
    {
        sleep(1);;
        $this->getElementByCssSelector($potrdiButtonSelector)->click();
        sleep(0.5);
        $this->getElementByCssSelector($confirmButtonSelector)->click();
        sleep(2);
        try {
            $ok = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('.ui-dialog-titlebar span.ui-dialog-title'));
            if ($ok->getText() == 'Podatki so bili uspešno shranjeni!') {
                return null;
            } else {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $errorStatus = $this->getErrorStatus();
            if ($errorStatus) {
                return $errorStatus;
            }
            $warningStatus = $this->getWarningStatus();
            if ($warningStatus) {
                return $warningStatus;
            }
            return null;
        }
    }

    public function enableAllDisabledElements()
    {
        $this->getSeleniumDriver()->wait(3, 100)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('FM:to22Read:vno_w60_id_orozja_dela'))
        );
        $this->getSeleniumDriver()->executeScript('
      var selects = this.window.document.getElementsByTagName("select");
      var inputs = this.window.document.getElementsByTagName("input");
      for (var i=0;i<selects.length;i++) {selects[i].disabled="";}
      for (var i=0;i<inputs.length;i++) {inputs[i].disabled="";}
      console.log("executed");
    ');

        echo "executed   \n\n";
    }

    protected function enableElementById($id)
    {
//    $this->getSeleniumDriver()->executeScript('console.log(window.frames,window.frames.length);');
        return $this->getSeleniumDriver()->executeScript('this.window.document.getElementById("' . $id . '").disabled="";');
    }

    protected function transformDate($date)
    {
        if (!$date) {
            throw new \Exception("Datum je napačen: " . $date);
        }
        try {
//            $newDate = \DateTime::createFromFormat('j-M-Y', $date);
            // Converting excel int timestamp to unix timestamp in seconds.
            $date = ($date - 25569) * 86400;
            $newDate = new \DateTime();
            $newDate->setTimestamp($date);
            return $newDate->format('d.m.Y');
        }
        catch (\Exception $e) {
            throw new \Exception("Datum je napačen: " . $date);
        }

    }

    public function goBack()
    {
        return $this->getSeleniumDriver()->navigate()->back();
    }

    public function getModelFromPage()
    {
        return $this->getElementById('FM:to22Read:vno_model')->getText();
    }

    public function getDobavnicaFromPage()
    {
        return $this->getElementById('FM:to22Read:vno_stv_dobavnice')->getText();
    }

    public function executeJS($js)
    {
        return $this->getSeleniumDriver()->executeScript($js);
    }

    private function createUser(User $user) {
        $dodajUserButtonSelector = "#kupecDialogForm > div > div > div > div.ui-toolbar.ui-widget.ui-widget-header.ui-corner-all > div > button:nth-child(2)";
        $potrdiAddingNewUserButtonSelector = "#to50DialogForm > div > div > div > div.ui-toolbar.ui-widget.ui-widget-header.ui-corner-all > div > button:nth-child(2)";
        $potrdiAddingNewUserConfirmButtonSelector = "#contentForm\\:confirmDialog > div.ui-dialog-footer > button.ui-confirmdialog-yes";

        $this->getElementByCssSelector($dodajUserButtonSelector)->click();
        sleep(2);
        $this->setVrstaKupca($user->getVrstaKupca());
        $vrstaKupcaCode = substr($user->getVrstaKupca(), 0, 1);
        sleep(1);
        // "2 - Poslovni subjekt", "3 - Trgovec z orožjem"
        if (in_array($vrstaKupcaCode, ["2", "3"])) {
            $this->writeById("to50DialogForm:dik_ds_ms", $user->getDavcna());
        }
        $this->writeById("to50DialogForm:dik_subjekt", $user->getIme());
        $this->writeById("to50DialogForm:dik_drzava", $user->getDrzava());
        $this->writeById("to50DialogForm:dik_naselje", $user->getMesto());
        $this->writeById("to50DialogForm:dik_ulica", $user->getNaslov());

        $this->getElementByCssSelector($potrdiAddingNewUserButtonSelector)->click();
        sleep(0.5);
        $this->getElementByCssSelector($potrdiAddingNewUserConfirmButtonSelector)->click();

        if ($this->getErrorStatus()) {
            throw new \Exception("Napaka pri dodajanju novega uporabnika {$user->getIme()}");
        }
        sleep(1);
        return $this;
    }

    public function selectUser(User $user) {
        $isciButtonSelector = "#kupecDialogForm > div > div > div > div.ui-fluid > div.ui-toolbar.ui-widget.ui-widget-header.ui-corner-all > div > button";
        $izberiUserButtonSelector = "#kupecDialogForm > div > div > div > div.ui-toolbar.ui-widget.ui-widget-header.ui-corner-all > div > button:nth-child(1)";

        // Write title to search.
        $this->writeById("kupecDialogForm:kupec_dialog_rel_subjekt", $user->getIme());
        // Click search button.
        $this->getElementByCssSelector($isciButtonSelector)->click();
        sleep(2);
        // If we do not have any hits, we need to create a new user.
        if ($this->getElementById("kupecDialogForm:kupecDataTable_data")->getText() == 'Ni zapisov.') {
            return $this->createUser($user);
        } // We have hits.
        else {
            // Checking if we have only one hit.
            if ($this->getElementByCssSelector('#kupecDialogForm\\:kupecDataTable_paginator_bottom .ui-paginator-current')->getText() == '1 - 1 od 1') {
                $this->getElementByCssSelector('#kupecDialogForm\\:kupecDataTable_data td')->click();
                $this->getElementByCssSelector($izberiUserButtonSelector)->click();
                // We have one hit - selecting it.
                return $this;
            } // We have multiple hits. We need to search by the street.
            else {
                // Selecting 50 hits per page.
                $this->getSeleniumDriver()
                    ->findElement(WebDriverBy::cssSelector('select[name="kupecDialogForm\\:kupecDataTable_rppDD"] option[value="50"]'))
                    ->click();
                sleep(1);
                // Getting all hits in an array.
                $elements = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#kupecDialogForm\\:kupecDataTable_data tr'));

                // Checking if any of the hits have the correct street name. If we find it, we select it.
                foreach ($elements as $element) {
                    if (strpos(strtolower($element->getText()), strtolower($user->getNaslov())) !== false) {
                        $element->findElement(WebDriverBy::cssSelector("td"))->click();
                        $this->getElementByCssSelector($izberiUserButtonSelector)->click();
                        return $this;
                    }
                }
                $this->createUser($user);
                return $this->selectUser($user);
            }
        }
    }
    /**
     * @param mixed $vrstaKupca
     * @return TorProxy
     */
    public function setVrstaKupca($vrstaKupca) {
        $vrstaKupcaCode = substr(trim($vrstaKupca), 0, 1);

        $this->clickById("to50DialogForm:dik_w64_id_vrste_subjekta");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector('#to50DialogForm\:dik_w64_id_vrste_subjekta_items li'));
        foreach ($options as $option) {
            $optionText = substr(trim($option->getText()), 0, 1);
            if ($optionText == $vrstaKupcaCode) {
                $this->clickById($option->getAttribute("id"));
                return $this;
            }
        }

        throw new \Exception("Vrsta kupca ni prava: {$vrstaKupca}");
    }

    /**
     * @param mixed $vrstaDovoljenja
     * @return TorRealizacija
     */
    public function setVrstaDovoljenja($vrstaDovoljenja, $vstaDovoljenjaBtnId1, $elementPrefix)
    {
        if (!$vrstaDovoljenja) {
            throw new \Exception('Manjka podatek o vrsti dovoljenja');
        }
        $vrstaDovoljenjaCode = explode(" ", trim($vrstaDovoljenja), 2);
        $vrstaDovoljenjaCode = $vrstaDovoljenjaCode[0];

        $vstaDovoljenjaBtnId2 = 13;

        $this->clickById($vstaDovoljenjaBtnId1);
        sleep(0.2);
        $vstaDovoljenjaBtnId1 = addcslashes($vstaDovoljenjaBtnId1, ":");
        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector("#{$vstaDovoljenjaBtnId1}_items li"));
        foreach ($options as $option) {
            $optionText = explode(" ", trim($option->getText()), 2);
            $optionText = $optionText[0];

            if ($optionText == $vrstaDovoljenjaCode) {
                $this->clickById($option->getAttribute("id"));
                if ($vrstaDovoljenjaCode != "99") {
                    return $this;
                }
            }
            // We are at the last item, so we need to select it.
            elseif($optionText == "99"){
                $this->clickById($option->getAttribute("id"));
            }
        }

        $this->clickById("contentForm:{$elementPrefix}_w{$vstaDovoljenjaBtnId2}_id_vrs_vlg_reg");
        sleep(0.2);

        $options = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector("#contentForm\:{$elementPrefix}_w{$vstaDovoljenjaBtnId2}_id_vrs_vlg_reg_items li"));
        foreach ($options as $option) {
            $optionText = explode(" ", trim($option->getText()), 2);
            $optionText = $optionText[0];

            if ($optionText == $vrstaDovoljenjaCode) {
                $this->clickById($option->getAttribute("id"));
                return $this;
            }
        }

        throw new \Exception('Vrsta dovoljenja "' . $vrstaDovoljenja . '", ni pravilna!');
    }
}
