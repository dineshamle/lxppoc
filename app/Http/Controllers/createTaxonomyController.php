<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class createTaxonomyController extends Controller
{
	public function createTaxonomyCaseJson()
	{
		// $arr = ['x'=>[], 'b' => '' ];
			$json = '{"assessments":[],"course_goals":{"3":{"title":"C1","desc":"Demonstrate the mapping and scoping of complex and diverse populations"},"4":{"title":"C2","desc":"Interrogate the assumptions of theory, research, practice and policy with complex and diverse populations"},"5":{"title":"C3","desc":"Demonstrate a commitment to radical social work practices to dismantle oppressive systems and create opportunities for equity for complex and diverse populations"},"6":{"title":"C4","desc":"Use scholarly knowledge and scholarly practices to critically elevate and evaluate the epistemologies that inform evidence based practices (i.e what is missing)"},"7":{"title":"C5","desc":"Formulate culturally-informed and responsive working alliances with clients from diverse and complex populations"}},"unit_level_objectives":{"12":{"title":"U1","desc":"Identify the scope of complex and diverse populations"},"13":{"title":"U2","desc":"Identify the importance of practice with complex and diverse populations"},"14":{"title":"U3","desc":"Examine self-location, social identities and intersubjectivity with complex and diverse populations; addressing issues of intersectionality; how do identities of therapists affect their work"},"15":{"title":"U4","desc":"Identify ethical imperatives in practice with complex and diverse populations"},"16":{"title":"U5","desc":"Identify the common assumptions attributed to various complex and diverse populations"},"17":{"title":"U6","desc":"Critically examine the source and history of assumptions made about complex and diverse populations"},"18":{"title":"U7","desc":"Provide alternate explanations that challenge the assumptions made about complex and diverse populations"},"19":{"title":"U8","desc":"Examine how assumptions impact application of skills to complex and diverse populations"},"20":{"title":"U9","desc":"Demonstrate the ability to communicate revolutionary thinking in verbal and written communications"},"21":{"title":"U10","desc":"Create a personal plan for continued growth in utilizing radical social work practices"},"22":{"title":"U11","desc":"Identify opportunities for equity for complex and diverse populations"},"23":{"title":"U12","desc":"Engaging a narrative inquiry lens as an anti-oppressive strategy of radical social work practices"},"24":{"title":"U13","desc":"Use specific cases to demonstrate knowledge of skill sets related to a particular population"},"25":{"title":"U14","desc":"Generate knowledge to support\/enhance skill applications with diverse and complex populations"},"26":{"title":"U15","desc":"Critically examine clinical processes of change with diverse and complex populations"},"27":{"title":"U16","desc":"Critically analyze the impact of intersubjectivity, transference, countertransference and the use of defenses when working with complex and diverse populations"},"28":{"title":"U17","desc":"Critically examine how one might go about decolonizing and decentering Western values and beliefs in these clinical processes"},"29":{"title":"U18","desc":"Generate knowledge strategies to enhance anti-oppressive frameworks in clinical practice"},"30":{"title":"U19","desc":"Develop knowledge on a particular population or focus area"},"31":{"title":"U20","desc":"Find contemporary examples of how radical social work has been practiced (or not practiced) with this population\/focus area"},"32":{"title":"U21","desc":"Describe radical social work practices that could be utilized for this population\/focus area"},"33":{"title":"U22","desc":"Using a case example\/study to apply radical social work skills"},"34":{"title":"U23","desc":""},"35":{"title":"U24","desc":""},"36":{"title":"U25","desc":""},"37":{"title":"U26","desc":""},"38":{"title":"U27","desc":""},"39":{"title":"U28","desc":""},"40":{"title":"U29","desc":""},"41":{"title":"U30","desc":""},"42":{"title":"U31","desc":""},"43":{"title":"U32","desc":""},"44":{"title":"U33","desc":""},"45":{"title":"U34","desc":""},"46":{"title":"U35","desc":""},"47":{"title":"U36","desc":""},"48":{"title":"U37","desc":""},"49":{"title":"U38","desc":""},"50":{"title":"U39","desc":""},"51":{"title":"U40","desc":""}},"cg_am_mapping":[],"ulo_am_mapping":[],"cg_ulo_mapping":{"12":[3],"13":[3],"14":[3],"15":[3],"16":[4],"17":[4],"18":[4],"19":[4],"20":[5],"21":[5],"22":[5],"23":[5],"24":[5],"25":[5],"26":[5],"27":[5],"28":[5],"29":[5],"30":[5],"31":[5],"32":[6],"33":[7]}}';
			$outcomesArr = json_decode($json, true);

			$caseJsonArr = [];
			$caseJsonArr['CFDocument'] = ['identifier' => 'identifier', 'title' => 'title'];
			$caseJsonArr['CFItems'] = [['identifier' => 'identifier', 'title' => 'title'], ['identifier' => 'identifier', 'title' => 'title']];
			$caseJsonArr['CFAssociations'] = [['identifier' => 'identifier', 'title' => 'title'], ['identifier' => 'identifier', 'title' => 'title']];
			$caseJsonArr['CFDefinitions'] = ['CFItemTypes' => [['identifier' => 'identifier', 'title' => 'title'], ['identifier' => 'identifier', 'title' => 'title']]];

			echo "<pre>";
			echo json_encode($caseJsonArr, JSON_PRETTY_PRINT);

			
			print_r($outcomesArr);
	
	}
}
