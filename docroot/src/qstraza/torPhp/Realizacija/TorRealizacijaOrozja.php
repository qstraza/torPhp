<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 16:45
 */

namespace qstraza\torPhp\Realizacija;


use Facebook\WebDriver\WebDriverBy;

class TorRealizacijaOrozja extends TorRealizacija {
  private $prevzemnikOrozja;
  private $stPriglasitvenegaLista;
  private $maticnaStPoslovalnice;
  private $datumPrevzemaVrnitveOrozja;
  private $datumIzdajePriglasitvenegaLista;
  private $vrstaDovoljenjaDrugo;

  /**
   * TorRealizacijaOrozja constructor.
   */
  public function __construct($clientName = null, $seleniumDriver = null) {
    parent::__construct($clientName, $seleniumDriver);
  }

  /**
   * @return mixed
   */
  public function getPrevzemnikOrozja() {
    return $this->prevzemnikOrozja;
  }

  /**
   * @param mixed $prevzemnikOrozja
   * @return TorRealizacijaOrozja
   */
  public function setPrevzemnikOrozja($prevzemnikOrozja) {
    $this->prevzemnikOrozja = $prevzemnikOrozja;
    $this->writeById('FM:rel_prevzemnik', $prevzemnikOrozja);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getStPriglasitvenegaLista() {
    return $this->stPriglasitvenegaLista;
  }

  /**
   * @param mixed $stPriglasitvenegaLista
   * @return TorRealizacijaOrozja
   */
  public function setStPriglasitvenegaLista($stPriglasitvenegaLista) {
    $this->stPriglasitvenegaLista = $stPriglasitvenegaLista;
    $this->writeById('FM:rel_ser_stv_prig_lista', $stPriglasitvenegaLista);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getMaticnaStPoslovalnice() {
    return $this->maticnaStPoslovalnice;
  }

  /**
   * @param mixed $maticnaStPoslovalnice
   * @return TorRealizacijaOrozja
   */
  public function setMaticnaStPoslovalnice($maticnaStPoslovalnice) {
    $this->maticnaStPoslovalnice = $maticnaStPoslovalnice;
    $this->writeById('FM:rel_pos_id_mat_stv', $maticnaStPoslovalnice);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumPrevzemaVrnitveOrozja() {
    return $this->datumPrevzemaVrnitveOrozja;
  }

  /**
   * @param mixed $datumPrevzemaVrnitveOrozja
   * @return TorRealizacijaOrozja
   */
  public function setDatumPrevzemaVrnitveOrozja($datumPrevzemaVrnitveOrozja) {
    $this->datumPrevzemaVrnitveOrozja = $this->transformDate($datumPrevzemaVrnitveOrozja);
    $this->writeById('FM:rel_dtm_prevzema', $this->datumPrevzemaVrnitveOrozja);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumIzdajePriglasitvenegaLista() {
    return $this->datumIzdajePriglasitvenegaLista;
  }

  /**
   * @param mixed $datumIzdajePriglasitvenegaLista
   * @return TorRealizacijaOrozja
   */
  public function setDatumIzdajePriglasitvenegaLista($datumIzdajePriglasitvenegaLista) {
    $this->datumIzdajePriglasitvenegaLista = $datumIzdajePriglasitvenegaLista;
    $this->writeById('FM:rel_dtm_izdaje_prig_lista', $datumIzdajePriglasitvenegaLista);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getVrstaDovoljenjaDrugo() {
    return $this->vrstaDovoljenjaDrugo;
  }

  public function setOpomba($opomba) {
    $this->writeById('FM:rel_opombe', $opomba);
    return $this;
  }

  /**
   * @param mixed $vrstaDovoljenjaDrugo
   * @return TorRealizacijaOrozja
   */
  public function setVrstaDovoljenjaDrugo($vrstaDovoljenjaDrugo) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=rel_w13_id_vrs_vlg_reg] option").each(function() {var name = /.*? - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Orožni list' => 'OL',
      'Orožni posestni list' => 'OP',
      'Dovoljenje za posest' => 'DP',
      'Dovoljenje za zbiranje orožja' => 'DZ',
      'Evropska orožna prepustnica' => 'EP',
      'Priglasitveni list' => 'PR',
      'Druga listina' => 'DL',
      'Listina ni potrebna' => 'NP',
      'Drugo' => '99',
    ];
    if (isset($validOptions[$vrstaDovoljenjaDrugo])) {
      $this->vrstaDovoljenjaDrugo = $validOptions[$vrstaDovoljenjaDrugo];
      $this->selectOption('FM:rel_w13_id_vrs_vlg_reg', $this->vrstaDovoljenjaDrugo);
      return $this;
    }
    throw new \Exception('Vrsta dovoljenja - Drugo "' . $vrstaDovoljenjaDrugo . '", ni pravilna!');
  }

  public function openItemBySerial($serijska) {
    $this->menuClick('TO10');
    sleep(4);
    $this->writeById('FM:vno_tov_stevilka', $serijska);
    $this->clickById('FM:IsciHeader');
    sleep(1);
    $error = $this->getErrorStatus();
    if ($error !== null) {
      return $error;
    }

//    $elements = $this->getSeleniumDriver()->findElements(WebDriverBy::cssSelector("table#FM\:to11DataTable tbody tr"));
    $elements = $this->getSeleniumDriver()->findElements(WebDriverBy::xpath('//table[@id="FM:to11DataTable"]/tbody/tr'));

    if (count($elements) > 1) {
      return "Več kot en zadetek za to serijsko!";
    }

    foreach ($elements as $element) {
      if (strpos($element->getText(), 'Realizacija') !== false) {
        return "Orožje je že realizirano!";
      }
    }
    sleep(2);
    $this->clickById('FM:to11DataTable:0:selected');
    $this->clickById('FM:RealizationHeader');
    $this->changeToWorkingFrame();
  }

}
