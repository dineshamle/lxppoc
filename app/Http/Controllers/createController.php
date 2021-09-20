<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

ini_set("memory_limit", "1024M");
class createController extends Controller
{
	public function create()
	{
		$client = $this->getClient();
		$service = new \Google_Service_Sheets($client);

		//create a new spreadsheet
		$spreadsheet = new \Google_Service_Sheets_Spreadsheet([
			'properties' => [
				'title' => "LXP sheet - <Course Name> - ".date('Y-m-d') //add course name dynamically
			]
		]);
		$spreadsheet = $service->spreadsheets->create($spreadsheet);

		echo  $spreadsheet->spreadsheetId;

		//rename the default sheet (Sheet 1) to "Overview"
		$requests = [
			new \Google_Service_Sheets_Request([
				'updateSheetProperties' => [
					'properties' => [
						'sheetId' => 0,
						'title' => 'Overview',
					],
					'fields' => 'title'
				]
			])
		];

		$batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
			'requests' => $requests
		]);

		$service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId, $batchUpdateRequest);

		//Add "Assessments" sheet
		$body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
			'requests' => array(
				'addSheet' => array(
					'properties' => array(
						'title' => 'Assessments'
					)
				)
			)
		));
		$service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId, $body);

		//Add "Outcomes" sheet
		$body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
			'requests' => array(
				'addSheet' => array(
					'properties' => array(
						'title' => 'Outcomes'
					)
				)
			)
		));
		$service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId, $body);

		//Add "Course Map" sheet
		$body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
			'requests' => array(
				'addSheet' => array(
					'properties' => array(
						'title' => 'Course Map'
					)
				)
			)
		));
		$service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId, $body);

		//get sheetIds
		$response = $service->spreadsheets->get($spreadsheet->spreadsheetId);
		$sheets = [];
		foreach ($response->getSheets() as $s) {
			$sheets[strtolower($s['properties']['title'])] = $s['properties']['sheetId'];
		}
		print_r($sheets);
	
		







		$values = [
			[
				'Course Overview', ''
			],
			[
				'Course Name', 1, 3
			],
			[
				'Program', 2
			],
			[],
			[
				'Course Details', ''
			],
			[
				'Course Drive Folder (external)', 7
			]
		];

		$body = new \Google_Service_Sheets_ValueRange([
			'values' => $values
		]);
		$params = [
			'valueInputOption' => 'RAW'
		];
		$result = $service->spreadsheets_values->update($spreadsheet->spreadsheetId, 'Overview',
		$body, $params);
		printf("%d cells updated.", $result->getUpdatedCells());

		

		$rangel = new \Google_Service_Sheets_GridRange();
$rangel->setStartRowIndex(0);
$rangel->setEndRowIndex(1);
$rangel->setStartColumnIndex(0);
$rangel->setEndColumnIndex(2);
$rangel->setHorizontalAlignment('CENTER');
$rangel->setSheetId(0);

$request = new \Google_Service_Sheets_MergeCellsRequest();
$request->setMergeType('MERGE_ROWS'); // Modified
$request->setRange($rangel); // Modified

$body = new \Google_Service_Sheets_Request(); // Added
$body->setMergeCells($request); // Added

$batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
$batchUpdateRequest->setRequests($body); // Modified

$response = $service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId,$batchUpdateRequest);




$rangel = new \Google_Service_Sheets_GridRange();
$rangel->setStartRowIndex(4);
$rangel->setEndRowIndex(5);
$rangel->setStartColumnIndex(0);
$rangel->setEndColumnIndex(2);
$rangel->setSheetId(0);

$request = new \Google_Service_Sheets_MergeCellsRequest();
$request->setMergeType('MERGE_ROWS'); // Modified
$request->setRange($rangel); // Modified

$body = new \Google_Service_Sheets_Request(); // Added
$body->setMergeCells($request); // Added

$batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
$batchUpdateRequest->setRequests($body); // Modified

$response = $service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId,$batchUpdateRequest);



		// $requests = [
		// 	new \Google_Service_Sheets_Request([
		// 		'updateSheetProperties' => [
		// 			'properties' => [
		// 				'sheetId' => 0,
		// 				'title' => 'Overview',
		// 			],
		// 			'fields' => 'title'
		// 		]
		// 	])
		// ];

		// $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
		// 	'requests' => $requests
		// ]);

		// $service->spreadsheets->batchUpdate($spreadsheet->spreadsheetId, $batchUpdateRequest);
	}

	function getClient()
	{
		$client = new \Google_Client();
		$client->setApplicationName('Google Sheets API PHP Quickstart');
		$client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
		// $client->setAuthConfig('credentials.json');
		$client->setAuthConfig(base_path() . '\credentials.json');
		$client->setAccessType('offline');
		$client->setPrompt('select_account consent');

		$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/lxpdataingest');
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
				// $authCode = trim($_REQUEST['code']);

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
