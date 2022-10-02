<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 18/12/2017
 * Time: 08:45
 */

namespace qstraza\torphp\Data;

use Facebook\WebDriver\WebDriverBy;
use Google\Spreadsheet\ListEntry;
use qstraza\torphp\Realizacija\TorRealizacijaOrozja;
use qstraza\torphp\TorIzdelanoOrozje;
use qstraza\torphp\TorNabavljenoOrozje;

class OrozjeItem extends OrozjeStrelivoItem
{
    protected $stPrigasitvenegaLista;
    protected $vrstaOrozja;
    protected $model;
    protected $serijska;
    protected $kategorija;
    protected $izdelan = false;
    protected $orozjeDelOrozja;
    protected $letoIzdelave;


    /**
     * OrozjeItem constructor.
     */
    public function __construct()
    {
    }

    public function realiziraj(TorRealizacijaOrozja $tor)
    {
        $kategorijaCode = explode(" ", $this->getKategorija());
        $kategorijaCode = $kategorijaCode[0];
        switch ($kategorijaCode) {
            case 'D8':
            case 'D9':
                $this->setVrstaEvidence("3 - Nabavljeno in prodano orožje in priglasitveni list");
                $return = $tor->openItemByCatBrandModel($this->getKategorija(), $this->getZnamka(), $this->getModel(), 'Ne', $this->getVrstaEvidence());
                if ($return !== null && array_key_exists("error", $return)) {
                    $this->returnMessage = $return["error"];
                    $this->error = true;
                    echo "error";
                    return;
                }
                $tor->setProdanaKolicina($this->getSerijska());
                break;
            default:
                $return = $tor->openItemBySerial($this->getSerijska());
                if ($return !== null && array_key_exists("vrsta evidence", $return)) {
                    $this->setVrstaEvidence($return["vrsta evidence"]);
                }
        }
        if ($return !== null && array_key_exists("error", $return)) {
            $this->returnMessage = $return["error"];
            $this->error = true;
            echo "error";
            return;
        }

        sleep(1);

        switch (substr($this->getVrstaEvidence(), 0, 1)) {
            case "1": // Izdelano orožje.

                $selectBuyerButtonId = 145;
                $potrdiButtonId = 174;
                $confirmButtonId = 179;
                break;

            case "3": // Nabavljeno in prodano orožje in priglasitveni list.
                $tor->setPrevzemnikOrozja($this->getUser()->getIme());
                $tor->setDatumPrevzemaVrnitveOrozja($this->getDate());
                $selectBuyerButtonId = 190;
                $potrdiButtonId = 231;
                $confirmButtonId = 236;
                break;
        }
        $tor->setDrzavaProdaje($this->getDrzava(), $this->getIsEU());
        $tor->selectBuyer($selectBuyerButtonId, $this->getVrstaEvidence(), $this->getUser());
        $tor->setVrstaDovoljenja($this->getVrstaDovoljenja());

        if ($this->getVrstaDovoljenja() != "NP - Listina ni potrebna") {
            $tor->setOrganIzdaje($this->getOrganIzdaje());
            $tor->setStevilkaListine($this->getStevilkaListine());
            $tor->setDatumIzdajeListine($this->getDatumIzdajeListine());
        }
        $tor->setDatumProdaje($this->date);
        $tor->setOpomba($this->getOpombaTor());

        $error = $tor->confirmPage($potrdiButtonId, $confirmButtonId);

        if ($error !== null) {
            // We have an error
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Realizirano";
        }
    }

    public function izdelaj(TorIzdelanoOrozje $tor)
    {
        $tor->menuClick('izdelano orozje');
        $tor->setOrozjeDelOrozja($this->getOrozjeDelOrozja());
        $tor->setKategorijaOrozja($this->getKategorija());
        $tor->setTipVrstaOrozja($this->getVrstaOrozja());
        $tor->setZnamka($this->getProizvajalec());
        $tor->setModel($this->getModel());
        $tor->setKaliber($this->getCal());
        $tor->setTovarniskaStevilka($this->getSerijska());
        $tor->setDatumIzdelave($this->getDate());
        $tor->setOpomba($this->getOpombaTor());
        $error = $tor->confirmPage(118, 123);
        if ($error !== null) {
            // We have an error.
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Izdelano";
        }
    }

