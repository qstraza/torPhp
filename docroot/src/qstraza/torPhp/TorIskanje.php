<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 10:40
 */

namespace qstraza\torPhp;

class TorIskanje extends TorProxy {
  private $isciPoNeaktivnih;
  private $strelivoDelStreliva;
  private $proizvajalec;
  private $nazivDobavitelja;
  private $vrstaEvidence;
  private $realizacija;
  private $datumVpisaOd;
  private $datumVpisaDo;
  private $datumRealizacijeOd;
  private $datumRealizacijeDo;

  /**
   * TorProxy constructor.
   */
  public function __construct($clientName) {
    parent::__construct($clientName);
    $this->menuClick('TO10');
  }

  /**
   * @return mixed
   */
  public function getIsciPoNeaktivnih() {
    return $this->isciPoNeaktivnih;
  }

  /**
   * @param mixed $isciPoNeaktivnih
   * @return TorIskanje
   */
  public function setIsciPoNeaktivnih($isciPoNeaktivnih) {
    $this->isciPoNeaktivnih = $isciPoNeaktivnih;
    $this->clickById('FM:show_all');
    return $this;
  }

  /**
   * @return mixed
   */
  public function getStrelivoDelStreliva() {
    return $this->strelivoDelStreliva;
  }

  /**
   * @param mixed $strelivoDelStreliva
   * @return TorIskanje
   */
  public function setStrelivoDelStreliva($strelivoDelStreliva) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=vno_w61_id_streliva_dela] option").each(function(index) {if (index==0) return; var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Strelivo izraženo v kosih' => '1',
      'Smodnik izražen v kg' => '2',
      'Netilke izražene v kosih' => '3',
      'Tulec z netilko izražen v kosih' => '4',
    ];
    if (isset($validOptions[$strelivoDelStreliva])) {
      $this->strelivoDelStreliva = $validOptions[$strelivoDelStreliva];
      $this->selectOption('FM:vno_w61_id_streliva_dela', $this->strelivoDelStreliva);
      return $this;
    }
    throw new \Exception('Strelivo / del streliva "' . $strelivoDelStreliva . '", ni prava!');
  }

  /**
   * @return mixed
   */
  public function getProizvajalec() {
    return $this->proizvajalec;
  }

  /**
   * @param mixed $proizvajalec
   * @return TorIskanje
   */
  public function setProizvajalec($proizvajalec) {
    $this->proizvajalec = $proizvajalec;
    $this->writeById('FM:vno_proizvajalec', $this->proizvajalec);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getNazivDobavitelja() {
    return $this->nazivDobavitelja;
  }

  /**
   * @param mixed $nazivDobavitelja
   * @return TorIskanje
   */
  public function setNazivDobavitelja($nazivDobavitelja) {
    $this->nazivDobavitelja = $nazivDobavitelja;
    $this->writeById('FM:vno_subjekt', $this->nazivDobavitelja);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getVrstaEvidence() {
    return $this->vrstaEvidence;
  }

  /**
   * @param mixed $vrstaEvidence
   * @return TorIskanje
   */
  public function setVrstaEvidence($vrstaEvidence) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=vno_w62_id_knjige_tor] option").each(function(index) {if (index==0) return; var name = /[\d+] - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Izdelano orožje' => '1',
      'Popravljeno, predelano in spremenjeno orožje' => '2',
      'Nabavljeno in prodano orožje in priglasitveni list' => '3',
      'Nabavljeno in prodano strelivo' => '4',
      'Skladiščenje in hramba orožja' => '5',
    ];
    if (isset($validOptions[$vrstaEvidence])) {
      $this->vrstaEvidence = $validOptions[$vrstaEvidence];
      $this->selectOption('FM:vno_w62_id_knjige_tor', $this->vrstaEvidence);
      return $this;
    }
    throw new \Exception('Vrsta evidence "' . $vrstaEvidence . '", ni prava!');
  }

  /**
   * @return mixed
   */
  public function getRealizacija() {
    return $this->realizacija;
  }

  /**
   * @param mixed $realizacija
   * @return TorIskanje
   */
  public function setRealizacija($realizacija) {
    $this->realizacija = (bool)$realizacija;
    $this->selectOption('FM:ui_ind_realizacija', $this->realizacija);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumVpisaOd() {
    return $this->datumVpisaOd;
  }

  /**
   * @param mixed $datumVpisaOd
   * @return TorIskanje
   */
  public function setDatumVpisaOd($datumVpisaOd) {
    $this->datumVpisaOd = $datumVpisaOd;
    $this->writeById('FM:vno_dtm_dog_od', $datumVpisaOd);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumVpisaDo() {
    return $this->datumVpisaDo;
  }

  /**
   * @param mixed $datumVpisaDo
   */
  public function setDatumVpisaDo($datumVpisaDo) {
    $this->datumVpisaDo = $datumVpisaDo;
    $this->writeById('FM:vno_dtm_dog_do', $datumVpisaDo);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumRealizacijeOd() {
    return $this->datumRealizacijeOd;
  }

  /**
   * @param mixed $datumRealizacijeOd
   */
  public function setDatumRealizacijeOd($datumRealizacijeOd) {
    $this->datumRealizacijeOd = $datumRealizacijeOd;
    $this->writeById('FM:vno_dtm_rel_od', $datumRealizacijeOd);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDatumRealizacijeDo() {
    return $this->datumRealizacijeDo;
  }

  public function realizirajFirstHit() {
    // TODO: check what happens if 0 results are returned.
    $this->clickById('FM:to11DataTable:0:selected');
    $this->clickById('FM:RealizationHeader');
  }

  /**
   * @param mixed $datumRealizacijeDo
   */
  public function setDatumRealizacijeDo($datumRealizacijeDo) {
    $this->datumRealizacijeDo = $datumRealizacijeDo;
    $this->writeById('FM:vno_dtm_rel_do', $datumRealizacijeDo);
    return $this;
  }

  public function confirmPage() {
    parent::confirmPage();
  }

}