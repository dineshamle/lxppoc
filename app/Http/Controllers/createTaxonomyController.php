<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use function GuzzleHttp\json_decode;
set_time_limit(0);
class createTaxonomyController extends Controller
{
	private $lastChangeDateTime;
	private $uri;
	const ADMIN_BASE_URL = 'https://dev.frost.2u.com';
	const ALIGN_BASE_URL = 'https://api.dev.frost.2u.com';
	const TWOU_DOMAIN = 'https://frost.2u.com';
	const PROGRAMS_TAXONOMY_NAME = 'List of Programs (LXP)';

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

		if(isset($params['data'])){
			$curlOptions[CURLOPT_POSTFIELDS] = $params['data'];
		}

		if(isset($params['headers'])){
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
				'url' => self::ADMIN_BASE_URL.'/oauth/customapi/v1/clientmanager/getclientbytenant',
				'method' => 'POST',
				'data' => json_encode(['TenantShortCode' => $tenant_code]),
				'headers' => [
					'Content-Type: application/json'
				]
			]
		);
		$response = json_decode($response, true);


		if(strtolower($response['status']) == 'failed'){ //tenant code doesn't exist. Create a new client on Admin using the API 

			//get tenant details from shortcode
			 $response = $this->httpRequest(
				[
					'url' => self::ADMIN_BASE_URL.'/admin/api/tenant/getconfigurationbyshortcode',
					'method' => 'POST',
					'data' => json_encode(['ShortCode' => $tenant_code]),
					'headers' => [
						'Content-Type: application/json',
						'origin: '.self::ADMIN_BASE_URL
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
				'scopes' => ["Organization","LORAPIACCESS"],
				'clientName' => $response['Name'],
				'tenantId' => $response['Id'],
				'tenantName' => $response['Name'] 
			];

			$response = $this->httpRequest(
				[
					'url' => self::ADMIN_BASE_URL.'/oauth/customapi/v1/clientmanager/create',
					'method' => 'POST',
					'data' => json_encode($data),
					'headers' => [
						'Content-Type: application/json'
					]
				]
			);
		}else{
			$clientId = $response['clients'][0]['clientID'];
			$clientSecret = $response['clients'][0]['clientSecret'];
		}

		//get access token
		$response = $this->httpRequest(
			[
				'url' => self::ADMIN_BASE_URL.'/oauth/connect/token',
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
	
	public function createTaxonomyCaseJson(Request $request)
	{
		// try{
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
			$firstItemFullStatement = 'item1 fs'.date('YmdHis');
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
			// print_r($caseJsonArr);
			// echo json_encode($caseJsonArr, JSON_PRETTY_PRINT);
			// exit;
			// print_r($outcomesArr);

			// write file to a json file
			$filename = storage_path('app/').$this->createUUID().'.json';
			$fp = fopen($filename, 'w');
			fwrite($fp, json_encode($caseJsonArr));
			fclose($fp);



			//get access token to call Align APIs		
			$authToken = $this->getAccessToken($request->input('tenant_code')); //pass tenant name and Do not pass tenant_code

			//get import identifier
			echo $response = $this->httpRequest(
				[
					'url' => self::ALIGN_BASE_URL.'/server/api/v1/importTaxonomy',
					'method' => 'POST',
					'data' => [
						'email' => $request->input('email'),
						'source_identifier' => $docIdentifier,
						'import_type' => '1',
						'is_ready_to_commit_changes' => '0'
					],
					'headers' => [
						'Authorization: '.$authToken
					]
				]
			);
			$response = json_decode($response, true);
			$importIndentifier = $response['data']['import_identifier'];

			echo "<br>";
			
			// //call case json api to create Taxonomy
			echo $response = $this->httpRequest(
				[
					'url' => self::ALIGN_BASE_URL.'/server/api/v1/importTaxonomy',
					'method' => 'POST',
					'data' => [
						'email' => $request->input('email'),
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
			

			//delete the json file
			// unlink($filename);

			//Once taxonomy is created, it needs to be linked with "List of Programs (LXP)" taxonomy. For that a node needs to be created under the List Of Programs taxonomy
			$this->updateListOfProgramsTaxonomy($authToken, $firstItemFullStatement, $docIdentifier); //TODO: pass proper program name
			$this->publishTaxonomy($authToken, $docIdentifier);

		// }catch(\Exception $e){
		// 	echo $e->getMessage();
		// }
	}

	private function updateListOfProgramsTaxonomy($authToken, $programName, $sourceDocIdentifier)
	{
		echo "<pre>";
		//check if "List of Programs (LXP)" exists
		$listOfProgramsTaxonomyExists = 0;
		$document_id = null;
		$docIdentifier = null;
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/taxonomy/list',
				'method' => 'GET',
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);
		$response = json_decode($response, true);

		foreach($response['data'] as $data){
			if($data['title'] == self::PROGRAMS_TAXONOMY_NAME){
				$listOfProgramsTaxonomyExists = 1;
				$document_id = $data['document_id'];
			}

			if($data['source_document_id'] == $sourceDocIdentifier){
				$docIdentifier = $data['document_id'];
			}
		}

		// Get node type id of document and default node
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/nodeTypes',
				'method' => 'GET',
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);
		$response = json_decode($response, true);

		$nodeTypeId = null;
		$childNodeTypeId = null;
		foreach($response['data']['nodetype'] as $data){
			if(strtolower($data['title']) == 'document'){
				$nodeTypeId = $data['node_type_id'];
			}

			if(strtolower($data['title']) == 'program'){
				$childNodeTypeId = $data['node_type_id'];
			}
		}

		

		//create "List of Programs (LXP)" taxonomy if it does not exist
		if(!$listOfProgramsTaxonomyExists){
			//create taxonomy
			$response = $this->httpRequest(
				[
					'url' => self::ALIGN_BASE_URL.'/server/api/v1/taxonomy',
					'method' => 'POST',
					'data' => json_encode([
						'node_template_id' => '',
						'document_title' => self::PROGRAMS_TAXONOMY_NAME,
						'document_title_html' => self::PROGRAMS_TAXONOMY_NAME,
						'document_node_type_id' => $nodeTypeId,
						'language_id'=> '',
						'metadataType'=>'',
						'items'=>[],
						'template_tiltle' => '',
						'document_type' => 1
					]),
					'headers' => [
						'Content-Type: application/json',
						'Authorization: '.$authToken
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
				'url' => self::ALIGN_BASE_URL.'/server/api/v2/taxonomy/getTreeHierarchyV5/'.$document_id,
				'method' => 'GET',
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);
		$response = json_decode($response, true);

		//check if the node for newly created taxonomy exists and if so, get the id 
		$nodeId = null;
		if(is_array($response['data']['children'][0]['children'])){
			foreach($response['data']['children'][0]['children'] as $data){
				if(strtolower($data['full_statement']) == $programName){
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

		if(is_null($nodeId)){//if not found, add the node
			if(is_null($childNodeTypeId)){
				//create Program nodetype
				$response = $this->httpRequest(
					[
						'url' => self::ALIGN_BASE_URL.'/server/api/v1/nodeTypes',
						'method' => 'POST',
						'data' => json_encode([
							'name' => 'Program'
						]),
						'headers' => [
							'Content-Type: application/json',
							'Authorization: '.$authToken
						]
					]
				);
				$response = json_decode($response, true);
				echo $childNodeTypeId = $response['data']['node_type_id'];
	
				//get metadata
				$response = $this->httpRequest(
					[
						'url' => self::ALIGN_BASE_URL.'/server/api/v1/metadata',
						'method' => 'GET',
						'headers' => [
							'Authorization: '.$authToken
						]
					]
				);
				$response = json_decode($response, true);
	
				//get list of metadatas required to map to node type
				$metaDataArr = [];
				$order = 1;
				foreach($response['data']['metadata'] as $data){
					if(in_array(strtolower($data['internal_name']), ['full_statement','human_coding_scheme', 'notes'])){
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
						'url' => self::ALIGN_BASE_URL.'/server/api/v1/nodeTypeMetadata/'.$childNodeTypeId,
						'method' => 'POST',
						'data' => json_encode($metaDataArr),
						'headers' => [
							'Content-Type: application/json',
							'Authorization: '.$authToken
						]
					]
				);
				$response = json_decode($response, true);
				print_r($response);
			}

			$response = $this->httpRequest(
				[
					'url' => self::ALIGN_BASE_URL.'/server/api/v1/cfitem/create',
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
						'list_enumeration'=> '', //need to check
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
						'Authorization: '.$authToken
					]
				]
			);
			$response = json_decode($response, true);
			$nodeId = $response['data']['item_id'];
		}

		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v2/taxonomy/getTreeHierarchyV5/'.$docIdentifier,
				'method' => 'GET',
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);
		$response = json_decode($response, true);

		// create the mapping
		$firstItemIdentifier = $response['data']['children'][0]['children'][0]['id'];
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/cfitem/createAssociations',
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
					'Authorization: '.$authToken
				]
			]
		);
	}

	public function publishTaxonomy($authToken, $sourceDocIdentifier)
    {
		echo '<pre>';
		echo $sourceDocIdentifier;
		echo "<br>";
		//get list of taxonomies
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/taxonomy/list',
				'method' => 'GET',
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);
		$response = json_decode($response, true);

		//get document identifier using source document identifier
		$docIdentifier = null;
		foreach($response['data'] as $data){
			if($data['source_document_id'] == $sourceDocIdentifier){
				echo $docIdentifier = $data['document_id'];
			}
		}

		//publish the taxonomy
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/taxonomy/publish',
				'method' => 'POST',
				'data' => json_encode([
					'email' => 'dinesh.amle@learningmate.com', //TODO: add dynamic value
					'adoption_status' => 2,
					'document_id' => $docIdentifier,
					'publish_status' => 1,
					'version_tag' => '',
					'sendNotification'=> false
				]),
				'headers' => [
					'Content-Type: application/json',
					'Authorization: '.$authToken
				]
			]
		);
		$response = json_decode($response, true);
		print_r($response);
		
		
	}
	
	public function checkPublishTaxonomy(Request $request)
	{
		//get status of publish
		$response = $this->httpRequest(
			[
				'url' => self::ALIGN_BASE_URL.'/server/api/v1/'.$request->input('tenant_code').'/ims/case/v1p0/CFDocuments',
				'method' => 'GET',
			]
		);

		$isTaxonomyPublished = 0;
		$response = json_decode($response, true);
		foreach($response['CFDocuments'] as $publishedTaxonomy){
			if($publishedTaxonomy['identifier'] == $request->input('source_document_id')){
				$isTaxonomyPublished = 1;
				break;
			}
		}

		if($isTaxonomyPublished){
			echo 'Taxonomy published';
		}else{
			echo 'Taxonomy Not published';
		}
	}

	public function copyTaxonomyToLOR(Request $request)
	{
		echo "<pre>";
		$authToken = 'Bearer eyJhbGciOiJSUzI1NiIsImtpZCI6IkVFOEMxNDdDNTAwM0ZBRDBFODAxQ0EwQjYxNDFDQTBCN0Q5Qzg3ODMiLCJ0eXAiOiJKV1QiLCJ4NXQiOiI3b3dVZkZBRC10RG9BY29MWVVIS0MzMmNoNE0ifQ.eyJuYmYiOjE2MzM2OTQ5NDIsImV4cCI6MTYzMzY5ODU0MiwiaXNzIjoiaHR0cHM6Ly9kZXYuZnJvc3QuMnUuY29tL29hdXRoIiwiYXVkIjpbImh0dHBzOi8vZGV2LmZyb3N0LjJ1LmNvbS9vYXV0aC9yZXNvdXJjZXMiLCJPcmdhbml6YXRpb24iXSwiY2xpZW50X2lkIjoiMmE2OTY1ZjktYjBmNi00M2Q3LTllMDQtNDYwYzU4NWY2NTkyIiwic3ViIjoiNDYiLCJhdXRoX3RpbWUiOjE2MzM2OTEzMDUsImlkcCI6ImxvY2FsIiwiQWNjb3VudElkIjoidG9sOC1kd0U0U25BbzRrZFNQMFpHZyIsIlVzZXJJZCI6ImRhYTE5YzIxLTU5OGQtNDE4Yy05MGY0LTgyMmZiZTczYmY3OCIsIkZpcnN0TmFtZSI6ImRpbmVzaCIsIkxhc3ROYW1lIjoiYW1sZSIsIkVtYWlsIjoiZGluZXNoLmFtbGVAbGVhcm5pbmdtYXRlLmNvbSIsIlVzZXJOYW1lIjoiZGluZXNoLmFtbGUiLCJBY3RpdmUiOiIxIiwiT3JnYW5pemF0aW9uSUQiOiJlYWUwYTMyMi0zOWVlLTQ5ODEtYWI5ZS1mMDU4MTdlZWVjOTkiLCJQbGF0Zm9ybVR5cGVzIjoiNCM2IzcjMiMzIiwiVGVuYW50SWQiOiIyYTk1OTgyZC1kMDMzLTRhMWEtYmJmZC0xZGFhOTk0OWVjNTEiLCJUZW5hbnRQbGF0Zm9ybXMiOiI0LDYsNywyLDMiLCJUZW5hbnRTaG9ydENvZGUiOiJBbGlnMiIsIkxhbmRpbmdQbGF0Zm9ybSI6IjIiLCJUZW5hbnRMb2dvIjoiIiwiUmVmcmVzaFRpbWUiOiIxODAwIiwiUm9sZUlkIjoiZDQ5ZDE0YTYtNTMwZi00ZWNiLWExZjMtMmM1ZDJhZTI5MThlIiwiUm9sZUlkZW50IjoiMSIsIkxUSU91dCI6IkZhbHNlIiwiSXNUZW5hbnRBZG1pbiI6IlRydWUiLCJUZW5hbnRVUkwiOiJodHRwczovL2Rldi5mcm9zdC4ydS5jb20vYXV0aC8jL2xvZ2luL0FsaWcyIiwic2NvcGUiOlsib3BlbmlkIiwicHJvZmlsZSIsIk9yZ2FuaXphdGlvbiIsIm9mZmxpbmVfYWNjZXNzIl0sImFtciI6WyJjdXN0b20iXX0.ngeOTldj61YoVmGNrdzvWEnnKodXrUNbhzYLbMs1eiZLGJFe_K0IkurJR4v_b-rSDM1YXaPjOGseViTQuZ4R5tOWXsoHSAC4idUJn5TThPhcqQBOUmQ7yQ8CNLLzbf3R7rCYYaCCLMoyjVII8H0Vf6t62SpqXClPCp-wJem7ASg14D1Ul8-Li6qm2BK-c5fwz605rQl6GUn2R6k88sVvHaDyfW7jFxCiWW0Ie13zPHxY80u3-XLcFGwpfgMmKgJxinNmopO90RMWLqdpCgp-2eHd_Kv8ouMveKVIdC5Zrd_UME3j4cNuTOC4XKTCkFiEtZ57yz3mMVvWr4EAmfIogg';
		// $authToken = $this->getAccessToken($request->input('tenant_code'));
		// //get case server
		// $response = $this->httpRequest(
		// 	[
		// 		'url' => self::ADMIN_BASE_URL.'/lor/api/laravel/public/api/metadata/caseserver',
		// 		// 'url' => 'http://localhost/coo.php',
		// 		'method' => 'GET',
		// 		'headers' => [
		// 			'Authorization: '.$authToken,
		// 			// 'cookie: SSOUser='.json_encode(['iss'=>,"TenantPlatforms" => "4,6,7,2,3"]).';'
		// 		]
		// 	]
		// );
		// $response = json_decode($response, true);
		// print_r($response);

		//Align case server
		// https://api.dev.frost.2u.com/server/api/v1/Alig2/ims/case/v1p0
		echo $response = $this->httpRequest(
			[
				'url' => self::ADMIN_BASE_URL.'/lor/api/laravel/public/api/metadata/caseserverTaxonomy?&caseserver=api.dev.frost.2u.com',
				'method' => 'GET',
				'headers' => [
					'Authorization: '.$authToken
				]
			]
		);
		// $response = json_decode($response, true);
		// print_r($response);
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
