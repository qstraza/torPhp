<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 10:40
 */

namespace qstraza\torphp;


class TorIzdelanoOrozje extends TorProxy {
  private $datumIzdelave;

  /**
   * TorIzdelanoOrozje constructor.
   *
   * @param null|String $clientName
   *   Holds client's name.
   * @param null|\Facebook\WebDriver\Remote\RemoteWebDriver $seleniumDriver
   *   Holds selenium Driver.
   */
  public function __construct($clientName = null, $seleniumDriver = null) {
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
    // Go to corensponding menu.
//    $this->menuClick('TO20');
  }

  /**
   * @param mixed $datumIzdelave
   * @return TorProxy
   */
  public function setDatumIzdelave($datumIzdelave) {
    $this->datumIzdelave = $this->transformDate($datumIzdelave);
    $this->writeById('FM:vno_dtm_dog', $this->datumIzdelave);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumIzdelave() {
    return $this->datumIzdelave;
  }

}