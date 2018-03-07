<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 18/12/2017
 * Time: 08:45
 */

namespace qstraza\torPhp\Data;

use Google\Spreadsheet\ListEntry;
use qstraza\torPhp\Realizacija\TorRealizacijaOrozja;
use qstraza\torPhp\TorIzdelanoOrozje;

class OrozjeItem {
  protected $date;
  protected $ime;
  protected $naslov;
  protected $mesto;
  protected $vrstaKupca;
  protected $vrstaDovoljenja;
  protected $davcna;
  protected $organIzdaje;
  protected $stevilkaListine;
  protected $datumIzdajeListine;
  protected $stPrigasitvenegaLista;
  protected $vrstaOrozja;
  protected $proizvajalec;
  protected $model;
  protected $cal;
  protected $serijska;
  protected $realiziranTor;
  protected $drzava;
  protected $isEU;
  protected $opombaTor;
  protected $kategorija;
  protected $spreadsheetEntry;
  protected $isPodjetje = false;
  protected $returnMessage;
  protected $error = false;
  protected $izdelan = false;
  protected $orozjeDelOrozja;

    /**
   * OrozjeItem constructor.
   */
  public function __construct() {
  }

  public function realiziraj(TorRealizacijaOrozja $tor) {
    $error = $tor->openItemBySerial($this->getSerijska());
    if ($error !== null) {
      $this->returnMessage = $error;
      $this->error = true;
      return;
    }

//    $tor->enableAllDisabledElements();
    if ($this->isEU) {
      if ($this->drzava == 'Slovenia') {
        $tor->setDrzavaProdaje('Slovenia');
        $tor->setKupecNazivPriimekIme($this->getIme());
        $tor->setVrstaKupca($this->getIsPodjetje() ? 'Trgovec z orožjem' : 'Posameznik');
      }
      else {
        sleep(3);
        $tor->setDrzavaProdaje('Transfer v EU');
        sleep(2);
        $tor->setKupecNazivPriimekIme($this->getIme());
        sleep(2);
        $tor->setVrstaKupca($this->getIsPodjetje() ? 'Trgovec z orožjem' : 'Posameznik');
        sleep(1);
        $tor->setDrzava($this->getDrzava());
      }
    }
    else {
      $tor->setDrzavaProdaje('Izvoz');
      $tor->setKupecNazivPriimekIme($this->getIme());
      $tor->setVrstaKupca($this->getIsPodjetje() ? 'Trgovec z orožjem' : 'Posameznik');
      $tor->setDrzava($this->getDrzava());
    }

//    if ($this->getIsPodjetje()) {
//      $tor->setMaticnaDavcnaPoslovnegaSubjekta($this->getDavcna());
//    }
    $tor->setNaselje($this->getMesto());
    $tor->setUlica($this->getNaslov());
    $tor->setHst('/');

    switch ($this->getVrstaDovoljenja()) {
      case 'brez':
        $tor->setVrstaDovoljenja('Drugo');
        $tor->setVrstaDovoljenjaDrugo('Listina ni potrebna');
        break;
      case 'iznos v EU':
        $tor->setVrstaDovoljenja('Dovoljenje za iznos orožja iz RS v EU');
        break;
      case 'izvoz izven EU':
        $tor->setVrstaDovoljenja('Dovoljenje za izvoz orožja');
        break;
      case 'nabavno dovoljenje':
      $tor->setVrstaDovoljenja('Dovoljenje za nabavo orožja');
        break;
      case 'priglasitev':
        $tor->setVrstaDovoljenja('Drugo');
        $tor->setVrstaDovoljenjaDrugo('Priglasitveni list');
        break;
    }
    if ($this->getVrstaDovoljenja() != 'brez') {
      $tor->setOrganIzdaje($this->getOrganIzdaje());
      $tor->setStevilkaListine($this->getStevilkaListine());
      $tor->setDatumIzdajeListine($this->getDatumIzdajeListine());
    }
    $tor->setDatumProdaje($this->date);
//    $tor->setPrevzemnikOrozja($this->ime);
//    $tor->setDatumPrevzemaVrnitveOrozja($this->date);
//    $tor->setStPriglasitvenegaLista($this->getStPrigasitvenegaLista());
    $tor->setOpomba($this->getOpombaTor());

    $error = $tor->confirmPage();
    echo $error;
    if ($error !== null) {
      // We have an error
      $this->returnMessage = $error;
      $this->error = true;
    }
    else {
      // All good.
      $this->returnMessage = "Realizirano";
    }
  }

  public function izdelaj(TorIzdelanoOrozje $tor) {
    $tor->menuClick('TO20');
    $tor->setOrozjeDelOrozja($this->getOrozjeDelOrozja());
    $tor->setKategorijaOrozja($this->getKategorija());
    $tor->setTipVrstaOrozja($this->getVrstaOrozja());
    $tor->setZnamka($this->getProizvajalec());
    $tor->setModel($this->getModel());
    $tor->setKaliber($this->getCal());
    $tor->setTovarniskaStevilka($this->getSerijska());
    $tor->setDatumIzdelave($this->getDate());

    $error = $tor->confirmPage();
    if ($error !== null) {
      // We have an error.
      $this->returnMessage = $error;
      $this->error = true;
    }
    else {
      // All good.
      $this->returnMessage = "Izdelano";
    }
  }

