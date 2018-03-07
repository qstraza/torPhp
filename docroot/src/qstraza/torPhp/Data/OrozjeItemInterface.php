<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 18/12/2017
 * Time: 08:52
 */

namespace qstraza\torPhp\Data;


interface OrozjeItemInterface {

  public function getDate();

  public function getIme();

  public function getNaslov();

  public function getMesto();

  public function getVrstaKupca();

  public function getVrstaDovoljenja();

  public function getDavcna();

  public function getOrganIzdaje();

  public function getStevilkaListine();

  public function getDatumIzdajeListine();

  public function getStPriglasitvenegaLista();

  public function getVrstaOrozja();

  public function getProizvajalec();

  public function getModel();

  public function getCal();

  public function getSerijska();

  public function getRealiziranTor();

  public function getDrzava();

  public function getIsEU();

  public function getOpombaTOR();

  public function setDate();

  public function setIme();

  public function setNaslov();

  public function setMesto();

  public function setVrstaKupca();

  public function setVrstaDovoljenja();

  public function setDavcna();

  public function setOrganIzdaje();

  public function setStevilkaListine();

  public function setDatumIzdajeListine();

  public function setStPriglasitvenegaLista();

  public function setVrstaOrozja();

  public function setProizvajalec();

  public function setModel();

  public function setCal();

  public function setSerijska();

  public function setRealiziranTor();

  public function setDrzava();

  public function setIsEU();

  public function setOpombaTOR();

  public function realiziraj();

  public function deleteRealizacijo();
}