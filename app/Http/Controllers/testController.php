<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


ini_set("memory_limit", "1024M");
set_time_limit(0);
// define('STDIN', fopen("php://stdin", "r"));
class testController extends Controller
{
    private $overviewData, $outcomesData, $assessmentsData, $courseMapData, $courseOverviewToolboxData;
    private $overviewDataMapped, $outcomesDataMapped, $assessmentsDataMapped, $courseMapDataMapped, $courseOverviewToolboxDataMapped = [];
    private $hiddenRowsArr, $hiddenColsArr = [];

    private $lastChangeDateTime;
    private $uri;
    private $sheetTitle, $courseName, $courseStatement, $programName =  '';

    const ADMIN_BASE_URL = 'https://dev.frost.2u.com';
    const ALIGN_BASE_URL = 'https://api.dev.frost.2u.com';
    const TWOU_DOMAIN = 'https://frost.2u.com';
    const PROGRAMS_TAXONOMY_NAME = 'List of Programs (LXP)';

    //tmp variables
    const TENANT_CODE = 'alig4';
    const USER_EMAIL = 'dinesh.amle@learningmate.com';

    public function parseUrl($url)
    {
        // $url = "https://docs.google.com/spreadsheets/d/15YO3e8lsRPwqpqZZCG7Ykyr2byNu5pe72-HzZTpzUlY/edit#gid=153567492";
        // $parsedUrl = parse_url($url);
        // print_r($parsedUrl);

        // $url = "https://docs.google.com/spreadsheets";
        $spreadsheetId = '';
        preg_match("/(?<=\\/d\\/)[^\\/]*/", $url, $parsedUrl);
        if (isset($parsedUrl[0])) {
            $spreadsheetId = $parsedUrl[0];
        }

        return $spreadsheetId;
    }

    public function lxpdataingest()
    {
        if (isset($_GET['code'])) { // user is redirected from authorization
            $stateData = json_decode(urldecode($_GET['state']), true);
            print_r($stateData);

            ///back
            $client = $this->getClient();
            $this->process($client, $stateData['sId']);
            $this->validations($stateData['cId']);
            //fetch tenant_code and user_email from course_id
            // $tenantCode = self::TENANT_CODE;
            // $userEmail = self::USER_EMAIL;
            
            // $this->createTaxonomyCaseJson($tenantCode, $userEmail);
        } else {
            //these params can be received from post 
            $url = "https://docs.google.com/spreadsheets/d/15YO3e8lsRPwqpqZZCG7Ykyr2byNu5pe72-HzZTpzUlY/edit#gid=153567492";
            // $url = "https://docs.google.com/document/d/1BGX76oSfJnsh0nmxNAeGeqt42NaDg0pCOFEdDb2vyec/edit";
            // $url = "https://docs.google.com/spreadsheets/d/1zgzfImQA-oN7zfrnYtt8mH88NBP2kmXs_82_KvpkMn8/edit#gid=0";
            // $url = "https://docs.google.com/document/d/1qzvjYIApcYZOWp1TBVL0IF4-G7vh2oe-mQKJes45XpI/edit";
            $courseId = 123;

            $spreadsheetId = $this->parseUrl($url);
            if ($spreadsheetId == '') {
                echo "handle error for Spreadsheet Id";
                exit;
            }

            if ($courseId == '') {
                echo "handle error for Course Id";
                exit;
            }

            $extraParamString = urlencode(json_encode(['cId' => $courseId, 'sId' => $spreadsheetId]));
            $client = $this->getClient($extraParamString);
            $this->process($client, $spreadsheetId);
            $this->validations($courseId);

            //fetch tenant_code and user_email from course_id
            // $tenantCode = self::TENANT_CODE;
            // $userEmail = self::USER_EMAIL;

            // $this->createTaxonomyCaseJson($tenantCode, $userEmail);
        }
    }

    public function validations($courseId)
    {
        echo "Do data validations here";
        echo $courseId;
    }

