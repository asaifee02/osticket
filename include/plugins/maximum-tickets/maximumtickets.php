<?php
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class MaximumTicketsPlugin extends Plugin {
    var $config_class = "MaximumTicketsPluginConfig";

    function bootstrap() {
        // $config = $this->getConfig();
		// define('MAXIMUM_TICKETS', $config->get('maximum-tickets'));
		Signal::connect('ticket.created', array($this, 'updateIssueCount'),'Ticket');
		// echo $this->getUserQuotaByUserId(6);
		// echo "<br>".$this->getUserIssueCountById(6);
		// if($this->isSupportExpired(10))
		// 	echo "greater";
		// else
		// 	echo "lower";
		// if($this->isSupportActive(12))
		// 	echo "active";
		// else
		// 	echo "not active";
		// if($this->hasMultipleSupport(6))
		// 	echo "active";
		// else
		// 	echo "not active";
    //$this->isSupportExpired(10);
    }

    public static function getPassportIdById($userId) {
		$sql = "SELECT passport_user_id FROM ".USER_EMAIL_TABLE." WHERE user_id=".$userId;
		$result = db_query($sql);
		if($result){
			$row = db_fetch_row($result);
			if(isset($row))
				return $row[0];
			else
				return 0;
		}
		else 
			return 0;
	}

    public static function isSupportActive($support_id){
    	$sql = "SELECT active FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$active = $row[0];
		return $active;
    }

	public static function isSupportExpired($support_id){
		$sql = "SELECT expiry_date FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$support_date = new DateTime($row[0]);
	    $current_date = new DateTime (date('Y-m-d H:i:s'));
	    return $current_date >= $support_date ? true:false;
	}

	public static function hasMultipleSupport($master_user_id){
		$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
		$sql = "SELECT issue_count FROM ost_ps_issue_monitor WHERE master_user_id=".$master_user_id;
		$result = db_query($sql);
		return db_num_rows($result)>1 ? true:false;
	}

	public static function getSupportsArray($master_user_id){
		$sql ="SELECT mo.*, pr.* FROM `ost_ps_issue_monitor` mo LEFT JOIN `ost_ticket_priority` pr ON (mo.`prioritysupport_order_type_id` = pr.`priority_id`) WHERE mo.`master_user_id` = ".$master_user_id." AND mo.active = 1 ORDER BY pr.`priority_urgency`, mo.`expiry_date`";
		
        $result = db_query($sql);
        $supports = array();
        while($row=db_fetch_row($result)){
            $supports[] = array('id'=>$row[0],'master_user_id'=>$row[1],'quota'=>$row[2],'issue_count'=>$row[3],'expiry_date'=>$row[4],'prioritysupport_order_type_id'=>$row[5],'active'=>$row[6]);
        }
        foreach ($supports as $key=>$support) {
        	if(MaximumTicketsPlugin::isSupportExpired($support['id']) OR !MaximumTicketsPlugin::isSupportActive($support['id']) OR ($support['quota'] <= $support['issue_count']))
        		unset($supports[$key]);
        }
        return array_values($supports);
	}

	public static function getSupportsArray_minus($master_user_id){
		$sql ="SELECT mo.*, pr.* FROM `ost_ps_issue_monitor` mo LEFT JOIN `ost_ticket_priority` pr ON (mo.`prioritysupport_order_type_id` = pr.`priority_id`) WHERE mo.`master_user_id` = ".$master_user_id." AND mo.active = 1 ORDER BY pr.`priority_urgency`, mo.`expiry_date`";
		
        $result = db_query($sql);
        $supports = array();
        while($row=db_fetch_row($result)){
            $supports[] = array('id'=>$row[0],'master_user_id'=>$row[1],'quota'=>$row[2],'issue_count'=>$row[3],'expiry_date'=>$row[4],'prioritysupport_order_type_id'=>$row[5],'active'=>$row[6]);
        }
        foreach ($supports as $key=>$support) {
        	if(MaximumTicketsPlugin::isSupportExpired($support['id']) OR !MaximumTicketsPlugin::isSupportActive($support['id']))
        		unset($supports[$key]);
        }
        return array_values($supports);
	}

	public static function updateIssueCount($ticket){
		$ticketId = $ticket->getId();		
		if($ticket->getSource() == "API"){
			$master_user_id = $ticket->getUserId();
			$master_user_ids = MaximumTicketsPlugin::getMasterUserIdsByUserIdWithActiveSupports_STAFF($master_user_id);
			$master_user_id = $master_user_ids[0];
		}
		else		
			$master_user_id = $_SESSION[parent_child];
		if(!$master_user_id)
            return "No record found for this user.";
        $master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
        $supports = MaximumTicketsPlugin::getSupportsArray($master_user_id);
        if(count($supports)==0){
        	$msg = "Support is expired or In-active";
		    echo $msg;
        }else{
    		MaximumTicketsPlugin::updateSupportById($supports[0]['id']);
    		MaximumTicketsPlugin::updateTicketPriorityByTicketId($ticketId,$supports[0]['prioritysupport_order_type_id'],$supports[0]['id']);
    		$comment = "New ticket created.";
    		$sql = "INSERT INTO `ost_ps_issue_monitor_log`(`user_id`, `action`, `action_date`, `comment`, `asposesupport_user_id`, `asposesupport_username`) VALUES (".$master_user_id.",'Increase',NOW(),'".$comment."',0,0)";
			$result = db_query($sql);
        }
	}

	public static function updateIssueCountByTicketId($ticketId){//used to decrement issue count when a ticket is moved from osticket to discourse
		$master_user_id = MaximumTicketsPlugin::getMasterUserId($ticketId);
		if(!$master_user_id)
            return "No record found for this user.";
        $master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
        $supports = MaximumTicketsPlugin::getSupportsArray($master_user_id);
        if(count($supports)==0){
        	$msg = "Support is expired or In-active";
		    echo $msg;
        }else{
    		MaximumTicketsPlugin::downgradeSupportById($supports[0]['id']);
    		$comment = "Ticket is being moved to discourse.";
    		$sql = "INSERT INTO `ost_ps_issue_monitor_log`(`user_id`, `action`, `action_date`, `comment`, `asposesupport_user_id`, `asposesupport_username`) VALUES (".$master_user_id.",'Decrease',NOW(),'".$comment."',0,0)";
			$result = db_query($sql);
			return true;
        }
	}

	public static function updateSupportById($supportId){
		$sql = "SELECT issue_count FROM ost_ps_issue_monitor WHERE id=".$supportId;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$issue_count = $row[0]+1;
		$sql = "UPDATE ost_ps_issue_monitor SET issue_count = ".$issue_count." WHERE id=".$supportId;
		db_query($sql);
	}

	public static function updateTicketPriorityByTicketId($ticketId,$priority_id,$supportId){
		$sql = "UPDATE ost_ticket__cdata SET priority = ".$priority_id." WHERE ticket_id=".$ticketId;
		db_query($sql);
		$sql = "UPDATE ost_ticket SET ps_id = ".$supportId." WHERE ticket_id=".$ticketId;
		db_query($sql);
	}

	public static function downgradeSupportById($supportId){
		$sql = "SELECT issue_count FROM ost_ps_issue_monitor WHERE id=".$supportId;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$issue_count = $row[0]-1;
		$sql = "UPDATE ost_ps_issue_monitor SET issue_count = ".$issue_count." WHERE id=".$supportId;
		db_query($sql);
	}

	public static function getUserTickets($userId, $userEmail) {
		if(isset($userEmail)) {
			$sql = "SELECT user_id FROM ".USER_EMAIL_TABLE." WHERE address='".$userEmail."' ";
			$result = db_query($sql);
			while($row = db_fetch_row($result)) {
		    	$userId = $row[0];
        	}
		}
		$master_user_ids = MaximumTicketsPlugin::getMasterUserIdsByUserId($userId);
		if(!$master_user_ids)
	    	return false;
		foreach ($master_user_ids as $master_user_id) {
		    $master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
		    $supports = MaximumTicketsPlugin::getSupportsArray($master_user_id);
		    if(count($supports)>0)
		    	return true;
		}
	    return false;
	}

	public static function getUserTickets_STAFF($userId, $userEmail) {
		$passport_user_id = MaximumTicketsPlugin::getPassportIdByEmail($userEmail);
		$userId = MaximumTicketsPlugin::getUserIdByEmail($userEmail);
		$sql = "SELECT id FROM ost_ps_issue_monitor WHERE master_user_id=".$passport_user_id;
		$result = db_query($sql);
		if(db_num_rows($result)>0){
			$master_user_ids = array();
			$master_user_ids[] = $userId;
		}
		else{
			$master_user_ids = array();
			$master_user_ids = MaximumTicketsPlugin::callPassportAPI($userEmail);
		}
		$master_user_ids = array_filter($master_user_ids);
		if(!$master_user_ids)
	    	return false;
		foreach ($master_user_ids as $master_user_id) {
			$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
		    $supports = MaximumTicketsPlugin::getSupportsArray($master_user_id);
		    if(count($supports)>0)
		    	return true;
		}
	    return false;
	}

	public static function callPassportAPI($email) {		
		$signingKey = dp_api_signing_key;
		$url = dp_api_url.'/users/masterusersbyemail?email='.$email.'&clientkey='.dp_api_client_key.'&signature=';
		$signature = base64_encode(hash_hmac("sha1", strtolower($url), $signingKey, true));
		if(substr($signature, -1) == '='){
			$signature = substr($signature, 0, - 1);
		}
		$url = $url . str_replace("+", "-", str_replace("/", "_", $signature));
		$headers = array();
		$headers[] = "Content-type: application/json";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPGET, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);
		$response_info = curl_getinfo($curl);

		if ($response_info['http_code'] == 0) {
			echo "TIMEOUT: api call to " . $url ." took more than 5s to return" ;
		} else if (in_array($response_info['http_code'], array(200, 201, 202))) {
			$data = json_decode($response);
		} else if ($response_info['http_code'] == 401) {
			echo "Unauthorized API request to " . $url .": ".json_decode($response)->message ;
		} else if ($response_info['http_code'] == 404) {
			$data = null;
		} else {
			echo "Can't connect to the api: " . $url ." response code: " . $response_info['http_code'];
		}

		if($data){
			$master_user_ids = array();
			foreach ($data as $key => $value) {
				$master_user_id = MaximumTicketsPlugin::getUserIdByEmail($value->{'email'});
				$master_user_ids[] = $master_user_id;
			}
			return $master_user_ids;
		}
		else
			return 0;
	}

	public static function getPassportData($id) {
		$signingKey = dp_api_signing_key;
		$url = dp_api_url.'/users/'.$id.'?clientkey='.dp_api_client_key.'&signature=';
		$signature = base64_encode(hash_hmac("sha1", strtolower($url), $signingKey, true));
		if(substr($signature, -1) == '='){
			$signature = substr($signature, 0, - 1);
		}
		$url = $url . str_replace("+", "-", str_replace("/", "_", $signature));
		$headers = array();
		$headers[] = "Content-type: application/json";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPGET, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);
		$response_info = curl_getinfo($curl);

		if ($response_info['http_code'] == 0) {
			//echo "TIMEOUT: api call to " . $url ." took more than 5s to return" ;
			return 0;
		} else if (in_array($response_info['http_code'], array(200, 201, 202))) {
			$data = json_decode($response);
			return $data;
		} else if ($response_info['http_code'] == 401) {
			//echo "Unauthorized API request to " . $url .": ".json_decode($response)->message ;
			return 0;
		} else if ($response_info['http_code'] == 404) {
			//$data = null;
			return 0;
		} else {
			//echo "Can't connect to the api: " . $url ." response code: " . $response_info['http_code'];
			return 0;
		}
	}

	public static function getPassportIdByEmail($email) {
		$sql = "SELECT passport_user_id FROM ".USER_EMAIL_TABLE." WHERE address='".$email."'";
		$result = db_query($sql);
		if($result){
			$row = db_fetch_row($result);
			if(isset($row))
				return $row[0];
			else
				return 0;
		}
		else 
			return 0;
	}
	
  	public static function getUserQuotaById($id){
    	$sql = "SELECT quota FROM ost_ps_issue_monitor WHERE id =".$id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$quota = $row[0];
		return $quota;
  	}

	public static function getUserQuota($userId, $userEmail) {
		if(isset($userEmail)) {
			$sql = "SELECT user_id FROM ".USER_EMAIL_TABLE." WHERE address='".$userEmail."' ";
			$result = db_query($sql);
			while($row = db_fetch_row($result)) {
		    	$userId = $row[0];
        	}
		}
		$quota = MaximumTicketsPlugin::getUserQuotaByUserId($userId);
		return $quota;
	}

	public static function getUserQuotaByUserId($userId) {		
		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $userId)
						$master_user_id = $key;
				}
			}
		}
		if($master_user_id){
			$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
			$sql = "SELECT quota FROM ost_ps_issue_monitor WHERE master_user_id =".$master_user_id;
			$result = db_query($sql);
			if(db_num_rows($result)){
				$row = db_fetch_row($result);
				$quota = $row[0];
				return $quota;
			}
		}
		return 0;
	}

	public static function getUserIssueCountById($userId) {
		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $userId)
						$master_user_id = $key;
				}
			}
		}
		if(!isset($master_user_id))
			return false;

		$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
		$sql = "SELECT issue_count FROM ost_ps_issue_monitor WHERE master_user_id =".$master_user_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$issue_count = $row[0];
		return $issue_count;
	}

	public static function getExcludeValue($ticketId){
		$sql = "SELECT exclude FROM ".TICKET_TABLE." WHERE ticket_id=".$ticketId;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		if($row[0])
			return "True";
		else
			return "False";
	}

	public static function getMasterUserId($ticketId){
		$sql = "SELECT user_id FROM ".TICKET_TABLE." WHERE ticket_id=".$ticketId;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$userId = $row[0];

		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $userId)
						$master_user_id = $key;
				}
			}
		}

		if(!isset($master_user_id))
			return false;
		return $master_user_id;
	}

	public static function getUserIdByTicketId($ticketId){
		$sql = "SELECT user_id FROM ".TICKET_TABLE." WHERE ticket_id=".$ticketId;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		return $row[0];
	}

	public static function getMasterUserIdByEmail($email){
		$sql = "SELECT user_id FROM ".USER_EMAIL_TABLE." WHERE address='".$email."'";
		$result = db_query($sql);
		$row = db_fetch_row($result);
		$userId = $row[0];

		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $userId)
						$master_user_id = $key;
				}
			}
		}

		if(!isset($master_user_id))
			return false;

		return $master_user_id;
	}

  	public static function getMasterUserIdByUserId($id){
		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $id)
						$master_user_id = $key;
				}
			}
		}
		
		if(isset($master_user_id))
			return $master_user_id;
		else
			return false;
	}

	public static function getMasterUserIdsByUserId($id){
		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			$master_user_ids = array();
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $id)
						$master_user_ids[] = $key;
				}
			}
		}
		
		if(isset($master_user_ids))
			return $master_user_ids;
		else
			return false;
	}
	
    public static function getMasterUserIdsByUserIdWithActiveSupports($id){
		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			$master_user_ids = array();
			foreach ($support_owners as $key => $support_owner) {
				foreach ($support_owner as $support_user) {
					if($support_user == $id)
						$master_user_ids[] = $key;
				}
			}
		}
		foreach ($master_user_ids as $master_user_id) {
			$master_user_id_local = $master_user_id;
			$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
		    $sql = "SELECT id,master_user_id,quota,issue_count,expiry_date,prioritysupport_order_type_id,active FROM ost_ps_issue_monitor WHERE master_user_id=".$master_user_id;
		    $result = db_query($sql);
		    while(list($id,$master_user_id,$quota,$issue_count,$expiry_date,$prioritysupport_order_type_id,$active)=db_fetch_row($result)){
		      $supports[] = array('id'=>$id,'master_user_id'=>$master_user_id,'quota'=>$quota,'issue_count'=>$issue_count,'expiry_date'=>$expiry_date,'prioritysupport_order_type_id'=>$prioritysupport_order_type_id,'active'=>$active);
		    }
		    foreach ($supports as $key=>$support) {
	    	if(MaximumTicketsPlugin::isSupportExpired($support['id']) OR !MaximumTicketsPlugin::isSupportActive($support['id']) OR ($support['quota'] <= $support['issue_count']))
	    		unset($supports[$key]);
		    }
		}
		$master_user_ids = array();
		$master_user_ids_org = array();
		foreach ($supports as $key => $support) {
			$master_user_ids[] = $support['master_user_id'];
		}
		foreach ($master_user_ids as $key => $value) {
			$master_user_ids_org[] = MaximumTicketsPlugin::getUserIdByPassportId($value);
		}
		$master_user_ids_org = array_unique($master_user_ids_org);
		if(isset($master_user_ids_org))
			return $master_user_ids_org;
		else
			return false;
	}

	public static function getUserIdByPassportId($passportId) {
		$sql = "SELECT user_id FROM ".USER_EMAIL_TABLE." WHERE passport_user_id=".$passportId;
		$result = db_query($sql);
		if($result){
			$row = db_fetch_row($result);
			if(isset($row))
				return $row[0];
			else
				return 0;
		}
		else 
			return 0;
	}

	public static function getUserEmailById($userId) {
		$sql = "SELECT address FROM ".USER_EMAIL_TABLE." WHERE user_id=".$userId;
		$result = db_query($sql);
		if($result){
			$row = db_fetch_row($result);
			if(isset($row))
				return $row[0];
			else
				return 0;
		}
		else 
			return 0;
	}

	public static function getUserIdByEmail($email) {
		$sql = "SELECT user_id FROM ".USER_EMAIL_TABLE." WHERE address='".$email."'";
		$result = db_query($sql);
		if($result){
			$row = db_fetch_row($result);
			if(isset($row))
				return $row[0];
			else
				return 0;
		}
		else 
			return 0;
	}

	public static function getMasterUserIdsByUserIdWithActiveSupports_STAFF($id){
		$userEmail = MaximumTicketsPlugin::getUserEmailById($id);
		$passport_user_id = MaximumTicketsPlugin::getPassportIdById($id);
		// $sql = "SELECT id FROM ost_ps_issue_monitor WHERE master_user_id=".$passport_user_id;
		// $result = db_query($sql);
		// if(db_num_rows($result)>0){
		// 	$master_user_ids = array();
		// 	$master_user_ids[] = $id;
		// }
		// else{
		// 	$master_user_ids = array();
		// 	$master_user_ids = MaximumTicketsPlugin::callPassportAPI($userEmail);
		// }
		$master_user_ids = array();
		$master_user_ids = MaximumTicketsPlugin::callPassportAPI($userEmail);
		if(empty($master_user_ids))
			$master_user_ids = array();
		$master_user_ids[] = $id;
		$master_user_ids = array_filter($master_user_ids);
		foreach ($master_user_ids as $master_user_id) {
			$master_user_id_local = $master_user_id;
			$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
		    $sql = "SELECT id,master_user_id,quota,issue_count,expiry_date,prioritysupport_order_type_id,active FROM ost_ps_issue_monitor WHERE master_user_id=".$master_user_id;
		    $result = db_query($sql);
		    while(list($id,$master_user_id,$quota,$issue_count,$expiry_date,$prioritysupport_order_type_id,$active)=db_fetch_row($result)){
		      $supports[] = array('id'=>$id,'master_user_id'=>$master_user_id,'quota'=>$quota,'issue_count'=>$issue_count,'expiry_date'=>$expiry_date,'prioritysupport_order_type_id'=>$prioritysupport_order_type_id,'active'=>$active);
		    }
		    foreach ($supports as $key=>$support) {
	    	if(MaximumTicketsPlugin::isSupportExpired($support['id']) OR !MaximumTicketsPlugin::isSupportActive($support['id']) OR ($support['quota'] <= $support['issue_count']))
	    		unset($supports[$key]);
		    }
		}
		$master_user_ids = array();
		$master_user_ids_org = array();
		foreach ($supports as $key => $support) {
			$master_user_ids[] = $support['master_user_id'];
		}
		foreach ($master_user_ids as $key => $value) {
			$master_user_ids_org[] = MaximumTicketsPlugin::getUserIdByPassportId($value);
		}
		$master_user_ids_org = array_unique($master_user_ids_org);
		if(isset($master_user_ids_org))
			return $master_user_ids_org;
		else
			return false;
	}

	public static function getUsersByMasterUserId($master_user_id){
		if(isset($_SESSION[support_users])){
			$support_owners = $_SESSION[support_users];
			if(isset($support_owners[$master_user_id]))
				return $support_owners[$master_user_id];
			else
				return false;			
		}
	}

	public static function getUserNameById($id){
		$sql = "SELECT name FROM ost_user WHERE id =".$id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		if(!empty($row[0]))
			return $row[0];
		$sql = "SELECT address FROM ost_user_email WHERE user_id =".$id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		if(!empty($row[0]))
			return $row[0];
	}	

	public static function changeExcludeValue($ticketId) {
		$exclude_value = self::getExcludeValue($ticketId);
		$sql = "SELECT ps_id FROM ost_ticket WHERE ticket_id = ".$ticketId;
		$res = db_query($sql);
		$row = db_fetch_row($res);
		$support_id_to_update = $row[0];
		
		if($exclude_value=="True"){
			$exclude_value = "0";
			MaximumTicketsPlugin::updateSupportById($support_id_to_update);
		}
		else{
			$exclude_value = "1";
			MaximumTicketsPlugin::downgradeSupportById($support_id_to_update);
		}

		$sql = "UPDATE ".TICKET_TABLE." SET exclude = ".$exclude_value." WHERE ticket_id=".$ticketId;
		if(db_query($sql))
			return true;
		else
			return false;
	}

	public static function create_ticket_validateSubscription($email) {
        $master_user_id = MaximumTicketsPlugin::getMasterUserIdByEmail($email);
        if(!$master_user_id)
            return "No record found for this user.";
        $master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
        $sql = "SELECT * FROM ost_ps_issue_monitor WHERE master_user_id=".$master_user_id;
        $result = db_query($sql);
        while(list($id,$master_user_id,$quota,$issue_count,$expiry_date,$prioritysupport_order_type_id,$active)=db_fetch_row($result)){
            $supports[] = array('id'=>$id,'master_user_id'=>$master_user_id,'quota'=>$quota,'issue_count'=>$issue_count,'expiry_date'=>$expiry_date,'prioritysupport_order_type_id'=>$prioritysupport_order_type_id,'active'=>$active);
        }

        foreach ($supports as $key=>$support) {
        	if(MaximumTicketsPlugin::isSupportExpired($support['id']) OR !MaximumTicketsPlugin::isSupportActive($support['id']) OR ($support['quota'] <= $support['issue_count']))
        		unset($supports[$key]);
        }
        if(count($supports)==0){
        	return "Customer has invalid subscription, you can't create ticket";
        }else{
        	return "ok";
        }
    }
    //Custom code to get tickets stats for custom dashboard
    public static function getTicketsStats($user_id,$display_all="") {
        if($display_all=="yes"){
            $master_user_ids = MaximumTicketsPlugin::getMasterUserIdsByUserId($user_id);
            $master_user_ids[] = $user_id;
            $master_user_ids = implode(', ', $master_user_ids);
            $where = ' WHERE ticket.user_id in ('.$master_user_ids.') ';
        }
        else{
            $where = ' WHERE ticket.user_id = '.db_input($user_id).' ';
        }

        $sql =  'SELECT \'open\', count( ticket.ticket_id ) AS tickets '
                .'FROM ' . TICKET_TABLE . ' ticket '
                .'INNER JOIN '.TICKET_STATUS_TABLE. ' status
                    ON (ticket.status_id=status.id
                            AND status.state=\'open\') '
                . $join
                . $where

                .'UNION SELECT \'closed\', count( ticket.ticket_id ) AS tickets '
                .'FROM ' . TICKET_TABLE . ' ticket '
                .'INNER JOIN '.TICKET_STATUS_TABLE. ' status
                    ON (ticket.status_id=status.id
                            AND status.state=\'closed\' ) '
                . $join
                . $where

                .'UNION SELECT \'deleted\', count( ticket.ticket_id ) AS tickets '
                .'FROM ' . TICKET_TABLE . ' ticket '
                .'INNER JOIN '.TICKET_STATUS_TABLE. ' status
                    ON (ticket.status_id=status.id
                            AND status.state=\'deleted\' ) '
                . $join
                . $where
             
                .'UNION SELECT \'archived\', count( ticket.ticket_id ) AS tickets '
                .'FROM ' . TICKET_TABLE . ' ticket '
                .'INNER JOIN '.TICKET_STATUS_TABLE. ' status
                    ON (ticket.status_id=status.id
                            AND status.state=\'archived\' ) '
                . $join
                . $where

                .'UNION SELECT \'resolved\', count( ticket.ticket_id ) AS tickets '
                .'FROM ' . TICKET_TABLE . ' ticket '
                .'INNER JOIN '.TICKET_STATUS_TABLE. ' status
                    ON (ticket.status_id=status.id
                            AND status.name=\'Resolved\' ) '
                . $join
                . $where;

        $res = db_query($sql);
        $stats = array();
        while($row = db_fetch_row($res)) {
            $stats[$row[0]] = $row[1];
        }

        return $stats;
    }
    //Custom code to get tickets stats for custom dashboard
    public static function getSupportIdByTicketId($ticket_id){
    	$sql = "SELECT ps_id FROM ost_ticket WHERE ticket_id = ".$ticket_id;
		$res = db_query($sql);
		$row = db_fetch_row($res);
		return $row[0];
    }
    public static function getIssueCountBySupportId($support_id){
    	$sql = "SELECT issue_count FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		return $row[0];
	}
	public static function getQuotaBySupportId($support_id){
    	$sql = "SELECT quota FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		return $row[0];
	}
	public static function getMasterUserIdBySupportId($support_id){
    	$sql = "SELECT master_user_id FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
		return $row[0];
	}
	public static function isQuotaExpired($support_id){
		$sql = "SELECT quota,issue_count FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
	    return $row[1] >= $row[0] ? true:false;
	}
	public static function isIssueCountZero($support_id){
		$sql = "SELECT issue_count FROM ost_ps_issue_monitor WHERE id=".$support_id;
		$result = db_query($sql);
		$row = db_fetch_row($result);
	    return $row[0] <= 0 ? true:false;
	}
	public static function delete_staff_comment_func($thread_id){/* Custom Code for delete comment functionality */
		$sql = "UPDATE `ost_ticket_thread` SET `thread_type`='D' WHERE `id`=".$thread_id;
		$result = db_query($sql);
		if($result){
			return true;
		}
		else{
			return false;
		}
	}
	public static function issue_count_plus_func($ticket_id,$comment){
		$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
		$master_user_id = MaximumTicketsPlugin::getMasterUserIdBySupportId($support_id);
		$staff_id = $_SESSION['_auth']['staff']['id'];
		$staff_username = $_SESSION['_auth']['staff']['key'];
		$staff_username = explode(":", $staff_username);
		$staff_username = $staff_username[1];
		$sql = "INSERT INTO `ost_ps_issue_monitor_log`(`user_id`, `action`, `action_date`, `comment`, `asposesupport_user_id`, `asposesupport_username`) VALUES (".$master_user_id.",'Increase',NOW(),'".$comment."',".$staff_id.",'".$staff_username."')";
		$result = db_query($sql);
		if($result){
			$supports = MaximumTicketsPlugin::getSupportsArray($master_user_id);
	        if(count($supports)==0){
	        	return false;
	        }else{
	    		MaximumTicketsPlugin::updateSupportById($supports[0]['id']);
	    		MaximumTicketsPlugin::updateTicketPriorityByTicketId($ticket_id,$supports[0]['prioritysupport_order_type_id'],$supports[0]['id']);
	    		return true;
	        }
		}
		else{
			return false;
		}
	}
	public static function issue_count_minus_func($ticket_id,$comment){
		$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
		$master_user_id = MaximumTicketsPlugin::getMasterUserIdBySupportId($support_id);
		$staff_id = $_SESSION['_auth']['staff']['id'];
		$staff_username = $_SESSION['_auth']['staff']['key'];
		$staff_username = explode(":", $staff_username);
		$staff_username = $staff_username[1];
		$sql = "INSERT INTO `ost_ps_issue_monitor_log`(`user_id`, `action`, `action_date`, `comment`, `asposesupport_user_id`, `asposesupport_username`) VALUES (".$master_user_id.",'Decrease',NOW(),'".$comment."',".$staff_id.",'".$staff_username."')";
		$result = db_query($sql);
		if($result){
			$supports = MaximumTicketsPlugin::getSupportsArray_minus($master_user_id);
	        if(count($supports)==0){
	        	return false;
	        }else{
	    		MaximumTicketsPlugin::downgradeSupportById($supports[0]['id']);
				return true;
	        }
		}
		else{
			return false;
		}
	}

	public static function edit_log_comment_func($log_id,$comment){
		$sql = "UPDATE `ost_ps_issue_monitor_log` SET `comment`='".$comment."' WHERE `id`=".$log_id;
		$result = db_query($sql);
		if($result){
			return true;
		}
		else{
			return false;
		}
	}

    //Custom code to get quota and issue count for a ticket
    public static function getIssueCountByTicketId($ticket_id){
    	$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
    	return MaximumTicketsPlugin::getIssueCountBySupportId($support_id);
    }
    public static function getQuotaByTicketId($ticket_id){
    	$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
    	return MaximumTicketsPlugin::getQuotaBySupportId($support_id);
    }    
    public static function getSupportOwnerByTicketId($ticket_id){
    	$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
    	$master_user_id_passport = MaximumTicketsPlugin::getMasterUserIdBySupportId($support_id);
    	$user_id = MaximumTicketsPlugin::getUserIdByPassportId($master_user_id_passport);
    	return MaximumTicketsPlugin::getUserNameById($user_id);
    }
    //Custom code to get quota and issue count for a ticket
    public static function connect_central_db(){
    	#Connect to the DB && get configuration from database
		$ferror=null;
		$options = array();
		if (defined('DBSSLCA'))
		    $options['ssl'] = array(
		        'ca' => DBSSLCA,
		        'cert' => DBSSLCERT,
		        'key' => DBSSLKEY
		    );
		$db_name = "dynabic_osticket_data_containerize";
		if (!db_connect(DBHOST, DBUSER, DBPASS, $options)) {
		    echo 'Unable to connect to the database — %s',db_connect_error();
		}elseif(!db_select_database($db_name)) {
		    echo 'Unknown or invalid database: %s',$db_name;
		}
    }
    public static function getAllSupports($ticket_id){
    	$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
    	$master_user_id = MaximumTicketsPlugin::getMasterUserIdBySupportId($support_id);
    	$sql ="SELECT mo.*, pr.* FROM `ost_ps_issue_monitor` mo LEFT JOIN `ost_ticket_priority` pr ON (mo.`prioritysupport_order_type_id` = pr.`priority_id`) WHERE mo.`master_user_id` = ".$master_user_id." AND mo.active = 1 ORDER BY mo.`active` DESC, pr.`priority_urgency`, mo.`expiry_date`";
        $result = db_query($sql);
        $supports = array();
        while($row=db_fetch_row($result)){
            $supports[] = array('id'=>$row[0],'master_user_id'=>$row[1],'quota'=>$row[2],'issue_count'=>$row[3],'expiry_date'=>$row[4],'prioritysupport_order_type_id'=>$row[5],'active'=>$row[6],'order_id'=>$row[7]);
        }
		echo '
		<table class="list" border="0" cellspacing="1" cellpadding="2" width="900">
			<thead>
				<tr>
					<th>Type</th>
					<th>Initial Quota</th>
					<th>Consumed Quota</th>
					<th>Expiry Date</th>
					<th>Status</th>
					<th>Master User Id</th>
					<th>Order Id</th>
				</tr>
			</thead>
			<tbody page="1" class="">
		';
				foreach ($supports as $support) {
					$support_type = MaximumTicketsPlugin::getPriorityDescription($support['prioritysupport_order_type_id']);
					$expiry_dates = explode(" ",$support['expiry_date']);
					$expiry_date = $expiry_dates[0];
					$expiry_date = strtotime($expiry_date);
					$expiry_date = date('F d,Y',$expiry_date);
					$supportstatus = ($support['active'] == 1) ? "Active":"Inactive";
					$order_id = $support['order_id'];
		echo '		
				<tr>					
					<th>'. $support_type.'</th>
					<td>'. $support['quota'].'</td>
					<td>'. $support['issue_count'].'</td>
					<td>'. $expiry_date.'</td>
					<td>'. $supportstatus.'</td>
					<td>'. $support['master_user_id'].'</td>
					<td>'. $support['order_id'].'</td>
				</tr>
		';
				}
		echo '		
			</tbody>
		</table>
		';
    }
    public static function getAllSupportsLogs($ticket_id){
    	$support_id = MaximumTicketsPlugin::getSupportIdByTicketId($ticket_id);
    	$master_user_id = MaximumTicketsPlugin::getMasterUserIdBySupportId($support_id);
    	$sql = "SELECT * FROM `ost_ps_issue_monitor_log` WHERE `user_id` = ".$master_user_id." ORDER BY `action_date` DESC";
		$result = db_query($sql);
		if($result){
			echo '
			<table class="list" border="0" cellspacing="1" cellpadding="2" width="900">
				<thead>
					<tr>
						<th>Action</th>
						<th>Action Date</th>
						<th>Comment</th>
						<th>Support Username</th>
						<th></th>
					</tr>
				</thead>
				<tbody page="1" class="">
			';
					while($row = db_fetch_row($result)){
						$username = $row[6]?$row[6]:"Client";
			echo '		
					<tr>					
						<th>'. $row[2].'</th>
						<td>'. $row[3].'</td>
						<td>'. $row[4].'</td>
						<td>'. $username.'</td>
						<td><a id="edit_comment_'.$row[0].'" class="confirm-action" href="#">Edit</a></td>
					</tr>
			';
			echo '
				<div style="display:none;width:550px;" class="dialog" id="edit_comment_pop_'.$row[0].'">
	                <h3>Support Logs</h3>
	                <a class="close" href=""><i class="icon-remove-circle"></i></a>
	                <hr/>
	                <textarea id="edit_comment_value_'.$row[0].'" cols="30" rows="3" wrap="soft" class="richtext ifhtml no-bar">'.$row[4].'</textarea>	                
	            	<hr/>
	            	<button onclick="save_comment_value('.$row[0].')" class="float-left submit-button" >Save</button>
	            </div>
	            <script type = "text/javascript">
	            $("#edit_comment_'.$row[0].'").on("click",function(e){
	                $("#edit_comment_pop_'.$row[0].'").show();
	                $("#overlay").show();
	            });
	            </script>
	        ';
					}
			echo '		
				</tbody>
			</table>				
			';
		}else{
			echo "Error in query!";
		}
		?>
		<script type="text/javascript">
        function save_comment_value(id){
        	var comment = $("#edit_comment_value_"+id).val();
            var data = '{ "log_id": '+id+',"comment" : "'+comment+'"}';
            var $url = 'ajax.php/edit_log_comment.json';
            $.ajax({
                type: "POST",
                url: $url,
                dataType: 'json',
                cache: false,
                data: data,
                success: function(response){
                    console.log(response);
                    var response = $.map(response, function(value, index) {
                        return [value];
                    });
                    if(response[0] == 200 ){
                        alert("Comment has been updated!");
                        location.reload();
                    }
                    else
                        alert("Could not update the comment!");
                }
            })
            .done(function() { 
            })
            .fail(function() {
                alert("Could not update the comment!");
            });
        }
        </script>
		<?php
    }

    function getPriorityDescription($priority_id){
    	$sql = "SELECT priority_desc FROM ost_ticket_priority WHERE priority_id=".$priority_id;
        $res = db_query($sql);
        if(db_num_rows($res)>0){
	        $row = db_fetch_row($res);
	        $priority_desc = $row[0];
	        return $priority_desc;
	    }
	    return "Invalid Priority";
    }
    public static function getAllSupportsByUserId($user_id){
    	//$master_user_ids = MaximumTicketsPlugin::getMasterUserIdsByUserId($user_id);
    	$userEmail = MaximumTicketsPlugin::getUserEmailById($user_id);
    	$passport_user_id = MaximumTicketsPlugin::getPassportIdByEmail($userEmail);
		$master_user_ids = array();		
		$master_user_ids = MaximumTicketsPlugin::callPassportAPI($userEmail);
		if($master_user_ids){
			$sql = "SELECT id FROM ost_ps_issue_monitor WHERE master_user_id=".$passport_user_id;
			$result = db_query($sql);
			if(db_num_rows($result)>0){
				$master_user_ids[] = $user_id;
			}
		}else{
			$master_user_ids = array();		
			$sql = "SELECT id FROM ost_ps_issue_monitor WHERE master_user_id=".$passport_user_id;
			$result = db_query($sql);
			if(db_num_rows($result)>0){
				$master_user_ids[] = $user_id;
			}
		}
		$master_user_ids = array_filter($master_user_ids);
		if($master_user_ids){
			$support_owners_names = array();
			$supports=array();
			foreach ($master_user_ids as $master_user_id) {
				$master_user_id_local = $master_user_id;
				$support_owners_names[]= MaximumTicketsPlugin::getUserNameById($master_user_id);
				$master_user_id = MaximumTicketsPlugin::getPassportIdById($master_user_id);
				$sql ="SELECT mo.*, pr.* FROM `ost_ps_issue_monitor` mo LEFT JOIN `ost_ticket_priority` pr ON (mo.`prioritysupport_order_type_id` = pr.`priority_id`) WHERE mo.`master_user_id` = ".$master_user_id." ORDER BY mo.`active` DESC, pr.`priority_urgency`, mo.`expiry_date`";
		        $result = db_query($sql);
		        
		        while($row=db_fetch_row($result)){
		        	$supports[] = array('id'=>$row[0],'master_user_id'=>$row[1],'quota'=>$row[2],'issue_count'=>$row[3],'expiry_date'=>$row[4],'prioritysupport_order_type_id'=>$row[5],'active'=>$row[6],'order_id'=>$row[7]);
		        }
			}
		}
		echo '
		<table class="list" border="0" cellspacing="1" cellpadding="2" width="900">
			<thead>
				<tr>
					<th>Type</th>
					<th>Initial Quota</th>
					<th>Consumed Quota</th>
					<th>Expiry Date</th>
					<th>Status</th>
					<th>Master User Id</th>
					<th>Order Id</th>
				</tr>
			</thead>
			<tbody page="1" class="">
		';
				foreach ($supports as $support) {
					$support_type = MaximumTicketsPlugin::getPriorityDescription($support['prioritysupport_order_type_id']);
					$expiry_dates = explode(" ",$support['expiry_date']);
					$expiry_date = $expiry_dates[0];
					$expiry_date = strtotime($expiry_date);
					$expiry_date = date('F d,Y',$expiry_date);
					$supportstatus = ($support['active'] == 1) ? "Active":"Inactive";
					$order_id = $support['order_id'];
		echo '		
				<tr>					
					<th>'. $support_type.'</th>
					<td>'. $support['quota'].'</td>
					<td>'. $support['issue_count'].'</td>
					<td>'. $expiry_date.'</td>
					<td>'. $supportstatus.'</td>
					<td>'. $support['master_user_id'].'</td>
					<td>'. $support['order_id'].'</td>
				</tr>
		';
				}
		echo '		
			</tbody>
		</table>
		';
    }
    public static function getAllSupportIdsByUserId($user_id){
    	$passport_user_id = MaximumTicketsPlugin::getPassportIdById($user_id);
    	$sql = "SELECT id FROM ost_ps_issue_monitor WHERE master_user_id=".$passport_user_id;
		$result = db_query($sql);
		$support_ids = array();
		if(db_num_rows($result)>0){
			while($row = db_fetch_row($result))
				$support_ids[] = $row[0];
		}
		return $support_ids;
	}

	public static function isPluginActive($plugin_name){
		$sql = "SELECT `isactive` FROM `ost_plugin` WHERE `name`='".$plugin_name."'";
		$result = db_query($sql);
		if(db_num_rows($result)==0)
			return 0;
		$row = db_fetch_row($result);
		return $row[0];
	}

}