<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class createTaxonomyController extends Controller
{
	private $lastChangeDateTime;
	private $uri;
	const ADMIN_BASE_URL = 'https://dev.frost.2u.com';
	const ALIGN_BASE_URL = 'https://api.dev.frost.2u.com';
	const TWOU_DOMAIN = 'https://frost.2u.com';

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
			CURLOPT_CUSTOMREQUEST => $params['method'],
			CURLOPT_POSTFIELDS => $params['data']
		];

		if(isset($params['headers'])){
			$curlOptions[CURLOPT_HTTPHEADER] = $params['headers'];
		}

		curl_setopt_array($curl, $curlOptions);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public function getAccessToken($tenant_code)
	{
		//get client_id and client_secret
		$response = $this->httpRequest(
			[
				'url' => self::ADMIN_BASE_URL.'/oauth/customapi/v1/clientmanager/getclientbytenant',
				'method' => 'POST',
				'data' => '{
					"TenantShortCode": "'.$tenant_code.'"
				}',
				'headers' => [
					'Content-Type: application/json'
				]
			]
		);

		$response = json_decode($response, true);
		echo "<pre>";
		print_r($response);

		//get access token
		$response = $this->httpRequest(
			[
				'url' => self::ADMIN_BASE_URL.'/oauth/connect/token',
				'method' => 'POST',
				'data' => [
					'client_id' => $response['clients'][0]['clientID'], 
					'client_secret' => $response['clients'][0]['clientSecret'], 
					'grant_type' => 'client_credentials'
				]
			]
		);

		echo "<pre>";
		$response = json_decode($response, true);
		print_r($response);
		return $response['access_token'];

		// exit;

		// $curl = curl_init();
		// curl_setopt_array($curl, array(
		// CURLOPT_URL => 'https://dev.frost.2u.com/oauth/customapi/v1/clientmanager/getclientbytenant',
		// CURLOPT_RETURNTRANSFER => true,
		// CURLOPT_ENCODING => '',
		// CURLOPT_MAXREDIRS => 10,
		// CURLOPT_TIMEOUT => 0,
		// CURLOPT_FOLLOWLOCATION => true,
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// CURLOPT_CUSTOMREQUEST => 'POST',
		// CURLOPT_POSTFIELDS =>'{
		// 	"TenantShortCode": "Align"
		// }',
		// CURLOPT_HTTPHEADER => array(
		// 	'Content-Type: application/json'
		// ),
		// ));

		// $response = curl_exec($curl);
		// curl_close($curl);
		// $response = json_decode($response, true);
		// echo "<pre>";
		// print_r($response);


		// $curl = curl_init();
		// curl_setopt_array($curl, array(
		// CURLOPT_URL => 'https://dev.frost.2u.com/oauth/connect/token',
		// CURLOPT_RETURNTRANSFER => true,
		// CURLOPT_ENCODING => '',
		// CURLOPT_MAXREDIRS => 10,
		// CURLOPT_TIMEOUT => 0,
		// CURLOPT_FOLLOWLOCATION => true,
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// CURLOPT_CUSTOMREQUEST => 'POST',
		// CURLOPT_POSTFIELDS => 
		// 	[
		// 		'client_id' => $response['clients'][0]['clientID'], 
		// 		'client_secret' => $response['clients'][0]['clientSecret'], 
		// 		'grant_type' => 'client_credentials'
		// 	],

		// ));

		// $response = curl_exec($curl);
		// $response = json_decode($response, true);
		// echo "<pre>";
		// print_r($response);
		// // return 'eyJhbGciOiJSUzI1NiIsImtpZCI6IkVFOEMxNDdDNTAwM0ZBRDBFODAxQ0EwQjYxNDFDQTBCN0Q5Qzg3ODMiLCJ0eXAiOiJKV1QiLCJ4NXQiOiI3b3dVZkZBRC10RG9BY29MWVVIS0MzMmNoNE0ifQ.eyJuYmYiOjE2MzI5ODgyNzIsImV4cCI6MTYzMjk5MTg3MiwiaXNzIjoiaHR0cHM6Ly9kZXYuZnJvc3QuMnUuY29tL29hdXRoIiwiYXVkIjpbImh0dHBzOi8vZGV2LmZyb3N0LjJ1LmNvbS9vYXV0aC9yZXNvdXJjZXMiLCJPcmdhbml6YXRpb24iXSwiY2xpZW50X2lkIjoiMmE2OTY1ZjktYjBmNi00M2Q3LTllMDQtNDYwYzU4NWY2NTkyIiwic3ViIjoiNDYiLCJhdXRoX3RpbWUiOjE2MzI5ODgyNDgsImlkcCI6ImxvY2FsIiwiQWNjb3VudElkIjoidG9sOC1kd0U0U25BbzRrZFNQMFpHZyIsIlVzZXJJZCI6ImRhYTE5YzIxLTU5OGQtNDE4Yy05MGY0LTgyMmZiZTczYmY3OCIsIkZpcnN0TmFtZSI6ImRpbmVzaCIsIkxhc3ROYW1lIjoiYW1sZSIsIkVtYWlsIjoiZGluZXNoLmFtbGVAbGVhcm5pbmdtYXRlLmNvbSIsIlVzZXJOYW1lIjoiZGluZXNoLmFtbGUiLCJBY3RpdmUiOiIxIiwiT3JnYW5pemF0aW9uSUQiOiJmNjJhYWM3ZS1iYjAxLTRhODktOWUyOS00NzIxZDkzN2JiYjciLCJQbGF0Zm9ybVR5cGVzIjoiNCM2IzcjMiMzIiwiVGVuYW50SWQiOiJmYjM1YWY4OS1hZjEwLTQ2OGQtYjI5Yi0xYzFhMTA2MjY1MzIiLCJUZW5hbnRQbGF0Zm9ybXMiOiI0LDYsNywyLDMiLCJUZW5hbnRTaG9ydENvZGUiOiJhbGlnbiIsIkxhbmRpbmdQbGF0Zm9ybSI6IjIiLCJUZW5hbnRMb2dvIjoiIiwiUmVmcmVzaFRpbWUiOiIxODAwIiwiUm9sZUlkIjoiYWJlM2VhZDQtNWQzMS00YmY3LTk3ZDEtNTFhMGRkMzkyZGY1IiwiUm9sZUlkZW50IjoiMSIsIkxUSU91dCI6IkZhbHNlIiwiSXNUZW5hbnRBZG1pbiI6IlRydWUiLCJUZW5hbnRVUkwiOiJodHRwczovL2Rldi5mcm9zdC4ydS5jb20vYXV0aC8jL2xvZ2luL2FsaWduIiwic2NvcGUiOlsib3BlbmlkIiwicHJvZmlsZSIsIk9yZ2FuaXphdGlvbiIsIm9mZmxpbmVfYWNjZXNzIl0sImFtciI6WyJjdXN0b20iXX0.aYV_gWHAn_j2aik0PUYOIFozM_iaE7uB-ZFGJ2zTYsCWH7dlIuvdt17u54aV563oMhlUb2vTXmp5xZaGJYjCKXrdDT4vK7Kos4RWsogKxf297RZ3SWbHpOUMPKvm9lu42pOJm6iPLp8Q8VtXDBzN7orCi5K4Hbhlv4oG36FOrbioDj5Ext5AWaOd-XKH6g5NufZvxgrMF4UQsnF-6nVB3zJlrDB-jC4Pii9ULMrbG7fdgHsXIYn9FY--ti5_Vi8dovcrqWvdrnQr9jFH8SID3lD22g-ChtsIC3khbddNkCWTSg9H1cughzErFCKYsdvHJz0-XEAx0y9n1b1bM-r4Ow';
		// return $response['access_token'];
	}
	
	public function createTaxonomyCaseJson()
	{
		$this->setLastChangeDateTime();
		$this->setUri();

		// $json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"},"5":{"title":"C3","desc":"Demonstrate a commitment to radical social work practices to dismantle oppressive systems and create opportunities for equity for complex and diverse populations"},"6":{"title":"C4","desc":"Use scholarly knowledge and scholarly practices to critically elevate and evaluate the epistemologies that inform evidence based practices (i.e what is missing)"},"7":{"title":"C5","desc":"Formulate culturally-informed and responsive working alliances with clients from diverse and complex populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"},"14":{"title":"U3","desc":"Examine self-location, social identities and intersubjectivity with complex and diverse populations; addressing issues of intersectionality; how do identities of therapists affect their work"},"15":{"title":"U4","desc":"Identify ethical imperatives in practice with complex and diverse populations"},"16":{"title":"U5","desc":"Identify the common assumptions attributed to various complex and diverse populations"},"17":{"title":"U6","desc":"Critically examine the source and history of assumptions made about complex and diverse populations"},"18":{"title":"U7","desc":"Provide alternate explanations that challenge the assumptions made about complex and diverse populations"},"19":{"title":"U8","desc":"Examine how assumptions impact application of skills to complex and diverse populations"},"20":{"title":"U9","desc":"Demonstrate the ability to communicate revolutionary thinking in verbal and written communications"},"21":{"title":"U10","desc":"Create a personal plan for continued growth in utilizing radical social work practices"},"22":{"title":"U11","desc":"Identify opportunities for equity for complex and diverse populations"},"23":{"title":"U12","desc":"Engaging a narrative inquiry lens as an anti-oppressive strategy of radical social work practices"},"24":{"title":"U13","desc":"Use specific cases to demonstrate knowledge of skill sets related to a particular population"},"25":{"title":"U14","desc":"Generate knowledge to support\/enhance skill applications with diverse and complex populations"},"26":{"title":"U15","desc":"Critically examine clinical processes of change with diverse and complex populations"},"27":{"title":"U16","desc":"Critically analyze the impact of intersubjectivity, transference, countertransference and the use of defenses when working with complex and diverse populations"},"28":{"title":"U17","desc":"Critically examine how one might go about decolonizing and decentering Western values and beliefs in these clinical processes"},"29":{"title":"U18","desc":"Generate knowledge strategies to enhance anti-oppressive frameworks in clinical practice"},"30":{"title":"U19","desc":"Develop knowledge on a particular population or focus area"},"31":{"title":"U20","desc":"Find contemporary examples of how radical social work has been practiced (or not practiced) with this population\/focus area"},"32":{"title":"U21","desc":"Describe radical social work practices that could be utilized for this population\/focus area"},"33":{"title":"U22","desc":"Using a case example\/study to apply radical social work skills"},"34":{"title":"U23","desc":""},"35":{"title":"U24","desc":""},"36":{"title":"U25","desc":""},"37":{"title":"U26","desc":""},"38":{"title":"U27","desc":""},"39":{"title":"U28","desc":""},"40":{"title":"U29","desc":""},"41":{"title":"U30","desc":""},"42":{"title":"U31","desc":""},"43":{"title":"U32","desc":""},"44":{"title":"U33","desc":""},"45":{"title":"U34","desc":""},"46":{"title":"U35","desc":""},"47":{"title":"U36","desc":""},"48":{"title":"U37","desc":""},"49":{"title":"U38","desc":""},"50":{"title":"U39","desc":""},"51":{"title":"U40","desc":""}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3],"13":[3],"14":[3],"15":[3],"16":[4],"17":[4],"18":[4],"19":[4],"20":[5],"21":[5],"22":[5],"23":[5],"24":[5],"25":[5],"26":[5],"27":[5],"28":[5],"29":[5],"30":[5],"31":[5],"32":[6],"33":[7]}}';
		$json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3,4],"13":[4]}}';
		$outcomesArr = json_decode($json, true);

		//doc node details
		$docTitle = date('YmdHis').'-testDinesh';
		$loggedInUser = 'tester';
		$docIdentifier = $this->createUUID();
		
		//item details
		foreach($outcomesArr['course_goals'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != '')
			$outcomesArr['course_goals'][$k]['uuid'] = $this->createUUID();
		}

		foreach($outcomesArr['unit_level_objectives'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != '')
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
				'creator' => $loggedInUser,
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
		$firstItemHumanCodingScheme = 'item1 hcs';
		$firstItemFullStatement = 'item1 fs';
		$caseJsonArr['CFItems'][] = $this->createItem([
			'id' => $firstItemIdentifier,
			'fullStatement' => $firstItemFullStatement,
			'courseItemTypeTitle' => $courseItemTypeTitle,
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'courseItemTypeId' => $courseItemTypeIdentifier,
			'humanCodingScheme' => $firstItemHumanCodingScheme
			]
		);

		//course statement item
		$courseStatementIdentifier = $this->createUUID();
		$courseStatementFullStatement = 'Course Statement';
		$caseJsonArr['CFItems'][] = $this->createItem([
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
		$statementFullStatement = 'statement';
		$caseJsonArr['CFItems'][] = $this->createItem([
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
		$caseJsonArr['CFItems'][] = $this->createItem([
			'id' => $courseGoalsIdentifier,
			'fullStatement' => $courseGoalsFullStatement,
			'courseItemTypeTitle' => $courseGoalsItemTypeTitle,
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'courseItemTypeId' => $courseGoalsItemTypeIdentifier
			]
		);

		foreach($outcomesArr['course_goals'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFItems'][] = $this->createItem([
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
		$caseJsonArr['CFItems'][] = $this->createItem([
			'id' => $uloIdentifier,
			'fullStatement' => $uloFullStatement,
			'courseItemTypeTitle' => $unitLevelObjectivesItemTypeTitle,
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'courseItemTypeId' => $unitLevelObjectivesItemTypeIdentifier
			]
		);
		
		foreach($outcomesArr['unit_level_objectives'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFItems'][] = $this->createItem([
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
		$caseJsonArr['CFAssociations'][] = $this->createAssociation([
			'associationType' => 'isChildOf',
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'originNodeId' => $firstItemIdentifier,
			'destinationNodeId' => $docIdentifier
			]
		);

		//course statement to first item
		$caseJsonArr['CFAssociations'][] = $this->createAssociation([
			'associationType' => 'isChildOf',
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'originNodeId' => $courseStatementIdentifier,
			'destinationNodeId' => $firstItemIdentifier,
			'sequenceNumber' => '1'
			]
		);

		//statement to course statement association
		$caseJsonArr['CFAssociations'][] = $this->createAssociation([
			'associationType' => 'isChildOf',
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'originNodeId' => $statementIdentifier,
			'destinationNodeId' => $courseStatementIdentifier
			]
		);

		//course goals to first item
		$caseJsonArr['CFAssociations'][] = $this->createAssociation([
			'associationType' => 'isChildOf',
			'docId' => $docIdentifier,
			'docTitle' => $docTitle,
			'originNodeId' => $courseGoalsIdentifier,
			'destinationNodeId' => $firstItemIdentifier,
			'sequenceNumber' => '2'
			]
		);

		//ULOs to first item
		$caseJsonArr['CFAssociations'][] = $this->createAssociation([
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
		foreach($outcomesArr['course_goals'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFAssociations'][] = $this->createAssociation([
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
		foreach($outcomesArr['unit_level_objectives'] as $k => $v){
			if(isset($v['desc']) && $v['desc'] != ''){
				$caseJsonArr['CFAssociations'][] = $this->createAssociation([
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
		foreach($outcomesArr['cg_ulo_mapping'] as $ulo => $courseGoals){
			foreach($courseGoals as $courseGoal){
				$caseJsonArr['CFAssociations'][] = $this->createAssociation([
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
		
		// echo "<pre>";
		// // print_r($caseJsonArr);
		// echo json_encode($caseJsonArr, JSON_PRETTY_PRINT);
		// exit;
		// print_r($outcomesArr);

		// write file to a json file
		$filename = storage_path('app/').(string) Str::uuid().'.json';
		$fp = fopen($filename, 'w');
		fwrite($fp, json_encode($caseJsonArr));
		fclose($fp);



		//get access token to call Align APIs		
		$authToken = $this->getAccessToken('Align');

		//get import identifier
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/importTaxonomy',
				'method' => 'POST',
				'data' => [
					'email' => 'dinesh.amle@learningmate.com',
					'source_identifier' => $docIdentifier,
					'import_type' => '1',
					'is_ready_to_commit_changes' => '0'
				],
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);

		// $curl = curl_init();
		// curl_setopt_array($curl, array(
		// CURLOPT_URL => 'https://api.dev.frost.2u.com/server/api/v1/importTaxonomy',
		// CURLOPT_RETURNTRANSFER => true,
		// CURLOPT_ENCODING => '',
		// CURLOPT_MAXREDIRS => 10,
		// CURLOPT_TIMEOUT => 0,
		// CURLOPT_FOLLOWLOCATION => true,
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// CURLOPT_CUSTOMREQUEST => 'POST',
		// CURLOPT_POSTFIELDS => array('email' => 'dinesh.amle@learningmate.com', 'source_identifier' => $docIdentifier, 'import_type' => '1','is_ready_to_commit_changes' => '0'),
		// CURLOPT_HTTPHEADER => array(
		// 	'Authorization: '.$authToken
		// ),
		// ));

		// $response = curl_exec($curl);
		$response = json_decode($response, true);
		$importIndentifier = $response['data']['import_identifier'];

		
		//call case json api
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/importTaxonomy',
				'method' => 'POST',
				'data' => [
					'email' => 'dinesh.amle@learningmate.com',
					'import_identifier' => $importIndentifier,
					'import_type' => '3',
					'is_ready_to_commit_changes' => '1',
					'case_json'=> new \CURLFILE($filename)
				],
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);

		// $curl = curl_init();
		// curl_setopt_array($curl, array(
		// CURLOPT_URL => 'https://api.dev.frost.2u.com/server/api/v1/importTaxonomy',
		// CURLOPT_RETURNTRANSFER => true,
		// CURLOPT_ENCODING => '',
		// CURLOPT_MAXREDIRS => 10,
		// CURLOPT_TIMEOUT => 0,
		// CURLOPT_FOLLOWLOCATION => true,
		// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// CURLOPT_CUSTOMREQUEST => 'POST',
		// CURLOPT_POSTFIELDS => array('email' => 'dinesh.amle@learningmate.com','import_identifier' => $importIndentifier, 'import_type' => '3','is_ready_to_commit_changes' => '1','case_json'=> new \CURLFILE($filename)),
		// CURLOPT_HTTPHEADER => array(
		// 	'Authorization: '.$authToken
		// ),
		// ));

		// $response = curl_exec($curl);

		// curl_close($curl);
		// unlink($filename);

		exit;
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

		if(isset($params['humanCodingScheme'])){
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

		if(isset($params['sequenceNumber'])){
			$associationArr['sequenceNumber'] = $params['sequenceNumber'];
		}

		return $associationArr;
	}
}
