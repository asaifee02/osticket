<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');
require_once('php-redmine-api-master/lib/autoload.php');

class DynabicRedminePlugin extends Plugin {
    var $config_class = "DynabicRedminePluginConfig";
	 /**
     * The Redmine WSDL endpoint.
     */

    function bootstrap() {
        $config = $this->getConfig();
        # ----- Dynabic.Redmine credentials ---------------------
        $dynabicRedmine = json_decode($config->get('dynabicRedmine-enabled'));
		define('Redmine_USERNAME', $config->get('dynabic-Redmine-username'));
		define('Redmine_PASSWORD', $config->get('dynabic-Redmine-password'));
    }

	public static function getRedmineInstance($issueKey) {
		$issueKey = self::remove_http($issueKey);
		$issueKey_values = explode('/issues/', $issueKey);
		return $issueKey_values[0];
    }

	public static function addIssue($ticketId, $issueKey, $pageURL) {
		$issueKey = self::remove_http($issueKey);
		$pageURL = $_SERVER["HTTP_HOST"];
		$pageURL = "http://".$pageURL."/scp/tickets.php?id=".$ticketId;
		$instance = "https://".self::getRedmineInstance($issueKey);
		$client = new Redmine\Client($instance, Redmine_USERNAME, Redmine_PASSWORD);

		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE issue_id='".$issueKey."' AND ticket_id=".$ticketId;
		$res = db_query($sql);
		$row = db_fetch_row($res);
		if(empty($row[0])) {
			$issueKey_values = explode('/', $issueKey);
			$issueId = $issueKey_values[sizeof($issueKey_values)-1];
			$result = $client->issue->show($issueId);
			if($result){
				$sql = "INSERT INTO ".TICKET_JIRA_TABLE." (ticket_id, issue_id) VALUES (". $ticketId .", '". $issueKey ."')";
				db_query($sql);

				$comment = "The issue has been mentioned in the following ticket: ". $pageURL;
				$result = $client->issue->addNoteToIssue($issueId, $comment);
				// $soapClient->addComment($token, $issueKey, array('body' => $comment));
			} else {
				return $issueKey;
			}
		}	
	}
	
	public static function getIssue($issueKey) {
		try{
			$instance = "https://".self::getRedmineInstance($issueKey);
			$client = new Redmine\Client($instance, Redmine_USERNAME, Redmine_PASSWORD);
		}
		catch(exception $e){
			echo "Redmine connection error!";
			return 0;
		}

		try{
			$issueKey_values = explode('/', $issueKey);
			$issueId = $issueKey_values[sizeof($issueKey_values)-1];
			$result = $client->issue->show($issueId);
		}
		catch(exception $e){
			echo "Redmine issue could not load properly.";
			return 0;
		}
		// $resolutionId = $result->resolution;
		// if(empty($resolutionId) && $resolutionId == '') {
		// 	$resolutionName = 'Unresolved';
		// } else {
		// 	$resolutionName = self::getResolution($resolutionId,$issueKey);
		// }
		//return $resolutionName;
		return $result['issue']['status']['name'];
	}
		
