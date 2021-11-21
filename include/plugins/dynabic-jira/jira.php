<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class DynabicJiraPlugin extends Plugin {
    var $config_class = "DynabicJiraPluginConfig";
	 /**
     * The Jira WSDL endpoint.
     */
    var $wsdl = '/rpc/soap/jirasoapservice-v2?wsdl';

    function bootstrap() {
        $config = $this->getConfig();

        $current_url = $_SERVER["HTTP_HOST"];
        $whitelist = explode('.', $current_url);
		if(!in_array('groupdocs', $whitelist)){//For Aspose tenant
	        # ----- Dynabic.Jira credentials ---------------------
	        $dynabicjira = json_decode($config->get('dynabicjira-enabled'));
			define('Auckland_HOST', $config->get('dynabic-aucklandJira-host').$this->wsdl);
			define('Auckland_USERNAME', $config->get('dynabic-aucklandJira-username'));
			define('Auckland_PASSWORD', $config->get('dynabic-aucklandJira-password'));
			define('Auckland_PROJECTS', $config->get('dynabic-aucklandJira-projects'));

			define('Nanjing_HOST', $config->get('dynabic-nanjingJira-host').$this->wsdl);
			define('Nanjing_USERNAME', $config->get('dynabic-nanjingJira-username'));
			define('Nanjing_PASSWORD', $config->get('dynabic-nanjingJira-password'));
			define('Nanjing_PROJECTS', $config->get('dynabic-nanjingJira-projects'));

			define('Saltov_HOST', $config->get('dynabic-saltovJira-host').$this->wsdl);
			define('Saltov_USERNAME', $config->get('dynabic-saltovJira-username'));
			define('Saltov_PASSWORD', $config->get('dynabic-saltovJira-password'));
			define('Saltov_PROJECTS', $config->get('dynabic-saltovJira-projects'));

			define('Lutsk_HOST', $config->get('dynabic-lutskJira-host').$this->wsdl);
			define('Lutsk_USERNAME', $config->get('dynabic-lutskJira-username'));
			define('Lutsk_PASSWORD', $config->get('dynabic-lutskJira-password'));
			define('Lutsk_PROJECTS', $config->get('dynabic-lutskJira-projects'));

			define('SPB_HOST', $config->get('dynabic-SPBJira-host').$this->wsdl);
			define('SPB_USERNAME', $config->get('dynabic-SPBJira-username'));
			define('SPB_PASSWORD', $config->get('dynabic-SPBJira-password'));
			define('SPB_PROJECTS', $config->get('dynabic-SPBJira-projects'));

			define('Rzeszow_HOST', $config->get('dynabic-RzeszowJira-host').$this->wsdl);
			define('Rzeszow_USERNAME', $config->get('dynabic-RzeszowJira-username'));
			define('Rzeszow_PASSWORD', $config->get('dynabic-RzeszowJira-password'));
			define('Rzeszow_PROJECTS', $config->get('dynabic-RzeszowJira-projects'));

			define('Kiev_HOST', $config->get('dynabic-KievJira-host').$this->wsdl);
			define('Kiev_USERNAME', $config->get('dynabic-KievJira-username'));
			define('Kiev_PASSWORD', $config->get('dynabic-KievJira-password'));
			define('Kiev_PROJECTS', $config->get('dynabic-KievJira-projects'));

			define('Kharkov_HOST', $config->get('dynabic-KharkovJira-host').$this->wsdl);
			define('Kharkov_USERNAME', $config->get('dynabic-KharkovJira-username'));
			define('Kharkov_PASSWORD', $config->get('dynabic-KharkovJira-password'));
			define('Kharkov_PROJECTS', $config->get('dynabic-KharkovJira-projects'));

			define('Bratislava_HOST', $config->get('dynabic-BratislavaJira-host').$this->wsdl);
			define('Bratislava_USERNAME', $config->get('dynabic-BratislavaJira-username'));
			define('Bratislava_PASSWORD', $config->get('dynabic-BratislavaJira-password'));
			define('Bratislava_PROJECTS', $config->get('dynabic-BratislavaJira-projects'));

			define('Ugresha_HOST', $config->get('dynabic-UgreshaJira-host').$this->wsdl);
			define('Ugresha_USERNAME', $config->get('dynabic-UgreshaJira-username'));
			define('Ugresha_PASSWORD', $config->get('dynabic-UgreshaJira-password'));
			define('Ugresha_PROJECTS', $config->get('dynabic-UgreshaJira-projects'));

			define('Sikorsky_HOST', $config->get('dynabic-SikorskyJira-host').$this->wsdl);
			define('Sikorsky_USERNAME', $config->get('dynabic-SikorskyJira-username'));
			define('Sikorsky_PASSWORD', $config->get('dynabic-SikorskyJira-password'));
			define('Sikorsky_PROJECTS', $config->get('dynabic-SikorskyJira-projects'));

			define('Suceava_HOST', $config->get('dynabic-SuceavaJira-host').$this->wsdl);
			define('Suceava_USERNAME', $config->get('dynabic-SuceavaJira-username'));
			define('Suceava_PASSWORD', $config->get('dynabic-SuceavaJira-password'));
			define('Suceava_PROJECTS', $config->get('dynabic-SuceavaJira-projects'));

			define('Nino_HOST', $config->get('dynabic-NinoJira-host').$this->wsdl);
			define('Nino_USERNAME', $config->get('dynabic-NinoJira-username'));
			define('Nino_PASSWORD', $config->get('dynabic-NinoJira-password'));
			define('Nino_PROJECTS', $config->get('dynabic-NinoJira-projects'));
		}else{//For Groupdocs tenant
			# ----- Dynabic.Jira credentials ---------------------
	        $dynabicjira = json_decode($config->get('dynabicjira-enabled'));
			define('Auckland_gd_HOST', $config->get('dynabic-aucklandJira-host_gd').$this->wsdl);
			define('Auckland_gd_USERNAME', $config->get('dynabic-aucklandJira-username_gd'));
			define('Auckland_gd_PASSWORD', $config->get('dynabic-aucklandJira-password_gd'));
			define('Auckland_gd_PROJECTS', $config->get('dynabic-aucklandJira-projects_gd'));

			define('Moscow_gd_HOST', $config->get('dynabic-moscowJira-host_gd').$this->wsdl);
			define('Moscow_gd_USERNAME', $config->get('dynabic-moscowJira-username_gd'));
			define('Moscow_gd_PASSWORD', $config->get('dynabic-moscowJira-password_gd'));
			define('Moscow_gd_PROJECTS', $config->get('dynabic-moscowJira-projects_gd'));

			define('Lisbon_gd_HOST', $config->get('dynabic-lisbonJira-host_gd').$this->wsdl);
			define('Lisbon_gd_USERNAME', $config->get('dynabic-lisbonJira-username_gd'));
			define('Lisbon_gd_PASSWORD', $config->get('dynabic-lisbonJira-password_gd'));
			define('Lisbon_gd_PROJECTS', $config->get('dynabic-lisbonJira-projects_gd'));
		}

    }
	
	public static function getJiraInstance($issueKey) {
    	$project = strtok($issueKey, "-");
    	$current_url = $_SERVER["HTTP_HOST"];
        $whitelist = explode('.', $current_url);
		if(!in_array('groupdocs', $whitelist)){//For Aspose tenant
	    	$Auckland_PROJECTS = explode(",", Auckland_PROJECTS);
	    	if(in_array($project, $Auckland_PROJECTS))
	    		return "Auckland";
	    	$Nanjing_PROJECTS = explode(",", Nanjing_PROJECTS);
	    	if(in_array($project, $Nanjing_PROJECTS))
	    		return "Nanjing";
	    	$Saltov_PROJECTS = explode(",", Saltov_PROJECTS);
	    	if(in_array($project, $Saltov_PROJECTS))
	    		return "Saltov";
	    	$Lutsk_PROJECTS = explode(",", Lutsk_PROJECTS);
	    	if(in_array($project, $Lutsk_PROJECTS))
	    		return "Lutsk";
	    	$SPB_PROJECTS = explode(",", SPB_PROJECTS);
	    	if(in_array($project, $SPB_PROJECTS))
	    		return "SPB";
	    	$Rzeszow_PROJECTS = explode(",", Rzeszow_PROJECTS);
	    	if(in_array($project, $Rzeszow_PROJECTS))
	    		return "Rzeszow";
	    	$Kiev_PROJECTS = explode(",", Kiev_PROJECTS);
	    	if(in_array($project, $Kiev_PROJECTS))
	    		return "Kiev";
	    	$Kharkov_PROJECTS = explode(",", Kharkov_PROJECTS);
	    	if(in_array($project, $Kharkov_PROJECTS))
	    		return "Kharkov";
	    	$Bratislava_PROJECTS = explode(",", Bratislava_PROJECTS);
	    	if(in_array($project, $Bratislava_PROJECTS))
	    		return "Bratislava";
	    	$Ugresha_PROJECTS = explode(",", Ugresha_PROJECTS);
	    	if(in_array($project, $Ugresha_PROJECTS))
	    		return "Ugresha";
	    	$Sikorsky_PROJECTS = explode(",", Sikorsky_PROJECTS);
	    	if(in_array($project, $Sikorsky_PROJECTS))
	    		return "Sikorsky";
	    	$Suceava_PROJECTS = explode(",", Suceava_PROJECTS);
	    	if(in_array($project, $Suceava_PROJECTS))
	    		return "Suceava";
	    	$Nino_PROJECTS = explode(",", Nino_PROJECTS);
	    	if(in_array($project, $Nino_PROJECTS))
	    		return "Nino";
	    	else
	    		return 0;
	    }else{//For Groupdocs tenant
	    	$Auckland_gd_PROJECTS = explode(",", Auckland_gd_PROJECTS);
	    	if(in_array($project, $Auckland_gd_PROJECTS))
	    		return "Auckland_gd";
	    	$Moscow_gd_PROJECTS = explode(",", Moscow_gd_PROJECTS);
	    	if(in_array($project, $Moscow_gd_PROJECTS))
	    		return "Moscow_gd";
	    	$Lisbon_gd_PROJECTS = explode(",", Lisbon_gd_PROJECTS);
	    	if(in_array($project, $Lisbon_gd_PROJECTS))
	    		return "Lisbon_gd";
	    	else
	    		return 0;
	    }
    }

	public static function addIssue($ticketId, $issueKey, $pageURL) {
		$pageURL = $_SERVER["HTTP_HOST"];
		$pageURL = "http://".$pageURL."/scp/tickets.php?id=".$ticketId;
		$instance = self::getJiraInstance($issueKey);
		if(!$instance)
			return $issueKey;
		$soapClient = new SoapClient(constant($instance."_HOST"));
		$token = $soapClient->login(constant($instance."_USERNAME"), constant($instance."_PASSWORD"));
		
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE issue_id='".$issueKey."' AND ticket_id=".$ticketId;
		$res = db_query($sql);
		$row = db_fetch_row($res);
		if(empty($row[0])) {
			// get issue
			$result = $soapClient->getIssue($token, $issueKey);
			$issueId = $result->id;
			
			if(isset($issueId)) {
				$sql = "INSERT INTO ".TICKET_JIRA_TABLE." (ticket_id, issue_id) VALUES (". $ticketId .", '". $issueKey ."')";
				db_query($sql);
			
				$comment = "The issue has been mentioned in the following ticket: ". $pageURL;
				$soapClient->addComment($token, $issueKey, array('body' => $comment));
			} else {
				return $issueKey;
			}
		}	
	}
	
	public static function getIssue($issueKey) {
		try{
			$instance = self::getJiraInstance($issueKey);
			$soapClient = new SoapClient(constant($instance."_HOST"));
			$token = $soapClient->login(constant($instance."_USERNAME"), constant($instance."_PASSWORD"));
		}
		catch(exception $e){
			echo "Jira connection error!";
			return 0;
		}

		try{
			$result = $soapClient->getIssue($token, $issueKey);
		}
		catch(exception $e){
			echo "Jira issue could not load properly.";
			return 0;
		}
		$resolutionId = $result->resolution;
		if(empty($resolutionId) && $resolutionId == '') {
			$resolutionName = 'Unresolved';
		} else {
			$resolutionName = self::getResolution($resolutionId,$issueKey);
		}
		return $resolutionName;
	}
	
	public static function getResolution($resolutionId,$issueKey) {
		$instance = self::getJiraInstance($issueKey);
		$soapClient = new SoapClient(constant($instance."_HOST"));
		$token = $soapClient->login(constant($instance."_USERNAME"), constant($instance."_PASSWORD"));
		$result = $soapClient->getResolutions($token);
		foreach($result as $row) {
			if($row->id == $resolutionId) {
				$resolutionName = $row->name;
				break;
			}
		}
		return $resolutionName;
	}
		
	public static function getIssueList($ticketId) {
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$resolutionName = '(' . self::getIssue($row[0]) . ')';
				$issueList .= '<a id="ticket-eta-'.$row[0].'" class="confirm-action" href="#jira">'."$row[0]</a>". $resolutionName . " ";
			}
        }
		return $issueList;
	}

	public static function getIssueListClient($ticketId) {
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$resolutionName = '(' . self::getIssue($row[0]) . ')';
				$issueList .= '<a href="#" id="myBtn-'.$row[0].'" >'."$row[0]</a>". $resolutionName . " ";
			}
        }
		return $issueList;
	}

	public static function issueType($issue_id){
		if (strpos($issue_id, '.'))
			return false;
		else
			return true;
	}
	
	public static function getIssuesForForm($ticketId) {
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		 
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0]))
				$issueList .= $row[0] . ';';
        }
		return rtrim($issueList, ';');
	}
	
	public static function deleteIssue($ticketId) {
		$sql = "DELETE FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId." AND issue_id NOT LIKE '%.%'";
		db_query($sql);
	}
	// Copied from Issue ETA plugin
	public static function getJiraDetails($ticketId){		
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$issueKey = $row[0];
				try{
					$instance = self::getJiraInstance($issueKey);
					$soapClient = new SoapClient(constant($instance."_HOST"));
					$token = $soapClient->login(constant($instance."_USERNAME"), constant($instance."_PASSWORD"));
					$statuses = $soapClient->getStatuses($token);
					$resolutions = $soapClient->getResolutions($token);
					$types = $soapClient->getIssueTypes($token);
					$priorities = $soapClient->getPriorities($token);
					$result = $soapClient->getIssue($token, $row[0]);
				}
				catch(exception $e){
					return 0;
				}
				$issue_affectsVersions = "";
				$issue_fixVersions = "";
				$issue_components = "";
				$status = "";
				$resolution = "";
				$priority = "";
				$eta_msg = "";
				foreach ($result->affectsVersions as $affectsVersion) {
					$issue_affectsVersions.=$affectsVersion->name.", ";
				}
				foreach ($result->fixVersions as $fixVersion) {
					$issue_fixVersions.=$fixVersion->name.", ";
				}
				foreach ($result->components as $component) {
					$issue_components.=$component->name.", ";
				}			
				foreach ($statuses as $issue_status) {
					if($issue_status->id == $result->status)
						$status = $issue_status->name;
				}
				foreach ($resolutions as $issue_resolution) {
					if($issue_resolution->id == $result->resolution)
						$resolution = $issue_resolution->name;
				}
				if($resolution==NULL)
					$resolution = "Unresolved";
				foreach ($types as $issue_type) {
					if($issue_type->id == $result->type)
						$type = $issue_type->name;
				}
				foreach ($priorities as $issue_priority) {
					if($issue_priority->id == $result->priority)
						$priority = $issue_priority->name;
				}
				if($instance == "Auckland" OR $instance == "Auckland_gd")
					$eta_msg = self::aucklandJira($status,$result->fixVersions,$resolution);
				elseif($instance == "Lisbon_gd")
					$eta_msg = self::lisbonJira($status,$result->fixVersions,$resolution);
				elseif($instance == "Nanjing")
					$eta_msg = self::nanjingJira($status,$result->fixVersions,$resolution);
				else
					$eta_msg = self::odessaJira($status,$result->fixVersions,$resolution);
				
			    $issueList .= '
					<div style="display:none;width:680px;" class="dialog" id="ticket-eta-pop-'.$row[0].'">
					    <h3>'.$row[0].'</h3>
					    <a class="close" href=""><i class="icon-remove-circle"></i></a>
					    <hr/>

						<table cellspacing="0" cellpadding="0" width="660px" border="0" style="background:#F4FAFF;border">
						    <tbody>
						    	<tr>
						        	<td width="50%" style="padding:20px;border-right: 1px solid #ddd;">
						            	<p><b>Type:</b> '.$type.'</p>
									    <p><b>Priority:</b> '.$priority.'</p>
									    <p><b>Affect Versions:</b> '.$issue_affectsVersions.'</p>
									    <p><b>Components:</b> '.$issue_components.'</p>
						        	</td>
						        	<td width="50%" style="padding:20px;">
						            	<p><b>Status:</b> '.$status.'</p>
									    <p><b>Resolution:</b> '.$resolution.'</p>
									    <p><b>Fix Versions:</b> '.$issue_fixVersions.'</p>
						        	</td>
						    	</tr>
							</tbody>
						</table>				    			    
					    
					    <p><b>Summary:</b> '.$result->summary.'</p>
					    <p><b>ETA:</b> '.$eta_msg.'</p>
					</div>
					<script type = "text/javascript">
					$("#ticket-eta-'.$row[0].'").on("click",function(e){
				        $("#ticket-eta-pop-'.$row[0].'").show();
				        $("#overlay").show();
				        $("#ticket-eta-pop-'.$row[0].'").css({"width":"680px", "top":"94.5714px", "left":"334.5px"});
				    });
					</script>
				';
			}
        }
		return $issueList;		
	}

	public static function getJiraDetailsClient($ticketId){		
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$issueKey = $row[0];
				try{
					$instance = self::getJiraInstance($issueKey);
					$soapClient = new SoapClient(constant($instance."_HOST"));
					$token = $soapClient->login(constant($instance."_USERNAME"), constant($instance."_PASSWORD"));
					$statuses = $soapClient->getStatuses($token);
					$resolutions = $soapClient->getResolutions($token);
					$types = $soapClient->getIssueTypes($token);
					$priorities = $soapClient->getPriorities($token);
					$result = $soapClient->getIssue($token, $row[0]);
				}
				catch(exception $e){
					return 0;
				}
				$issue_affectsVersions = "";
				$issue_fixVersions = "";
				$issue_components = "";
				$status = "";
				$resolution = "";
				$priority = "";
				$eta_msg = "";
				foreach ($result->affectsVersions as $affectsVersion) {
					$issue_affectsVersions.=$affectsVersion->name.", ";
				}
				foreach ($result->fixVersions as $fixVersion) {
					$issue_fixVersions.=$fixVersion->name.", ";
				}
				foreach ($result->components as $component) {
					$issue_components.=$component->name.", ";
				}			
				foreach ($statuses as $issue_status) {
					if($issue_status->id == $result->status)
						$status = $issue_status->name;
				}
				foreach ($resolutions as $issue_resolution) {
					if($issue_resolution->id == $result->resolution)
						$resolution = $issue_resolution->name;
				}
				if($resolution==NULL)
					$resolution = "Unresolved";
				foreach ($types as $issue_type) {
					if($issue_type->id == $result->type)
						$type = $issue_type->name;
				}
				foreach ($priorities as $issue_priority) {
					if($issue_priority->id == $result->priority)
						$priority = $issue_priority->name;
				}
				if($instance == "Auckland" OR $instance == "Auckland_gd")
					$eta_msg = self::aucklandJira($status,$result->fixVersions,$resolution);
				elseif($instance == "Lisbon_gd")
					$eta_msg = self::lisbonJira($status,$result->fixVersions,$resolution);
				elseif($instance == "Nanjing")
					$eta_msg = self::nanjingJira($status,$result->fixVersions,$resolution);
				else
					$eta_msg = self::odessaJira($status,$result->fixVersions,$resolution);
				
			    $issueList .= '
					<!-- The Modal -->
					<div id="myModal-'.$row[0].'" class="modal">

					  <!-- Modal content -->
					  <div class="modal-content" style="width:50%;	">
					    <span class="close" id="close-'.$row[0].'">&times;</span>
					    <h3>'.$row[0].'</h3>
					    <hr/>

						<div style="background:#F4FAFF;">
				        	<div style="width:40%;padding:20px;display:inline-block;vertical-align:top;">
				            	<p><b>Type:</b> '.$type.'</p>
							    <p><b>Priority:</b> '.$priority.'</p>
							    <p><b>Affect Versions:</b> '.$issue_affectsVersions.'</p>
							    <p><b>Components:</b> '.$issue_components.'</p>
				        	</div>
				        	<div style="width:40%;padding:20px;display:inline-block;vertical-align:top;">
				            	<p><b>Status:</b> '.$status.'</p>
							    <p><b>Resolution:</b> '.$resolution.'</p>
							    <p><b>Fix Versions:</b> '.$issue_fixVersions.'</p>
				        	</div>					    
						</div>				    			    
					    
					    <p><b>Summary:</b> '.$result->summary.'</p>
					    <p><b>ETA:</b> '.$eta_msg.'</p>
					  </div>

					</div>

					<script>
					document.getElementById("myBtn-'.$row[0].'").onclick = function() {
					    document.getElementById("myModal-'.$row[0].'").style.display = "block";
					}
					document.getElementById("close-'.$row[0].'").onclick = function() {
					    document.getElementById("myModal-'.$row[0].'").style.display = "none";
					}
					window.onclick = function(event) {
					    if (event.target == document.getElementById("myModal-'.$row[0].'")) {
					        document.getElementById("myModal-'.$row[0].'").style.display = "none";
					    }
					}
					</script>
				';
			}
        }
		return $issueList;		
	}

	public static function aucklandJira($status,$result_fixVersions,$resolution){
		if($status=="Postponed"){
			$eta_msg = "This issue has been postponed which means we have not planned a fix for this issue in the near future. For more information about this issue, please consult with the Aspose team in the support forum.";
		}
		else{
			if($result_fixVersions==NULL){
				if($status=="New"){
					$eta_msg = "This issue is pending for analysis and more information will be available after analysis is complete.";
				}
				else{
					$eta_msg = "This issue is in ".$status." phase, however an ETA is not available at the moment.";
				}
			}
			else{
				if($status=="Closed"){
					if($resolution=="Won't Fix" OR $resolution=="Not a Bug"){
						$eta_msg = "This issue is found not to be a bug or a feature that we will implement. Consult the support team for further details.";
					}
					elseif($result_fixVersions){
						$check = 0;
						foreach ($result_fixVersions as $fixVersion) {
							if($fixVersion->released!=NULL){
								$eta_msg = "The fix is already publicly available in the ".$fixVersion->name." release and in any later release.";
								$check = 1;
							}
						}
						if($check==0){
							$eta_msg = "The fix for this issue will be available in the ".$result_fixVersions[0]->name." release.";
						}
					}
				}
				else{
					$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix for this issue will be available in ".$result_fixVersions[0]->name." release.";
				}
			}
		}
		return $eta_msg;
	}

	public static function nanjingJira($status,$result_fixVersions,$resolution){
		if($result->fixVersions==NULL){
			if($status=="Open" OR $status=="Reopened"){
				$eta_msg = "The development activities for this issue are yet to start. The issue might be pending for analysis or under analysis.";
			}
			else{
				$eta_msg = "This issue is in ".$status." phase, but ETA is not available at the moment.";
			}
		}
		else{
			if($status=="Closed"){
				$eta_msg = "The fix to this issue was included in ".$result_fixVersions[0]->name." release.";
			}
			else{
				if($status=="Feedback" OR $status=="Resolved"){
					$eta_msg = "The issue has been resolved by our developer and is pending for QA. If passed by QA, the fix will be included in ".$result_fixVersions[0]->name." release.";
				}
				else{
					$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix will be included in ".$result_fixVersions[0]->name." release.";
				}
			}
		}
		return $eta_msg;
	}

	public static function odessaJira($status,$result_fixVersions,$resolution){
		if($status == "Postponed"){
			$eta_msg = "This issue has been postponed which means we have not planned a fix for this issue in the near future. For more information about this issue, please consult with the Aspose team in the support forum.";
		}
		else{
			if($result_fixVersions==NULL){
				if($status=="Open" OR $status=="Reopened"){
					$eta_msg = "The development activities for this issue are yet to start. The issue might be pending for analysis or under analysis.";
				}
				else{
					$eta_msg = "This issue is in ".$status." phase, but ETA is not available at the moment.";
				}
			}
			else{
				if($status=="Closed"){
					$eta_msg = "The fix to this issue was included in ".$result_fixVersions[0]->name." release.";
				}
				else{
					if($status=="Feedback" OR $status=="Resolved"){
						$eta_msg = "The issue has been resolved by our developer and is pending for QA. If passed by QA, the fix will be included in ".$result_fixVersions[0]->name." release.";
					}
					else{
						$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix will be included in ".$result_fixVersions[0]->name." release.";
					}
				}
			}
		}
		return $eta_msg;	
	}	

	public static function lisbonJira($status,$result_fixVersions,$resolution){
		if($resolution=="Won't Fix" OR $resolution=="Not a Bug"){
			$eta_msg = "Reported issue is not a bug. Consult support team for more details.";
		}
		elseif($result_fixVersions==NULL){
			if($status=="Open" OR $status=="Reopened"){
				$eta_msg = "The development activities for this issue are yet to start. The issue might be pending for analysis or under analysis.";
			}
			else{
				$eta_msg = "This issue is in ".$status." phase, but ETA is not available at the moment.";
			}
		}
		elseif($status=="Closed" OR $status=="Resolved"){
			if($result_fixVersions){
				$check = 0;
				foreach ($result_fixVersions as $fixVersion) {
					if($fixVersion->released!=NULL){
						$eta_msg = "The fix is already publicly available in the ".$fixVersion->name." release and in any later release.";
						$check = 1;
					}
				}
				if($check==0){
					$eta_msg = "The fix for this issue will be available in the ".$result_fixVersions[0]->name." release.";
				}
			}
		}
		else{
			$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix for this issue will be available in ".$result_fixVersions[0]->name." release.";
		}
		return $eta_msg;
	}	
	// Copied from Issue ETA plugin	
}