    public function izdelajIstiModel(TorIzdelanoOrozje $tor) {
        try {
            $tor->clickById("to20DoneDialogForm:j_idt133");
            sleep(1);
        } catch (\Exception $e) {
            $this->returnMessage = "Ne morem klikniti gumba za dodajanje istega modela!";
            $this->error = true;
            return;
        }

        $tor->setTovarniskaStevilka($this->getSerijska());
        $error = $tor->confirmPage(118, 123);
        if ($error !== null) {
            // We have an error.
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Izdelano";
        }
    }

    public function nabavi(TorNabavljenoOrozje $tor)
    {
        $tor->menuClick('nabavljeno prodano orozje');
        $tor->setKomisijskaNabava($this->getKomisijskaNabava());
        $tor->setDrzavaNabave($this->getDrzava(), $this->getIsEU());
        $tor->selectSeller($this->getUser());

        if ($this->getDrzava() == "Slovenia") {
            $tor->setVrstaDovoljenja("NP - Listina ni potrebna");
        } else {
            if ($this->getIsEU()) {
                $tor->setVrstaDovoljenja("01 - Dovoljenje za vnos orožja v RS iz EU");
            } else {
                $tor->setVrstaDovoljenja("03 - Dovoljenje za uvoz orožja");
            }
            $tor->setOrganIzdaje($this->getOrganIzdaje());
            $tor->setStevilkaListine($this->getStevilkaListine());
            $tor->setDatumIzdajeListine($this->getDatumIzdajeListine());
        }
        $tor->setOrozjeDelOrozja($this->getOrozjeDelOrozja());
        $tor->setKategorijaOrozja($this->getKategorija());
        $tor->setTipVrstaOrozja($this->getVrstaOrozja());
        $tor->setZnamka($this->getZnamka());
        $tor->setModel($this->getModel());
        $tor->setKaliber($this->getCal());


        $kategorijaCode = explode(" ", $this->getKategorija());
        $kategorijaCode = $kategorijaCode[0];
        switch ($kategorijaCode) {
            case 'D8':
            case 'D9':
                $tor->setKolicina($this->getSerijska());
                break;
            default:
                $tor->setTovarniskaStevilka($this->getSerijska());
                break;
        }

        $tor->setProizvajalec($this->getProizvajalec());
        $tor->setLetoIzdelave($this->getLetoIzdelave());
        $tor->setDrzavaProizvajalka($this->getDrzavaProizvajalka());
        $tor->setDatumPrejemaOrozja($this->getDate());
        $tor->setStevilkaDobavnice($this->getStevilkaPrevzema());
        $tor->setOpomba($this->getOpombaTor());

        $error = $tor->confirmPage(164, 169);

        if ($error !== null) {
            // We have an error.
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Nabavljeno";
        }
    }

    public function nabaviIstiModel(TorNabavljenoOrozje $tor)
    {
        try {
            $tor->clickById("to22DoneDialogForm:j_idt232");
            sleep(1);
        } catch (\Exception $e) {
            $this->returnMessage = "Ne morem klikniti gumba za dodajanje istega modela!";
            $this->error = true;
            return;
        }

        // TODO: Napaka v TORu. Ko klikneš, da želiš dodati še en kos za dobavitelja, se kategorija izgubi. Zato je treba izbrati napačno in še 1x pravo.
        $tor->setKategorijaOrozja("A2 - Avtomatsko strelno orožje");
        $tor->setKategorijaOrozja($this->getKategorija());
        $tor->setTipVrstaOrozja($this->getVrstaOrozja());

        $tor->setTovarniskaStevilka($this->getSerijska());
        $error = $tor->confirmPage(164, 169);
        if ($error !== null) {
            // We have an error.
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Nabavljeno";
        }
    }

