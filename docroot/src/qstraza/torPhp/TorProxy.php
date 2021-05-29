<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 09:29
 */

namespace qstraza\torPhp;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class TorProxy {
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
  public function __construct($clientName) {
    $this->clientName = $clientName;
    $this->initBrowser();
  }

  protected function initBrowser() {
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
  private function goHome() {
    $this->seleniumDriver->get($this->url);
    try{
      if ($link = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('div.errorDetail a'))) {
        if ($link->getText() == 'Prisilna odjava uporabnika') {
          $link->click();
          return $this->goHome();
        }
      }
    }
    catch(\Exception $e) {}
  }
  /**
   * @return \Facebook\WebDriver\Remote\RemoteWebDriver
   */
  public function getSeleniumDriver() {
    return $this->seleniumDriver;
  }

  /**
   * @param \Facebook\WebDriver\Remote\RemoteWebDriver $seleniumDriver
   * @return TorProxy
   */
  public function setSeleniumDriver(RemoteWebDriver $seleniumDriver): TorProxy {
    $this->seleniumDriver = $seleniumDriver;
    return $this;
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * @param string $url
   * @return TorProxy
   */
  public function setUrl(string $url): TorProxy {
    $this->url = $url;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOrozjeDelOrozja() {
    return $this->orozjeDelOrozja;
  }

  /**
   * @param $orozjeDelOrozja
   * @return $this
   * @throws \Exception
   */
  public function setOrozjeDelOrozja($orozjeDelOrozja) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=vno_w60_id_orozja_dela] option").each(function() {var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Orožje' => '1',
      'Menjalna cev' => '2',
      'Vložna cev' => '3',
      'Zaklep' => '4',
      'Zaklepišče' => '5',
      'Ležišče naboja s cevjo' => '6',
      'Boben z ležišči naboja' => '7',
    ];

