<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set("memory_limit", "1024M");
class createExcelController extends Controller
{
	//variables for overview data
	private $courseOverviewData;
	private $courseDetailsData;
	private $designSummariesData;

	//variables for Assessment data
	private $courseAssessmentRawData;
	private $courseAssessmentData;
	private $feedbackCols = ['automatic', 'self', 'peer', 'instructor', 'rubic?'];
	private $courseAssessmentDataColWidth = [
		'assessment name' => 30,
		'assessment tool' => 30,
		'assessment category' => 30,
		'graded' => 30,
		'description' => 50,
		'automatic' => 10,
		'self' => 10,
		'peer' => 10,
		'instructor' => 10,
		'rubic?' => 10,
		'unit/week due' => 30,
		'additional notes' => 30,
		'weight %' => 15,
		'scale' => 15,
		'proctoring' => 15,
		'turnitin' => 15,
		'group grading' => 20,
		'other considerations' => 30
	];

	//variables for Outcomes data
	private $courseOutcomesCourseStatementData;
	private $courseOutcomesCourseGoalsData;
	private $courseOutcomesCourseGoalsHCSData;
	private $courseOutcomesCourseULOData;
	private $courseOutcomesULO2CGMapping;
	private $courseOutcomesAssociationsData;
	private $courseOutcomesCG2AssessmentMappingData;
	private $courseOutcomesULO2AssessmentMappingData;

	//variables for CourseMap data
	private $courseMapData;
	private $courseMapDataColWidth = [
		'unit' => 7,
		'page' => 5,
		'item' => 5,
		'when' => 7,
		'title' => 30,
		'delivery' => 30,
		'design notes (flexible)' => 40,
		'design sign-off comments (as needed)' => 40,
		'learning category' => 30,
		'learning event' => 40,
		'learning action' => 40,
		'uos' => 8,
		'graded' => 30,
		'time category' => 25,
		'learning time' => 10,
		'notes' => 25,
		'probable video type' => 30,
		'probable video format' => 25,
		'estimated video time' => 25,
		'outline build' => 20,
		'canvas equivalent' => 20,
		'moodle equivalent' => 20,
		'atrio equivalent' => 20,
		'build notes (flexible)' => 30,
		'confirmed time category' => 20,
		'confirmed learning time' => 15,
		'confirmed video type' => 25,
		'confirmed video format' => 25,
		'confirmed video estimate' => 15,
		'final video time (as filmed)' => 15,
		'student-facing text and links (as needed)' => 40,
		'links to asset' => 25,
		'asset status' => 20,
		'notes (flexible)' => 30,
	];

	//variables for CourseMap and toolbox data
	private $courseOverViewAndToolboxData;
	private $courseOverViewAndToolboxDataColWidth = [
		'page' => 5,
		'item' => 5,
		'title' => 30,
		'delivery' => 30,
		'notes (flexible)' => 40,
		'design sign-off comments (as needed)' => 40,
		'learning category' => 30,
		'learning event' => 40,
		'learning action' => 40,
		'uos' => 8,
		'graded' => 30,
		'time (as needed)' => 10,
		'notes' => 25,
		'probable video type' => 30,
		'probable video format' => 25,
		'estimated video time' => 25,
		'outline build' => 20,
		'canvas equivalent' => 20,
		'moodle equivalent' => 20,
		'atrio equivalent' => 20,
		'build notes (flexible)' => 30,
		'confirmed time (as needed)' => 20,
		'confirmed video type' => 25,
		'confirmed video format' => 25,
		'confirmed video estimate' => 15,
		'final video time (as filmed)' => 15,
		'student-facing text and links (as needed)' => 40,
		'links to asset' => 25,
		'asset status' => 20,
		'notes (flexible)' => 30
	];

	public function create()
	{
		$spreadsheet = new Spreadsheet();
		$spreadsheet->setActiveSheetIndex(0);

		// set title of default sheet to Overview
		$spreadsheet->getActiveSheet()->setTitle('Overview');
		//set data for Overview Sheet
		$this->setOverviewSheetData();
		//write data to Overview sheet
		$this->writeOverviewSheetData($spreadsheet, 0);


		//add Assessments Sheet
		$this->addSheet($spreadsheet, 1, 'Assessments');
		//set data for Assessments Sheet
		$this->setAssessmentsSheetData();
		//write data to Assessments sheet
		$this->writeAssessmentsSheetData($spreadsheet, 1);

		//add Outcomes Sheet
		$this->addSheet($spreadsheet, 2, 'Outcomes');
		//set data for Outcomes Sheet
		$this->setOutcomesSheetData();
		//write data to Outcomes sheet
		$this->writeOutcomesSheetData($spreadsheet, 2);


		//add Course Map Sheet
		$this->addSheet($spreadsheet, 3, 'Course Map');
		//set data for Course Map Sheet
		$this->setCourseMapSheetData();
		//write data to Course Map sheet
		$this->writeCourseMapSheetData($spreadsheet, 3);

		//add Course Overview & toolbox Sheet
		$this->addSheet($spreadsheet, 4, 'Course Overview & Toolbox');
		//set data for Course Overview & toolbox Sheet
		$this->setCourseOverviewAndToolboxSheetData();
		//write data to Course Overview & toolbox sheet
		$this->writeCourseOverviewAndToolboxSheetData($spreadsheet, 4);

		$spreadsheet->setActiveSheetIndex(0);
		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="file' . date('YmdHis') . '.xls"');
		$writer->save('php://output');
	}

	private function setOverviewSheetData() //get data from DB or through APIs and it needs to be in below format
	{
		$this->courseOverviewData =
			[
				[
					'Course Name', 'sample course'
				],
				[
					'Program', 'sample program'
				]
			];

		$this->courseDetailsData =
			[
				[
					'Course Drive Folder (external)', 'drive folder'
				],
				[
					'Creative Video Brief (if applicable)', 'video brief'
				],
				[
					'key3', 'val3'
				],
				[
					'key4', 'val4'
				],
			];

		$this->designSummariesData =
			[
				[
					'Summary of Learning Design', 'summary'
				],
				[
					'Summary of Assessment Design', 'learning assessment'
				],
				[
					'key3', 'val3'
				],
			];
	}