    public function fix(TorRealizacijaOrozja $tor)
    {
//        $error = $tor->openItemBySerial($this->getSerijska());
//        if ($error !== null) {
//            $this->returnMessage = $error;
//            $this->error = true;
//            return;
//        }
//
//        if ($this->isEU) {
//            if ($this->drzava == 'Slovenija') {
//                $tor->setDrzavaProdaje('Slovenija');
//                $tor->setKupecNazivPriimekIme($this->getIme());
//            } else {
//                $tor->setDrzavaProdaje('Transfer v EU');
//                $tor->setKupecNazivPriimekIme($this->getIme());
//                $tor->setDrzava($this->getDrzava());
//            }
//        } else {
//            $tor->setDrzavaProdaje('Izvoz');
//            $tor->setKupecNazivPriimekIme($this->getIme());
//            $tor->setDrzava($this->getDrzava());
//        }
//
//        $tor->setNaselje($this->getMesto());
//        $tor->setUlica($this->getNaslov());
//        $tor->setHst('/');
//
//        switch ($this->getVrstaDovoljenja()) {
//            case 'brez':
//                $tor->setVrstaDovoljenja('Drugo');
//                $tor->setVrstaDovoljenjaDrugo('Listina ni potrebna');
//                break;
//            case 'iznos v EU':
//                $tor->setVrstaDovoljenja('Dovoljenje za iznos orožja iz RS v EU');
//                break;
//            case 'izvoz izven EU':
//                $tor->setVrstaDovoljenja('Dovoljenje za izvoz orožja');
//                break;
//            case 'nabavno dovoljenje':
//                $tor->setVrstaDovoljenja('Dovoljenje za nabavo orožja');
//                break;
//            case 'priglasitev':
//                $tor->setVrstaDovoljenja('Drugo');
//                $tor->setVrstaDovoljenjaDrugo('Priglasitveni list');
//                break;
//        }
//        if ($this->getVrstaDovoljenja() != 'brez') {
//            $tor->setOrganIzdaje($this->getOrganIzdaje());
//            $tor->setStevilkaListine($this->getStevilkaListine());
//            $tor->setDatumIzdajeListine($this->getDatumIzdajeListine());
//        }
//        $tor->setDatumProdaje($this->date);
//        $tor->setOpomba($this->getOpombaTor());
//        $tor->setVrstaKupca($this->getIsPodjetje() ? 'Trgovec z orožjem' : 'Posameznik');
//        $error = $tor->confirmPage();
//        echo $error;
//        if ($error !== null) {
//            // We have an error
//            $this->returnMessage = $error;
//            $this->error = true;
//        } else {
//            // All good.
//            $this->returnMessage = "Realizirano";
//        }
    }


    /**
     * @return mixed
     */
    public function getStPrigasitvenegaLista()
    {
        return $this->stPrigasitvenegaLista;
    }

    /**
     * @param mixed $stPrigasitvenegaLista
     * @return OrozjeItem
     */
    public function setStPrigasitvenegaLista($stPrigasitvenegaLista)
    {
        $this->stPrigasitvenegaLista = $stPrigasitvenegaLista;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVrstaOrozja()
    {
        return $this->vrstaOrozja;
    }

    /**
     * @param mixed $vrstaOrozja
     * @return OrozjeItem
     */
    public function setVrstaOrozja($vrstaOrozja)
    {
        $this->vrstaOrozja = $vrstaOrozja;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     * @return OrozjeItem
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSerijska()
    {
        return $this->serijska;
    }

    /**
     * @param mixed $serijska
     * @return OrozjeItem
     */
    public function setSerijska($serijska)
    {
        $this->serijska = $serijska;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getKategorija()
    {
        return $this->kategorija;
    }

    /**
     * @param mixed $kategorija
     * @return OrozjeItem
     */
    public function setKategorija($kategorija)
    {
        $this->kategorija = $kategorija;
        return $this;
    }

    /**
     * @return \Google\Spreadsheet\ListEntry
     */
    public function getSpreadsheetEntry()
    {
        return $this->spreadsheetEntry;
    }

    /**
     * @return bool
     */
    public function isIzdelan(): bool
    {
        return $this->izdelan;
    }

    /**
     * @param bool $izdelan
     * @return OrozjeItem
     */
    public function setIzdelan(bool $izdelan): OrozjeItem
    {
        $this->izdelan = $izdelan;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrozjeDelOrozja()
    {
        return $this->orozjeDelOrozja;
    }

    /**
     * @param mixed $orozjeDelOrozja
     * @return OrozjeItem
     */
    public function setOrozjeDelOrozja($orozjeDelOrozja)
    {
        $this->orozjeDelOrozja = $orozjeDelOrozja;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLetoIzdelave()
    {
        return $this->letoIzdelave;
    }

    /**
     * @param mixed $letoIzdelave
     */
    public function setLetoIzdelave($letoIzdelave): void
    {
        $this->letoIzdelave = $letoIzdelave;
    }

}
