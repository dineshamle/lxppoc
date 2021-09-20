<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

ini_set("memory_limit", "-1");
define('STDIN', fopen("php://stdin", "r"));
class testController extends Controller
{
    private $overviewData, $outcomesData, $assessmentsData, $courseMapData;
    private $overviewDataMapped, $outcomesDataMapped, $assessmentsDataMapped, $courseMapDataMapped;
    private $hiddenRowsArr, $hiddenColsArr = [];

    public function test()
    {
        // echo env('TEST_ATTRIBUTE');
        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);

        // Prints the names and majors of students in a sample spreadsheet:
        // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        // $spreadsheetId = '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms';
        // $spreadsheetId = '18pW-2X1FL5hdEjut6CmdgnotVCmVpsut9JwqUmm7ako';
        // $spreadsheetId = '1c_EqDGs3OvzncVeNEIG7ZWlHXviF011FnLS4rcnQzbk';
        $spreadsheetId = '15YO3e8lsRPwqpqZZCG7Ykyr2byNu5pe72-HzZTpzUlY';


        //get sheet titles and hidden rows
        $response = $service->spreadsheets->get($spreadsheetId);
        $sheets = [];
        foreach ($response->getSheets() as $s) {
            $title = strtolower($s['properties']['title']);
            if(!in_array($title, config('lxp.spreadsheet.sheet_titles'))){ continue; };
            $sheets[] = $title;
            $sheetMetadata = $service->spreadsheets->get($spreadsheetId, ["ranges" => [$s['properties']['title']], "fields" => "sheets"])->getSheets();
            
            $this->hiddenRowsArr[$title] = [];
            $this->hiddenColsArr[$title] = [];
            //get hidden rows index
            $rowMetadata = $sheetMetadata[0]->getData()[0]->getRowMetadata();
            foreach ($rowMetadata as $i => $r) {
                if ((isset($r['hiddenByFilter']) && $r['hiddenByFilter'] == 1) || (isset($r['hiddenByUser']) && $r['hiddenByUser'] == 1)) {
                    $this->hiddenRowsArr[$title][] = $i;
                }
            }

            // get hidden cols index
            $colMetadata = $sheetMetadata[0]->getData()[0]->getColumnMetadata();
            foreach ($colMetadata as $i => $r) {
                if ((isset($r['hiddenByFilter']) && $r['hiddenByFilter'] == 1) || (isset($r['hiddenByUser']) && $r['hiddenByUser'] == 1)) {
                    $this->hiddenColsArr[$title][] = $i;
                }
            }
        }
        // echo "<pre>";
        // print_r($this->hiddenColsArr);
        // print_r($this->hiddenRowsArr);
        // exit;
        //lower case the sheet names
        $sheets = array_map('strtolower', $sheets);
        // print_r($sheets);

        //mandatory sheets check
        $diff = array_diff(config('lxp.spreadsheet.sheet_titles'), $sheets);
        if (!empty($diff)) {
            echo "Following sheet(s) are not found: " . implode(", ", $diff);
        }

    
        foreach ($response->getSheets() as $s) {
            if (!in_array(strtolower($s['properties']['title']), config('lxp.spreadsheet.sheet_titles'))) continue;
            $sheetTitle = $s['properties']['title'];
            $response = $service->spreadsheets_values->get($spreadsheetId, $sheetTitle);
            $values = $response->getValues();
            
            array_walk_recursive($values, function(&$arrValue, $arrKey){ $arrValue = trim($arrValue);});
            
            if (strtolower($sheetTitle) == 'overview')
                $this->overviewData = $values;

            if (strtolower($sheetTitle) == 'outcomes')
                $this->outcomesData = $values;

            if (strtolower($sheetTitle) == 'assessments')
                $this->assessmentsData = $values;

            if (strtolower($sheetTitle) == 'course map')
                $this->courseMapData = $values;

            // $this->datamapper($sheetTitle);
        }

        echo "<pre>";
        print_r($this->assessmentsData);
        exit;