    if (isset($validOptions[$orozjeDelOrozja])) {
      $this->orozjeDelOrozja = $validOptions[$orozjeDelOrozja];
      $this->selectOption('FM:vno_w60_id_orozja_dela', $this->orozjeDelOrozja);
      return $this;
    }
    throw new \Exception('Orožje / del orožja "' . $orozjeDelOrozja . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getKategorijaOrozja() {
    return $this->kategorijaOrozja;
  }

  /**
   * @param mixed $kategorijaOrozja
   * @return TorProxy
   */
  public function setKategorijaOrozja($kategorijaOrozja) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=ui_w05_kategorija_orozja] option").each(function() {output+= "'" + jQuery(this).val() + "', "}); console.log(output)
    $validOptions = [
      'B-B1', 'B-B2', 'B-B3', 'B-B4', 'B-B5', 'B-B6', 'B-B7', 'C-C1', 'C-C2', 'C-C3', 'C-C4', 'D-D1', 'D-D2', 'D-D3', 'D-D4', 'D-D5', 'D-D6', 'D-D7', 'D-D8', 'D-D9', '9-99',
    ];
    if (in_array($kategorijaOrozja[0], ['B', 'C', 'D', '99'])) {
      $kategorijaOrozja = $kategorijaOrozja[0] . '-' . $kategorijaOrozja;
      if (in_array($kategorijaOrozja, $validOptions)) {
        $this->kategorijaOrozja = $kategorijaOrozja;
        $this->selectOption('FM:ui_w05_kategorija_orozja', $this->kategorijaOrozja);
        return $this;
      }
    }
    throw new \Exception('Kategorija orozja "' . $kategorijaOrozja . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getTipVrstaOrozja() {
    return $this->tipVrstaOrozja;
  }

  /**
   * @param mixed $tipVrstaOrozja
   * @return TorProxy
   */
  public function setTipVrstaOrozja($tipVrstaOrozja) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=ui_w01_vrsta_orozja] option").each(function() {var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Polavtomatska pištola' => '1-001',
      'Pištola' => '1-002',
      'Revolver' => '1-003',
      'PAP z risano cevjo' => '1-004',
      'PAP z gladko cevjo' => '1-005',
      'RP z risano cevjo' => '1-006',
      'RP z gladko cevjo' => '1-007',
      'Puška z risano cevjo' => '1-008',
      'Puška z gladko cevjo' => '1-009',
      'Kombinirana puška' => '1-010',
      'Puška' => '1-011',
      'Avtomatska pištola' => '1-012',
      'Mitraljez' => '1-013',
      'Puškomitraljez' => '1-014',
      'AP z risano cevjo' => '1-015',
      'AP z gladko cevjo' => '1-016',
      'Brzostrelka' => '1-017',
      'Možnar cevni' => '1-018',
      'Ostalo' => '1-019',
      'Lok' => '1-020',
      'Samostrel' => '1-021',
      'Boksar' => '1-022',
      'Bodalo' => '1-023',
      'Bajonet' => '1-024',
      'Buzdovan' => '1-025',
      'Električni paralizator - šoker' => '1-064',
      'Razpršilec - sprej' => '1-065',
      'Vložna cev' => '2-001',
      'Menjalna cev' => '2-002',
      'Vrtljivi boben' => '2-003',
      'Vse vrste streliva' => '3-001',
    ];
    if (isset($validOptions[$tipVrstaOrozja])) {
      $this->tipVrstaOrozja = $validOptions[$tipVrstaOrozja];
      $this->selectOption('FM:ui_w01_vrsta_orozja', $this->tipVrstaOrozja);
      return $this;
    }
    throw new \Exception('Vrsta orozja "' . $tipVrstaOrozja . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getZnamka() {
    return $this->znamka;
  }

  /**
   * @param mixed $znamka
   * @return TorProxy
   */
  public function setZnamka($znamka) {
    $this->znamka = $znamka;
    $this->writeById('FM:vno_znamka', $this->znamka);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getKaliber() {
    return $this->kaliber;
  }

  /**
   * @param mixed $kaliber
   * @return TorProxy
   */
  public function setKaliber($kaliber) {
    $this->kaliber = $kaliber;
    $this->writeById('FM:vno_kaliber', $this->kaliber);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOpomba() {
    return $this->opomba;
  }

  /**
   * @param mixed $opomba
   * @return TorProxy
   */
  public function setOpomba($opomba) {
    $this->opomba = $opomba;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * @param mixed $model
   * @return TorProxy
   */
  public function setModel($model) {
    $this->model = $model;
    $this->writeById('FM:vno_model', $this->model);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getTovarniskaStevilka() {
    return $this->tovarniskaStevilka;
  }

  /**
   * @param mixed $tovarniskaStevilka
   * @return TorProxy
   */
  public function setTovarniskaStevilka($tovarniskaStevilka) {
    $this->tovarniskaStevilka = $tovarniskaStevilka;
    $this->writeById('FM:vno_tov_stevilka', $this->tovarniskaStevilka);
    return $this;
  }

  private function changeFrame($frameName) {
    $this->seleniumDriver->switchTo()->defaultContent();
    $this->seleniumDriver->wait(10, 500)->until(
      WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($frameName)
    );
//    $frame = $this->seleniumDriver->findElement(WebDriverBy::cssSelector('frame[name=' . $frameName . ']'));
//    $this->seleniumDriver->switchTo()->frame($frame);
  }

  protected function changeToWorkingFrame() {
    $this->changeFrame('workingScreen');
  }

  private function changeToMenuFrame() {
    $this->changeFrame('menuLevel1');
  }

  /**
   * Clicks on a menu item.
   *
   * @param $code
   *   Defines which menu item to click. Check the code for possible values.
   * @throws \Exception
   */
  public function menuClick($code) {
    $validOptions = [
      'TO20' => 'FM:menu_item_to_20', // Izdelano orožje
      'TO21' => 'FM:menu_item_to_21', // Popravljeno, predelano in spremenljeno orožje
      'TO22' => 'FM:menu_item_to_22', // Nabavljeno, prodano orožje in priglasitveni listi
      'TO23' => 'FM:menu_item_to_21', // Nabavljeno in prodano strelivo
      'TO24' => 'FM:menu_item_to_24', // Skladiščenje in hramba orožja
      'TO10' => 'FM:menu_item_to_10', // Iskanje orožja in streliva
      'TO50' => 'FM:menu_item_to_50', // Pregled kupcev / dobaviteljev
    ];
    if (!isset($validOptions[$code])) {
      throw new \Exception('Koda za meni "' . $code . '", ni pravilna!');
    }
    $id = $validOptions[$code];
    $this->changeToMenuFrame();
//    $this->seleniumDriver->wait(10, 1000)->until(
//      WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id($id))
//    );
    $this->seleniumDriver->findElement(WebDriverBy::id($id))->click();
    $this->changeToWorkingFrame();
  }

  protected function selectOption($id, $val) {
    $element = $this->seleniumDriver->wait(10, 1000)->until(
      WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id))
    );
    $select = new WebDriverSelect($element);
    $select->selectByValue($val);
  }
  /**
   * @param $id
   * @return \Facebook\WebDriver\Remote\RemoteWebElement
   */
  protected function clickById($id) {
    return $this->getElementById($id)->click();
  }
  protected function writeById($id, $value) {
    return $this->clickById($id)
      ->sendKeys($value);
  }

  protected function getElementById($id) {
    $element = $this->seleniumDriver->wait(10, 1000)->until(
      WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id))
    );
    $element->getLocationOnScreenOnceScrolledIntoView();
    return $element;
  }

  protected function getElementByCssSelector($selector) {
    return $this->getSeleniumDriver()->findElement(WebDriverBy::cssSelector($selector));
  }

  protected function waitUntilElement($elementId) {
    return $this->getSeleniumDriver()->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($elementId)));
  }

  public function logOut() {
    $this->changeFrame('header');
    $this->clickById('TOR:logoff_link');
    return $this->seleniumDriver->close();
  }

  protected function getErrorStatus() {
    $this->changeToWorkingFrame();

    try {
      /** @var \Facebook\WebDriver\Remote\RemoteWebElement $errorStatus */
      $errorStatus = $this->seleniumDriver->wait(10, 1000)->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('span.error-status'))
      );
    }
    catch (\Exception $e) {
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
  public function getClientName() {
    return $this->clientName;
  }

  /**
   * @param mixed $clientName
   * @return TorProxy
   */
  public function setClientName($clientName) {
    $this->clientName = $clientName;
    return $this;
  }

  public function createTorIzdelanoOrozje() {
    return new TorIzdelanoOrozje($this->clientName, $this->seleniumDriver);
  }

  public function confirmPage() {
    $this->changeToWorkingFrame();
    $this->clickById('FM:PotrdiFooter');
    sleep(2);
    $this->changeToWorkingFrame();
    $this->clickById('FM:potrdiButton');
    sleep(2);
    $this->changeToWorkingFrame();
    return $this->getErrorStatus();
  }

  public function enableAllDisabledElements() {
    $this->getSeleniumDriver()->wait(10, 1000)->until(
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
  protected function enableElementById($id) {
//    $this->getSeleniumDriver()->executeScript('console.log(window.frames,window.frames.length);');
    return $this->getSeleniumDriver()->executeScript('this.window.document.getElementById("' . $id . '").disabled="";');
  }

  protected function transformDate($date) {
    $newDate = \DateTime::createFromFormat('j-M-Y', $date);
    return $newDate->format('d.m.Y');
  }

  public function goBack() {
    return $this->getSeleniumDriver()->navigate()->back();
  }

  public function getModelFromPage() {
      return $this->getElementById('FM:to22Read:vno_model')->getText();
  }

    public function getDobavnicaFromPage() {
        return $this->getElementById('FM:to22Read:vno_stv_dobavnice')->getText();
    }
}