	public static function getIssueList($ticketId) {
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$resolutionName = '(' . self::getIssue($row[0]) . ')';
				$issueKey_values = explode('/', $row[0]);
				$id_class = $issueKey_values[sizeof($issueKey_values)-1];
				$issueList .= '<a id="ticket-eta-'.$id_class.'" class="confirm-action" href="#redmine">'."$id_class</a>". $resolutionName . " ";
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
				$issueKey_values = explode('/', $row[0]);
				$id_class = $issueKey_values[sizeof($issueKey_values)-1];
				$issueList .= '<a href="#" id="myBtn-'.$id_class.'" >'."$id_class</a>". $resolutionName . " ";
			}
        }
		return $issueList;
	}

	public static function issueType($issue_id){
		if (strpos($issue_id, '.'))
			return true;
		else
			return false;
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
		$sql = "DELETE FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId." AND issue_id LIKE '%.%'";
		db_query($sql);
	}

	public static function getRedmineVersion($versionId,$instance) {
		$client = new Redmine\Client($instance, Redmine_USERNAME, Redmine_PASSWORD);
		return $client->version->show($versionId);
	}

	public static function remove_http($url) {
	$url = rtrim($url,"/");
   	$disallowed = array('http://', 'https://');
	   foreach($disallowed as $d) {
	      if(strpos($url, $d) === 0) {
	         return str_replace($d, '', $url);
	      }
	   }
	   return $url;
	}

	public static function getRedmineDetails($ticketId){		
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$issueKey = $row[0];
				$instance = "https://".self::getRedmineInstance($issueKey);
				$client = new Redmine\Client($instance, Redmine_USERNAME, Redmine_PASSWORD);
				$issueKey_values = explode('/', $issueKey);
				$issueId = $issueKey_values[sizeof($issueKey_values)-1];
				$result = $client->issue->show($issueId);
				$type = "";
				//$issue_fixVersions = "";
				$fixed_version = "";
				//$fixed_versions_array = array();
				$status = "";
				$resolution = "";
				$priority = "";
				$eta_msg = "";
				$created = "";

				$custom_fields = $result['issue']['custom_fields'];
				foreach ($custom_fields as $custom_field) {
					if($custom_field['name'] == 'Resolution')
						$resolution = $custom_field['value'];
					// elseif($custom_field['name'] == 'Fix Version/s')
					// 	$fixed_versions = $custom_field['value'];
				}
				// foreach ($fixed_versions as $fixed_versionId) {
				// 	$fixVersionDetails = self::getRedmineVersion($fixed_versionId,$instance);
				// 	$issue_fixVersions.=$fixVersionDetails['version']['name'].", ";
				// 	$fixed_versions_array[] = $fixVersionDetails;
				// }

				$type = $result['issue']['tracker']['name'];
				$status = $result['issue']['status']['name'];
				$priority = $result['issue']['priority']['name'];
				$subject = $result['issue']['subject'];
				$description = $result['issue']['description'];
				$created = $result['issue']['created_on'];
				$d = new DateTime($created);
				$created = $d->format('Y-m-d');
				$fixed_version = $result['issue']['fixed_version']['name'];

				$instance_name = explode('.', $instance);
				if(in_array("auckland", $instance_name))
					$eta_msg = self::aucklandJira($status,$fixed_version,$resolution);
				elseif(in_array("lisbon", $instance_name))
					$eta_msg = self::lisbonJira($status,$fixed_version,$resolution);
				elseif(in_array("nanjing", $instance_name))
					$eta_msg = self::nanjingJira($status,$fixed_version,$resolution);
				else
					$eta_msg = self::odessaJira($status,$fixed_version,$resolution);
				$issueKey_values = explode('/', $row[0]);
			   	$id_class = $issueKey_values[sizeof($issueKey_values)-1];
			    $issueList .= '
					<div style="display:none;width:680px;" class="dialog" id="ticket-eta-pop-'.$id_class.'">
					    <h3>Redmine Issue: <a href= "http://'.$row[0].'">'.$id_class.'</a></h3>
					    <a class="close" href=""><i class="icon-remove-circle"></i></a>
					    <hr/>

						<table cellspacing="0" cellpadding="0" width="660px" border="0" style="background:#F4FAFF;border">
						    <tbody>
						    	<tr>
						        	<td width="50%" style="padding:20px;border-right: 1px solid #ddd;">
						            	<p><b>Type:</b> '.$type.'</p>
									    <p><b>Priority:</b> '.$priority.'</p>
									    <p><b>Created on:</b> '.$created.'</p>
						        	</td>
						        	<td width="50%" style="padding:20px;">
						            	<p><b>Status:</b> '.$status.'</p>
									    <p><b>Resolution:</b> '.$resolution.'</p>
									    <p><b>Fix Versions:</b> '.$fixed_version.'</p>
						        	</td>
						    	</tr>
							</tbody>
						</table>				    			    
					    
					    <p><b>Summary:</b> '.$subject.'</p>
					    <p><b>ETA:</b> '.$eta_msg.'</p>
					</div>
					<script type = "text/javascript">
					$("#ticket-eta-'.$id_class.'").on("click",function(e){
				        $("#ticket-eta-pop-'.$id_class.'").show();
				        $("#overlay").show();
				        $("#ticket-eta-pop-'.$id_class.'").css({"width":"680px", "top":"94.5714px", "left":"334.5px"});
				    });
					</script>
				';
			}
        }
		return $issueList;		
	}

	public static function getRedmineDetailsClient($ticketId){		
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticketId;
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if(self::issueType($row[0])){
				$issueKey = $row[0];
				$instance = "https://".self::getRedmineInstance($issueKey);
				$client = new Redmine\Client($instance, Redmine_USERNAME, Redmine_PASSWORD);
				$issueKey_values = explode('/', $issueKey);
				$issueId = $issueKey_values[sizeof($issueKey_values)-1];
				$result = $client->issue->show($issueId);
				$type = "";
				//$issue_fixVersions = "";
				$fixed_version = "";
				$status = "";
				$resolution = "";
				$priority = "";
				$eta_msg = "";
				$created = "";				

				$custom_fields = $result['issue']['custom_fields'];
				foreach ($custom_fields as $custom_field) {
					if($custom_field['name'] == 'Resolution')
						$resolution = $custom_field['value'];
					// elseif($custom_field['name'] == 'Fix Versions')
					// 	$fixed_versions = $custom_field['value'];
				}
				// foreach ($fixed_versions as $fixed_version) {
				// 	$issue_fixVersions.=$fixed_version.", ";
				// }		

				$type = $result['issue']['tracker']['name'];
				$status = $result['issue']['status']['name'];
				$priority = $result['issue']['priority']['name'];
				$subject = $result['issue']['subject'];
				$description = $result['issue']['description'];
				$created = $result['issue']['created_on'];
				$d = new DateTime($created);
				$created = $d->format('Y-m-d');
				$fixed_version = $result['issue']['fixed_version']['name'];

				$instance_name = explode('.', $instance);
				if(in_array("auckland", $instance_name))
					$eta_msg = self::aucklandJira($status,$fixed_version,$resolution);
				elseif(in_array("lisbon", $instance_name))
					$eta_msg = self::lisbonJira($status,$fixed_version,$resolution);
				elseif(in_array("nanjing", $instance_name))
					$eta_msg = self::nanjingJira($status,$fixed_version,$resolution);
				else
					$eta_msg = self::odessaJira($status,$fixed_version,$resolution);
				$issueKey_values = explode('/', $row[0]);
			   	$id_class = $issueKey_values[sizeof($issueKey_values)-1];
			    $issueList .= '
					<!-- The Modal -->
					<div id="myModal-'.$id_class.'" class="modal">

					  <!-- Modal content -->
					  <div class="modal-content" style="width:50%;	">
					    <span class="close" id="close-'.$id_class.'">&times;</span>
					    <h3>Redmine Issue: <a href= "http://'.$row[0].'">'.$id_class.'</a></h3>
					    <hr/>

						<div style="background:#F4FAFF;">
				        	<div style="width:40%;padding:20px;display:inline-block;vertical-align:top;">
				            	<p><b>Type:</b> '.$type.'</p>
							    <p><b>Priority:</b> '.$priority.'</p>
							    <p><b>Created on:</b> '.$created.'</p>
				        	</div>
				        	<div style="width:40%;padding:20px;display:inline-block;vertical-align:top;">
				            	<p><b>Status:</b> '.$status.'</p>
							    <p><b>Resolution:</b> '.$resolution.'</p>
							    <p><b>Fix Versions:</b> '.$fixed_version.'</p>
				        	</div>					    
						</div>				    			    
					    
					    <p><b>Summary:</b> '.$subject.'</p>
					    <p><b>ETA:</b> '.$eta_msg.'</p>
					  </div>

					</div>

					<script>
					document.getElementById("myBtn-'.$id_class.'").onclick = function() {
					    document.getElementById("myModal-'.$id_class.'").style.display = "block";
					}
					document.getElementById("close-'.$id_class.'").onclick = function() {
					    document.getElementById("myModal-'.$id_class.'").style.display = "none";
					}
					window.onclick = function(event) {
					    if (event.target == document.getElementById("myModal-'.$id_class.'")) {
					        document.getElementById("myModal-'.$id_class.'").style.display = "none";
					    }
					}
					</script>
				';
			}
        }
		return $issueList;		
	}

	public static function aucklandJira($status,$fixed_version,$resolution){
		if($status=="Postponed"){
			$eta_msg = "This issue has been postponed which means we have not planned a fix for this issue in the near future. For more information about this issue, please consult with the Aspose team in the support forum.";
		}
		else{
			if($fixed_version==""){
				if($status=="New" OR $status=="Open"){
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
					elseif($status=="Resolved" OR $resolution=="Resolved" ){
						$eta_msg = "The fix for this issue should be available in the ".$fixed_version." release.";
					}
				}
				else{
					$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix for this issue will be available in ".$fixed_version." release.";
				}
			}
		}
		return $eta_msg;
	}

	public static function nanjingJira($status,$fixed_version,$resolution){
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
				$eta_msg = "The fix to this issue was included in ".$fixed_version." release.";
			}
			else{
				if($status=="Feedback" OR $status=="Resolved"){
					$eta_msg = "The issue has been resolved by our developer and is pending for QA. If passed by QA, the fix will be included in ".$fixed_version." release.";
				}
				else{
					$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix will be included in ".$fixed_version." release.";
				}
			}
		}
		return $eta_msg;
	}

	public static function odessaJira($status,$fixed_version,$resolution){
		if($status == "Postponed"){
			$eta_msg = "This issue has been postponed which means we have not planned a fix for this issue in the near future. For more information about this issue, please consult with the Aspose team in the support forum.";
		}
		else{
			if($fixed_version==""){
				if($status=="New" OR $status=="Open" OR $status=="Reopened"){
					$eta_msg = "The development activities for this issue are yet to start. The issue might be pending for analysis or under analysis.";
				}
				else{
					$eta_msg = "This issue is in ".$status." phase, but ETA is not available at the moment.";
				}
			}
			else{
				if($status=="Closed"){
					$eta_msg = "The fix to this issue was included in ".$fixed_version." release.";
				}
				else{
					if($status=="Feedback" OR $status=="Resolved"){
						$eta_msg = "The issue has been resolved by our developer and is pending for QA. If passed by QA, the fix will be included in ".$fixed_version." release.";
					}
					else{
						$eta_msg = "This issue is in ".$status." phase. If everything goes as we have planned the fix will be included in ".$fixed_version." release.";
					}
				}
			}
		}
		return $eta_msg;	
	}	

	public static function lisbonJira($status,$fixed_version,$resolution){
		if($resolution=="Won't Fix" OR $resolution=="Not a Bug"){
			$eta_msg = "Reported issue is not a bug. Consult support team for more details.";
		}
		elseif($fixed_version==""){
			if($status=="New" OR $status=="Reopened"){
				$eta_msg = "The development activities for this issue are yet to start. The issue might be pending for analysis.";
			}
			else{
				$eta_msg = "This issue is in ".$status." phase, but ETA is not available at the moment.";
			}
		}
		elseif($status=="Resolved" OR $resolution=="Resolved" ){
			$eta_msg = "The fix for this issue should be available in the ".$fixed_version." release.";
		}
		else{
			$eta_msg = "This issue is in ".$status." phase. If everything goes as planned, the fix will be included in ".$fixed_version." release.";
		}
		return $eta_msg;
	}

	public static function checkIfAllIssuesClosed($ticket_id){
		$sql = "SELECT issue_id FROM ".TICKET_JIRA_TABLE." WHERE ticket_id=".$ticket_id;
		$res = db_query($sql);
		$result = "Yes";
		while($row = db_fetch_row($res)) {
			//echo "<br>".$row[0];
			if(self::issueType($row[0])){//Check if its Redmine issue or Jira issue
				$status = self::getIssue($row[0]);
				//echo " Status: ".$status;
				if($status != "Resolved" AND $status != "Closed")
					$result = "No";
			}
		}
		return $result;
	}	
	// Copied from Issue ETA plugin	
}