        $this->overviewDatamapper();
        $this->assessmentDatamapper();
        $this->outcomesDatamapper();
        $this->courseMapDatamapper();

        // exit;
        // if (empty($values)) {
        //     print "No data found.\n";
        // } else {
        //     print "Name, Major:\n";
        //     foreach ($values as $row) {
        //         // Print columns A and E, which correspond to indices 0 and 4.
        //         printf("%s, %s\n", $row[0], $row[4]);
        //     }
        // }
    }

    
    function overviewDatamapper()
    {
        echo "<h1>Original Overview Data</h1><br>";
        echo "<pre>";
        print_r($this->overviewData);
        echo "</pre>";
        echo "</br>";

        $this->overviewDataMapped = [];
        $key = '';
        foreach($this->overviewData as $k => $v){
            if(in_array($k, $this->hiddenRowsArr['overview'])){ continue; }
            
            if(empty($v)){
                $key = '';
            }

            if(!empty($v[0]) && empty($v[1])){
                $key = $v[0];
            }

            if($key != '' && !empty($v[0]) && !empty($v[1])){
                $this->overviewDataMapped[$key][trim($v[0])] = trim($v[1]);
            }
        }

        // echo "<pre>";
        // print_r($this->overviewDataMapped);
        // echo "</pre>";


        // $this->overviewDataMapped = [];
        // foreach($this->overviewData as $row){
        //     if(!empty($row[0]) && !empty($row[1])){
        //         $this->overviewDataMapped[trim($row[0])] = trim($row[1]);
        //     }
        // }

        echo "<h1>Structured Data</h1><br>";
        echo "<pre>";
        print_r($this->overviewDataMapped);
        echo "</pre>";
        echo "</br>";
    }
    
    function assessmentDatamapper()
    {
        echo "<h1>Original Assessment Data</h1><br>";
        echo "<pre>";
        print_r($this->assessmentsData);
        echo "</pre>";
        echo "</br>";

        //add feedback string to row 2 feedback types - such as 'automatic', 'peer', 'instructor', 'rubric'
        $assessmentKeysArr = [];
        foreach($this->assessmentsData[0] as $k => $v){
            $assessmentKeysArr[] = (empty($v)?'Feedback':$v) ." ". (isset($this->assessmentsData[1][$k])?$this->assessmentsData[1][$k]:'');
        }

        $assessmentKeysArr = array_map('trim', $assessmentKeysArr);

        // echo "<h1>Keys</h1><br>";
        // echo "<pre>";
        // print_r($assessmentKeysArr);
        // echo "</pre>";


        $headerKeyCnt = count($assessmentKeysArr);
        for($i = 2; $i < count($this->assessmentsData); $i++){
            if(in_array($i, $this->hiddenRowsArr['assessments'])){ continue; }
            if($this->assessmentsData[$i][0] == ""){
                break;
            }

            if(count($this->assessmentsData[$i]) < $headerKeyCnt){
                $key =  0;
                while($key < $headerKeyCnt){
                    if(!(array_key_exists($key, $this->assessmentsData[$i]))){
                        $this->assessmentsData[$i][$key] = '';
                    }
                    $key++;
                }
            }
            $this->assessmentsDataMapped[] = array_combine($assessmentKeysArr, $this->assessmentsData[$i]);
        }

       
        echo "<h1>Structured Data</h1><br>";
        echo "<pre>";
        print_r($this->assessmentsDataMapped);
        echo "</pre>";
        echo "</br>";
    }

    function courseMapDatamapper()
    {

        echo "<h1>Original Course Map Data</h1><br>";
        echo "<pre>";
        print_r($this->courseMapData);
        echo "</pre>";
        echo "</br>";

        $startEndArr = [];
        $prevWeek = null;
        foreach($this->courseMapData as $k => $v){
            if(isset($v[0]) && strtolower($v[0]) == 'week'){
                if(isset($prevWeek)){
                    $startEndArr[$prevWeek]['endsAt'] = $k - 1;
                }
                $prevWeek = $v[1]; 
                $startEndArr[$v[1]]['startsAt'] = $k;
            }
        }

        //add end row to the last element
        $startEndArr[array_key_last($startEndArr)]['endsAt'] =array_key_last($this->courseMapData);

        $data = [];
        //Add description and ULOs to weeks
        foreach($startEndArr as $k => $v){
            $data[$k]['desc'] = $this->courseMapData[$v['startsAt']+1][0]; //+1 means next row
        }

        //Add ULOs to data
        foreach($startEndArr as $k => $v){
            for($i = $v['startsAt']+2; $i < $v['endsAt']; $i++){ //skip 2 rows as ULOs will start 2 rows after the word 'week'
                if(in_array($i, $this->hiddenRowsArr['course map'])){ continue; }
                if(isset($this->courseMapData[$i][0])) {
                    if(strtolower($this->courseMapData[$i][0]) == 'w'){
                        //sync / sync values
                        $data[$k]['async_total_time'] = (isset($this->courseMapData[$i-2][10]) && !in_array($i-2, $this->hiddenRowsArr['course map']))?$this->courseMapData[$i-2][10]:'';
                        $data[$k]['sync_total_time'] = (isset($this->courseMapData[$i-1][10]) && !in_array($i-1, $this->hiddenRowsArr['course map']))?$this->courseMapData[$i-1][10]:'';
                        break; //break the inner for loop and repeat with the next week
                    }
                }

                if(isset($this->courseMapData[$i][1]) && $this->courseMapData[$i][1] != ''){
                    $data[$k]['ulo'][] = ['title' => $this->courseMapData[$i][1], 'desc' => (isset($this->courseMapData[$i][1])?$this->courseMapData[$i][2]:'')];
                    // echo $this->courseMapData[$i][1];
                    // echo "<br>";
                }
            }
        }
        

        //get course data start row
        foreach($startEndArr as $k => $v){
            for($i = $v['startsAt']; $i < $v['endsAt']; $i++){
                if(in_array($i, $this->hiddenRowsArr['course map'])){ continue; }
                if(isset($this->courseMapData[$i][0])) {
                    if(strtolower($this->courseMapData[$i][0]) == 'w'){
                        $startEndArr[$k]['courseStartsAt'] = $i;
                        break;
                    }
                }
            }
        }

        //map course data
        foreach($startEndArr as $k => $v){
            $header = $this->courseMapData[$v['courseStartsAt']];
            $headerKeyCnt = count($header);
            // echo "<pre>";
            // print_r($header);
            // echo "</pre>";
            $sectionKey = '';
            for($i = $v['courseStartsAt']+1; $i < $v['endsAt']; $i++){//course data starts right after the header row
                if(in_array($i, $this->hiddenRowsArr['course map'])){ continue; }

                if(in_array(strtolower($this->courseMapData[$i][0]), ['readings', 'live session'])){
                    $sectionKey = strtolower($this->courseMapData[$i][0]);
                    continue;
                }

                // if($sectionKey != 'readings'){
                //     echo "Readings unavailable in the 'Course Map' sheet";
                //     exit;
                // }
                
                //add missing keys in rows so that associative array can be created
                if(count($this->courseMapData[$i]) < $headerKeyCnt){
                    $key =  0;
                    while($key < $headerKeyCnt){
                        if(!(array_key_exists($key, $this->courseMapData[$i]))){
                            $this->courseMapData[$i][$key] = '';
                        }
                        $key++;
                    }
                }

                $data[$k]['course_data'][$sectionKey][] = array_combine($header, $this->courseMapData[$i]);
            }
        }
        /*
        echo "<table style='table-layout:fixed; word-wrap:break-all; width:1500px' border='1'>
        <tr>
          <td style='table-layout:fixed; width:750px; word-wrap:break-all;overflow: hidden;' valign='top'><h1>Mapped Data</h1><div style='resize: both;'><pre>";
          print_r($data);
          print_r($startEndArr);
          echo "</pre></div></td>
          <td style='table-layout:fixed; width:750px; word-wrap:break-all;overflow: hidden;' valign='top'><h1>Parsed Data</h1><br><pre>";
          print_r($this->courseMapData);
          echo "</pre></td>
        </tr>
      </table>";*/

        echo "<h1>Structured Data</h1><br>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        echo "</br>";

    }

    function outcomesDatamapper()
    {
        echo "<h1>Original Outcomes Data</h1><br>";
        echo "<pre>";
        print_r($this->outcomesData);
        echo "</pre>";
        echo "</br>";

        //check first row for Course Statement
        if(strtolower($this->outcomesData[0][0]) != 'course statement'){
            echo "Course Statement unavailable in the 'Outcomes' sheet";
            exit;
        }

        //check assessment starting point
        $assessmentsStartAt  = null;
        foreach($this->outcomesData[0] as $k => $v){
            if(strtolower($v) == 'assessments'){
                $assessmentsStartAt = $k;
            }
        }

        if(is_null($assessmentsStartAt)){
            echo "Assessments  unavailable in the 'Outcomes' sheet";
            exit;
        }

        // echo "<pre>";
        // print_r($this->outcomesData);
        // echo "</pre>";
        //get assessment data
        $assessmentsArr = [];
        $assessmentsEndAt  = null;
        for($i = $assessmentsStartAt; $i < count($this->outcomesData[1]); $i++){
            if(in_array($i, $this->hiddenColsArr['outcomes'])){ continue; }
            $assessmentsArr[$i]['title'] = isset($this->outcomesData[1][$i])?$this->outcomesData[1][$i]:'';
            $assessmentsArr[$i]['type'] = isset($this->outcomesData[2][$i])?$this->outcomesData[2][$i]:'';
            $assessmentsEndAt = $i;
        }

        // echo "<pre>";
        // print_r($assessmentsArr);
        // echo "</pre>";


        // echo "Assessments start at col: ".$assessmentsStartAt;
        // echo "<br>";
        // echo "Assessments end at col: ".$assessmentsEndAt;
        // echo "<br>";
        // echo "<pre>";
        // print_r($assessmentsArr);
        // echo "</pre>";

        $courseGoalsStartAt = $courseGoalsEndAt = null;
        $courseGoalsFound = false;
        $courseGoalsArr = [];
        foreach($this->outcomesData as $k => $v){
            if(in_array($k, $this->hiddenRowsArr['outcomes'])){ continue; }

            if($courseGoalsFound == true && !isset($v[0])){
                $courseGoalsEndAt = $k - 1;
                $courseGoalsFound = false;
                break;
            }

            if($courseGoalsFound){
                if(is_null($courseGoalsStartAt)){
                    $courseGoalsStartAt = $k;
                }
                $courseGoalsArr[$k]['title'] = $v[0];
                $courseGoalsArr[$k]['desc'] = $v[1];
            }

            if($courseGoalsFound == false && isset($v[0]) && strtolower($v[0]) == 'course goals'){
                $courseGoalsFound = true;
            }
        }

        $courseGoalsExist = false;
        if(empty($courseGoalsArr)){
            echo "Course goals not found";
            exit;
        }else{
            $courseGoalsExist = true;
        }
       
        if(!$courseGoalsExist){
            echo "Course Goals unavailable in the 'Outcomes' sheet";
            exit;
        }

        // echo "Course goals start at: ".$courseGoalsStartAt;
        // echo "<br>";
        // echo "Course goals end at: ".$courseGoalsEndAt;
        // echo "<br>";
        // echo "<pre>";
        // print_r($courseGoalsArr);
        // echo "</pre>";

        $uloStartAt = $uloEndAt = null;
        $uloFound = false;
        $uloArr = [];
        foreach($this->outcomesData as $k => $v){
            if(in_array($k, $this->hiddenRowsArr['outcomes'])){ continue; }

            if($uloFound == true && (!isset($v[0]) || $v[0] == "")){
                continue;
            }

            if($uloFound){
                if(is_null($uloStartAt)){
                    $uloStartAt = $k;
                }
                $uloArr[$k]['title'] = $v[0];
                $uloArr[$k]['desc'] = $v[1];
                $uloEndAt = $k;
                
            }

            if($uloFound == false && isset($v[0]) && strtolower($v[0]) == 'unit level objectives'){
                $uloFound = true;
            }
        }

        // echo "ULOs start at: ".$uloStartAt;
        // echo "<br>";
        // echo "ULOs end at: ".$uloEndAt;
        // echo "<br>";
        // echo "<pre>";
        // print_r($uloArr);
        // echo "</pre>";

        // echo "Assessments start at col: ".$assessmentsStartAt;
        // echo "<br>";
        // echo "Assessments start at col: ".$assessmentsEndAt;

        //courseGoals to assessment mapping
        $courseAssessmentMapping = [];
        for($i = $courseGoalsStartAt; $i <= $courseGoalsEndAt; $i++){
            if(in_array($i, $this->hiddenRowsArr['outcomes'])){ continue; }
            for($j = $assessmentsStartAt; $j <= $assessmentsEndAt; $j++){
                if(strtolower($this->outcomesData[$i][$j]) == 'true'){
                    $courseAssessmentMapping[$i][] = $j;
                }
            }
        }

        // echo "<h1>courseGoals to assessment mapping</h1><br>";
        // echo "<pre>";
        // print_r($courseAssessmentMapping);
        // echo "</pre>";

        //ULOs to assessment mapping
        $uloAssessmentMapping = [];
        for($i = $uloStartAt; $i <= $uloEndAt; $i++){
            if(in_array($i, $this->hiddenRowsArr['outcomes'])){ continue; }
            for($j = $assessmentsStartAt; $j <= $assessmentsEndAt; $j++){
                if(strtolower($this->outcomesData[$i][$j]) == 'true'){
                    $uloAssessmentMapping[$i][] = $j;
                }
            }
        }

        // echo "<h1>ULOs to assessment mapping</h1><br>";
        // echo "<pre>";
        // print_r($uloAssessmentMapping);
        // echo "</pre>";

        //get course goals columns
        $courseGoalsColArr = array_filter($this->outcomesData[$uloStartAt-1]);
        $courseGoalsColStartAt = min(array_keys($courseGoalsColArr));
        $courseGoalsColEndAt = max(array_keys($courseGoalsColArr));

        //courseGoals to ULOs mapping
        $uloCoursegoalMapping = [];
        for($i = $uloStartAt; $i <= $uloEndAt; $i++){
            if(in_array($i, $this->hiddenRowsArr['outcomes'])){ continue; }
            for($j = $courseGoalsColStartAt; $j <= $courseGoalsColEndAt; $j++){
                if(in_array($j, $this->hiddenRowsArr['outcomes'])){ continue; }
                if(strtolower($this->outcomesData[$i][$j]) == 'true'){
                    $uloCoursegoalMapping[$i][] = $j;
                }
            }
        }

        // echo "<h1>ULOs to course goals mapping</h1><br>";
        // echo "<pre>";
        // print_r($uloCoursegoalMapping);
        // echo "</pre>";

        $this->outcomesDataMapped['assessments'] = $assessmentsArr;
        $this->outcomesDataMapped['course_goals'] = $courseGoalsArr;
        $this->outcomesDataMapped['unit_level_objectives'] = $uloArr;
        $this->outcomesDataMapped['cg_am_mapping'] = $courseAssessmentMapping;
        $this->outcomesDataMapped['ulo_am_mapping'] = $uloAssessmentMapping;
        $this->outcomesDataMapped['cg_ulo_mapping'] = $uloCoursegoalMapping;

        echo "<h1>Structured Data</h1><br>";
        echo "<pre>";
        print_r($this->outcomesDataMapped);
        echo "</pre>";
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
