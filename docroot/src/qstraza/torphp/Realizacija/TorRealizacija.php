<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 16:44
 */

namespace qstraza\torphp\Realizacija;


use qstraza\torphp\TorProxy;

class TorRealizacija extends TorProxy {
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
//    $this->menuClick('TO10');
  }

  /**
   * @return mixed
   */
  public function getVrnitev() {
    return $this->vrnitev;
  }

  /**
   * @param mixed $vrnitev
   * @return TorRealizacija
   */
  public function setVrnitev($vrnitev) {
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
  public function getVrstaKupca() {
    return $this->vrstaKupca;
  }

  /**
   * @param mixed $vrstaKupca
   * @return TorRealizacija
   */
  public function setVrstaKupca($vrstaKupca) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=rel_w64_id_vrste_subjekta] option").each(function(index) {if (index==0) return; var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Posameznik' => '1',
      'Poslovni subjekt' => '2',
      'Trgovec z orožjem' => '3',
    ];
    if (isset($validOptions[$vrstaKupca])) {
      $this->vrstaKupca = $validOptions[$vrstaKupca];
      $this->enableElementById("FM:rel_w64_id_vrste_subjekta");
      $this->selectOption('FM:rel_w64_id_vrste_subjekta', $this->vrstaKupca);
      return $this;
    }
    throw new \Exception('Vrsta kupca "' . $vrstaKupca . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getDrzavaProdaje() {
    return $this->drzavaProdaje;
  }

  /**
   * @param mixed $drzavaProdaje
   * @return TorRealizacija
   */
  public function setDrzavaProdaje($drzavaProdaje) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=rel_w63_id_drzave_prodaje] option").each(function() {var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Slovenija' => '1',
      'Izvoz' => '2',
      'Transfer v EU' => '3',
    ];
    if (isset($validOptions[$drzavaProdaje])) {
      $this->drzavaProdaje = $validOptions[$drzavaProdaje];
      $this->selectOption('FM:rel_w63_id_drzave_prodaje', $this->drzavaProdaje);
      return $this;
    }
    throw new \Exception('Država prodaje "' . $drzavaProdaje . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getDrzava() {
    return $this->drzava;
  }

  /**
   * @param mixed $drzava
   * @return TorRealizacija
   */
  public function setDrzava($drzava) {
    $this->drzava = $drzava;
    $this->enableElementById("FM:rel_drzava_prod");
    $this->writeById('FM:rel_drzava_prod', $drzava);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getKupecNazivPriimekIme() {
    return $this->kupecNazivPriimekIme;
  }

  /**
   * @param mixed $kupecNazivPriimekIme
   * @return TorRealizacija
   */
  public function setKupecNazivPriimekIme($kupecNazivPriimekIme) {
    $this->kupecNazivPriimekIme = $kupecNazivPriimekIme;
    $this->writeById('FM:rel_subjekt', $kupecNazivPriimekIme);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getMaticnaDavcnaPoslovnegaSubjekta() {
    return $this->maticnaDavcnaPoslovnegaSubjekta;
  }

  /**
   * @param mixed $maticnaDavcnaPoslovnegaSubjekta
   * @return TorRealizacija
   */
  public function setMaticnaDavcnaPoslovnegaSubjekta($maticnaDavcnaPoslovnegaSubjekta) {
    $this->maticnaDavcnaPoslovnegaSubjekta = $maticnaDavcnaPoslovnegaSubjekta;
    // $this->writeById('FM:rel_pos_id_mat_stv_kup', $maticnaDavcnaPoslovnegaSubjekta);
    $this->writeById('FM:rel_ds_ms', $maticnaDavcnaPoslovnegaSubjekta);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumProdaje() {
    return $this->datumProdaje;
  }

  /**
   * @param mixed $datumProdaje
   * @return TorRealizacija
   */
  public function setDatumProdaje($datumProdaje) {
    $this->datumProdaje = $this->transformDate($datumProdaje);
    $this->writeById('FM:rel_dtm_dog', $this->datumProdaje);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getNaselje() {
    return $this->naselje;
  }

  /**
   * @param mixed $naselje
   * @return TorRealizacija
   */
  public function setNaselje($naselje) {
    $this->naselje = $naselje;
    $this->enableElementById("FM:rel_obcina");
    $this->writeById('FM:rel_obcina', $naselje);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getUlica() {
    return $this->ulica;
  }

  /**
   * @param mixed $ulica
   * @return TorRealizacija
   */
  public function setUlica($ulica) {
    $this->ulica = $ulica;
    $this->enableElementById("FM:rel_naselje");
    $this->writeById('FM:rel_naselje', $ulica);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getHst() {
    return $this->hst;
  }

  /**
   * @param mixed $hst
   * @return TorRealizacija
   */
  public function setHst($hst) {
    $this->hst = $hst;
    $this->enableElementById("FM:rel_ulc_hst");
    $this->writeById('FM:rel_ulc_hst', $hst);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getVrstaDovoljenja() {
    return $this->vrstaDovoljenja;
  }

  /**
   * @param mixed $vrstaDovoljenja
   * @return TorRealizacija
   */
  public function setVrstaDovoljenja($vrstaDovoljenja) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=rel_w67_id_vrs_dovoljenja] option").each(function() {var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Dovoljenje za iznos orožja iz RS v EU' => '02',
      'Dovoljenje za izvoz orožja' => '04',
      'Dovoljenje za nabavo orožja' => '05',
      'Drugo' => '99',
    ];
    if (isset($validOptions[$vrstaDovoljenja])) {
      $this->vrstaDovoljenja = $validOptions[$vrstaDovoljenja];
      $this->selectOption('FM:rel_w67_id_vrs_dovoljenja', $this->vrstaDovoljenja);
      return $this;
    }
    throw new \Exception('Vrsta dovoljenja "' . $vrstaDovoljenja . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getOrganIzdaje() {
    return $this->organIzdaje;
  }

  /**
   * @param mixed $organIzdaje
   * @return TorRealizacija
   */
  public function setOrganIzdaje($organIzdaje) {
    $this->organIzdaje = $organIzdaje;
    $this->writeById('FM:rel_organ_izdaje', $organIzdaje);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getStevilkaListine() {
    return $this->stevilkaListine;
  }

  /**
   * @param mixed $stevilkaListine
   * @return TorRealizacija
   */
  public function setStevilkaListine($stevilkaListine) {
    $this->stevilkaListine = $stevilkaListine;
    $this->writeById('FM:rel_stv_listine', $stevilkaListine);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumIzdajeListine() {
    return $this->datumIzdajeListine;
  }

  /**
   * @param mixed $datumIzdajeListine
   * @return TorRealizacija
   */
  public function setDatumIzdajeListine($datumIzdajeListine) {
    $this->datumIzdajeListine = $datumIzdajeListine;
    $this->writeById('FM:rel_dtm_izdaje_lst', $datumIzdajeListine);
    return $this;
  }

}