  /**
   * @return mixed
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * @param mixed $date
   * @return OrozjeItem
   */
  public function setDate($date) {
    $this->date = $date;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getIme() {
    return $this->ime;
  }

  /**
   * @param mixed $ime
   * @return OrozjeItem
   */
  public function setIme($ime) {
    $this->ime = $ime;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getNaslov() {
    return $this->naslov;
  }

  /**
   * @param mixed $naslov
   * @return OrozjeItem
   */
  public function setNaslov($naslov) {
    $this->naslov = $naslov;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getMesto() {
    return $this->mesto;
  }

  /**
   * @param mixed $mesto
   * @return OrozjeItem
   */
  public function setMesto($mesto) {
    $this->mesto = $mesto;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getVrstaKupca() {
    return $this->vrstaKupca;
  }

  /**
   * @param mixed $vrstaKupca
   * @return OrozjeItem
   */
  public function setVrstaKupca($vrstaKupca) {
    $this->vrstaKupca = $vrstaKupca;
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
   * @return OrozjeItem
   */
  public function setVrstaDovoljenja($vrstaDovoljenja) {
    $this->vrstaDovoljenja = $vrstaDovoljenja;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDavcna() {
    return $this->davcna;
  }

  /**
   * @param mixed $davcna
   * @return OrozjeItem
   */
  public function setDavcna($davcna) {
    $this->davcna = $davcna;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOrganIzdaje() {
    return $this->organIzdaje;
  }

  /**
   * @param mixed $organIzdaje
   * @return OrozjeItem
   */
  public function setOrganIzdaje($organIzdaje) {
    $this->organIzdaje = $organIzdaje;
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
   * @return OrozjeItem
   */
  public function setStevilkaListine($stevilkaListine) {
    $this->stevilkaListine = $stevilkaListine;
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
   * @return OrozjeItem
   */
  public function setDatumIzdajeListine($datumIzdajeListine) {
    $this->datumIzdajeListine = $datumIzdajeListine;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getStPrigasitvenegaLista() {
    return $this->stPrigasitvenegaLista;
  }

  /**
   * @param mixed $stPrigasitvenegaLista
   * @return OrozjeItem
   */
  public function setStPrigasitvenegaLista($stPrigasitvenegaLista) {
    $this->stPrigasitvenegaLista = $stPrigasitvenegaLista;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getVrstaOrozja() {
    return $this->vrstaOrozja;
  }

  /**
   * @param mixed $vrstaOrozja
   * @return OrozjeItem
   */
  public function setVrstaOrozja($vrstaOrozja) {
    $this->vrstaOrozja = $vrstaOrozja;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getProizvajalec() {
    return $this->proizvajalec;
  }

  /**
   * @param mixed $proizvajalec
   * @return OrozjeItem
   */
  public function setProizvajalec($proizvajalec) {
    $this->proizvajalec = $proizvajalec;
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
   * @return OrozjeItem
   */
  public function setModel($model) {
    $this->model = $model;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getCal() {
    return $this->cal;
  }

  /**
   * @param mixed $cal
   * @return OrozjeItem
   */
  public function setCal($cal) {
    $this->cal = $cal;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getSerijska() {
    return $this->serijska;
  }

  /**
   * @param mixed $serijska
   * @return OrozjeItem
   */
  public function setSerijska($serijska) {
    $this->serijska = $serijska;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getRealiziranTor() {
    return $this->realiziranTor;
  }

  /**
   * @param mixed $realiziranTor
   * @return OrozjeItem
   */
  public function setRealiziranTor($realiziranTor) {
    $this->realiziranTor = $realiziranTor;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDrzava() {
    return $this->drzava;
  }

  /**
   * @param mixed $drzava
   * @return OrozjeItem
   */
  public function setDrzava($drzava) {
    $this->drzava = $drzava;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getIsEU() {
    return $this->isEU;
  }

  /**
   * @param mixed $isEU
   * @return OrozjeItem
   */
  public function setIsEU($isEU) {
    $this->isEU = $isEU;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOpombaTor() {
    return $this->opombaTor;
  }

  /**
   * @param mixed $opombaTor
   * @return OrozjeItem
   */
  public function setOpombaTor($opombaTor) {
    $this->opombaTor = $opombaTor;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getKategorija() {
    return $this->kategorija;
  }

  /**
   * @param mixed $kategorija
   * @return OrozjeItem
   */
  public function setKategorija($kategorija) {
    $this->kategorija = $kategorija;
    return $this;
  }

  /**
   * @return \Google\Spreadsheet\ListEntry
   */
  public function getSpreadsheetEntry() {
    return $this->spreadsheetEntry;
  }

  /**
   * @param \Google\Spreadsheet\ListEntry $spreadsheetEntry
   * @return OrozjeItem
   */
  public function setSpreadsheetEntry(ListEntry $spreadsheetEntry) {
    $this->spreadsheetEntry = $spreadsheetEntry;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getIsPodjetje() {
    return $this->isPodjetje;
  }

  /**
   * @param mixed $isPodjetje
   * @return OrozjeItem
   */
  public function setIsPodjetje($isPodjetje) {
    $this->isPodjetje = $isPodjetje;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getReturnMessage() {
    return $this->returnMessage;
  }

  /**
   * @return bool
   */
  public function isError(): bool {
    return $this->error;
  }

  /**
   * @return bool
   */
  public function isIzdelan(): bool {
    return $this->izdelan;
  }

  /**
   * @param bool $izdelan
   * @return OrozjeItem
   */
  public function setIzdelan(bool $izdelan): OrozjeItem {
    $this->izdelan = $izdelan;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOrozjeDelOrozja() {
    return $this->orozjeDelOrozja;
  }

  /**
   * @param mixed $orozjeDelOrozja
   * @return OrozjeItem
   */
  public function setOrozjeDelOrozja($orozjeDelOrozja) {
    $this->orozjeDelOrozja = $orozjeDelOrozja;
    return $this;
  }


}
