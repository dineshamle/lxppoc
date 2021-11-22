<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

ini_set("memory_limit", "1024M");
class createController extends Controller
{
	private function createSheet($service)
	{

		//create a new spreadsheet
		$spreadsheet = new \Google_Service_Sheets_Spreadsheet([
			'properties' => [
				'title' => "LXP sheet - <Course Name> - " . date('Y-m-d') //add course name dynamically
			]
		]);
		$spreadsheet = $service->spreadsheets->create($spreadsheet);

		return $spreadsheet->spreadsheetId;
	}

	private function renameSheet($service, $spreadsheetId, $sheetId, $newName){
		$requests = [
			new \Google_Service_Sheets_Request([
				'updateSheetProperties' => [
					'properties' => [
						'sheetId' => $sheetId,
						'title' => $newName,
					],
					'fields' => 'title'
				]
			])
		];

		$batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
			'requests' => $requests
		]);

		$service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
	}

	private function addSheet($service, $spreadsheetId, $name){
		$body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
			'requests' => array(
				'addSheet' => array(
					'properties' => array(
						'title' => $name
					)
				)
			)
		));
		$service->spreadsheets->batchUpdate($spreadsheetId, $body);
	}

	private function addDataToSheet($service, $spreadsheetId, $sheetName, $values)
	{
		$body = new \Google_Service_Sheets_ValueRange([
			'values' => $values
		]);
		$params = [
			'valueInputOption' => 'RAW'
		];
		$service->spreadsheets_values->update(
			$spreadsheetId,
			$sheetName,
			$body,
			$params
		);
		// printf("%d cells updated.", $result->getUpdatedCells());
	}

	private function getRange($dataArr)
	{
		$range = new \Google_Service_Sheets_GridRange();
		$range->setStartRowIndex($dataArr['index']['startRow']);
		$range->setEndRowIndex($dataArr['index']['endRow']);
		$range->setStartColumnIndex($dataArr['index']['startColumn']);
		$range->setEndColumnIndex($dataArr['index']['endColumn']);
		$range->setSheetId($dataArr['sheetId']);

		return $range;
	}

	private function getColor($dataArr)
	{
		$color = new \Google_Service_Sheets_Color();
		$color->setRed($dataArr['r']);
		$color->setGreen($dataArr['g']);
		$color->setBlue($dataArr['b']);

		return $color;
	}

	private function mergeCells($mergeType, $range)
	{
		$request = new \Google_Service_Sheets_MergeCellsRequest();
		$request->setMergeType($mergeType);
		$request->setRange($range);

		$body = new \Google_Service_Sheets_Request();
		$body->setMergeCells($request);

		return $body;
	}

	private function cellTextFormat($dataArr)
	{
		//set text format
		$cellTextFormat = new \Google_Service_Sheets_TextFormat();
		//set text color
		$cellTextColor = $this->getColor($dataArr['color']);
		$cellTextFormat->setForegroundColor($cellTextColor);
		$cellTextFormat->setBold($dataArr['bold']);
		return $cellTextFormat;
	}

	private function updateCellText($dataArr)
	{
		$cellFormat = new \Google_Service_Sheets_CellFormat();
		$cellFormat->setTextFormat($dataArr['cellTextFormat']);
		$cellData = new \Google_Service_Sheets_CellData();
		$cellData->setUserEnteredFormat($cellFormat);
		$rowData = new \Google_Service_Sheets_RowData();
		$rowData->setValues([$cellData]);
		$rows[] = $rowData;
		$request = new \Google_Service_Sheets_UpdateCellsRequest();
		$request->setRows($rows);
		$request->setFields($dataArr['fields']);
		$request->setRange($dataArr['range']);
		$body = new \Google_Service_Sheets_Request();
		$body->setUpdateCells($request);
		return $body;
	}

	private function updateCell($dataArr)
	{
		$cellFormat = new \Google_Service_Sheets_CellFormat();
		$cellFormat->setHorizontalAlignment($dataArr['horizontalAlign']);
		//set background color
		$cellBGColor = $this->getColor($dataArr['bgcolor']);
		$cellFormat->setBackgroundColor($cellBGColor);

		$cellData = new \Google_Service_Sheets_CellData();
		$cellData->setUserEnteredFormat($cellFormat);
		$rowData = new \Google_Service_Sheets_RowData();
		$rowData->setValues([$cellData]);
		$rows[] = $rowData;
		$request = new \Google_Service_Sheets_UpdateCellsRequest();
		$request->setRows($rows);
		$request->setFields($dataArr['fields']);
		$request->setRange($dataArr['range']);
		$body = new \Google_Service_Sheets_Request();
		$body->setUpdateCells($request);
		return $body;
	}

	private function updateCellWidth($dataArr)
	{
		$dimensionRange = new \Google_Service_Sheets_DimensionRange();
		$dimensionRange->setSheetId($dataArr['sheetId']);
		$dimensionRange->setDimension($dataArr['dimension']);
		$dimensionRange->setStartIndex($dataArr['startIndex']);
		$dimensionRange->setEndIndex($dataArr['endIndex']);

		$dimensionProperties = new \Google_Service_Sheets_DimensionProperties();
		$dimensionProperties->setPixelSize($dataArr['width']);

		$request = new \Google_Service_Sheets_UpdateDimensionPropertiesRequest();
		$request->setProperties($dimensionProperties);
		$request->setRange($dimensionRange);
		$request->setFields('pixelSize');

		$body = new \Google_Service_Sheets_Request();
		$body->setUpdateDimensionProperties($request);
		return $body;
	}

	public function create()
	{
		$client = $this->getClient();
		$service = new \Google_Service_Sheets($client);

		$spreadsheetId = $this->createSheet($service);

		//rename the default sheet (Sheet 1) to "Overview"
		$this->renameSheet($service, $spreadsheetId, 0, 'Overview');

		//Add "Assessments" sheet
		$this->addSheet($service, $spreadsheetId, 'Assessments');

		//Add "Outcomes" sheet
		$this->addSheet($service, $spreadsheetId, 'Outcomes');
		
		//Add "Course Map" sheet
		$this->addSheet($service, $spreadsheetId, 'Course Map');

		//get sheetIds
		$response = $service->spreadsheets->get($spreadsheetId);

		//map sheet titles to sheet ids
		$sheets = [];
		foreach ($response->getSheets() as $s) {
			$sheets[strtolower($s['properties']['title'])] = $s['properties']['sheetId'];
		}


		//add data to overview sheet
		$values = [
			[
				'Course Overview'
			],
			[
				'Course Name', 1
			],
			[
				'Program', 2
			],
			[],
			[
				'Course Details'
			],
			[
				'Course Drive Folder (external)', 7
			]
		];

		//Add data to sheet
		$this->addDataToSheet($service, $spreadsheetId, 'Overview', $values);

		////modifying sheet styling
		$updateArr = [];
		//get 'Overview' sheet id
		$overviewSheetId = $sheets['overview'];

		//get range of first two cols of 1st row
		$range = $this->getRange(
			[
				'index' => [
					'startRow' => 0,
					'endRow' => 1,
					'startColumn' => 0,
					'endColumn' => 2
				],
				'sheetId' => $overviewSheetId
			]
		);

		//merge first two columns in row1
		$updateArr[] = $this->mergeCells('MERGE_ROWS', $range);

		//set text color and font weight
		$cellTextFormat = $this->cellTextFormat([
			'color' => [
				'r' => 1,
				'g' => 1,
				'b' => 1
			],
			'bold' => true
		]);

		$updateArr[] = $this->updateCellText([
			'cellTextFormat' => $cellTextFormat,
			'fields' => 'userEnteredFormat.textFormat',
			'range' => $range
		]);

		////set cell format: Change horizontalAlignment to "CENTER". set background color
		$updateArr[] = $this->updateCell([
			'bgcolor' => [
				'r' => 0.21,
				'g' => 0.118,
				'b' => 0.212
			],
			'horizontalAlign' => 'CENTER',
			'fields' => 'userEnteredFormat.horizontalAlignment,userEnteredFormat.backgroundColor',
			'range' => $range
		]);
		
		//set cell width
		$updateArr[] = $this->updateCellWidth([
			'sheetId' => $overviewSheetId,
			'dimension' => 'COLUMNS',
			'startIndex' => 0,
			'endIndex'=> 1,
			'width' => 350
		]);

		$updateArr[] = $this->updateCellWidth([
			'sheetId' => $overviewSheetId,
			'dimension' => 'COLUMNS',
			'startIndex' => 1,
			'endIndex'=> 2,
			'width' => 700
		]);
	
		//get range of first two cols of 1st row
		$range = $this->getRange(
			[
				'index' => [
					'startRow' => 4,
					'endRow' => 5,
					'startColumn' => 0,
					'endColumn' => 2
				],
				'sheetId' => $overviewSheetId
			]
		);

		//merge first two columns in row1
		$updateArr[] = $this->mergeCells('MERGE_ROWS', $range);

		//set text color and font weight
		$cellTextFormat = $this->cellTextFormat([
			'color' => [
				'r' => 1,
				'g' => 1,
				'b' => 1
			],
			'bold' => true
		]);

		$updateArr[] = $this->updateCellText([
			'cellTextFormat' => $cellTextFormat,
			'fields' => 'userEnteredFormat.textFormat',
			'range' => $range
		]);

		////set cell format: Change horizontalAlignment to "CENTER". set background color
		$updateArr[] = $this->updateCell([
			'bgcolor' => [
				'r' => 0.21,
				'g' => 0.118,
				'b' => 0.212
			],
			'horizontalAlign' => 'CENTER',
			'fields' => 'userEnteredFormat.horizontalAlignment,userEnteredFormat.backgroundColor',
			'range' => $range
		]);

		$range1 = $this->getRange(
			[
				'index' => [
					'startRow' => 1,
					'endRow' => 4,
					'startColumn' => 0,
					'endColumn' => 1
				],
				'sheetId' => $overviewSheetId
			]
		);

		$updateArr[] = $this->updateCell([
			'bgcolor' => [
				'r' => 0.212,
				'g' => 0.213,
				'b' => 0.18
			],
			'horizontalAlign' => 'LEFT',
			'fields' => 'userEnteredFormat.horizontalAlignment,userEnteredFormat.backgroundColor',
			'range' => $range1
		]);



		//update spreadsheet
		$batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
		$batchUpdateRequest->setRequests($updateArr); // Modified

		$response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
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
