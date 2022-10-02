<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 30/11/2017
 * Time: 14:57
 */

namespace qstraza\torphp\Data;

use Google\Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;


/**
 * Class SpreadSheetData
 * @package qstraza\Data
 */
class SpreadSheetData
{
    private $accessToken;
    private $spreadsheetId;
    private $companies;
    protected $worksheetName;
    private $clientName;
    protected $worksheetFeed;
    public $service;
    public $zapisnik = [];

    /**
     * @return int
     */
    public function getZapisnik($n)
    {
        if (is_int($n)) {
            return $this->zapisnik[$n];
        } else {
            for ($i = 0; $i < count($this->zapisnik); $i++) {
                if (strtolower($this->zapisnik[$i]) === strtolower($n)) {
                    return $i;
                }
            }
        }
        throw new \Exception("Stolpec z imenom {$n} ne obstaja");
    }

    /**
     * @return Google_Service_Sheets
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * SpreadSheetData constructor.
     */
    public function __construct($spreadsheetId = null, $worksheetName = null, $clientName = null)
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->worksheetName = $worksheetName;
        $this->clientName = $clientName;
        $this->auth();
//        $this->getCompanies();
    }

    /**
     * @return null
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @param null $clientName
     * @return SpreadSheetData
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpreadsheetId()
    {
        return $this->spreadsheetId;
    }

    /**
     * @param mixed $spreadsheetId
     * @return SpreadSheetData
     */
    public function setSpreadsheetId($spreadsheetId)
    {
        $this->spreadsheetId = $spreadsheetId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorksheetName()
    {
        return $this->worksheetName;
    }

    /**
     * @param mixed $worksheetName
     * @return SpreadSheetData
     */
    public function setWorksheetName($worksheetName)
    {
        $this->worksheetName = $worksheetName;
        return $this;
    }

    /**
     * Returns an authorized API client.
     * @return \Google_Client the authorized client object
     */
    private function getClient()
    {
        $client = new \Google\Client();
        putenv('GOOGLE_APPLICATION_CREDENTIALS=/google-jsons/' . $this->clientName . '.json');
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/drive', 'https://spreadsheets.google.com/feeds']);
        if ($client->isAccessTokenExpired()) {
            $client->refreshTokenWithAssertion();
        };

        $tokenArray = $client->fetchAccessTokenWithAssertion();
        $accessToken = $tokenArray["access_token"];
        $this->accessToken = $accessToken;
    }

    private function auth()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=/google-jsons/' . $this->clientName . '.json');
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        //    $client->addScope(\Google_Service_Sheets::SPREADSHEETS);
        $client->setScopes(['https://www.googleapis.com/auth/drive', 'https://spreadsheets.google.com/feeds']);

        if ($client->isAccessTokenExpired()) {
            $client->refreshTokenWithAssertion();
        };

        $tokenArray = $client->fetchAccessTokenWithAssertion();
        $accessToken = $tokenArray["access_token"];
        $this->accessToken = $accessToken;

        $this->service = new Google_Service_Sheets($client);
    }

//  protected function getSpreadsheet() {
//    $serviceRequest = new DefaultServiceRequest($this->accessToken);
//    ServiceRequestFactory::setInstance($serviceRequest);
//    $spreadsheetService = new SpreadsheetService();
//    $spreadsheetFeed = $spreadsheetService->getSpreadsheetFeed();
//    $spreadsheet = $spreadsheetFeed->getById('https://spreadsheets.google.com/feeds/spreadsheets/private/full/' . $this->spreadsheetId);
//    $worksheetFeed = $spreadsheet->getWorksheetFeed();
//    $this->worksheetFeed = $worksheetFeed;
//  }
    protected function getCompanyData($name)
    {
        foreach ($this->companies as $entry) {
            if ($entry['Naziv'] == $name) {
                return $entry;
            }
        }
        return FALSE;
    }

    private function getCompanies()
    {
        $response = $this->getService()->spreadsheets_values->get($this->getSpreadsheetId(), 'podjetja!A:I');

        $indexes = $response->getValues()[0];
        $entities = [];
        foreach ($response as $entry) {
            $entity = [];
            for ($i = 0; $i < count($entry); $i++) {
                $entity[$indexes[$i]] = $entry[$i];
            }
            $entities[] = $entity;
        }
        $this->companies = $entities;
    }
    protected function getSpreadsheetValues($worksheet, $range, $saveHeader = true) {
        $response = $this->getService()->spreadsheets_values->get($this->getSpreadsheetId(), "{$worksheet}!{$range}",
            ["valueRenderOption" => "UNFORMATTED_VALUE"]
        );
        if ($saveHeader) {
            $this->zapisnik = $response[0];
        }
        return $response;
    }

    protected function columnToLetter ($column) {
        $tmp = "";
        $letter = "";

        while ($column >=0 ){
            $tmp = $column % 26;
            $letter = chr($tmp + 65) . $letter;
            $column = ($column - $tmp - 1) / 26;
        }
        return $letter;
    }
    protected function getCellValue($worksheet, $cell) {
        $cellValue = $this->getSpreadsheetValues($worksheet, $cell, false);
        $cellValue = $cellValue->getValues();
        if ($cellValue) {
            $cellValue = $cellValue[0][0];
        }
        else {
            $cellValue = null;
        }
        return $cellValue;
    }
    protected function updateCell ($range, $value) {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [[$value]]
        ]);
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        return $this->service->spreadsheets_values->update($this->getSpreadsheetId(), $range,
            $body, $params);
    }

}