	private function writeOverviewSheetData($spreadsheetObj, $index)
	{
		$spreadsheetObj->setActiveSheetIndex($index);
		$sheet = $spreadsheetObj->getActiveSheet();

		$headerStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		/*add course overview data*/
		$bulkStyleRows = [];
		$startColumnIndexNumber = 1;
		$startColumn = $this->getColumnNameFromNumber($startColumnIndexNumber);
		$startRow = 1;
		//add header
		$this->addCell($sheet, $startColumn . $startRow, 'Course Overview');
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow, $headerStyleArray);
		//merge the header row and col1,col2
		$mergeTillColumn = $this->getColumnNameFromNumber($startColumnIndexNumber + 1);
		$mergeTillRow = $startRow;
		$this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . $mergeTillRow);
		//add data
		$startRow = $startRow + 1;
		$this->addRows($sheet, $startColumn, $startRow, $this->courseOverviewData);
		//get row numbers for bulk styling
		for ($i = 0; $i < count($this->courseOverviewData); $i++) {
			$bulkStyleRows[] = $startRow + $i;
		}
		//set column widths
		$this->setColumnWidth($spreadsheetObj, $startColumn, 50);
		$secondColumn = $this->getColumnNameFromNumber($startColumnIndexNumber + 1);
		$this->setColumnWidth($spreadsheetObj, $secondColumn, 100);


		/*add course details data*/
		$startRow = $startRow + count($this->courseOverviewData) + 1; //+1 to add an empty row
		//add header
		$this->addCell($sheet, $startColumn . $startRow, 'Course Details');
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow, $headerStyleArray);
		//merge the header row and col1,col2
		$mergeTillColumn = $this->getColumnNameFromNumber($startColumnIndexNumber + 1);
		$mergeTillRow = $startRow;
		$this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . $mergeTillRow);
		//add data
		$startRow = $startRow + 1;
		$this->addRows($sheet, $startColumn, $startRow, $this->courseDetailsData);
		//get row numbers for bulk styling
		for ($i = 0; $i < count($this->courseDetailsData); $i++) {
			$bulkStyleRows[] = $startRow + $i;
		}

		/*add Design Summaries data*/
		$startRow = $startRow + count($this->courseDetailsData) + 1; //+1 to add an empty row
		//add header
		$this->addCell($sheet, $startColumn . $startRow, 'Design Summaries');
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow, $headerStyleArray);
		//merge the header row and col1,col2
		$mergeTillColumn = $this->getColumnNameFromNumber($startColumnIndexNumber + 1);
		$mergeTillRow = $startRow;
		$this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . $mergeTillRow);
		//add data
		$startRow = $startRow + 1;
		$this->addRows($sheet, $startColumn, $startRow, $this->designSummariesData);
		//get row numbers for bulk styling
		for ($i = 0; $i < count($this->designSummariesData); $i++) {
			$bulkStyleRows[] = $startRow + $i;
		}

		/*Bulk styling*/
		//set column A style
		$columnAStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1576D4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			],
			'borders' => [
				'outline' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$this->setColumnRowsStyle($spreadsheetObj, 'A', $bulkStyleRows, $columnAStyleArray);

		//set column B style
		$columnBStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => 'FFF2CC')
			],
			'font'  => [
				'color' => array('rgb' => '520052'),
			],
			'borders' => [
				'outline' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$this->setColumnRowsStyle($spreadsheetObj, 'B', $bulkStyleRows, $columnBStyleArray);

		//set row height
		$this->setRowHeight($spreadsheetObj, $bulkStyleRows, 18);
	}

	private function setAssessmentsSheetData() //get data from DB or through APIs and it needs to be in below format
	{
		$this->courseAssessmentRawData =
			[
				[
					'id' => '1',
					'Assessment Name' => 'Assessment Name1',
					'Assessment Tool' => 'Assessment Tool1',
					'Assessment Category' => 'Assessment Category1',
					'Graded' => 'Graded1',
					'Description' => 'Description',
					'Automatic' => true,
					'Self' => true,
					'Peer' => false,
					'Instructor' => false,
					'rubic?' => true,
					'Unit/Week Due' => 'Unit/Week Due1',
					'Additional Notes' => 'Additional Notes1',
					'Weight %' => 'Weight %1',
					'Scale' => 'Scale1',
					'Proctoring' => true,
					'TurnItIn' => true,
					'Group Grading' => false,
					'Other Considerations' => 'Other Considerations1'
				],
				[
					'id' => '2',
					'Assessment Name' => 'Assessment Name2',
					'Assessment Tool' => 'Assessment Tool2',
					'Assessment Category' => 'Assessment Category2',
					'Graded' => 'Graded2',
					'Description' => 'Description2',
					'Automatic' => true,
					'Self' => true,
					'Peer' => false,
					'Instructor' => false,
					'rubic?' => true,
					'Unit/Week Due' => 'Unit/Week Due2',
					'Additional Notes' => 'Additional Notes2',
					'Weight %' => 'Weight %2',
					'Scale' => 'Scale2',
					'Proctoring' => true,
					'TurnItIn' => true,
					'Group Grading' => false,
					'Other Considerations' => 'Other Consideration2'
				],
				[
					'id' => '3',
					'Assessment Name' => 'Assessment Name3',
					'Assessment Tool' => 'Assessment Tool3',
					'Assessment Category' => 'Assessment Category3',
					'Graded' => 'Graded3',
					'Description' => 'Description3',
					'Automatic' => true,
					'Self' => true,
					'Peer' => false,
					'Instructor' => false,
					'rubic?' => true,
					'Unit/Week Due' => 'Unit/Week Due3',
					'Additional Notes' => 'Additional Notes3',
					'Weight %' => 'Weight %3',
					'Scale' => 'Scale3',
					'Proctoring' => true,
					'TurnItIn' => true,
					'Group Grading' => false,
					'Other Considerations' => 'Other Considerations3'
				]
			];

		//As checkbox is not supported, we are going to use UTF8 tick character. Hence replacing where checkbox value is true to tick caharacter
		foreach ($this->courseAssessmentRawData as $k => $v) {
			$this->courseAssessmentData[$k] = $this->courseAssessmentRawData[$k];
			unset($this->courseAssessmentData[$k]['id']); //unsetting id as it is not required in spreadsheet
			foreach ($v as $k1 => $v1) {
				if ($v1 === true) {
					$this->courseAssessmentData[$k][$k1] = $this->getUTF8Symbol('&#10003;');
				}
			}
		}
	}

	private function writeAssessmentsSheetData($spreadsheetObj, $index)
	{
		$spreadsheetObj->setActiveSheetIndex($index);
		$sheet = $spreadsheetObj->getActiveSheet();
		//heaeders styale
		$headerStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'bottom'
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$rowStyleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			],
		];
		//get headers from the array
		$headers = array_keys($this->courseAssessmentData[0]);
		$endColNum = count($headers);
		$endColName = $this->getColumnNameFromNumber($endColNum);
		//get second row headers
		$secondHeaders = [];
		$feedbackStartColIndex = null;
		$feedbackEndColIndex = null;
		$itr = 1;
		foreach ($headers as $col) {
			if (in_array(strtolower($col), $this->feedbackCols)) {
				$secondHeaders[] = strtolower($col);
				//below log id=s to get statting and ending column index of feedback data
				if (is_null($feedbackStartColIndex)) {
					$feedbackStartColIndex = $itr;
				}
				$feedbackEndColIndex = $itr;
			} elseif (strtolower($col) == 'assessment category') {
				$secondHeaders[] = '(if applicable)';
			} else {
				$secondHeaders[] = '';
			}
			$itr++;
		}

		$startColumnIndexNumber = 1;
		$startColumn = $this->getColumnNameFromNumber($startColumnIndexNumber);
		$startRow = 1;
		//add header row
		$this->addRows($sheet, $startColumn, $startRow, [$headers]);
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow . ':' . $endColName . $startRow, $headerStyleArray);
		//set width of all cols
		$i = 1;
		foreach ($headers as $col) {
			if (in_array(strtolower($col), array_keys($this->courseAssessmentDataColWidth))) {
				$width = $this->courseAssessmentDataColWidth[strtolower($col)];
			} else {
				$width = 50;
			}
			$colName = $this->getColumnNameFromNumber($i);
			$this->setColumnWidth($spreadsheetObj, $colName, $width);
			$i++;
		}

		//set height of header
		$this->setRowHeight($spreadsheetObj, [$startRow], 70);
		//merge feedback cols in row1
		$startFeedbackColumn = $this->getColumnNameFromNumber($feedbackStartColIndex);
		$endFeedbackColumn = $this->getColumnNameFromNumber($feedbackEndColIndex);
		$this->mergeCells($sheet, $startFeedbackColumn . $startRow . ":" . $endFeedbackColumn . $startRow);
		$this->addCell($sheet, $startFeedbackColumn . $startRow, 'Feedback');
		//add second headers
		$startRow = $startRow + 1; //start from the next row after header
		$this->addRows($sheet, $startColumn, $startRow, [$secondHeaders]);
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow . ':' . $endColName . $startRow, $headerStyleArray);
		//add data
		$startRow = $startRow + 1; //start from the next row after header
		$rows = [];
		foreach ($this->courseAssessmentData as $row) {
			$this->addRows($sheet, $startColumn, $startRow, [array_values($row)]);
			$this->setCellStyle($spreadsheetObj, $startColumn . $startRow . ':' . $endColName . $startRow, $rowStyleArray);
			$startRow++;
		}
		//add column for design section
		$desginColName = 'E';
		$this->addColumnBefore($spreadsheetObj, $desginColName);
		$this->setColumnWidth($spreadsheetObj, $desginColName, 4);
		$this->mergeCells($sheet, $desginColName . '1' . ":" . $desginColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $desginColName . '1', 'D E S I G N ' . $this->getUTF8Symbol('&#9660;'));
		$headerStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];
		$this->setCellStyle($spreadsheetObj, $desginColName . '1', $headerStyleArray);

		//Add column for develop section
		$developColName = 'N';
		$this->addColumnBefore($spreadsheetObj, $developColName);
		$this->setColumnWidth($spreadsheetObj, $developColName, 4);
		$this->mergeCells($sheet, $developColName . '1' . ":" . $developColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $developColName . '1', 'D E V E L O P ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $developColName . '1', $headerStyleArray);

		//group cells
		$this->groupColumns($spreadsheetObj, 1, false, ['F', 'M']);
		$this->groupColumns($spreadsheetObj, 1, false, ['O', 'T']);
	}


	private function setOutcomesSheetData() //get data from DB or through APIs and it needs to be in below format
	{
		$this->courseOutcomesCourseStatementData =
			[
				[
					'Course Statement', 'Course Statement 1'
				]
			];

		$courseGoals =
			[
				'1' => [
					'hcs' => 'C1', //hcs stand for humna coding scheme
					'fs' => 'course goal 1' //fs stands for full statement
				],
				'2' => [
					'hcs' => 'C2',
					'fs' => 'course goal 2'
				],
				'3' => [
					'hcs' => 'C3',
					'fs' => 'course goal 3'
				],
				'4' => [
					'hcs' => 'C4',
					'fs' => 'course goal 4'
				],
				'5' => [
					'hcs' => 'C5',
					'fs' => 'course goal 5'
				],
				'6' => [
					'hcs' => 'C6',
					'fs' => 'course goal 6'
				],
				'7' => [
					'hcs' => 'C7',
					'fs' => 'course goal 7'
				]
			];

		foreach ($courseGoals as $courseGoal) {
			$this->courseOutcomesCourseGoalsData[] = [$courseGoal['hcs'], $courseGoal['fs']];
			$this->courseOutcomesCourseGoalsHCSData[] = $courseGoal['hcs'];
		}

		$ulos =
			[
				'1' => [
					'hcs' => 'U1',
					'fs' => 'ULO 1'
				],
				'2' => [
					'hcs' => 'U2',
					'fs' => 'ULO 2'
				],
				'3' => [
					'hcs' => 'U3',
					'fs' => 'ULO 3'
				],
				'4' => [
					'hcs' => 'U4',
					'fs' => 'ULO 4'
				],
				'5' => [
					'hcs' => 'U5',
					'fs' => 'ULO 5'
				],
				'6' => [
					'hcs' => 'U6',
					'fs' => 'ULO 6'
				],
				'7' => [
					'hcs' => 'U7',
					'fs' => 'ULO 7'
				],
				'8' => [
					'hcs' => 'U8',
					'fs' => 'ULO 8'
				],
				'9' => [
					'hcs' => 'U9',
					'fs' => 'ULO 9'
				],
				'10' => [
					'hcs' => 'U10',
					'fs' => 'ULO 10'
				],
				'11' => [
					'hcs' => 'U11',
					'fs' => 'ULO 11'
				],
			];

		foreach ($ulos as $ulo) {
			$this->courseOutcomesCourseULOData[] = [$ulo['hcs'], $ulo['fs']];
		}

		$courseOutcomesULO2CGMapping =
			[
				'1' => ['1', '2'],
				'2' => ['1', '2'],
				'3' => ['1', '2'],
				'4' => ['3'],
				'5' => ['3'],
				'6' => ['4'],
				'7' => ['4'],
				'8' => ['5'],
				'9' => ['5'],
				'10' => [],
				'11' => ['6']
			];

		foreach ($courseOutcomesULO2CGMapping as $mapping) {
			$arr = [];
			foreach ($courseGoals as $cgKey => $cgval) {
				if (in_array($cgKey, $mapping)) {
					$arr[] = $this->getUTF8Symbol('&#10003;');
				} else {
					$arr[] = '';
				}
			}
			$this->courseOutcomesULO2CGMapping[] = $arr;
		}


		$assessments = [];
		$assessmentTypes = [];
		foreach ($this->courseAssessmentRawData as $assessment) {
			$assessments[] = $assessment['Assessment Name'];
			$assessmentTypes[] = $assessment['Graded'];
		}
		$this->courseOutcomesAssociationsData = [$assessments, $assessmentTypes];

		$courseOutcomesCG2AssessmentMappingData =
			[
				'1' => ['1', '2'],
				'2' => ['1', '2', '3'],
				'3' => ['1', '2'],
				'4' => ['3'],
				'5' => ['3'],
				'6' => ['3'],
				'7' => ['3'],
			];

		foreach ($courseOutcomesCG2AssessmentMappingData as $mapping) {
			$arr = [];
			foreach ($this->courseAssessmentRawData as $assmtval) {
				if (in_array($assmtval['id'], $mapping)) {
					$arr[] = $this->getUTF8Symbol('&#10003;');
				} else {
					$arr[] = '';
				}
			}
			$this->courseOutcomesCG2AssessmentMappingData[] = $arr;
		}

		$courseOutcomesULO2AssessmentMappingData =
			[
				'1' => ['1', '2'],
				'2' => ['1', '2', '3'],
				'3' => ['1', '2'],
				'4' => ['3'],
				'5' => ['3'],
				'6' => ['3'],
				'7' => ['3'],
				'8' => ['3'],
				'9' => ['3'],
				'10' => [],
				'11' => ['2']
			];

		foreach ($courseOutcomesULO2AssessmentMappingData as $mapping) {
			$arr = [];
			foreach ($this->courseAssessmentRawData as $assmtval) {
				if (in_array($assmtval['id'], $mapping)) {
					$arr[] = $this->getUTF8Symbol('&#10003;');
				} else {
					$arr[] = '';
				}
			}
			$this->courseOutcomesULO2AssessmentMappingData[] = $arr;
		}
	}

	private function writeOutcomesSheetData($spreadsheetObj, $index)
	{
		$spreadsheetObj->setActiveSheetIndex($index);
		$sheet = $spreadsheetObj->getActiveSheet();
		//heaeders styale
		$headerStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center',
				'wrapText' => true
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$textStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'top',
				'wrapText' => true
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			]
		];

		$checkboxStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'middle'
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			]
		];

		$humanCodingSchemeStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1576D4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];

		$assessmentTypeStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => 'EAD1DC')
			],
			'font'  => [
				'size' => 6
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];

		$startColumnIndexNumber = 1;
		$startColumn = $this->getColumnNameFromNumber($startColumnIndexNumber);
		$startRow = 1;
		//add header row
		$this->addRows($sheet, $startColumn, $startRow, $this->courseOutcomesCourseStatementData);
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow, $headerStyleArray);
		$this->setCellStyle($spreadsheetObj, 'B' . $startRow, $textStyleArray);
		$this->setRowHeight($spreadsheetObj, [$startRow], 35);
		$this->setColumnWidth($spreadsheetObj, $startColumn, 15);
		$this->setColumnWidth($spreadsheetObj, 'B', 60);


		/*add course goals*/
		$bulkStyleRows = [];
		//add header row
		$startRow = $startRow + 2;
		$this->addCell($sheet, $startColumn . $startRow, 'Course Goals');
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow, $headerStyleArray);
		//merge the header row and col1,col2
		$mergeTillColumn = $this->getColumnNameFromNumber($startColumnIndexNumber + 1);
		$mergeTillRow = $startRow;
		$this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . $mergeTillRow);
		//add data
		$startRow = $startRow + 1;
		$this->addRows($sheet, $startColumn, $startRow, $this->courseOutcomesCourseGoalsData);
		//get row numbers for bulk styling
		for ($i = 0; $i < count($this->courseOutcomesCourseGoalsData); $i++) {
			$bulkStyleRows[] = $startRow + $i;
		}
		$this->setColumnRowsStyle($spreadsheetObj, 'A', $bulkStyleRows, $humanCodingSchemeStyleArray);
		$this->setColumnRowsStyle($spreadsheetObj, 'B', $bulkStyleRows, $textStyleArray);

		/*add ULOs*/
		$bulkStyleRows = [];
		//add header row
		$startRow = $uloStartRow = $startRow + count($this->courseOutcomesCourseGoalsData) + 1; //+1 to add an empty row
		$this->addCell($sheet, $startColumn . $startRow, 'Unit Level Objectives');
		//merge the header row and col1,col2
		$mergeTillColumn = $this->getColumnNameFromNumber($startColumnIndexNumber + 1);
		$mergeTillRow = $startRow;
		// $this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . $mergeTillRow);
		$this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . ($startRow + 2));
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $startColumn . $startRow . ":" . $mergeTillColumn . ($startRow + 2), $headerStyleArray);
		//add data
		$startRow = $startRow + 3;
		$this->addRows($sheet, $startColumn, $startRow, $this->courseOutcomesCourseULOData);
		//get row numbers for bulk styling
		for ($i = 0; $i < count($this->courseOutcomesCourseULOData); $i++) {
			$bulkStyleRows[] = $startRow + $i;
		}
		$this->setColumnRowsStyle($spreadsheetObj, 'A', $bulkStyleRows, $humanCodingSchemeStyleArray);
		$this->setColumnRowsStyle($spreadsheetObj, 'B', $bulkStyleRows, $textStyleArray);

		/*add empty column*/
		$this->setColumnWidth($spreadsheetObj, 'C', 2);

		/*course goals to ULOs mapping*/
		//add course golas header
		$mappingStartRow = $uloStartRow;
		$mappingStartColNum = 4;
		$mappingStartColName = $this->getColumnNameFromNumber($mappingStartColNum);
		$this->addCell($sheet, $mappingStartColName . $mappingStartRow, 'Course Goals');
		$mergeTillColumnNum = $mappingStartColNum + count($this->courseOutcomesCourseGoalsData) - 1; //subtracting 1 to adjust starting column value
		$mergeTillColumnName = $this->getColumnNameFromNumber($mergeTillColumnNum);
		$mergeTillRow = $mappingStartRow + 1;
		// $this->mergeCells($sheet, $startColumn . $startRow . ":" . $mergeTillColumn . $mergeTillRow);
		$this->mergeCells($sheet, $mappingStartColName . $mappingStartRow . ":" . $mergeTillColumnName . $mergeTillRow);
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $mappingStartColName . $mappingStartRow . ":" . $mergeTillColumnName . $mergeTillRow, $headerStyleArray);
		//add course goals horizontally
		$mappingCGStartRow = ($mergeTillRow + 1);
		$this->addRows($sheet, $mappingStartColName, $mappingCGStartRow, [$this->courseOutcomesCourseGoalsHCSData]);
		$this->setCellStyle($spreadsheetObj, $mappingStartColName . $mappingCGStartRow . ":" . $mergeTillColumnName . $mappingCGStartRow, $humanCodingSchemeStyleArray);
		for ($i = $mappingStartColNum; $i <= $mergeTillColumnNum; $i++) {
			$colName = $this->getColumnNameFromNumber($i);
			$this->setColumnWidth($spreadsheetObj, $colName, 5);
			$this->setColumnRowsStyle($spreadsheetObj, $colName, $bulkStyleRows, $checkboxStyleArray);
		}

		$this->addRows($sheet, $mappingStartColName, $mappingCGStartRow, [$this->courseOutcomesCourseGoalsHCSData]);
		$this->addRows($sheet, $mappingStartColName, $mappingCGStartRow + 1, $this->courseOutcomesULO2CGMapping);
		$this->groupColumns($spreadsheetObj, 1, true, [$mappingStartColName, $mergeTillColumnName]);

		/*add empty column*/
		$spacingColNum = $mergeTillColumnNum + 1;
		$spacingColName = $this->getColumnNameFromNumber($spacingColNum);
		$this->setColumnWidth($spreadsheetObj, $spacingColName, 2);

		/*add assessments*/
		$assessmentColNum = $spacingColNum + 1;
		$assessmentColName = $this->getColumnNameFromNumber($assessmentColNum);
		$assessmentHeaderRowNum = 1;
		$this->addCell($sheet, $assessmentColName . $assessmentHeaderRowNum, 'Assessments');
		$mergeTillColumnNum = $assessmentColNum + count($this->courseOutcomesAssociationsData[0]) - 1; //subtracting 1 to adjust starting column value
		$mergeTillColumnName = $this->getColumnNameFromNumber($mergeTillColumnNum);
		$this->mergeCells($sheet, $assessmentColName . $assessmentHeaderRowNum . ":" . $mergeTillColumnName . $assessmentHeaderRowNum);
		//add style to header1
		$this->setCellStyle($spreadsheetObj, $assessmentColName . $assessmentHeaderRowNum . ":" . $mergeTillColumnName . $assessmentHeaderRowNum, $headerStyleArray);
		for ($i = $assessmentColNum; $i <= $mergeTillColumnNum; $i++) {
			$colName = $this->getColumnNameFromNumber($i);
			$this->setColumnWidth($spreadsheetObj, $colName, 15);
		}

		//add assessment data
		$assessmentDataRowNum = 2;
		$this->addRows($sheet, $assessmentColName, $assessmentDataRowNum, $this->courseOutcomesAssociationsData);
		$this->setCellStyle($spreadsheetObj, $assessmentColName . '2' . ":" . $mergeTillColumnName . '2', $textStyleArray);
		$this->setCellStyle($spreadsheetObj, $assessmentColName . '3' . ":" . $mergeTillColumnName . '3', $assessmentTypeStyleArray);

		//add course goals to assessment mapping data
		$this->addRows($sheet, $assessmentColName, 4, $this->courseOutcomesCG2AssessmentMappingData);

		//add ULOs to assessment mapping data
		$this->addRows($sheet, $assessmentColName, $mappingCGStartRow + 1, $this->courseOutcomesULO2AssessmentMappingData);

		$lastRow = $mappingCGStartRow + count($this->courseOutcomesULO2AssessmentMappingData);
		$this->setCellStyle($spreadsheetObj, $assessmentColName . '4' . ":" . $mergeTillColumnName . $lastRow, $checkboxStyleArray);
		$this->groupColumns($spreadsheetObj, 1, true, [$assessmentColName, $mergeTillColumnName]);
	}

	private function setCourseMapSheetData() //get data from DB or through APIs and it needs to be in below format
	{
		$this->courseMapData =
			[
				[
					'unitNo' => 1,
					'desc' => 'T1',
					'ulo' =>
					[
						[
							'title' => 'U1',
							'desc' => 'desc1'
						],
						[
							'title' => 'U2',
							'desc' => 'desc2'
						],
						[
							'title' => 'U3',
							'desc' => 'desc3'
						],
						[
							'title' => 'U4',
							'desc' => 'desc4'
						],
					],
					'courseData' =>
					[
						[
							'Unit' => '1',
							'Page' => '1',
							'Item' => 'a',
							'When' => 'Pre',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Design Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time Category' => 'Non-Contact',
							'Learning Time' => '20 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time Category' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Type' => 'Lecture (solo)',
							'2U Video Type' => '',
							'Confirmed Video Format' => 'Remote',
							'Cost Calculator Video Format' => '',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => '',
							// 'Final Review Sign-Off Comments (as needed)' => ''
						],
						[
							'Unit' => '2',
							'Page' => '1',
							'Item' => 'a',
							'When' => 'Pre',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Design Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time Category' => 'Non-Contact',
							'Learning Time' => '20 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time Category' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Type' => 'Lecture (solo)',
							'2U Video Type' => '',
							'Confirmed Video Format' => 'Remote',
							'Cost Calculator Video Format' => '',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => '',
							// 'Final Review Sign-Off Comments (as needed)' => ''
						],
						[
							'Unit' => '3',
							'Page' => '1',
							'Item' => 'a',
							'When' => 'Pre',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Design Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time Category' => 'Non-Contact',
							'Learning Time' => '20 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time Category' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Type' => 'Lecture (solo)',
							'2U Video Type' => '',
							'Confirmed Video Format' => 'Remote',
							'Cost Calculator Video Format' => '',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => '',
							// 'Final Review Sign-Off Comments (as needed)' => ''
						]
					]
				],
				[
					'unitNo' => 2,
					'desc' => 'T1',
					'ulo' =>
					[
						[
							'title' => 'U1',
							'desc' => 'desc1'
						],
						[
							'title' => 'U2',
							'desc' => 'desc2'
						],
						[
							'title' => 'U3',
							'desc' => 'desc3'
						],
						[
							'title' => 'U4',
							'desc' => 'desc4'
						],
					],
					'courseData' =>
					[
						[
							'Unit' => '1',
							'Page' => '1',
							'Item' => 'a',
							'When' => 'Pre',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Design Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time Category' => 'Non-Contact',
							'Learning Time' => '20 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time Category' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Type' => 'Lecture (solo)',
							'2U Video Type' => '',
							'Confirmed Video Format' => 'Remote',
							'Cost Calculator Video Format' => '',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => '',
							// 'Final Review Sign-Off Comments (as needed)' => ''
						],
						[
							'Unit' => '2',
							'Page' => '1',
							'Item' => 'a',
							'When' => 'Pre',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Design Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time Category' => 'Non-Contact',
							'Learning Time' => '20 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time Category' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Type' => 'Lecture (solo)',
							'2U Video Type' => '',
							'Confirmed Video Format' => 'Remote',
							'Cost Calculator Video Format' => '',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => '',
							// 'Final Review Sign-Off Comments (as needed)' => ''
						],
						[
							'Unit' => '3',
							'Page' => '1',
							'Item' => 'a',
							'When' => 'Pre',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Design Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time Category' => 'Non-Contact',
							'Learning Time' => '20 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time Category' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Type' => 'Lecture (solo)',
							'2U Video Type' => '',
							'Confirmed Video Format' => 'Remote',
							'Cost Calculator Video Format' => '',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => '',
							// 'Final Review Sign-Off Comments (as needed)' => ''
						]
					]
				]
			];
	}

	private function writeCourseMapSheetData($spreadsheetObj, $index)
	{
		$spreadsheetObj->setActiveSheetIndex($index);
		$sheet = $spreadsheetObj->getActiveSheet();
		//heaeders styale
		$headerStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center',
				'wrapText' => true
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerStyle1Array = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$textStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'top',
				'wrapText' => true
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			]
		];

		$checkboxStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'middle'
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			]
		];

		$humanCodingSchemeStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1576D4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$assessmentTypeStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => 'EAD1DC')
			],
			'font'  => [
				'size' => 6
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];

		$headerVerticalStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size' => 8
			]
		];

		$headerVerticalDevelopStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '8C4BAF')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerVerticalDevelopSubsectionsStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '302758')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size' => 8
			]
		];

		$headerVerticalInitialBuildStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '11C1C4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerVerticalDesignStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1576D4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerVerticalDesignSubsectionsStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '064C90')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size' => 8
			]
		];

		$dottedBorderStyleArray = [
			'borders' => [
				'top' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,
					'color' => array('argb' => 'FFFFFF'),
				]
			],
		];


		$lastRow = 0;
		$lastColNum = count($this->courseMapData[0]['courseData'][0]);
		$lastColName = $this->getColumnNameFromNumber($lastColNum);
		foreach ($this->courseMapData as $cmKey => $cmVal) {
			//add unit
			$unitDataColNum = 1;
			$unitDataColName = $this->getColumnNameFromNumber($unitDataColNum);
			$unitDataRowNum = $lastRow + 1;
			$unitData = ['UNIT', $cmVal['unitNo']];
			$this->addRows($sheet, $unitDataColName, $unitDataRowNum, [$unitData]);
			$this->setCellStyle($spreadsheetObj, $unitDataColName . $unitDataRowNum . ':' . $lastColName . $unitDataRowNum, $headerStyleArray);
			$this->setRowHeight($spreadsheetObj, [$unitDataRowNum], 20);

			//add desc
			$descDataColNum = 1;
			$descDataColName = $this->getColumnNameFromNumber($descDataColNum);
			$descDataRowNum = $unitDataRowNum + 1;
			$this->addRows($sheet, $descDataColName, $descDataRowNum, [$cmVal['desc']]);
			$this->setCellStyle($spreadsheetObj, $descDataColName . $descDataRowNum . ':' . $lastColName . $descDataRowNum, $headerStyleArray);
			$this->setRowHeight($spreadsheetObj, [$descDataRowNum], 25);

			//add ULO data
			$uloDataColNum = 1;
			$uloDataColName = $this->getColumnNameFromNumber($uloDataColNum);
			$uloDataRowNum = $descDataRowNum + 1;
			$uloDataLastRowNum = $descDataRowNum;
			$uloData = [];
			foreach ($cmVal['ulo'] as $ulo) {
				$uloData[] = [$ulo['title'], $ulo['desc']];
				$uloDataLastRowNum++;
			}
			$this->addRows($sheet, $uloDataColName, $uloDataRowNum, $uloData);
			$this->setCellStyle($spreadsheetObj, $uloDataColName . $uloDataRowNum . ':' . $lastColName . $uloDataLastRowNum, $headerStyle1Array);
			//add top border
			$this->setCellStyle($spreadsheetObj,  $uloDataColName . $uloDataRowNum . ':H' . $uloDataRowNum, $dottedBorderStyleArray);

			//add course data header1
			$courseHeader1DataColNum = 1;
			$courseHeader1DataColName = $this->getColumnNameFromNumber($courseHeader1DataColNum);
			$courseHeader1DataRowNum = $uloDataLastRowNum + 1;
			$courseHeader1Data = ['', '', '', '', '', '', '', '', 'L E A R N I N G    S E Q U E N C E    D A T A', '', '', '', '', 'L E A R N I N G   T I M E   D E T A I L S', '', '', 'V I D E O    P R O D U C T I O N    E S T I M A T E S', '', '', 'I N I T I A L    B U I L D    M O D A L I T I E S', '', '', '', '', 'C O N F I R M E D   T I M E    +   V I D E O    P R O D U C T I O N    D E T A I L S', '', '', '', '', '', '', '', 'C O U R S E    P L A N    D E V E L O P M E N T    +    A S S E T    M A N A G E M E N T', '', '', '', ''];
			$this->addRows($sheet, $courseHeader1DataColName, $courseHeader1DataRowNum, [$courseHeader1Data]);
			$this->setCellStyle($spreadsheetObj, $courseHeader1DataColName . $courseHeader1DataRowNum . ':' . $lastColName . $courseHeader1DataRowNum, $headerStyle1Array);
			$this->setCellStyle($spreadsheetObj, $uloDataColName . $uloDataRowNum . ':' . $uloDataColName . $courseHeader1DataRowNum, $humanCodingSchemeStyleArray);

			//add course data header2
			$courseHeader2DataColNum = 1;
			$courseHeader2DataColName = $this->getColumnNameFromNumber($courseHeader2DataColNum);
			$courseHeader2DataRowNum = $courseHeader1DataRowNum + 1;
			$courseHeader2Data = array_keys($cmVal['courseData'][0]);
			$this->addRows($sheet, $courseHeader2DataColName, $courseHeader2DataRowNum, [$courseHeader2Data]);
			$this->setCellStyle($spreadsheetObj, $courseHeader2DataColName . $courseHeader2DataRowNum . ':' . $lastColName . $courseHeader2DataRowNum, $headerStyleArray);
			//add top border
			$this->setCellStyle($spreadsheetObj,  $courseHeader2DataColName . $courseHeader2DataRowNum . ':' . $spreadsheetObj->getActiveSheet()->getHighestColumn() . $courseHeader2DataRowNum, $dottedBorderStyleArray);

			//add course data
			$courseDataColNum = 1;
			$courseDataColName = $this->getColumnNameFromNumber($courseDataColNum);
			$courseDataRowNum = $courseHeader2DataRowNum + 1;
			$courseData = [];
			foreach ($cmVal['courseData'] as $data) {
				$courseData[] = array_values($data);
			}
			$this->addRows($sheet, $courseDataColName, $courseDataRowNum, $courseData);

			$lastRow = $courseHeader2DataRowNum + count($courseData);
		}

		//set column width
		$i = 1;
		foreach ($courseHeader2Data as $col) {
			// echo strtolower($col);
			if (in_array(strtolower($col), array_keys($this->courseMapDataColWidth))) {
				$width = $this->courseMapDataColWidth[strtolower($col)];
			} else {
				$width = 50;
			}
			$colName = $this->getColumnNameFromNumber($i);
			$this->setColumnWidth($spreadsheetObj, $colName, $width);
			$i++;
		}

		//pane freeze
		$spreadsheetObj->getActiveSheet()->freezePane('G1');

		//add "A S S E T   D E V E L O P M E N T    ▼"
		$assetDevelopmentColName = 'AG';
		$this->addColumnBefore($spreadsheetObj, $assetDevelopmentColName);
		$this->setColumnWidth($spreadsheetObj, $assetDevelopmentColName, 3);
		$this->mergeCells($sheet, $assetDevelopmentColName . '2' . ":" . $assetDevelopmentColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $assetDevelopmentColName . '2', 'A S S E T   D E V E L O P M E N T   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $assetDevelopmentColName . '2', $headerVerticalDevelopSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $assetDevelopmentColName . '1', $headerVerticalDevelopSubsectionsStyleArray);

		//add "T I M E   +   V I D E O    D E T A I L S    ▼"
		$timeVideoDetailsColName = 'Y';
		$this->addColumnBefore($spreadsheetObj, $timeVideoDetailsColName);
		$this->setColumnWidth($spreadsheetObj, $timeVideoDetailsColName, 3);
		$this->mergeCells($sheet, $timeVideoDetailsColName . '2' . ":" . $timeVideoDetailsColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $timeVideoDetailsColName . '2', 'T I M E   +   V I D E O    D E T A I L S   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $timeVideoDetailsColName . '2', $headerVerticalDevelopSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $timeVideoDetailsColName . '1', $headerVerticalDevelopSubsectionsStyleArray);

		//add "D E V E L O P    ▼"
		$developColName = 'Y';
		$this->addColumnBefore($spreadsheetObj, $developColName);
		$this->setColumnWidth($spreadsheetObj, $developColName, 3);
		$this->mergeCells($sheet, $developColName . '1' . ":" . $developColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $developColName . '1', 'D E V E L O P   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $developColName . '1', $headerVerticalDevelopStyleArray);

		//add "I N I T I A L   B U I L D    ▼"
		$initialBuildColName = 'T';
		$this->addColumnBefore($spreadsheetObj, $initialBuildColName);
		$this->setColumnWidth($spreadsheetObj, $initialBuildColName, 3);
		$this->mergeCells($sheet, $initialBuildColName . '1' . ":" . $initialBuildColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $initialBuildColName . '1', 'I N I T I A L   B U I L D   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $initialBuildColName . '1', $headerVerticalInitialBuildStyleArray);

		//add "V I D E O    E S T I M A T E S    ▼"
		$videoEstimatesColName = 'Q';
		$this->addColumnBefore($spreadsheetObj, $videoEstimatesColName);
		$this->setColumnWidth($spreadsheetObj, $videoEstimatesColName, 3);
		$this->mergeCells($sheet, $videoEstimatesColName . '2' . ":" . $videoEstimatesColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $videoEstimatesColName . '2', 'V I D E O    E S T I M A T E S   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $videoEstimatesColName . '2', $headerVerticalDesignSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $videoEstimatesColName . '1', $headerVerticalDesignSubsectionsStyleArray);

		//add " T I M E   D E T A I L S    ▼"
		$timeDetailsColName = 'N';
		$this->addColumnBefore($spreadsheetObj, $timeDetailsColName);
		$this->setColumnWidth($spreadsheetObj, $timeDetailsColName, 3);
		$this->mergeCells($sheet, $timeDetailsColName . '2' . ":" . $timeDetailsColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $timeDetailsColName . '2', 'T I M E   D E T A I L S   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $timeDetailsColName . '2', $headerVerticalDesignSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $timeDetailsColName . '1', $headerVerticalDesignSubsectionsStyleArray);

		//add "L E A R N I N G   D A T A    ▼"
		$learningDataColName = 'I';
		$this->addColumnBefore($spreadsheetObj, $learningDataColName);
		$this->setColumnWidth($spreadsheetObj, $learningDataColName, 3);
		$this->mergeCells($sheet, $learningDataColName . '2' . ":" . $learningDataColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $learningDataColName . '2', 'L E A R N I N G   D A T A   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $learningDataColName . '2', $headerVerticalDesignSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $learningDataColName . '1', $headerVerticalDesignSubsectionsStyleArray);

		//add "D E S I G N    ▼"
		$designColName = 'I';
		$this->addColumnBefore($spreadsheetObj, $designColName);
		$this->setColumnWidth($spreadsheetObj, $designColName, 3);
		$this->mergeCells($sheet, $designColName . '1' . ":" . $designColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $designColName . '1', 'D E S I G N   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $designColName . '1', $headerVerticalDesignStyleArray);

		//group cells
		$this->groupColumns($spreadsheetObj, 1, false, ['J', 'W']);
		$this->groupColumns($spreadsheetObj, 2, false, ['K', 'O']);
		$this->groupColumns($spreadsheetObj, 2, false, ['Q', 'S']);
		$this->groupColumns($spreadsheetObj, 2, false, ['U', 'W']);

		$this->groupColumns($spreadsheetObj, 1, false, ['Y', 'AC']);

		$this->groupColumns($spreadsheetObj, 1, false, ['AE', 'AR']);
		$this->groupColumns($spreadsheetObj, 2, false, ['AF', 'AM']);
		$this->groupColumns($spreadsheetObj, 2, false, ['AO', 'AR']);
	}

	private function setCourseOverviewAndToolboxSheetData() //get data from DB or through APIs and it needs to be in below format
	{
		$this->courseOverViewAndToolboxData =
			[
				[
					'unitNo' => 1,
					'desc' => 'COURSE OVERVIEW',
					'courseData' =>
					[
						[
							'Page' => '1',
							'Item' => 'a',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time (as needed) ' => '10 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time (as needed)' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Format' => 'Lecture (solo)',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => ''
						],
						[
							'Page' => '1',
							'Item' => 'a',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time (as needed) ' => '10 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time (as needed)' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Format' => 'Lecture (solo)',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => ''
						],
						[
							'Page' => '1',
							'Item' => 'a',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time (as needed) ' => '10 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time (as needed)' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Format' => 'Lecture (solo)',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => ''
						]
					]
				],
				[
					'unitNo' => 2,
					'desc' => 'TOOLBOX',
					'courseData' =>
					[
						[
							'Page' => '1',
							'Item' => 'a',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time (as needed) ' => '10 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time (as needed)' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Format' => 'Lecture (solo)',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => ''
						],
						[
							'Page' => '1',
							'Item' => 'a',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time (as needed) ' => '10 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time (as needed)' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Format' => 'Lecture (solo)',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => ''
						],
						[
							'Page' => '1',
							'Item' => 'a',
							'Title' => 'ssasd',
							'Delivery' => 'Question Set',
							'Notes (flexible)' => 'sdf',
							'Design Sign-Off Comments (as needed)' => 'sdf',
							'Learning Category' => 'Staging',
							'Learning Event' => 'Setting learning objectives',
							'Learning Action' => 'TBD',
							'UOs' => 'U1',
							'Graded' => 'Yes',
							'Time (as needed) ' => '10 min',
							'Notes' => 'dfsd',
							'Probable Video Type' => '',
							'Probable Video Format' => '',
							'Estimated Video Time' => '',
							'Outline Build' => 'LEAP LTI',
							'Canvas Equivalent' => 'LEAP LTI',
							'Moodle Equivalent' => 'LEAP LTI',
							'Atrio Equivalent' => 'Segment: Question Set',
							'Build Notes (flexible)' => 'sdf',
							'Confirmed Time (as needed)' => 'Non-Contact',
							'Confirmed Learning Time' => '20 min',
							'Confirmed Video Format' => 'Lecture (solo)',
							'Confirmed Video Estimate' => 'sdf',
							'Final Video Time (as filmed)' => '20 min',
							'Student-Facing Text and Links (as needed)' => '',
							'Links to Asset' => '',
							'Asset Status' => 'Completed',
							'Notes (flexible)' => ''
						]
					]
				]
			];
	}

	private function writeCourseOverviewAndToolboxSheetData($spreadsheetObj, $index)
	{
		$spreadsheetObj->setActiveSheetIndex($index);
		$sheet = $spreadsheetObj->getActiveSheet();
		//heaeders styale
		$headerStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center',
				'wrapText' => true
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerStyle1Array = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$textStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'top',
				'wrapText' => true
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			]
		];

		$checkboxStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'middle'
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				]
			]
		];

		$humanCodingSchemeStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1576D4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$assessmentTypeStyleArray = [
			'alignment' => [
				'horizontal' => 'left',
				'vertical' => 'center'
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => 'EAD1DC')
			],
			'font'  => [
				'size' => 6
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];

		$headerVerticalStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '043461')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size' => 8
			]
		];

		$headerVerticalDevelopStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '8C4BAF')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerVerticalDevelopSubsectionsStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '302758')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size' => 8
			]
		];

		$headerVerticalInitialBuildStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '11C1C4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerVerticalDesignStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1576D4')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
			]
		];

		$headerVerticalDesignSubsectionsStyleArray = [
			'alignment' => [
				'horizontal' => 'center',
				'vertical' => 'top',
				'textRotation' => 90
			],
			'fill' => [
				'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => array('rgb' => '064C90')
			],
			'font'  => [
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size' => 8
			]
		];

		$dottedBorderStyleArray = [
			'borders' => [
				'top' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED,
					'color' => array('argb' => 'FFFFFF'),
				]
			],
		];


		$lastRow = 0;
		$lastColNum = count($this->courseOverViewAndToolboxData[0]['courseData'][0]);
		$lastColName = $this->getColumnNameFromNumber($lastColNum);
		foreach ($this->courseOverViewAndToolboxData as $cmKey => $cmVal) {
			//add unit
			$unitDataColNum = 1;
			$unitDataColName = $this->getColumnNameFromNumber($unitDataColNum);
			$unitDataRowNum = $lastRow + 1;
			$unitData = ['UNIT'];
			$this->addRows($sheet, $unitDataColName, $unitDataRowNum, [$unitData]);
			$this->setCellStyle($spreadsheetObj, $unitDataColName . $unitDataRowNum . ':' . $lastColName . $unitDataRowNum, $headerStyleArray);
			$this->setRowHeight($spreadsheetObj, [$unitDataRowNum], 20);

			//add desc
			$descDataColNum = 1;
			$descDataColName = $this->getColumnNameFromNumber($descDataColNum);
			$descDataRowNum = $unitDataRowNum + 1;
			$this->addRows($sheet, $descDataColName, $descDataRowNum, [$cmVal['desc']]);
			$this->setCellStyle($spreadsheetObj, $descDataColName . $descDataRowNum . ':' . $lastColName . $descDataRowNum, $headerStyle1Array);
			$this->setRowHeight($spreadsheetObj, [$descDataRowNum], 25);

			//add ULO data
			$uloDataColNum = 1;
			$uloDataColName = $this->getColumnNameFromNumber($uloDataColNum);
			$uloDataRowNum = $descDataRowNum + 1;
			$uloDataLastRowNum = $descDataRowNum;
			$uloData = [];
			for ($i = 1; $i <= 5; $i++) { //5 dummy rows in place of ULOs
				$uloData[] = [];
				$uloDataLastRowNum++;
			}
			$this->addRows($sheet, $uloDataColName, $uloDataRowNum, $uloData);
			$this->setCellStyle($spreadsheetObj, $uloDataColName . $uloDataRowNum . ':' . $lastColName . $uloDataLastRowNum, $headerStyle1Array);
			//add top border
			$this->setCellStyle($spreadsheetObj,  $uloDataColName . $uloDataRowNum . ':F' . $uloDataRowNum, $dottedBorderStyleArray);

			//add course data header1
			$courseHeader1DataColNum = 1;
			$courseHeader1DataColName = $this->getColumnNameFromNumber($courseHeader1DataColNum);
			$courseHeader1DataRowNum = $uloDataLastRowNum + 1;
			$courseHeader1Data = ['', '', '', '', '', '', 'L E A R N I N G    S E Q U E N C E    D A T A', '', '', '', '', '', '', 'V I D E O    P R O D U C T I O N    E S T I M A T E S', '', '', 'I N I T I A L    B U I L D    M O D A L I T I E S', '', '', '', '', '', '', '', '', '', 'C O U R S E    P L A N    D E V E L O P M E N T    +    A S S E T    M A N A G E M E N T', '', '', ''];
			$this->addRows($sheet, $courseHeader1DataColName, $courseHeader1DataRowNum, [$courseHeader1Data]);
			$this->setCellStyle($spreadsheetObj, $courseHeader1DataColName . $courseHeader1DataRowNum . ':' . $lastColName . $courseHeader1DataRowNum, $headerStyle1Array);

			//add course data header2
			$courseHeader2DataColNum = 1;
			$courseHeader2DataColName = $this->getColumnNameFromNumber($courseHeader2DataColNum);
			$courseHeader2DataRowNum = $courseHeader1DataRowNum + 1;
			$courseHeader2Data = array_keys($cmVal['courseData'][0]);
			$this->addRows($sheet, $courseHeader2DataColName, $courseHeader2DataRowNum, [$courseHeader2Data]);
			$this->setCellStyle($spreadsheetObj, $courseHeader2DataColName . $courseHeader2DataRowNum . ':' . $lastColName . $courseHeader2DataRowNum, $headerStyleArray);
			//add top border
			$this->setCellStyle($spreadsheetObj,  $courseHeader2DataColName . $courseHeader2DataRowNum . ':' . $spreadsheetObj->getActiveSheet()->getHighestColumn() . $courseHeader2DataRowNum, $dottedBorderStyleArray);

			//add course data
			$courseDataColNum = 1;
			$courseDataColName = $this->getColumnNameFromNumber($courseDataColNum);
			$courseDataRowNum = $courseHeader2DataRowNum + 1;
			$courseData = [];
			foreach ($cmVal['courseData'] as $data) {
				$courseData[] = array_values($data);
			}
			$this->addRows($sheet, $courseDataColName, $courseDataRowNum, $courseData);

			$lastRow = $courseHeader2DataRowNum + count($courseData);
		}
		//set column width
		$i = 1;
		foreach ($courseHeader2Data as $col) {
			// echo strtolower($col);
			if (in_array(strtolower($col), array_keys($this->courseOverViewAndToolboxDataColWidth))) {
				$width = $this->courseOverViewAndToolboxDataColWidth[strtolower($col)];
			} else {
				$width = 50;
			}
			// echo "<br>";
			// echo $width;
			$colName = $this->getColumnNameFromNumber($i);
			$this->setColumnWidth($spreadsheetObj, $colName, $width);
			$i++;
		}

		//pane freeze
		$spreadsheetObj->getActiveSheet()->freezePane('E1');

		//add "A S S E T   D E V E L O P M E N T    ▼"
		$assetDevelopmentColName = 'AA';
		$this->addColumnBefore($spreadsheetObj, $assetDevelopmentColName);
		$this->setColumnWidth($spreadsheetObj, $assetDevelopmentColName, 3);
		$this->mergeCells($sheet, $assetDevelopmentColName . '2' . ":" . $assetDevelopmentColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $assetDevelopmentColName . '2', 'A S S E T   D E V E L O P M E N T   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $assetDevelopmentColName . '2', $headerVerticalDevelopSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $assetDevelopmentColName . '1', $headerVerticalDevelopSubsectionsStyleArray);

		//add "T I M E   +   V I D E O    D E T A I L S    ▼"
		$timeVideoDetailsColName = 'V';
		$this->addColumnBefore($spreadsheetObj, $timeVideoDetailsColName);
		$this->setColumnWidth($spreadsheetObj, $timeVideoDetailsColName, 3);
		$this->mergeCells($sheet, $timeVideoDetailsColName . '2' . ":" . $timeVideoDetailsColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $timeVideoDetailsColName . '2', 'T I M E   +   V I D E O    D E T A I L S   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $timeVideoDetailsColName . '2', $headerVerticalDevelopSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $timeVideoDetailsColName . '1', $headerVerticalDevelopSubsectionsStyleArray);

		//add "D E V E L O P    ▼"
		$developColName = 'V';
		$this->addColumnBefore($spreadsheetObj, $developColName);
		$this->setColumnWidth($spreadsheetObj, $developColName, 3);
		$this->mergeCells($sheet, $developColName . '1' . ":" . $developColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $developColName . '1', 'D E V E L O P   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $developColName . '1', $headerVerticalDevelopStyleArray);

		//add "I N I T I A L   B U I L D    ▼"
		$initialBuildColName = 'Q';
		$this->addColumnBefore($spreadsheetObj, $initialBuildColName);
		$this->setColumnWidth($spreadsheetObj, $initialBuildColName, 3);
		$this->mergeCells($sheet, $initialBuildColName . '1' . ":" . $initialBuildColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $initialBuildColName . '1', 'I N I T I A L   B U I L D   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $initialBuildColName . '1', $headerVerticalInitialBuildStyleArray);

		//add "V I D E O    E S T I M A T E S    ▼"
		$videoEstimatesColName = 'N';
		$this->addColumnBefore($spreadsheetObj, $videoEstimatesColName);
		$this->setColumnWidth($spreadsheetObj, $videoEstimatesColName, 3);
		$this->mergeCells($sheet, $videoEstimatesColName . '2' . ":" . $videoEstimatesColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $videoEstimatesColName . '2', 'V I D E O    E S T I M A T E S   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $videoEstimatesColName . '2', $headerVerticalDesignSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $videoEstimatesColName . '1', $headerVerticalDesignSubsectionsStyleArray);

		//add " T I M E   D E T A I L S    ▼"
		$timeDetailsColName = 'L';
		$this->addColumnBefore($spreadsheetObj, $timeDetailsColName);
		$this->setColumnWidth($spreadsheetObj, $timeDetailsColName, 3);
		$this->mergeCells($sheet, $timeDetailsColName . '2' . ":" . $timeDetailsColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $timeDetailsColName . '2', 'T I M E   D E T A I L S   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $timeDetailsColName . '2', $headerVerticalDesignSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $timeDetailsColName . '1', $headerVerticalDesignSubsectionsStyleArray);

		//add "L E A R N I N G   D A T A    ▼"
		$learningDataColName = 'G';
		$this->addColumnBefore($spreadsheetObj, $learningDataColName);
		$this->setColumnWidth($spreadsheetObj, $learningDataColName, 3);
		$this->mergeCells($sheet, $learningDataColName . '2' . ":" . $learningDataColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $learningDataColName . '2', 'L E A R N I N G   D A T A   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $learningDataColName . '2', $headerVerticalDesignSubsectionsStyleArray);
		$this->setCellStyle($spreadsheetObj, $learningDataColName . '1', $headerVerticalDesignSubsectionsStyleArray);

		//add "D E S I G N    ▼"
		$designColName = 'G';
		$this->addColumnBefore($spreadsheetObj, $designColName);
		$this->setColumnWidth($spreadsheetObj, $designColName, 3);
		$this->mergeCells($sheet, $designColName . '1' . ":" . $designColName . $spreadsheetObj->getActiveSheet()->getHighestRow());
		$this->addCell($sheet, $designColName . '1', 'D E S I G N   ' . $this->getUTF8Symbol('&#9660;'));
		$this->setCellStyle($spreadsheetObj, $designColName . '1', $headerVerticalDesignStyleArray);

		//group cells
		$this->groupColumns($spreadsheetObj, 1, false, ['H', 'T']);
		$this->groupColumns($spreadsheetObj, 2, false, ['I', 'M']);
		$this->groupColumns($spreadsheetObj, 2, false, ['O', 'P']);
		$this->groupColumns($spreadsheetObj, 2, false, ['R', 'T']);

		$this->groupColumns($spreadsheetObj, 1, false, ['V', 'Z']);

		$this->groupColumns($spreadsheetObj, 1, false, ['AB', 'AK']);
		$this->groupColumns($spreadsheetObj, 2, false, ['AC', 'AG']);
		$this->groupColumns($spreadsheetObj, 2, false, ['AI', 'AK']);
	}

	private function addSheet($spreadsheetObj, $index, $title)
	{
		$spreadsheetObj->createSheet();
		$spreadsheetObj->setActiveSheetIndex($index);
		$spreadsheetObj->getActiveSheet()->setTitle($title);
	}

	private function addCell($sheet, $cellNotation, $cellValue)
	{
		$sheet->setCellValue($cellNotation, $cellValue);
	}

	private function addRows($sheet, $coloumnName, $rowIndex, $sheetData)
	{
		foreach ($sheetData as $data) {
			$sheet->fromArray([$data], NULL, $coloumnName . $rowIndex);
			$rowIndex++;
		}
	}

	private function mergeCells($sheet, $cellNotation)
	{
		$sheet->mergeCells($cellNotation);
	}

	private function setCellStyle($spreadsheetObj, $cellNotation, $styleArray)
	{
		$spreadsheetObj
			->getActiveSheet()
			->getStyle($cellNotation)
			->applyFromArray($styleArray);
	}

	private function setColumnWidth($spreadsheetObj, $columnName, $width)
	{
		$spreadsheetObj
			->getActiveSheet()
			->getColumnDimension($columnName)
			->setWidth($width);
	}

	private function setColumnRowsStyle($spreadsheetObj, $columnName, $rows, $columnAStyleArray)
	{
		foreach ($rows as $row) {
			$this->setCellStyle($spreadsheetObj, $columnName . $row, $columnAStyleArray);
		}
	}

	private function setRowHeight($spreadsheetObj, $rows, $rowHeight)
	{
		foreach ($rows as $row) {
			$spreadsheetObj
				->getActiveSheet()
				->getRowDimension($row)
				->setRowHeight($rowHeight);
		}
	}

	private function groupColumns($spreadsheetObj, $outlineLevel, $visible, $columns)
	{
		$startColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columns[0]);
		$endColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columns[1]);
		for ($i = $startColumnIndex; $i <= $endColumnIndex; $i++) {
			$col = $this->getColumnNameFromNumber($i);
			$spreadsheetObj->getActiveSheet()
				->getColumnDimension($col)
				->setOutlineLevel($outlineLevel)
				->setVisible($visible)
				->setCollapsed(true);
		}
		$spreadsheetObj->getActiveSheet()->setShowSummaryRight(false);
	}

	private function getColumnNameFromNumber($num)
	{
		$numeric = ($num - 1) % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval(($num - 1) / 26);
		if ($num2 > 0) {
			return $this->getColumnNameFromNumber($num2) . $letter;
		} else {
			return $letter;
		}
	}

	private function getUTF8Symbol($code)
	{
		$html = new \PhpOffice\PhpSpreadsheet\Helper\Html();
		return $html->toRichTextObject($code);
	}

	private function addColumnBefore($spreadsheetObj, $columnName)
	{
		$spreadsheetObj->getActiveSheet()->insertNewColumnBefore($columnName, 1);
	}
}
