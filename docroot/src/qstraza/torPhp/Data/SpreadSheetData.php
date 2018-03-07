<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 30/11/2017
 * Time: 14:57
 */

namespace qstraza\torPhp\Data;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

/**
 * Class SpreadSheetData
 * @package qstraza\Data
 */
class SpreadSheetData {
  private $accessToken;
  private $spreadsheetId;
  protected $worksheetName;
  private $clientName;
  protected $worksheetFeed;

  /**
   * SpreadSheetData constructor.
   */
  public function __construct($spreadsheetId = null, $worksheetName = null, $clientName = null) {
    $this->spreadsheetId = $spreadsheetId;
    $this->worksheetName = $worksheetName;
    $this->clientName = $clientName;
    $this->auth();
    $this->getSpreadsheet();
  }

  /**
   * @return null
   */
  public function getClientName() {
    return $this->clientName;
  }

  /**
   * @param null $clientName
   * @return SpreadSheetData
   */
  public function setClientName($clientName) {
    $this->clientName = $clientName;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getSpreadsheetId() {
    return $this->spreadsheetId;
  }

  /**
   * @param mixed $spreadsheetId
   * @return SpreadSheetData
   */
  public function setSpreadsheetId($spreadsheetId) {
    $this->spreadsheetId = $spreadsheetId;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getWorksheetName() {
    return $this->worksheetName;
  }

  /**
   * @param mixed $worksheetName
   * @return SpreadSheetData
   */
  public function setWorksheetName($worksheetName) {
    $this->worksheetName = $worksheetName;
    return $this;
  }

  private function auth() {
    putenv('GOOGLE_APPLICATION_CREDENTIALS=/google-jsons/' . $this->clientName . '.json');
    $client = new \Google_Client();
    $client->useApplicationDefaultCredentials();
//    $client->addScope(\Google_Service_Sheets::SPREADSHEETS);
    $client->setScopes(['https://www.googleapis.com/auth/drive','https://spreadsheets.google.com/feeds']);

    if ($client->isAccessTokenExpired()) {
      $client->refreshTokenWithAssertion();
    };

    $tokenArray=$client->fetchAccessTokenWithAssertion();
    $accessToken=$tokenArray["access_token"];
    $this->accessToken = $accessToken;
  }

  protected function getSpreadsheet() {
    $serviceRequest = new DefaultServiceRequest($this->accessToken);
    ServiceRequestFactory::setInstance($serviceRequest);
    $spreadsheetService = new SpreadsheetService();
    $spreadsheetFeed = $spreadsheetService->getSpreadsheetFeed();
    $spreadsheet = $spreadsheetFeed->getById('https://spreadsheets.google.com/feeds/spreadsheets/private/full/' . $this->spreadsheetId);
    $worksheetFeed = $spreadsheet->getWorksheetFeed();
    $this->worksheetFeed = $worksheetFeed;
  }

  protected function getCompanyData($name) {
    $worksheet = $this->worksheetFeed->getByTitle("podjetja");
    $listFeed = $worksheet->getListFeed(["sq" => "naziv = \"$name\""]);
    $entries = $listFeed->getEntries();
    if ($entries && count($entries) == 1) {
      return $entries[0]->getValues();
    }
    return null;
  }

}
