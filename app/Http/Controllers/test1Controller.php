<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

define('STDIN', fopen("php://stdin", "r"));
class testController extends Controller
{
    private $overviewData, $outcomesData, $assessmentsData, $courseMapData;
    private $overviewDataMapped, $outcomesDataMapped, $assessmentsDataMapped, $courseMapDataMapped;

    public function test()
    {
        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);

        // Prints the names and majors of students in a sample spreadsheet:
        // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        // $spreadsheetId = '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms';
        // $spreadsheetId = '18pW-2X1FL5hdEjut6CmdgnotVCmVpsut9JwqUmm7ako';
        $spreadsheetId = '1c_EqDGs3OvzncVeNEIG7ZWlHXviF011FnLS4rcnQzbk';

        $sheets = $service->spreadsheets->get($spreadsheetId, ["ranges" => ['Outcomes'], "fields" => "sheets"])->getSheets();
        $rowMetadata = $sheets[0]->getData()[0]->getRowMetadata();
        echo "<pre>";
        print_r($rowMetadata);
        echo "</pre>";
        $filteredRows = array(
            'hiddenRows' => array(),
            'showingRows' => array()
        );
        foreach ($rowMetadata as $i => $r) {
        //     echo "<pre>";
        // print_r($r['pixelSize']);
        // echo "</pre>";exit;
            if ((isset($r['hiddenByFilter']) && $r['hiddenByFilter'] == 1) || (isset($r['hiddenByUser']) && $r['hiddenByUser'] == 1)) {
                array_push($filteredRows['hiddenRows'], $i + 1);
            } else {
                array_push($filteredRows['showingRows'], $i + 1);
            };
        };
        print_r($filteredRows['hiddenRows']);exit;

        $response = $service->spreadsheets->get($spreadsheetId);
        print_r($response[0]->getData());exit;
        $sheets = [];
        foreach ($response->getSheets() as $s) {
            $sheets[] = $s['properties']['title'];
        }
        // exit;
        $sheets = array_map('strtolower', $sheets);

        //mandatory sheets check
        $diff = array_diff(config('lxp.spreadsheet.sheet_titles'), $sheets);
        if (!empty($diff)) {
            echo "Following sheet(s) are not found: " . implode(", ", $diff);
        }

        foreach ($response->getSheets() as $s) {
            if (!in_array(strtolower($s['properties']['title']), config('lxp.spreadsheet.sheet_titles'))) continue;
            $sheetTitle = $s['properties']['title'];
            $response = $service->spreadsheets_values->get($spreadsheetId, $sheetTitle);
            var_dump($response[0]->getRowMetadata());exit;
            // $values = $response->getValues();

            // array_walk_recursive($values, function (&$arrValue, $arrKey) {
            //     $arrValue = trim($arrValue);
            // });

            // echo "<h1>".$sheetTitle."</h1>";
            // echo "<br>";
            // echo "<pre>";
            // print_r($values);
            // echo "</pre>";
        }
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS_READONLY);
        // $client->setAuthConfig('credentials.json');
        $client->setAuthConfig(base_path() . '\credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/test');
        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.

        //comment this section later
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        //end

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();

                if (!isset($_GET['code'])) {
                    header("Location: $authUrl", true, 302);
                    exit;
                }

                // printf("Open the following link in your browser:\n%s\n", $authUrl);
                // print 'Enter verification code: ';
                //  $authCode = trim(fgets(fopen("php://stdin","r")));
                // print_r($_REQUEST['code']);
                $authCode = trim($_REQUEST['code']);

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode(trim($_REQUEST['code']));
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            //comment this section later
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            //end
        }
        return $client;
    }
}