    public function process($client, $spreadsheetId)
    {
        // Get the API client and construct the service object.
        try {
            $service = new \Google_Service_Sheets($client);
            // echo "<pre>";
            // var_dump($service);exit;
            // Prints the names and majors of students in a sample spreadsheet:
            // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
            // $spreadsheetId = '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms';
            // $spreadsheetId = '18pW-2X1FL5hdEjut6CmdgnotVCmVpsut9JwqUmm7ako';
            // $spreadsheetId = '1c_EqDGs3OvzncVeNEIG7ZWlHXviF011FnLS4rcnQzbk';
            // $spreadsheetId = '15YO3e8lsRPwqpqZZCG7Ykyr2byNu5pe72-HzZTpzUlY';

            //get sheet titles and hidden rows
            $response = $service->spreadsheets->get($spreadsheetId);
            $this->sheetTitle = $response->getProperties()->getTitle();
            // var_dump($response);exit;

            $sheets = [];
            foreach ($response->getSheets() as $s) {
                $title = strtolower($s['properties']['title']);
                if (!in_array($title, config('lxp.spreadsheet.sheet_titles'))) {
                    continue;
                };
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

            $rowMetadata = null;
            $colMetadata = null;
            $sheetMetadata = null;
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
                echo "Following mandatory sheet(s) are not found: " . implode(", ", $diff);
                exit;
            }

            foreach ($response->getSheets() as $s) {
                if (!in_array(strtolower($s['properties']['title']), config('lxp.spreadsheet.sheet_titles'))) continue;
                echo $sheetTitle = $s['properties']['title'];
                $res = $service->spreadsheets_values->get($spreadsheetId, $sheetTitle);
                $values = $res->getValues();
                $res = null;

                if (is_array($values))
                    array_walk_recursive($values, function (&$arrValue, $arrKey) {
                        $arrValue = trim($arrValue);
                    });

                if (strtolower($sheetTitle) == 'overview')
                    $this->overviewData = $values;

                if (strtolower($sheetTitle) == 'outcomes')
                    $this->outcomesData = $values;

                if (strtolower($sheetTitle) == 'assessments')
                    $this->assessmentsData = $values;

                if (strtolower($sheetTitle) == 'course map')
                    $this->courseMapData = $values;

                if (strtolower($sheetTitle) == 'course overview & toolbox')
                    $this->courseOverviewToolboxData = $values;

                $values = null;
            }

            $response = null;
            // $this->overviewDatamapper();
            // $this->assessmentDatamapper();
            // $this->outcomesDatamapper();
            $this->courseMapDatamapper();
            // $this->courseOverviewToolboxDatamapper();

        } catch (Exception $e) {
            echo $e->getMessage();
            // $k = json_decode($e->getMessage(), true);
            // print_r($k);
            // echo $k->error->message;
            echo json_decode($e->getMessage(), true)['error']['message'];
        }
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
        foreach ($this->overviewData as $k => $v) {
            if (in_array($k, $this->hiddenRowsArr['overview'])) {
                continue;
            }

            if (empty($v)) {
                $key = '';
            }

            if (!empty($v[0]) && empty($v[1])) {
                $key = $v[0];
            }

            if ($key != '' && !empty($v[0]) && !empty($v[1])) {
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

        $this->courseName = $this->overviewDataMapped['Course Overview']['Course Name'];
        $this->programName = $this->overviewDataMapped['Course Overview']['Program'];

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

        //add feedback string to row 2 feedback types - such as 'automatic', 'self', 'peer', 'instructor', 'rubric'
        $assessmentKeysArr = [];
        foreach ($this->assessmentsData[0] as $k => $v) {
            if (strlen($v) != strlen(utf8_decode($v))) { //column has BLACK DOWN-POINTING TRIANGLE in value
                $v = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $v); // remove special chracters
                //    $v = preg_replace('/\s+/', '', $v); // remove spaces between alphabets
            }
            $assessmentKeysArr[] = (empty($v) ? 'Feedback' : $v) . " " . (isset($this->assessmentsData[1][$k]) ? $this->assessmentsData[1][$k] : '');
        }

        $assessmentKeysArr = array_map('trim', $assessmentKeysArr);

        // echo "<h1>Keys</h1><br>";
        // echo "<pre>";
        // print_r($assessmentKeysArr);
        // echo "</pre>";



        $headerKeyCnt = count($assessmentKeysArr);
        for ($i = 2; $i < count($this->assessmentsData); $i++) {
            if (in_array($i, $this->hiddenRowsArr['assessments'])) {
                continue;
            }
            if ($this->assessmentsData[$i][0] == "") {
                break;
            }

            if (count($this->assessmentsData[$i]) < $headerKeyCnt) {
                $key =  0;
                while ($key < $headerKeyCnt) {
                    if (!(array_key_exists($key, $this->assessmentsData[$i]))) {
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
        // echo "<h1>Original Course Map Data</h1><br>";
        // echo "<pre>";
        // print_r($this->courseMapData);
        // echo "</pre>";
        // echo "</br>";
        $this->courseMapDataMapped = $startEndArr = [];

        //get start and end row numbers
        $startEndArr = [];
        $prevWeek = null;
        foreach ($this->courseMapData as $k => $v) {
            if (isset($v[0]) && $v[0] == 'UNIT') {
                if (isset($prevWeek)) {
                    $startEndArr[$prevWeek]['endsAt'] = $k - 1;
                }
                $prevWeek = $v[1];
                $startEndArr[$v[1]]['startsAt'] = $k;
            }
        }

        //add end row to the last element
        $startEndArr[array_key_last($startEndArr)]['endsAt'] = array_key_last($this->courseMapData);

        //get column number for Unit Design Sign-Off
        $unitDesignSignOffColNum = null;
        foreach ($this->courseMapData[0] as $colkey => $colData) {
            if (strtolower($colData) == 'unit design sign-off') {
                $unitDesignSignOffColNum = $colkey;
                break;
            }
        }

        //get column number for Unit Final Review Signoff
        $unitFinalReviewSignOffColNum = null;
        foreach ($this->courseMapData[0] as $colkey => $colData) {
            if (strtolower($colData) == 'unit final review sign-off') {
                $unitFinalReviewSignOffColNum = $colkey;
                break;
            }
        }

        //get column number for Confirmed Time Estimates
        $preliminaryTimeEstimateColNum = null;
        foreach ($this->courseMapData[1] as $colkey => $colData) {
            if (strtolower($colData) == 'p r e l i m i n a r y   t i m e   e s t i m a t e s') {
                $preliminaryTimeEstimateColNum = $colkey;
                break;
            }
        }

        //get column number for Confirmed Time Estimates
        $confirmedTimeEstimateColNum = null;
        foreach ($this->courseMapData[0] as $colkey => $colData) {
            if (strtolower($colData) == 'c o n f i r m e d   t i m e   e s t i m a t e s') {
                $confirmedTimeEstimateColNum = $colkey;
                break;
            }
        }

        //Add description and ULOs to weeks
        foreach ($startEndArr as $k => $v) {
            $this->courseMapDataMapped[$k]['desc'] = $this->courseMapData[$v['startsAt'] + 1][0]; //+1 means next row
        }

        //Add ULOs to data
        foreach ($startEndArr as $k => $v) {
            //Unit design sign off
            if (!is_null($unitDesignSignOffColNum)) {
                $this->courseMapDataMapped[$k]['unitDesignSignOff'] = $this->courseMapData[$v['startsAt'] + 1][$unitDesignSignOffColNum];
                $this->courseMapDataMapped[$k]['unitDesignSignOffComment'] = $this->courseMapData[$v['startsAt'] + 2][$unitDesignSignOffColNum];
            }

            //Unit Final Review Signoff
            if (!is_null($unitFinalReviewSignOffColNum)) {
                $this->courseMapDataMapped[$k]['unitFinalReviewSignOff'] = isset($this->courseMapData[$v['startsAt'] + 1][$unitFinalReviewSignOffColNum]) ? $this->courseMapData[$v['startsAt'] + 1][$unitFinalReviewSignOffColNum] : '';
                $this->courseMapDataMapped[$k]['unitFinalReviewSignOffComment'] = isset($this->courseMapData[$v['startsAt'] + 2][$unitFinalReviewSignOffColNum]) ? $this->courseMapData[$v['startsAt'] + 2][$unitFinalReviewSignOffColNum] : '';
            }

            //preliminary time estimate data
            if (!is_null($preliminaryTimeEstimateColNum)) {
                $this->courseMapDataMapped[$k]['preliminaryTimeEstimate'][$this->courseMapData[$v['startsAt'] + 2][$preliminaryTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 2][$preliminaryTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['preliminaryTimeEstimate'][$this->courseMapData[$v['startsAt'] + 3][$preliminaryTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 3][$preliminaryTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['preliminaryTimeEstimate'][$this->courseMapData[$v['startsAt'] + 4][$preliminaryTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 4][$preliminaryTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['preliminaryTimeEstimate'][$this->courseMapData[$v['startsAt'] + 5][$preliminaryTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 5][$preliminaryTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['preliminaryTimeEstimate'][$this->courseMapData[$v['startsAt'] + 6][$preliminaryTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 6][$preliminaryTimeEstimateColNum];
            }

            //confirmed time estimate data
            if (!is_null($confirmedTimeEstimateColNum)) {
                $this->courseMapDataMapped[$k]['confirmedTimeEstimate'][$this->courseMapData[$v['startsAt'] + 1][$confirmedTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 1][$confirmedTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['confirmedTimeEstimate'][$this->courseMapData[$v['startsAt'] + 2][$confirmedTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 2][$confirmedTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['confirmedTimeEstimate'][$this->courseMapData[$v['startsAt'] + 3][$confirmedTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 3][$confirmedTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['confirmedTimeEstimate'][$this->courseMapData[$v['startsAt'] + 4][$confirmedTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 4][$confirmedTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['confirmedTimeEstimate'][$this->courseMapData[$v['startsAt'] + 5][$confirmedTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 5][$confirmedTimeEstimateColNum];
                $this->courseMapDataMapped[$k]['confirmedTimeEstimate'][$this->courseMapData[$v['startsAt'] + 6][$confirmedTimeEstimateColNum - 1]] = $this->courseMapData[$v['startsAt'] + 6][$confirmedTimeEstimateColNum];
            }

            for ($i = $v['startsAt'] + 2; $i < $v['endsAt']; $i++) { //skip 2 rows as ULOs will start 2 rows after the word 'week'
                if (in_array($i, $this->hiddenRowsArr['course map'])) {
                    continue;
                } //ignore hidden rows

                if (isset($this->courseMapData[$i][0])) {
                    if ($this->courseMapData[$i][0] == 'Unit') {
                        break; //break the inner for loop and repeat with the next unit
                    }
                }

                if (isset($this->courseMapData[$i][0]) && $this->courseMapData[$i][0] != '') {
                    $this->courseMapDataMapped[$k]['ulo'][] = ['title' => $this->courseMapData[$i][0], 'desc' => (isset($this->courseMapData[$i][1]) ? $this->courseMapData[$i][1] : '')];
                }
            }
        }

        //get course data start row
        foreach ($startEndArr as $k => $v) {
            for ($i = $v['startsAt']; $i < $v['endsAt']; $i++) {
                if (in_array($i, $this->hiddenRowsArr['course map'])) {
                    continue;
                }
                if (isset($this->courseMapData[$i][0])) {
                    if ($this->courseMapData[$i][0] == 'Unit') {
                        $startEndArr[$k]['courseStartsAt'] = $i;
                        break;
                    }
                }
            }
        }

        //map course data
        foreach ($startEndArr as $k => $v) {
            $header = $this->courseMapData[$v['courseStartsAt']];
            $headerKeyCnt = count($header);
            for ($i = $v['courseStartsAt'] + 1; $i <= $v['endsAt']; $i++) { //course data starts right after the header row
                if (in_array($i, $this->hiddenRowsArr['course map'])) {
                    continue;
                }

                //add missing keys in rows so that associative array can be created
                if (count($this->courseMapData[$i]) < $headerKeyCnt) {
                    $key =  0;
                    while ($key < $headerKeyCnt) {
                        if (!(array_key_exists($key, $this->courseMapData[$i]))) {
                            $this->courseMapData[$i][$key] = '';
                        }
                        $key++;
                    }
                }

                $this->courseMapDataMapped[$k]['courseData'][] = array_combine($header, $this->courseMapData[$i]);
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
        print_r($this->courseMapDataMapped);
        echo "</pre>";
        echo "</br>";
    }

    function outcomesDatamapper()
    {
        // echo "<h1>Original Outcomes Data</h1><br>";
        // echo "<pre>";
        // print_r($this->outcomesData);
        // echo "</pre>";
        // echo "</br>";

        //check first row for Course Statement
        if (strtolower($this->outcomesData[0][0]) != 'course statement') {
            echo "Course Statement unavailable in the 'Outcomes' sheet";
            exit;
        }

        $this->courseStatement = $this->outcomesData[0][1];
        //check assessment starting point
        $assessmentsStartAt  = null;
        foreach ($this->outcomesData[0] as $k => $v) {
            if (strtolower($v) == 'assessments') {
                $assessmentsStartAt = $k;
            }
        }

        if (is_null($assessmentsStartAt)) {
            echo "Assessments  unavailable in the 'Outcomes' sheet";
            exit;
        }

        // echo "<pre>";
        // print_r($this->outcomesData);
        // echo "</pre>";
        //get assessment data
        $assessmentsArr = [];
        $assessmentsEndAt  = null;
        for ($i = $assessmentsStartAt; $i < count($this->outcomesData[1]); $i++) {
            if (in_array($i, $this->hiddenColsArr['outcomes'])) {
                continue;
            }
            $assessmentsArr[$i]['title'] = isset($this->outcomesData[1][$i]) ? $this->outcomesData[1][$i] : '';
            $assessmentsArr[$i]['type'] = isset($this->outcomesData[2][$i]) ? $this->outcomesData[2][$i] : '';
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
        foreach ($this->outcomesData as $k => $v) {
            if (in_array($k, $this->hiddenRowsArr['outcomes'])) {
                continue;
            }

            if ($courseGoalsFound == true && !isset($v[0])) {
                $courseGoalsEndAt = $k - 1;
                $courseGoalsFound = false;
                break;
            }

            if ($courseGoalsFound) {
                if (is_null($courseGoalsStartAt)) {
                    $courseGoalsStartAt = $k;
                }
                $courseGoalsArr[$k]['title'] = $v[0];
                $courseGoalsArr[$k]['desc'] = $v[1];
            }

            if ($courseGoalsFound == false && isset($v[0]) && strtolower($v[0]) == 'course goals') {
                $courseGoalsFound = true;
            }
        }

        $courseGoalsExist = false;
        if (empty($courseGoalsArr)) {
            echo "Course goals not found";
            exit;
        } else {
            $courseGoalsExist = true;
        }

        if (!$courseGoalsExist) {
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
        foreach ($this->outcomesData as $k => $v) {
            if (in_array($k, $this->hiddenRowsArr['outcomes'])) {
                continue;
            }

            if ($uloFound == true && (!isset($v[0]) || $v[0] == "")) {
                continue;
            }

            if ($uloFound) {
                if (is_null($uloStartAt)) {
                    $uloStartAt = $k;
                }
                $uloArr[$k]['title'] = $v[0];
                $uloArr[$k]['desc'] = $v[1];
                $uloEndAt = $k;
            }

            if ($uloFound == false && isset($v[0]) && strtolower($v[0]) == 'unit level objectives') {
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
        for ($i = $courseGoalsStartAt; $i <= $courseGoalsEndAt; $i++) {
            if (in_array($i, $this->hiddenRowsArr['outcomes'])) {
                continue;
            }
            for ($j = $assessmentsStartAt; $j <= $assessmentsEndAt; $j++) {
                if (strtolower($this->outcomesData[$i][$j]) == 'true') {
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
        for ($i = $uloStartAt; $i <= $uloEndAt; $i++) {
            if (in_array($i, $this->hiddenRowsArr['outcomes'])) {
                continue;
            }
            for ($j = $assessmentsStartAt; $j <= $assessmentsEndAt; $j++) {
                if (strtolower($this->outcomesData[$i][$j]) == 'true') {
                    $uloAssessmentMapping[$i][] = $j;
                }
            }
        }

        // echo "<h1>ULOs to assessment mapping</h1><br>";
        // echo "<pre>";
        // print_r($uloAssessmentMapping);
        // echo "</pre>";

        //get course goals columns
        $courseGoalsColArr = array_filter($this->outcomesData[$uloStartAt - 1]);
        $courseGoalsColStartAt = min(array_keys($courseGoalsColArr));
        $courseGoalsColEndAt = max(array_keys($courseGoalsColArr));

        //courseGoals to ULOs mapping
        $uloCoursegoalMapping = [];
        for ($i = $uloStartAt; $i <= $uloEndAt; $i++) {
            if (in_array($i, $this->hiddenRowsArr['outcomes'])) {
                continue;
            }
            for ($j = $courseGoalsColStartAt; $j <= $courseGoalsColEndAt; $j++) {
                if (in_array($j, $this->hiddenRowsArr['outcomes'])) {
                    continue;
                }
                if (strtolower($this->outcomesData[$i][$j]) == 'true') {
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

    function courseOverviewToolboxDatamapper()
    {
        echo "<h1>Original Course Map Data</h1><br>";
        echo "<pre>";
        print_r($this->courseOverviewToolboxData);
        echo "</pre>";
        echo "</br>";
        $this->courseOverviewToolboxDataMapped = $startEndArr = [];

        //get start and end row numbers
        $startEndArr = [];
        $prevWeek = null;
        $iterator = 1;
        foreach ($this->courseOverviewToolboxData as $k => $v) {
            if (isset($v[0]) && strtolower($v[0]) == 'unit') {
                if (isset($prevWeek)) {
                    $startEndArr[$prevWeek]['endsAt'] = $k - 1;
                }
                $prevWeek = $iterator;
                $startEndArr[$iterator]['startsAt'] = $k;
                $iterator++;
            }
        }


        //add end row to the last element
        $startEndArr[array_key_last($startEndArr)]['endsAt'] = array_key_last($this->courseOverviewToolboxData);

        //get column number for Unit Design Sign-Off
        // $unitDesignSignOffColNum = null;
        // foreach($this->courseOverviewToolboxData[0] as $colkey => $colData){
        //     if(strtolower($colData) == 'unit design sign-off'){
        //         $unitDesignSignOffColNum = $colkey;
        //         break;
        //     }
        // }

        //get column number for Unit Final Review Signoff
        // $unitFinalReviewSignOffColNum = null;
        // foreach($this->courseOverviewToolboxData[0] as $colkey => $colData){
        //     if(strtolower($colData) == 'unit final review sign-off'){
        //         $unitFinalReviewSignOffColNum = $colkey;
        //         break;
        //     }
        // }

        //get column number for Confirmed Time Estimates
        // $preliminaryTimeEstimateColNum = null;
        // foreach($this->courseOverviewToolboxData[1] as $colkey => $colData){
        //     if(strtolower($colData) == 'p r e l i m i n a r y   t i m e   e s t i m a t e s'){
        //         $preliminaryTimeEstimateColNum = $colkey;
        //         break;
        //     }
        // }

        //get column number for Confirmed Time Estimates
        // $confirmedTimeEstimateColNum = null;
        // foreach($this->courseOverviewToolboxData[0] as $colkey => $colData){
        //     if(strtolower($colData) == 'c o n f i r m e d   t i m e   e s t i m a t e s'){
        //         $confirmedTimeEstimateColNum = $colkey;
        //         break;
        //     }
        // }

        //Add description and ULOs to weeks
        foreach ($startEndArr as $k => $v) {
            $this->courseOverviewToolboxDataMapped[$k]['desc'] = $this->courseOverviewToolboxData[$v['startsAt'] + 1][0]; //+1 means next row
        }


        //Add ULOs to data
        // foreach($startEndArr as $k => $v){
        //Unit design sign off
        // if(!is_null($unitDesignSignOffColNum)){
        //     $this->courseOverviewToolboxDataMapped[$k]['unitDesignSignOff'] = isset($this->courseOverviewToolboxData[$v['startsAt']+1][$unitDesignSignOffColNum])?$this->courseOverviewToolboxData[$v['startsAt']+1][$unitDesignSignOffColNum]:'';
        //     $this->courseOverviewToolboxDataMapped[$k]['unitDesignSignOffComment'] = isset($this->courseOverviewToolboxData[$v['startsAt']+2][$unitDesignSignOffColNum])?$this->courseOverviewToolboxData[$v['startsAt']+2][$unitDesignSignOffColNum]:'';
        // }
        //Unit Final Review Signoff
        // if(!is_null($unitFinalReviewSignOffColNum)){
        //     $this->courseOverviewToolboxDataMapped[$k]['unitFinalReviewSignOff'] = isset($this->courseOverviewToolboxData[$v['startsAt']+1][$unitFinalReviewSignOffColNum])?$this->courseOverviewToolboxData[$v['startsAt']+1][$unitFinalReviewSignOffColNum]:'';
        //     $this->courseOverviewToolboxDataMapped[$k]['unitFinalReviewSignOffComment'] = isset($this->courseOverviewToolboxData[$v['startsAt']+2][$unitFinalReviewSignOffColNum])?$this->courseOverviewToolboxData[$v['startsAt']+2][$unitFinalReviewSignOffColNum]:'';
        // }

        // //preliminary time estimate data
        // if(!is_null($preliminaryTimeEstimateColNum)){
        //     $this->courseOverviewToolboxDataMapped[$k]['preliminaryTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+2][$preliminaryTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+2][$preliminaryTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['preliminaryTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+3][$preliminaryTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+3][$preliminaryTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['preliminaryTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+4][$preliminaryTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+4][$preliminaryTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['preliminaryTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+5][$preliminaryTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+5][$preliminaryTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['preliminaryTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+6][$preliminaryTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+6][$preliminaryTimeEstimateColNum];
        // }

        // //confirmed time estimate data
        // if(!is_null($confirmedTimeEstimateColNum)){
        //     $this->courseOverviewToolboxDataMapped[$k]['confirmedTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+1][$confirmedTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+1][$confirmedTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['confirmedTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+2][$confirmedTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+2][$confirmedTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['confirmedTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+3][$confirmedTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+3][$confirmedTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['confirmedTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+4][$confirmedTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+4][$confirmedTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['confirmedTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+5][$confirmedTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+5][$confirmedTimeEstimateColNum];
        //     $this->courseOverviewToolboxDataMapped[$k]['confirmedTimeEstimate'][$this->courseOverviewToolboxData[$v['startsAt']+6][$confirmedTimeEstimateColNum-1]] = $this->courseOverviewToolboxData[$v['startsAt']+6][$confirmedTimeEstimateColNum];
        // }

        // for($i = $v['startsAt']+2; $i < $v['endsAt']; $i++){ //skip 2 rows as ULOs will start 2 rows after the word 'week'
        //     if(in_array($i, $this->hiddenRowsArr['course overview & toolbox'])){ continue; } //ignore hidden rows

        //     if(isset($this->courseOverviewToolboxData[$i][0])) {
        //         if($this->courseOverviewToolboxData[$i][0] == 'Page'){
        //             break; //break the inner for loop and repeat with the next unit
        //         }
        //     }

        //     if(isset($this->courseOverviewToolboxData[$i][0]) && $this->courseOverviewToolboxData[$i][0] != ''){
        //         $this->courseOverviewToolboxDataMapped[$k]['ulo'][] = ['title' => $this->courseOverviewToolboxData[$i][0], 'desc' => (isset($this->courseOverviewToolboxData[$i][1])?$this->courseOverviewToolboxData[$i][1]:'')];
        //     }
        // }
        // }

        //get course data start row
        foreach ($startEndArr as $k => $v) {
            for ($i = $v['startsAt']; $i < $v['endsAt']; $i++) {
                if (in_array($i, $this->hiddenRowsArr['course overview & toolbox'])) {
                    continue;
                }
                if (isset($this->courseOverviewToolboxData[$i][0])) {
                    if ($this->courseOverviewToolboxData[$i][0] == 'Page') {
                        $startEndArr[$k]['courseStartsAt'] = $i;
                        break;
                    }
                }
            }
        }

        //map course data
        foreach ($startEndArr as $k => $v) {
            $header = $this->courseOverviewToolboxData[$v['courseStartsAt']];
            $headerKeyCnt = count($header);
            for ($i = $v['courseStartsAt'] + 1; $i <= $v['endsAt']; $i++) { //course data starts right after the header row
                if (in_array($i, $this->hiddenRowsArr['course overview & toolbox'])) {
                    continue;
                }
                if (!isset($this->courseOverviewToolboxData[$i][2])) {
                    continue;
                }

                //add missing keys in rows so that associative array can be created
                if (count($this->courseOverviewToolboxData[$i]) < $headerKeyCnt) {
                    $key =  0;
                    while ($key < $headerKeyCnt) {
                        if (!(array_key_exists($key, $this->courseOverviewToolboxData[$i]))) {
                            $this->courseOverviewToolboxData[$i][$key] = '';
                        }
                        $key++;
                    }
                }

                $this->courseOverviewToolboxDataMapped[$k]['courseData'][] = array_combine($header, $this->courseOverviewToolboxData[$i]);
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
          print_r($this->courseOverviewToolboxData);
          echo "</pre></td>
        </tr>
      </table>";*/

        echo "<h1>Structured Data</h1><br>";
        echo "<pre>";
        print_r($this->courseOverviewToolboxDataMapped);
        echo "</pre>";
        echo "</br>";
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient($extraParamString = '')
    {
        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        // $client->setAuthConfig('credentials.json');
        $client->setAuthConfig(base_path() . '\credentials.json');
        $client->setAccessType('offline');
        if (isset($extraParamString) && $extraParamString != '') {
            $client->setState($extraParamString);
        }
        $client->setPrompt('select_account consent');

        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/lxpdataingest');
        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.

        //need to use db connection instead of toekn.json file
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
            //Need to store tokens in DB instead of file
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            //end
        }
        return $client;
    }



















    private function httpRequest($params)
    {
        $curl = curl_init();
        $curlOptions = [
            CURLOPT_URL => $params['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $params['method']
        ];

        if (isset($params['data'])) {
            $curlOptions[CURLOPT_POSTFIELDS] = $params['data'];
        }

        if (isset($params['headers'])) {
            $curlOptions[CURLOPT_HTTPHEADER] = $params['headers'];
        }

        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function getAccessToken($tenant_code)
    {
        //get client_id and client_secret
        $response = $this->httpRequest(
            [
                'url' => self::ADMIN_BASE_URL . '/oauth/customapi/v1/clientmanager/getclientbytenant',
                'method' => 'POST',
                'data' => json_encode(['TenantShortCode' => $tenant_code]),
                'headers' => [
                    'Content-Type: application/json'
                ]
            ]
        );
        $response = json_decode($response, true);


        if (strtolower($response['status']) == 'failed') { //tenant code doesn't exist. Create a new client on Admin using the API 

            //get tenant details from shortcode
            $response = $this->httpRequest(
                [
                    'url' => self::ADMIN_BASE_URL . '/admin/api/tenant/getconfigurationbyshortcode',
                    'method' => 'POST',
                    'data' => json_encode(['ShortCode' => $tenant_code]),
                    'headers' => [
                        'Content-Type: application/json',
                        'origin: ' . self::ADMIN_BASE_URL
                    ]
                ]
            );
            $response = json_decode($response, true);

            //create client in Admin
            $clientId = $this->createUUID();
            $clientSecret = $this->createUUID();
            $data = [
                'clientId' => $clientId,
                'secret' => $clientSecret,
                'allowedGrantTypes' => ['ClientCredentials'],
                'scopes' => ["Organization", "LORAPIACCESS"],
                'clientName' => $response['Name'],
                'tenantId' => $response['Id'],
                'tenantName' => $response['Name']
            ];

            $response = $this->httpRequest(
                [
                    'url' => self::ADMIN_BASE_URL . '/oauth/customapi/v1/clientmanager/create',
                    'method' => 'POST',
                    'data' => json_encode($data),
                    'headers' => [
                        'Content-Type: application/json'
                    ]
                ]
            );
        } else {
            $clientId = $response['clients'][0]['clientID'];
            $clientSecret = $response['clients'][0]['clientSecret'];
        }

        //get access token
        $response = $this->httpRequest(
            [
                'url' => self::ADMIN_BASE_URL . '/oauth/connect/token',
                'method' => 'POST',
                'data' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials'
                ]
            ]
        );

        $response = json_decode($response, true);
        return $response['access_token'];
    }

    public function createTaxonomyCaseJson($tenantCode, $userEmail)
    {
        // try{
        $this->setLastChangeDateTime();
        $this->setUri();

        // $json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"},"5":{"title":"C3","desc":"Demonstrate a commitment to radical social work practices to dismantle oppressive systems and create opportunities for equity for complex and diverse populations"},"6":{"title":"C4","desc":"Use scholarly knowledge and scholarly practices to critically elevate and evaluate the epistemologies that inform evidence based practices (i.e what is missing)"},"7":{"title":"C5","desc":"Formulate culturally-informed and responsive working alliances with clients from diverse and complex populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"},"14":{"title":"U3","desc":"Examine self-location, social identities and intersubjectivity with complex and diverse populations; addressing issues of intersectionality; how do identities of therapists affect their work"},"15":{"title":"U4","desc":"Identify ethical imperatives in practice with complex and diverse populations"},"16":{"title":"U5","desc":"Identify the common assumptions attributed to various complex and diverse populations"},"17":{"title":"U6","desc":"Critically examine the source and history of assumptions made about complex and diverse populations"},"18":{"title":"U7","desc":"Provide alternate explanations that challenge the assumptions made about complex and diverse populations"},"19":{"title":"U8","desc":"Examine how assumptions impact application of skills to complex and diverse populations"},"20":{"title":"U9","desc":"Demonstrate the ability to communicate revolutionary thinking in verbal and written communications"},"21":{"title":"U10","desc":"Create a personal plan for continued growth in utilizing radical social work practices"},"22":{"title":"U11","desc":"Identify opportunities for equity for complex and diverse populations"},"23":{"title":"U12","desc":"Engaging a narrative inquiry lens as an anti-oppressive strategy of radical social work practices"},"24":{"title":"U13","desc":"Use specific cases to demonstrate knowledge of skill sets related to a particular population"},"25":{"title":"U14","desc":"Generate knowledge to support\/enhance skill applications with diverse and complex populations"},"26":{"title":"U15","desc":"Critically examine clinical processes of change with diverse and complex populations"},"27":{"title":"U16","desc":"Critically analyze the impact of intersubjectivity, transference, countertransference and the use of defenses when working with complex and diverse populations"},"28":{"title":"U17","desc":"Critically examine how one might go about decolonizing and decentering Western values and beliefs in these clinical processes"},"29":{"title":"U18","desc":"Generate knowledge strategies to enhance anti-oppressive frameworks in clinical practice"},"30":{"title":"U19","desc":"Develop knowledge on a particular population or focus area"},"31":{"title":"U20","desc":"Find contemporary examples of how radical social work has been practiced (or not practiced) with this population\/focus area"},"32":{"title":"U21","desc":"Describe radical social work practices that could be utilized for this population\/focus area"},"33":{"title":"U22","desc":"Using a case example\/study to apply radical social work skills"},"34":{"title":"U23","desc":""},"35":{"title":"U24","desc":""},"36":{"title":"U25","desc":""},"37":{"title":"U26","desc":""},"38":{"title":"U27","desc":""},"39":{"title":"U28","desc":""},"40":{"title":"U29","desc":""},"41":{"title":"U30","desc":""},"42":{"title":"U31","desc":""},"43":{"title":"U32","desc":""},"44":{"title":"U33","desc":""},"45":{"title":"U34","desc":""},"46":{"title":"U35","desc":""},"47":{"title":"U36","desc":""},"48":{"title":"U37","desc":""},"49":{"title":"U38","desc":""},"50":{"title":"U39","desc":""},"51":{"title":"U40","desc":""}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3],"13":[3],"14":[3],"15":[3],"16":[4],"17":[4],"18":[4],"19":[4],"20":[5],"21":[5],"22":[5],"23":[5],"24":[5],"25":[5],"26":[5],"27":[5],"28":[5],"29":[5],"30":[5],"31":[5],"32":[6],"33":[7]}}';
        // $json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3,4],"13":[4]}}';
        // $outcomesArr = json_decode($json, true);

        $outcomesArr = $this->outcomesDataMapped;
        //doc node details
        $docTitle = $this->sheetTitle;
        // $loggedInUser = 'tester';
        $docIdentifier = $this->createUUID();

        //item details
        foreach ($outcomesArr['course_goals'] as $k => $v) {
            if (isset($v['desc']) && $v['desc'] != '')
                $outcomesArr['course_goals'][$k]['uuid'] = $this->createUUID();
        }

        foreach ($outcomesArr['unit_level_objectives'] as $k => $v) {
            if (isset($v['desc']) && $v['desc'] != '')
                $outcomesArr['unit_level_objectives'][$k]['uuid'] = $this->createUUID();
        }

        $caseJsonArr = [];

        //document section
        $caseJsonArr['CFDocument'] =
            [
                'identifier' => $docIdentifier,
                'uri' => $this->uri,
                'title' => $docTitle,
                'lastChangeDateTime' => $this->lastChangeDateTime,
                // 'creator' => $loggedInUser,
                'adoptionStatus' => 'Draft',
                'CFPackageURI' =>
                [
                    'identifier' => $docIdentifier,
                    'uri' => $this->uri,
                    'title' => $docTitle
                ]
            ];

        //item types
        // Course Statement
        $courseStatementItemTypeIdentifier = $this->createUUID();
        $courseStatementItemTypeTitle = 'Course Statement';
        $caseJsonArr['CFDefinitions']['CFItemTypes'][] = $this->createItemTypes($courseStatementItemTypeIdentifier, $courseStatementItemTypeTitle);

        // Course
        $courseItemTypeIdentifier = $this->createUUID();
        $courseItemTypeTitle = 'Course';
        $caseJsonArr['CFDefinitions']['CFItemTypes'][] = $this->createItemTypes($courseItemTypeIdentifier, $courseItemTypeTitle);

        // Course Goals
        $courseGoalsItemTypeIdentifier = $this->createUUID();
        $courseGoalsItemTypeTitle = 'Course Goals';
        $caseJsonArr['CFDefinitions']['CFItemTypes'][] = $this->createItemTypes($courseGoalsItemTypeIdentifier, $courseGoalsItemTypeTitle);

        // Unit Level Objectives
        $unitLevelObjectivesItemTypeIdentifier = $this->createUUID();
        $unitLevelObjectivesItemTypeTitle = 'Unit Level Objectives';
        $caseJsonArr['CFDefinitions']['CFItemTypes'][] = $this->createItemTypes($unitLevelObjectivesItemTypeIdentifier, $unitLevelObjectivesItemTypeTitle);

        //item section
        //first item
        $firstItemIdentifier = $this->createUUID();
        // $firstItemHumanCodingScheme = 'item1 hcs';
        $firstItemFullStatement = $this->courseName;
        $caseJsonArr['CFItems'][] = $this->createItem(
            [
                'id' => $firstItemIdentifier,
                'fullStatement' => $firstItemFullStatement,
                'courseItemTypeTitle' => $courseItemTypeTitle,
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'courseItemTypeId' => $courseItemTypeIdentifier
                // 'humanCodingScheme' => $firstItemHumanCodingScheme
            ]
        );

        //course statement item
        $courseStatementIdentifier = $this->createUUID();
        $courseStatementFullStatement = 'Course Statement';
        $caseJsonArr['CFItems'][] = $this->createItem(
            [
                'id' => $courseStatementIdentifier,
                'fullStatement' => $courseStatementFullStatement,
                'courseItemTypeTitle' => $courseStatementItemTypeTitle,
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'courseItemTypeId' => $courseStatementItemTypeIdentifier
            ]
        );

        //Statement item
        $statementIdentifier = $this->createUUID();
        $statementFullStatement = $this->courseStatement;
        $caseJsonArr['CFItems'][] = $this->createItem(
            [
                'id' => $statementIdentifier,
                'fullStatement' => $statementFullStatement,
                'courseItemTypeTitle' => $courseStatementItemTypeTitle,
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'courseItemTypeId' => $courseStatementItemTypeIdentifier
            ]
        );

        //course goals
        $courseGoalsIdentifier = $this->createUUID();
        $courseGoalsFullStatement = 'Course Goals';
        $caseJsonArr['CFItems'][] = $this->createItem(
            [
                'id' => $courseGoalsIdentifier,
                'fullStatement' => $courseGoalsFullStatement,
                'courseItemTypeTitle' => $courseGoalsItemTypeTitle,
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'courseItemTypeId' => $courseGoalsItemTypeIdentifier
            ]
        );

        foreach ($outcomesArr['course_goals'] as $k => $v) {
            if (isset($v['desc']) && $v['desc'] != '') {
                $caseJsonArr['CFItems'][] = $this->createItem(
                    [
                        'id' => $v['uuid'],
                        'fullStatement' => $v['desc'],
                        'courseItemTypeTitle' => $courseGoalsItemTypeTitle,
                        'docId' => $docIdentifier,
                        'docTitle' => $docTitle,
                        'courseItemTypeId' => $courseGoalsItemTypeIdentifier,
                        'humanCodingScheme' =>  $v['title']
                    ]
                );
            }
        }

        //unit level objectives
        $uloIdentifier = $this->createUUID();
        $uloFullStatement = 'Unit Level Objectives';
        $caseJsonArr['CFItems'][] = $this->createItem(
            [
                'id' => $uloIdentifier,
                'fullStatement' => $uloFullStatement,
                'courseItemTypeTitle' => $unitLevelObjectivesItemTypeTitle,
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'courseItemTypeId' => $unitLevelObjectivesItemTypeIdentifier
            ]
        );

        foreach ($outcomesArr['unit_level_objectives'] as $k => $v) {
            if (isset($v['desc']) && $v['desc'] != '') {
                $caseJsonArr['CFItems'][] = $this->createItem(
                    [
                        'id' => $v['uuid'],
                        'fullStatement' => $v['desc'],
                        'courseItemTypeTitle' => $unitLevelObjectivesItemTypeTitle,
                        'docId' => $docIdentifier,
                        'docTitle' => $docTitle,
                        'courseItemTypeId' => $unitLevelObjectivesItemTypeIdentifier,
                        'humanCodingScheme' =>  $v['title']
                    ]
                );
            }
        }

        // association section
        //first item to doc association
        $caseJsonArr['CFAssociations'][] = $this->createAssociation(
            [
                'associationType' => 'isChildOf',
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'originNodeId' => $firstItemIdentifier,
                'destinationNodeId' => $docIdentifier
            ]
        );

        //course statement to first item
        $caseJsonArr['CFAssociations'][] = $this->createAssociation(
            [
                'associationType' => 'isChildOf',
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'originNodeId' => $courseStatementIdentifier,
                'destinationNodeId' => $firstItemIdentifier,
                'sequenceNumber' => '1'
            ]
        );

        //statement to course statement association
        $caseJsonArr['CFAssociations'][] = $this->createAssociation(
            [
                'associationType' => 'isChildOf',
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'originNodeId' => $statementIdentifier,
                'destinationNodeId' => $courseStatementIdentifier
            ]
        );

        //course goals to first item
        $caseJsonArr['CFAssociations'][] = $this->createAssociation(
            [
                'associationType' => 'isChildOf',
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'originNodeId' => $courseGoalsIdentifier,
                'destinationNodeId' => $firstItemIdentifier,
                'sequenceNumber' => '2'
            ]
        );

        //ULOs to first item
        $caseJsonArr['CFAssociations'][] = $this->createAssociation(
            [
                'associationType' => 'isChildOf',
                'docId' => $docIdentifier,
                'docTitle' => $docTitle,
                'originNodeId' => $uloIdentifier,
                'destinationNodeId' => $firstItemIdentifier,
                'sequenceNumber' => '3'
            ]
        );

        //'isChildOf' associations for course goals
        $sequenceNumber = 1;
        foreach ($outcomesArr['course_goals'] as $k => $v) {
            if (isset($v['desc']) && $v['desc'] != '') {
                $caseJsonArr['CFAssociations'][] = $this->createAssociation(
                    [
                        'associationType' => 'isChildOf',
                        'docId' => $docIdentifier,
                        'docTitle' => $docTitle,
                        'originNodeId' => $v['uuid'],
                        'destinationNodeId' => $courseGoalsIdentifier,
                        'sequenceNumber' => $sequenceNumber
                    ]
                );
                $sequenceNumber++;
            }
        }

        //'isChildOf' associations for ULOs
        $sequenceNumber = 1;
        foreach ($outcomesArr['unit_level_objectives'] as $k => $v) {
            if (isset($v['desc']) && $v['desc'] != '') {
                $caseJsonArr['CFAssociations'][] = $this->createAssociation(
                    [
                        'associationType' => 'isChildOf',
                        'docId' => $docIdentifier,
                        'docTitle' => $docTitle,
                        'originNodeId' => $v['uuid'],
                        'destinationNodeId' => $uloIdentifier,
                        'sequenceNumber' => $sequenceNumber
                    ]
                );
                $sequenceNumber++;
            }
        }

        // 'isRelatedTo' association between Course Goals and ULOs
        $sequenceNumber = 1;
        foreach ($outcomesArr['cg_ulo_mapping'] as $ulo => $courseGoals) {
            foreach ($courseGoals as $courseGoal) {
                if(isset($outcomesArr['unit_level_objectives'][$ulo]['uuid'])){
                    $caseJsonArr['CFAssociations'][] = $this->createAssociation(
                        [
                            'associationType' => 'isRelatedTo',
                            'docId' => $docIdentifier,
                            'docTitle' => $docTitle,
                            'originNodeId' => $outcomesArr['unit_level_objectives'][$ulo]['uuid'],
                            'destinationNodeId' => $outcomesArr['course_goals'][$courseGoal]['uuid'],
                            'sequenceNumber' => $sequenceNumber
                        ]
                    );
                    $sequenceNumber++;
                }
            }
        }

        // echo "<pre>";
        // print_r($caseJsonArr);
        // echo json_encode($caseJsonArr, JSON_PRETTY_PRINT);
        // exit;

        // print_r($outcomesArr);

        // write file to a json file
        $filename = storage_path('app/') . $this->createUUID() . '.json';
        $fp = fopen($filename, 'w');
        fwrite($fp, json_encode($caseJsonArr));
        fclose($fp);



        //get access token to call Align APIs		
        $authToken = $this->getAccessToken($tenantCode); //pass tenant name and Do not pass tenant_code

        //get import identifier
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v1/importTaxonomy',
                'method' => 'POST',
                'data' => [
                    'email' => $userEmail,
                    'source_identifier' => $docIdentifier,
                    'import_type' => '1',
                    'is_ready_to_commit_changes' => '0'
                ],
                'headers' => [
                    'Authorization: ' . $authToken
                ]
            ]
        );
        $response = json_decode($response, true);
        $importIndentifier = $response['data']['import_identifier'];


        // //call case json api to create Taxonomy
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v1/importTaxonomy',
                'method' => 'POST',
                'data' => [
                    'email' => $userEmail,
                    'import_identifier' => $importIndentifier,
                    'import_type' => '3',
                    'is_ready_to_commit_changes' => '1',
                    'case_json' => new \CURLFILE($filename)
                ],
                'headers' => [
                    'Authorization: ' . $authToken
                ]
            ]
        );


        //delete the json file
        unlink($filename);

        //Once taxonomy is created, it needs to be linked with "List of Programs (LXP)" taxonomy. For that a node needs to be created under the List Of Programs taxonomy
        $this->updateListOfProgramsTaxonomy($authToken, $this->programName, $docIdentifier); //TODO: pass proper program name
        // $this->publishTaxonomy($docIdentifier);
        // }catch(\Exception $e){
        // 	echo $e->getMessage();
        // }
    }

    private function updateListOfProgramsTaxonomy($authToken, $programName, $sourceDocIdentifier)
    {
        echo "<pre>";
        //get list of taxonomies
        $listOfProgramsTaxonomyExists = 0;
        $document_id = null;
        $docIdentifier = null;
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v1/taxonomy/list',
                'method' => 'GET',
                'headers' => [
                    'Authorization: ' . $authToken
                ]
            ]
        );
        $response = json_decode($response, true);

        //check if "List of Programs (LXP)" exists. Also get the document_id of the course taxonomy
        foreach ($response['data'] as $data) {
            if ($data['title'] == self::PROGRAMS_TAXONOMY_NAME) {
                $listOfProgramsTaxonomyExists = 1;
                $document_id = $data['document_id'];
            }

            if ($data['source_document_id'] == $sourceDocIdentifier) {
                $docIdentifier = $data['document_id'];
            }
        }

        //get all node types
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v1/nodeTypes',
                'method' => 'GET',
                'headers' => [
                    'Authorization: ' . $authToken
                ]
            ]
        );
        $response = json_decode($response, true);

        // Get node type id of document and program node
        $nodeTypeId = null;
        $childNodeTypeId = null;
        foreach ($response['data']['nodetype'] as $data) {
            if (strtolower($data['title']) == 'document') {
                $nodeTypeId = $data['node_type_id'];
            }

            if (strtolower($data['title']) == 'program') {
                $childNodeTypeId = $data['node_type_id'];
            }
        }


        //create "List of Programs (LXP)" taxonomy if it does not exist
        if (!$listOfProgramsTaxonomyExists) {
            //create taxonomy
            $response = $this->httpRequest(
                [
                    'url' => self::ALIGN_BASE_URL . '/server/api/v1/taxonomy',
                    'method' => 'POST',
                    'data' => json_encode([
                        'node_template_id' => '',
                        'document_title' => self::PROGRAMS_TAXONOMY_NAME,
                        'document_title_html' => self::PROGRAMS_TAXONOMY_NAME,
                        'document_node_type_id' => $nodeTypeId,
                        'language_id' => '',
                        'metadataType' => '',
                        'items' => [],
                        'template_tiltle' => '',
                        'document_type' => 1
                    ]),
                    'headers' => [
                        'Content-Type: application/json',
                        'Authorization: ' . $authToken
                    ]
                ]
            );
            $response = json_decode($response, true);
            $document_id = $response['data'];
        }
        //If a node for newly created taxonomy's program does not exist, add a new node
        //get nodes from "List of Programs (LXP)" taxonomy
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v2/taxonomy/getTreeHierarchyV5/' . $document_id,
                'method' => 'GET',
                'headers' => [
                    'Authorization: ' . $authToken
                ]
            ]
        );
        $response = json_decode($response, true);

        //check if the node exists. 
        $nodeId = null;
        if (is_array($response['data']['children'][0]['children'])) {
            foreach ($response['data']['children'][0]['children'] as $data) {
                if ($data['full_statement'] == $programName) {
                    $nodeId = $data['id'];
                    break;
                }
            }
        }

        // if(!is_null($nodeId)){//if found, delete the node
        // 	$response = $this->httpRequest(
        // 		[
        // 			'url' => self::ALIGN_BASE_URL.'/server/api/v1/taxonomy/delete/'.$nodeId,
        // 			'method' => 'DELETE',
        // 			'headers' => [
        // 				'Authorization: '.$authToken
        // 			]
        // 		]
        // 	);
        // }

        if (is_null($nodeId)) { //if not found, add the node
            //if program node type doesn't exist, create it
            if (is_null($childNodeTypeId)) {
                //create Program nodetype
                $response = $this->httpRequest(
                    [
                        'url' => self::ALIGN_BASE_URL . '/server/api/v1/nodeTypes',
                        'method' => 'POST',
                        'data' => json_encode([
                            'name' => 'Program'
                        ]),
                        'headers' => [
                            'Content-Type: application/json',
                            'Authorization: ' . $authToken
                        ]
                    ]
                );
                $response = json_decode($response, true);
                echo $childNodeTypeId = $response['data']['node_type_id'];

                //get metadatas
                $response = $this->httpRequest(
                    [
                        'url' => self::ALIGN_BASE_URL . '/server/api/v1/metadata',
                        'method' => 'GET',
                        'headers' => [
                            'Authorization: ' . $authToken
                        ]
                    ]
                );
                $response = json_decode($response, true);

                //get select list of metadatas required to map to node type
                $metaDataArr = [];
                $order = 1;
                foreach ($response['data']['metadata'] as $data) {
                    if (in_array(strtolower($data['internal_name']), ['full_statement', 'human_coding_scheme', 'notes'])) {
                        $metaDataArr['metadata'][] = [
                            'id' => $data['metadata_id'],
                            'order' => $order,
                            'name' => $data['name'],
                            'is_custom' => 0,
                            'is_mandatory' => 1
                        ];
                        $order++;
                    }
                }

                echo json_encode($metaDataArr);

                //map metadat to nodetype
                $response = $this->httpRequest(
                    [
                        'url' => self::ALIGN_BASE_URL . '/server/api/v1/nodeTypeMetadata/' . $childNodeTypeId,
                        'method' => 'POST',
                        'data' => json_encode($metaDataArr),
                        'headers' => [
                            'Content-Type: application/json',
                            'Authorization: ' . $authToken
                        ]
                    ]
                );
                $response = json_decode($response, true);
                print_r($response);
            }

            $response = $this->httpRequest(
                [
                    'url' => self::ALIGN_BASE_URL . '/server/api/v1/cfitem/create',
                    'method' => 'POST',
                    'data' => json_encode([
                        'children' => [],
                        'currentIndex' => '',
                        'cut' => 0,
                        'document_id' => $document_id,
                        'full_statement' => $programName,
                        'human_coding_scheme' => '',
                        'id' => '',
                        'is_editable' => 1,
                        'item_id' => '',
                        'list_enumeration' => '', //need to check
                        'metadataType' => '',
                        'node_type' => 'Program',
                        'node_type_id' => $childNodeTypeId,
                        'parent_id' => $document_id,
                        'paste' => 0,
                        'project_id' => null,
                        'reorder' => 0,
                        'sequence_number' => '', //need to check
                        'template_tiltle' => '',
                        'title' => ''
                    ]),
                    'headers' => [
                        'Content-Type: application/json',
                        'Authorization: ' . $authToken
                    ]
                ]
            );
            $response = json_decode($response, true);
            $nodeId = $response['data']['item_id'];
        }

        //get id of first node of course taxonomy for creating mapping between course taxonomy node and program list taxonomy
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v2/taxonomy/getTreeHierarchyV5/' . $docIdentifier,
                'method' => 'GET',
                'headers' => [
                    'Authorization: ' . $authToken
                ]
            ]
        );
        $response = json_decode($response, true);
        $firstItemIdentifier = $response['data']['children'][0]['children'][0]['id'];

        // create the mapping between course taxonomy node and program list taxonomy
        $response = $this->httpRequest(
            [
                'url' => self::ALIGN_BASE_URL . '/server/api/v1/cfitem/createAssociations',
                'method' => 'POST',
                'data' => json_encode([
                    'origin_node_id' => $firstItemIdentifier,
                    'destination_node_ids' => [$nodeId],
                    'project_type' => 1,
                    'association_type' => 6,
                    'project_id' => '',
                    'description' => null,
                    'case_custom_association_type' => '6_0'
                ]),
                'headers' => [
                    'Content-Type: application/json',
                    'Authorization: ' . $authToken
                ]
            ]
        );
    }

    private function publishTaxonomy()
    {

    }

    private function createUUID()
    {
        return (string) Str::uuid();
    }

    private function setLastChangeDateTime()
    {
        return $this->lastChangeDateTime = date('Y-m-d H:i:s');
    }

    private function setUri()
    {
        return $this->uri = self::TWOU_DOMAIN;
    }

    private function createItemTypes($id, $title)
    {
        return [
            'identifier' => $id,
            'uri' => $this->uri,
            'title' => $title,
            'lastChangeDateTime' => $this->lastChangeDateTime,
            'hierarchyCode' => '',
            'description' => ''
        ];
    }

    private function createItem($params)
    {
        $itemArr =  [
            'identifier' => $params['id'],
            'uri' => $this->uri,
            "fullStatement" => $params['fullStatement'],
            'lastChangeDateTime' => $this->lastChangeDateTime,
            'CFItemType' => $params['courseItemTypeTitle'],
            // 'listEnumeration' => '',
            'CFDocumentURI' =>
            [
                'identifier' => $params['docId'],
                'uri' => $this->uri,
                'title' => $params['docTitle']
            ],
            'CFItemTypeURI' =>
            [
                'identifier' => $params['courseItemTypeId'],
                'uri' => $this->uri,
                'title' => $params['courseItemTypeTitle']
            ],
        ];

        if (isset($params['humanCodingScheme'])) {
            $itemArr['humanCodingScheme'] = $params['humanCodingScheme'];
        }

        return $itemArr;
    }

    private function createAssociation($params)
    {
        $associationArr =  [
            'identifier' => $this->createUUID(),
            'associationType' => $params['associationType'],
            'CFDocumentURI' =>
            [
                'identifier' => $params['docId'],
                'uri' => $this->uri,
                'title' => $params['docTitle']
            ],
            'uri' => $this->uri,
            'originNodeURI' =>
            [
                'identifier' => $params['originNodeId'],
                'uri' => $this->uri,
                // 'title' => $docTitle
            ],
            'destinationNodeURI' =>
            [
                'identifier' => $params['destinationNodeId'],
                'uri' => $this->uri,
                // 'title' => $docTitle
            ],
            'lastChangeDateTime' => $this->lastChangeDateTime
        ];

        if (isset($params['sequenceNumber'])) {
            $associationArr['sequenceNumber'] = $params['sequenceNumber'];
        }

        return $associationArr;
    }
}
