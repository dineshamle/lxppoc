<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class createTaxonomyController extends Controller
{
	public function createTaxonomyCaseJson()
	{
		
		$json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"},"5":{"title":"C3","desc":"Demonstrate a commitment to radical social work practices to dismantle oppressive systems and create opportunities for equity for complex and diverse populations"},"6":{"title":"C4","desc":"Use scholarly knowledge and scholarly practices to critically elevate and evaluate the epistemologies that inform evidence based practices (i.e what is missing)"},"7":{"title":"C5","desc":"Formulate culturally-informed and responsive working alliances with clients from diverse and complex populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"},"14":{"title":"U3","desc":"Examine self-location, social identities and intersubjectivity with complex and diverse populations; addressing issues of intersectionality; how do identities of therapists affect their work"},"15":{"title":"U4","desc":"Identify ethical imperatives in practice with complex and diverse populations"},"16":{"title":"U5","desc":"Identify the common assumptions attributed to various complex and diverse populations"},"17":{"title":"U6","desc":"Critically examine the source and history of assumptions made about complex and diverse populations"},"18":{"title":"U7","desc":"Provide alternate explanations that challenge the assumptions made about complex and diverse populations"},"19":{"title":"U8","desc":"Examine how assumptions impact application of skills to complex and diverse populations"},"20":{"title":"U9","desc":"Demonstrate the ability to communicate revolutionary thinking in verbal and written communications"},"21":{"title":"U10","desc":"Create a personal plan for continued growth in utilizing radical social work practices"},"22":{"title":"U11","desc":"Identify opportunities for equity for complex and diverse populations"},"23":{"title":"U12","desc":"Engaging a narrative inquiry lens as an anti-oppressive strategy of radical social work practices"},"24":{"title":"U13","desc":"Use specific cases to demonstrate knowledge of skill sets related to a particular population"},"25":{"title":"U14","desc":"Generate knowledge to support\/enhance skill applications with diverse and complex populations"},"26":{"title":"U15","desc":"Critically examine clinical processes of change with diverse and complex populations"},"27":{"title":"U16","desc":"Critically analyze the impact of intersubjectivity, transference, countertransference and the use of defenses when working with complex and diverse populations"},"28":{"title":"U17","desc":"Critically examine how one might go about decolonizing and decentering Western values and beliefs in these clinical processes"},"29":{"title":"U18","desc":"Generate knowledge strategies to enhance anti-oppressive frameworks in clinical practice"},"30":{"title":"U19","desc":"Develop knowledge on a particular population or focus area"},"31":{"title":"U20","desc":"Find contemporary examples of how radical social work has been practiced (or not practiced) with this population\/focus area"},"32":{"title":"U21","desc":"Describe radical social work practices that could be utilized for this population\/focus area"},"33":{"title":"U22","desc":"Using a case example\/study to apply radical social work skills"},"34":{"title":"U23","desc":""},"35":{"title":"U24","desc":""},"36":{"title":"U25","desc":""},"37":{"title":"U26","desc":""},"38":{"title":"U27","desc":""},"39":{"title":"U28","desc":""},"40":{"title":"U29","desc":""},"41":{"title":"U30","desc":""},"42":{"title":"U31","desc":""},"43":{"title":"U32","desc":""},"44":{"title":"U33","desc":""},"45":{"title":"U34","desc":""},"46":{"title":"U35","desc":""},"47":{"title":"U36","desc":""},"48":{"title":"U37","desc":""},"49":{"title":"U38","desc":""},"50":{"title":"U39","desc":""},"51":{"title":"U40","desc":""}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3],"13":[3],"14":[3],"15":[3],"16":[4],"17":[4],"18":[4],"19":[4],"20":[5],"21":[5],"22":[5],"23":[5],"24":[5],"25":[5],"26":[5],"27":[5],"28":[5],"29":[5],"30":[5],"31":[5],"32":[6],"33":[7]}}';
		// $json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3,4],"13":[4]}}';
		$outcomesArr = json_decode($json, true);

		// echo Str::uuid();
		//doc node details
		$docTitle = date('YmdHis').'-testDinesh';
		$loggedInUser = 'tester';
		$docIdentifier = (string) Str::uuid();
		
		//item details
		foreach($outcomesArr['course_goals'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != '')
			$outcomesArr['course_goals'][$k]['uuid'] = (string) Str::uuid();
		}

		foreach($outcomesArr['unit_level_objectives'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != '')
			$outcomesArr['unit_level_objectives'][$k]['uuid'] = (string) Str::uuid();
		}
		

		$caseJsonArr = [];

		//document section
		$caseJsonArr['CFDocument'] =
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle,
				'lastChangeDateTime' => date('Y-m-d H:i:s'),
				'creator' => $loggedInUser,
				'adoptionStatus' => 'Draft',
				'CFPackageURI' =>
				[
					'identifier' => $docIdentifier,
					'uri' => '',
					'title' => $docTitle
				] 
			];

		//item types
		// Course Statement
		$courseStatementItemTypeIdentifier = (string) Str::uuid();
		$courseStatementItemTypeTitle = 'Course Statement';
		$caseJsonArr['CFDefinitions']['CFItemTypes'][] = 
		[
			'identifier' => $courseStatementItemTypeIdentifier,
			'uri' => '',
			'title' => $courseStatementItemTypeTitle,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'hierarchyCode' => '',
			'description' => ''
		];

		// Course
		$courseItemTypeIdentifier = (string) Str::uuid();
		$courseItemTypeTitle = 'Course';
		$caseJsonArr['CFDefinitions']['CFItemTypes'][] = 
		[
			'identifier' => $courseItemTypeIdentifier,
			'uri' => '',
			'title' => $courseItemTypeTitle,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'hierarchyCode' => '',
			'description' => ''
		];
		
		// Course Goals
		$courseGoalsItemTypeIdentifier = (string) Str::uuid();
		$courseGoalsItemTypeTitle = 'Course Goals';
		$caseJsonArr['CFDefinitions']['CFItemTypes'][] = 
		[
			'identifier' => $courseGoalsItemTypeIdentifier,
			'uri' => '',
			'title' => $courseGoalsItemTypeTitle,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'hierarchyCode' => '',
			'description' => ''
		];
		// Unit Level Objectives
		$unitLevelObjectivesItemTypeIdentifier = (string) Str::uuid();
		$unitLevelObjectivesItemTypeTitle = 'Unit Level Objectives';
		$caseJsonArr['CFDefinitions']['CFItemTypes'][] = 
		[
			'identifier' => $unitLevelObjectivesItemTypeIdentifier,
			'uri' => '',
			'title' => $unitLevelObjectivesItemTypeTitle,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'hierarchyCode' => '',
			'description' => ''
		];



		//item section
		//first item
		$firstItemIdentifier = (string) Str::uuid();
		$firstItemHumanCodingScheme = 'item1 hcs';
		$firstItemFullStatement = 'item1 fs';
		$caseJsonArr['CFItems'][] = [
			'identifier' => $firstItemIdentifier,
			'uri' => '',
			"fullStatement" => $firstItemFullStatement,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'CFItemType' => $courseItemTypeTitle,
			'humanCodingScheme' => $firstItemHumanCodingScheme,
			// 'listEnumeration' => '',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'CFItemTypeURI' =>
			[
				'identifier' => $courseItemTypeIdentifier,
				'uri' => '',
				'title' => $courseItemTypeTitle
			],
		];

		//course statement item
		$courseStatementIdentifier = (string) Str::uuid();
		$courseStatementFullStatement = 'Course Statement';
		$caseJsonArr['CFItems'][] = [
			'identifier' => $courseStatementIdentifier,
			'uri' => '',
			'fullStatement' => $courseStatementFullStatement,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'CFItemType' => $courseStatementItemTypeTitle,
			// 'humanCodingScheme' => '',
			// 'listEnumeration' => '',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'CFItemTypeURI' =>
			[
				'identifier' => $courseStatementItemTypeIdentifier,
				'uri' => '',
				'title' => $courseStatementItemTypeTitle
			],
		];

		//Statement item
		$statementIdentifier = (string) Str::uuid();
		$statementFullStatement = 'statement';
		$caseJsonArr['CFItems'][] = [
			'identifier' => $statementIdentifier,
			'uri' => '',
			'fullStatement' => $statementFullStatement,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'CFItemType' => $courseStatementItemTypeTitle,
			// 'humanCodingScheme' => '',
			// 'listEnumeration' => '',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'CFItemTypeURI' =>
			[
				'identifier' => $courseStatementItemTypeIdentifier,
				'uri' => '',
				'title' => $courseStatementItemTypeTitle
			],
		];
	
		//course goals
		$courseGoalsIdentifier = (string) Str::uuid();
		$courseGoalsFullStatement = 'Course Goals';
		$caseJsonArr['CFItems'][] = [
			'identifier' => $courseGoalsIdentifier,
			'uri' => '',
			'fullStatement' => $courseGoalsFullStatement,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'CFItemType' => $courseGoalsItemTypeTitle,
			// 'humanCodingScheme' => '',
			// 'listEnumeration' => '',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'CFItemTypeURI' =>
			[
				'identifier' => $courseGoalsItemTypeIdentifier,
				'uri' => '',
				'title' => $courseGoalsItemTypeTitle
			],
		];

		foreach($outcomesArr['course_goals'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFItems'][] = [
					'identifier' => $v['uuid'],
					'uri' => '',
					'fullStatement' => $v['desc'],
					'lastChangeDateTime' => date('Y-m-d H:i:s'),
					'CFItemType' => $courseGoalsItemTypeTitle,
					'humanCodingScheme' => $v['title'],
					// 'listEnumeration' => '',
					'CFDocumentURI' =>
					[
						'identifier' => $docIdentifier,
						'uri' => '',
						'title' => $docTitle
					],
					'CFItemTypeURI' =>
					[
						'identifier' => $courseGoalsItemTypeIdentifier,
						'uri' => '',
						'title' => $courseGoalsItemTypeTitle
					],
				];
			}
		}

		//unit level objectives
		$uloIdentifier = (string) Str::uuid();
		$uloFullStatement = 'Unit Level Objectives';
		$caseJsonArr['CFItems'][] = [
			'identifier' => $uloIdentifier,
			'uri' => '',
			'fullStatement' => $uloFullStatement,
			'lastChangeDateTime' => date('Y-m-d H:i:s'),
			'CFItemType' => $unitLevelObjectivesItemTypeTitle,
			// 'humanCodingScheme' => '',
			// 'listEnumeration' => '',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'CFItemTypeURI' =>
			[
				'identifier' => $unitLevelObjectivesItemTypeIdentifier,
				'uri' => '',
				'title' => $unitLevelObjectivesItemTypeTitle
			],
		];

		foreach($outcomesArr['unit_level_objectives'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFItems'][] = [
					'identifier' => $v['uuid'],
					'uri' => '',
					'fullStatement' => $v['desc'],
					'lastChangeDateTime' => date('Y-m-d H:i:s'),
					'CFItemType' => $unitLevelObjectivesItemTypeTitle,
					'humanCodingScheme' => $v['title'],
					// 'listEnumeration' => '',
					'CFDocumentURI' =>
					[
						'identifier' => $docIdentifier,
						'uri' => '',
						'title' => $docTitle
					],
					'CFItemTypeURI' =>
					[
						'identifier' => $unitLevelObjectivesItemTypeIdentifier,
						'uri' => '',
						'title' => $unitLevelObjectivesItemTypeTitle
					],
				];
			}
		}

		// association section
		//first item to doc association
		$caseJsonArr['CFAssociations'][] = [
			'identifier' => (string) Str::uuid(),
			'associationType' => 'isChildOf',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			// 'sequenceNumber' => '',
			'uri' => '',
			'originNodeURI' =>
			[
				'identifier' => $firstItemIdentifier,
				// 'uri' => '',
				// 'title' => $docTitle
			],
			'destinationNodeURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				// 'title' => $docTitle
			],
			'lastChangeDateTime' => date('Y-m-d H:i:s')
		];

		//course statement to first item
		$caseJsonArr['CFAssociations'][] = [
			'identifier' => (string) Str::uuid(),
			'associationType' => 'isChildOf',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'sequenceNumber' => '1',
			'uri' => '',
			'originNodeURI' =>
			[
				'identifier' => $courseStatementIdentifier,
				// 'uri' => '',
				// 'title' => $docTitle
			],
			'destinationNodeURI' =>
			[
				'identifier' => $firstItemIdentifier,
				'uri' => '',
				// 'title' => $docTitle
			],
			'lastChangeDateTime' => date('Y-m-d H:i:s')
		];

		//statement to course statement association
		$caseJsonArr['CFAssociations'][] = [
			'identifier' => (string) Str::uuid(),
			'associationType' => 'isChildOf',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			// 'sequenceNumber' => '',
			'uri' => '',
			'originNodeURI' =>
			[
				'identifier' => $statementIdentifier,
				// 'uri' => '',
				// 'title' => $docTitle
			],
			'destinationNodeURI' =>
			[
				'identifier' => $courseStatementIdentifier,
				'uri' => '',
				// 'title' => $docTitle
			],
			'lastChangeDateTime' => date('Y-m-d H:i:s')
		];

		//course goals to first item
		$caseJsonArr['CFAssociations'][] = [
			'identifier' => (string) Str::uuid(),
			'associationType' => 'isChildOf',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'sequenceNumber' => '2',
			'uri' => '',
			'originNodeURI' =>
			[
				'identifier' => $courseGoalsIdentifier,
				// 'uri' => '',
				// 'title' => $docTitle
			],
			'destinationNodeURI' =>
			[
				'identifier' => $firstItemIdentifier,
				'uri' => '',
				// 'title' => $docTitle
			],
			'lastChangeDateTime' => date('Y-m-d H:i:s')
		];

		//ULOs to first item
		$caseJsonArr['CFAssociations'][] = [
			'identifier' => (string) Str::uuid(),
			'associationType' => 'isChildOf',
			'CFDocumentURI' =>
			[
				'identifier' => $docIdentifier,
				'uri' => '',
				'title' => $docTitle
			],
			'sequenceNumber' => '3',
			'uri' => '',
			'originNodeURI' =>
			[
				'identifier' => $uloIdentifier,
				// 'uri' => '',
				// 'title' => $docTitle
			],
			'destinationNodeURI' =>
			[
				'identifier' => $firstItemIdentifier,
				'uri' => '',
				// 'title' => $docTitle
			],
			'lastChangeDateTime' => date('Y-m-d H:i:s')
		];

		//'isChildOf' associations for course goals
		foreach($outcomesArr['course_goals'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFAssociations'][] = [
					'identifier' => (string) Str::uuid(),
					'associationType' => 'isChildOf',
					'CFDocumentURI' =>
					[
						'identifier' => $docIdentifier,
						'uri' => '',
						'title' => $docTitle
					],
					// 'sequenceNumber' => '',
					'uri' => '',
					'originNodeURI' =>
					[
						'identifier' => $v['uuid'],
						// 'uri' => '',
						// 'title' => $docTitle
					],
					'destinationNodeURI' =>
					[
						'identifier' => $courseGoalsIdentifier,
						'uri' => '',
						// 'title' => $docTitle
					],
					'lastChangeDateTime' => date('Y-m-d H:i:s')
				];
			}
		}

		//'isChildOf' associations for ULOs
		foreach($outcomesArr['unit_level_objectives'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFAssociations'][] = [
					'identifier' => (string) Str::uuid(),
					'associationType' => 'isChildOf',
					'CFDocumentURI' =>
					[
						'identifier' => $docIdentifier,
						'uri' => '',
						'title' => $docTitle
					],
					// 'sequenceNumber' => '',
					'uri' => '',
					'originNodeURI' =>
					[
						'identifier' => $v['uuid'],
						// 'uri' => '',
						// 'title' => $docTitle
					],
					'destinationNodeURI' =>
					[
						'identifier' => $uloIdentifier,
						'uri' => '',
						// 'title' => $docTitle
					],
					'lastChangeDateTime' => date('Y-m-d H:i:s')
				];
			}
		}

		//'isRelatedTo' association between Course Goals and ULOs
		foreach($outcomesArr['cg_ulo_mapping'] as $ulo => $courseGoals){
			foreach($courseGoals as $courseGoal){
				$caseJsonArr['CFAssociations'][] = [
					'identifier' => (string) Str::uuid(),
					'associationType' => 'isRelatedTo',
					'CFDocumentURI' =>
					[
						'identifier' => $docIdentifier,
						'uri' => '',
						'title' => $docTitle
					],
					// 'sequenceNumber' => '',
					'uri' => '',
					'originNodeURI' =>
					[
						'identifier' => $outcomesArr['unit_level_objectives'][$ulo]['uuid'],
						// 'uri' => '',
						// 'title' => $docTitle
					],
					'destinationNodeURI' =>
					[
						'identifier' => $outcomesArr['course_goals'][$courseGoal]['uuid'],
						'uri' => '',
						// 'title' => $docTitle
					],
					'lastChangeDateTime' => date('Y-m-d H:i:s')
				];
			}
		}
		
		// echo "<pre>";
		// echo json_encode($caseJsonArr, JSON_PRETTY_PRINT);


		// print_r($outcomesArr);

		// write file to a json file
		// echo storage_path('app');exit;
		$filename = storage_path('app/').(string) Str::uuid().'.json';
		$fp = fopen($filename, 'w');
		fwrite($fp, json_encode($caseJsonArr));
		fclose($fp);



		//get import identifier
		$authToken = 'eyJhbGciOiJSUzI1NiIsImtpZCI6IkVFOEMxNDdDNTAwM0ZBRDBFODAxQ0EwQjYxNDFDQTBCN0Q5Qzg3ODMiLCJ0eXAiOiJKV1QiLCJ4NXQiOiI3b3dVZkZBRC10RG9BY29MWVVIS0MzMmNoNE0ifQ.eyJuYmYiOjE2MzIzMTA5MTgsImV4cCI6MTYzMjMxNDUxOCwiaXNzIjoiaHR0cHM6Ly9kZXYuZnJvc3QuMnUuY29tL29hdXRoIiwiYXVkIjpbImh0dHBzOi8vZGV2LmZyb3N0LjJ1LmNvbS9vYXV0aC9yZXNvdXJjZXMiLCJPcmdhbml6YXRpb24iXSwiY2xpZW50X2lkIjoiMmE2OTY1ZjktYjBmNi00M2Q3LTllMDQtNDYwYzU4NWY2NTkyIiwic3ViIjoiNDYiLCJhdXRoX3RpbWUiOjE2MzIyODkyOTIsImlkcCI6ImxvY2FsIiwiQWNjb3VudElkIjoidG9sOC1kd0U0U25BbzRrZFNQMFpHZyIsIlVzZXJJZCI6ImRhYTE5YzIxLTU5OGQtNDE4Yy05MGY0LTgyMmZiZTczYmY3OCIsIkZpcnN0TmFtZSI6ImRpbmVzaCIsIkxhc3ROYW1lIjoiYW1sZSIsIkVtYWlsIjoiZGluZXNoLmFtbGVAbGVhcm5pbmdtYXRlLmNvbSIsIlVzZXJOYW1lIjoiZGluZXNoLmFtbGUiLCJBY3RpdmUiOiIxIiwiT3JnYW5pemF0aW9uSUQiOiJmNjJhYWM3ZS1iYjAxLTRhODktOWUyOS00NzIxZDkzN2JiYjciLCJQbGF0Zm9ybVR5cGVzIjoiNCM2IzcjMiMzIiwiVGVuYW50SWQiOiJmYjM1YWY4OS1hZjEwLTQ2OGQtYjI5Yi0xYzFhMTA2MjY1MzIiLCJUZW5hbnRQbGF0Zm9ybXMiOiI0LDYsNywyLDMiLCJUZW5hbnRTaG9ydENvZGUiOiJhbGlnbiIsIkxhbmRpbmdQbGF0Zm9ybSI6IjIiLCJUZW5hbnRMb2dvIjoiIiwiUmVmcmVzaFRpbWUiOiIxODAwIiwiUm9sZUlkIjoiYWJlM2VhZDQtNWQzMS00YmY3LTk3ZDEtNTFhMGRkMzkyZGY1IiwiUm9sZUlkZW50IjoiMSIsIkxUSU91dCI6IkZhbHNlIiwiSXNUZW5hbnRBZG1pbiI6IlRydWUiLCJUZW5hbnRVUkwiOiJodHRwczovL2Rldi5mcm9zdC4ydS5jb20vYXV0aC8jL2xvZ2luL2FsaWduIiwic2NvcGUiOlsib3BlbmlkIiwicHJvZmlsZSIsIk9yZ2FuaXphdGlvbiIsIm9mZmxpbmVfYWNjZXNzIl0sImFtciI6WyJjdXN0b20iXX0.lkXcgnDydEd3i4hDuy7YTZNhOkOcoECwYSwXBELnZxVAtembsla2Uc0K_dDSc8PLG0W7bx2tKK9Rehq-XqKbUIiSdW0bjo1KzRvMmfTIdK-FZI4Mo7ig5EeromufziXibi7o7qr7wwTYP0Dn2fbBqeK6TOTg3BPYsuASP9jOR-usY3t70_4wEtYnFvoEuxDlm_oj_7U6T0iPQ6q280LovMeayAt_00FP3pVqDQY6LsPdkgIYu9xm21UwBa7hrs_U9tG_VUDyInhg6FrAL6jexjrH6uZRa2GxMoMYFujUJJJJniFewQIevKHmsuz4aSlNBzdjeeAt5DHC1iRILnQwIg';

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.dev.frost.2u.com/server/api/v1/importTaxonomy',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => array('source_identifier' => $docIdentifier, 'import_type' => '1','is_ready_to_commit_changes' => '0'),
		CURLOPT_HTTPHEADER => array(
			'Authorization: '.$authToken
		),
		));

		$response = curl_exec($curl);
		$response = json_decode($response, true);
		// print_r($response);
		$importIndentifier = $response['data']['import_identifier'];

		
		//call case json api
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.dev.frost.2u.com/server/api/v1/importTaxonomy',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => array('import_identifier' => $importIndentifier, 'import_type' => '3','is_ready_to_commit_changes' => '1','case_json'=> new \CURLFILE($filename)),
		CURLOPT_HTTPHEADER => array(
			'Authorization: '.$authToken
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		echo $response;
		unlink($filename);

		exit;
	}
}
