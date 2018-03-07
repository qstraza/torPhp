<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 04/12/2017
 * Time: 18:16
 */

namespace qstraza\torPhp\Realizacija;


class TorRealizacijaStrelivo extends TorRealizacija {
  private $vrstaDovoljenjaDrugo;
  private $prodanaKolicina;
  /**
   * TorRealizacijaStrelivo constructor.
   */
  public function __construct() {
  }

  /**
   * @return mixed
   */
  public function getVrstaDovoljenjaDrugo() {
    return $this->vrstaDovoljenjaDrugo;
  }

  /**
   * @param mixed $vrstaDovoljenjaDrugo
   * @return TorRealizacijaStrelivo
   */
  public function setVrstaDovoljenjaDrugo($vrstaDovoljenjaDrugo) {
    // To get valid options, execute following jQuery onpage
    // var output = "";jQuery("select[id$=rel_w13_id_vrs_vlg_reg] option").each(function() {var name = /.*? - (.*)/.exec(jQuery(this).text());output+= "'" + name[1] + "' => '" + jQuery(this).val() + "',\n"}); console.log(output)
    $validOptions = [
      'Orožni list' => 'OL',
      'Orožni posestni list' => 'OP',
      'Druga listina' => 'DL',
      'Listina ni potrebna' => 'NP',
    ];
    if (isset($validOptions[$vrstaDovoljenjaDrugo])) {
      $this->vrstaDovoljenjaDrugo = $validOptions[$vrstaDovoljenjaDrugo];
      $this->selectOption('FM:rel_w13_id_vrs_vlg_reg', $this->vrstaDovoljenjaDrugo);
      return $this;
    }
    throw new \Exception('Vrsta dovoljenja - Drugo "' . $vrstaDovoljenjaDrugo . '", ni pravilna!');
  }

  /**
   * @return mixed
   */
  public function getProdanaKolicina() {
    return $this->prodanaKolicina;
  }

  /**
   * @param int $prodanaKolicina
   * @return TorRealizacijaStrelivo
   */
  public function setProdanaKolicina($prodanaKolicina) {
    $this->prodanaKolicina = $prodanaKolicina;
    $this->writeById('FM:rel_kolicina', $prodanaKolicina);
    return $this;
  }

}