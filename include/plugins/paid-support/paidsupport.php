<?php
include_once INCLUDE_DIR.'class.user.php';
include_once INCLUDE_DIR.'class.api.php';
include_once INCLUDE_DIR.'class.ticket.php';
require_once(INCLUDE_DIR.'class.plugin.php');
include_once INCLUDE_DIR.'class.app.php';//Added to support osticket-v-1.10.4
require_once('config.php');

class PaidSupportPlugin extends Plugin {
    var $config_class = "PaidSupportPluginConfig";

    function bootstrap() {
        $config = $this->getConfig();
        //
        $whitelist = array('127.0.0.1', '::1');
        if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){//Localhost: Create static array
            $_SESSION['support_users'] = array(
                2 => array(3,2),
                4 => array(3),
                5 => array(3)
            );
        }else{//Server: Passport array defined in dynabic-passport.php
        }
        $registerClass = new Application();
        $desc = "Search Orders";
        $href = "order.php";
        $registerClass -> registerStaffApp($desc, $href, $info=array());
        //
    }
}

class PaidSupportAPIController extends ApiController {
	//    

    /* add message to an existing ticket thread */
    function message($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        $message = $this->addMessage($req);

        if(!$message)
            return $this->exerr(500, __("Unable to add a message to thread: unknown error"));

        $this->response(201, $message->getId());
    }

    /* private helper functions */

    function addMessage($data) {
        # Assign default value to source if not defined, or defined as NULL
        $data['source'] = isset($data['source']) ? $data['source'] : 'API';

        $errors = array();
        $ticket_id = Ticket::getIdByNumber($data['ticket_number']);
        $message = null;
        if($ticket_id) {
            $ticket = new Ticket($ticket_id);
            $message = $ticket->postMessage($data);
        }
        return $message;
    }
    
    function monitor($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        if(!isset($req['master_user_id']))
            return $this->exerr(400, __("master_user_id is required field"));
        if(!isset($req['quota']))
            return $this->exerr(400, __("quota is required field"));
        if(!isset($req['issue_count']))
            return $this->exerr(400, __("issue_count is required field"));
        if(!isset($req['expiry_date']))
            return $this->exerr(400, __("expiry_date is required field"));
        if(!isset($req['prioritysupport_order_type_id']))
            return $this->exerr(400, __("prioritysupport_order_type_id is required field"));
        if(!isset($req['active']))
            return $this->exerr(400, __("active is required field"));
        if(!isset($req['order_id']))
            return $this->exerr(400, __("order_id is required field"));

        $monitor = $this->addMonitor($req);
        $user = $this->createUser($req["master_user_id"]);
        if(!$monitor)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $this->response(201,'{"Code": 200,"Status": "OK"}');
    }

    /* private helper functions */

    function createUser($master_user_id){
        $response = MaximumTicketsPlugin::getPassportData($master_user_id);
        if($response){
            $sql = "SELECT user_id FROM ost_user_email WHERE address='".$response->email."'";
            $result = db_query($sql);
            if(db_num_rows($result)==0){
                $data = array(
                    "email" => $response->email,
                    0 => $response->email,
                    "name" => $response->first_name." ".$response->last_name,
                    1 => $response->first_name." ".$response->last_name,
                    "phone" => $response->main_addr->phone_number,
                    2 => $response->main_addr->phone_number,
                    "notes" => NULL, 
                    3 => NULL 
                    );
                $user = User::fromVars($data);

                $vars = array(
                    "id" => $user,
                    "backend" => "client",
                    "username" => $response->username,
                    "passwd1" => "1234qwea",
                    "passwd2" => "1234qwea",
                    "timezone_id" => 21,
                    );
                $acct = UserAccount::register($user, $vars, $errors=array());

                $sql='UPDATE '.USER_EMAIL_TABLE.' SET passport_user_id = '.$response->id.' WHERE address="'.$response->email.'"';
                if(db_query($sql)){
                    return $user;
                }
            }
        }
        else
            return 0;
    }

    function addMonitor($data) {
        $master_user_id = $data['master_user_id'];
        $quota = $data['quota'];
        $issue_count = $data['issue_count'];
        $expiry_date = "'".$data['expiry_date']."'";
        $prioritysupport_order_type_id = $data['prioritysupport_order_type_id'];
        $active = $data['active'];
        $order_id = "'".$data['order_id']."'";
        $sql = "INSERT INTO ost_ps_issue_monitor (master_user_id, quota, issue_count, expiry_date, prioritysupport_order_type_id, active, order_id) VALUES (". $master_user_id .", ". $quota .", ". $issue_count .", ". $expiry_date .", ". $prioritysupport_order_type_id .", ". $active .", ". $order_id .")";
        $res = db_query($sql);
        $errors = array();
        if($res)
            return $res;
        else
            return false;
    }

    /* add support method for support API call */
    function monitor_log($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        if(!isset($req['user_id']))
            return $this->exerr(400, __("user_id is required field"));
        if(!isset($req['action']))
            return $this->exerr(400, __("action is required field"));
        if(!isset($req['action_date']))
            return $this->exerr(400, __("action_date is required field"));
        if(!isset($req['comment']))
            return $this->exerr(400, __("comment is required field"));
        if(!isset($req['asposesupport_user_id']))
            return $this->exerr(400, __("asposesupport_user_id is required field"));
        if(!isset($req['asposesupport_username']))
            return $this->exerr(400, __("asposesupport_username is required field"));
        $monitor_log = $this->addMonitor_log($req);

        if(!$monitor_log)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $this->response(201,'{"Code": 200,"Status": "OK"}');
    }

    /* private helper functions */

    function addMonitor_log($data) {
    	$user_id = $data['user_id'];
    	$action = $data['action'];
    	$action_date = "'".$data['action_date']."'";
    	$comment = $data['comment'];
    	$asposesupport_user_id = $data['asposesupport_user_id'];
    	$asposesupport_username = $data['asposesupport_username'];
        $sql = "INSERT INTO ost_ps_issue_monitor_log (user_id, action, action_date, comment, asposesupport_user_id, asposesupport_username) VALUES (". $user_id .", '". $action ."', ". $action_date .", '". $comment ."', ". $asposesupport_user_id .", '". $asposesupport_username ."' )";
        $res = db_query($sql);
        $errors = array();
        if($res)
            return $res;
        else
            return false;
    }

    /* changeOwnership method for changeownership API call (case 2)*/
    function changeOwnership($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        if($req['reciever_id']==NULL)
            return $this->exerr(400, __("reciever_id is required field"));
        if($req['support_id']==NULL)
            return $this->exerr(400, __("support_id is required field"));
        if($req['number_of_issues_to_transfer']==NULL)
            return $this->exerr(400, __("number_of_issues_to_transfer is required field"));

        $changeOwnership = $this->changeOwnership_func($req);

        if(!$changeOwnership)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $this->response(201,'{"Code": 200,"Status": "OK"}');
    }

    /* private helper functions */

    function changeOwnership_func($data) {
        $reciever_id = $data['reciever_id'];
        $support_id = $data['support_id'];
        $number_of_issues_to_transfer = $data['number_of_issues_to_transfer'];
        $res = $this->changeOwnership_update($support_id,$number_of_issues_to_transfer);
        $errors = array();
        if($res){
          $master_user_id = $reciever_id;
          $quota = $number_of_issues_to_transfer;
          $issue_count = 0;
          $expiry_date = $this->getExpiryDateById($support_id);
          $prioritysupport_order_type_id = $this->getPrioritySupport_order_type_idById($support_id);
          $active = 1;
          $order_id = $this->getOrderIdById($support_id);
          $arr = array(
            "master_user_id" => $master_user_id,
            "quota" => $quota,
            "issue_count" => $issue_count,
            "expiry_date" => $expiry_date,
            "prioritysupport_order_type_id" => $prioritysupport_order_type_id,
            "active" => $active,
            "order_id" => $order_id
          );
          $res = $this->addMonitor($arr);
          if($res)
            return $res;
          else
            return false;
        }
        else
          return false;
    }

    function changeOwnership_update($supportId,$number_of_issues_to_transfer){
      $sql = "SELECT quota FROM ost_ps_issue_monitor WHERE id=".$supportId;
      $result = db_query($sql);
      $row = db_fetch_row($result);
      $quota = $row[0]-$number_of_issues_to_transfer;
      $sql = "UPDATE ost_ps_issue_monitor SET quota = ".$quota." WHERE id=".$supportId;
      $res = db_query($sql);
      return $res;
    }

    function getExpiryDateById($support_id){
      $sql = "SELECT expiry_date FROM ost_ps_issue_monitor WHERE id=".$support_id;
      $result = db_query($sql);
      $row = db_fetch_row($result);
      return $row[0];
    }

    function getPrioritySupport_order_type_idById($support_id){
      $sql = "SELECT prioritysupport_order_type_id FROM ost_ps_issue_monitor WHERE id=".$support_id;
      $result = db_query($sql);
      $row = db_fetch_row($result);
      return $row[0];
    }

    function getOrderIdById($support_id){
      $sql = "SELECT order_id FROM ost_ps_issue_monitor WHERE id=".$support_id;
      $result = db_query($sql);
      $row = db_fetch_row($result);
      return $row[0];
    }

    /* validateSubscription method for support API call */
    function validateSubscription($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        if($req['user_email']==NULL AND $req['user_id']==NULL)
            return $this->exerr(400, __("user_email or user_id is required field"));

        $response = $this->validateSubscription_func($req);

        if(!$response)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $response = array(
            "Code" => 200,
            "Status" => "OK"
            );
        $this->response(201,json_encode($response));
    }

    /* private helper functions */

    function validateSubscription_func($data) {
        $user_email = $data['user_email'];
        $user_id = $data['user_id'];
        if(!isset($user_id) OR empty($user_id)){
	        $sql = "SELECT passport_user_id FROM ".USER_EMAIL_TABLE." WHERE address='".$user_email."'";
	        $result = db_query($sql);
	        $row = db_fetch_row($result);
	        $user_id = $row[0];
	    }
        $master_user_id = $user_id;        
        $sql = "SELECT * FROM ost_ps_issue_monitor WHERE master_user_id=".$master_user_id;
        $result = db_query($sql);
        while(list($id,$master_user_id,$quota,$issue_count,$expiry_date,$prioritysupport_order_type_id,$active,$order_id)=db_fetch_row($result)){
            $supports[] = array('id'=>$id,'master_user_id'=>$master_user_id,'quota'=>$quota,'issue_count'=>$issue_count,'expiry_date'=>$expiry_date,'prioritysupport_order_type_id'=>$prioritysupport_order_type_id,'active'=>$active,'order_id'=>$order_id);
        }

        foreach ($supports as $key=>$support) {
        	if(MaximumTicketsPlugin::isSupportExpired($support['id']) OR !MaximumTicketsPlugin::isSupportActive($support['id']) OR ($support['quota'] <= $support['issue_count']))
        		unset($supports[$key]);
        }
        if(count($supports)==0)
        	return false;
        else
        	return true;
    }
    
    /* transferOwnership method for transferownership API call (Case 1)*/
    function transferOwnership($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        if(!isset($req['reciever_id']))
            return $this->exerr(400, __("reciever_id is required field"));
        if(!isset($req['support_id']))
            return $this->exerr(400, __("support_id is required field"));

        $transferOwnership = $this->transferOwnership_func($req);

        if(!$transferOwnership)
            return $this->exerr(500, __("Unable to transfer the ownership: unknown error"));

        $this->response(201, "Success: Ownership has been transfered!");
    }

    /* private helper functions */

    function transferOwnership_func($data) {
        $reciever_id = $data['reciever_id'];
        $support_id = $data['support_id'];
        $sql = "UPDATE ost_ps_issue_monitor SET master_user_id = ".$reciever_id." WHERE id=".$support_id;
        $res = db_query($sql);
        return $res;
    }

    /* get_support method for support API call */
    function get_support($master_user_id) {
        if(!($key=$this->requireApiKey()))
            return $this->exerr(401, __('API key not authorized'));
        $response = PaidSupportAPIController::get_support_func($master_user_id);
        if(!$response)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $response = array(
            "Code" => 200,
            "Status" => "OK",
            "supports" => $response
            );
        $this->response(201,json_encode($response));
    }

    function get_support_func($master_user_id){
        $sql = "SELECT * FROM ost_ps_issue_monitor WHERE master_user_id=".$master_user_id;
        $result = db_query($sql);
        if(!result)
            return false;
        while(list($id,$master_user_id,$quota,$issue_count,$expiry_date,$prioritysupport_order_type_id,$active,$order_id)=db_fetch_row($result)){
            $supports[] = array('id'=>$id,'master_user_id'=>$master_user_id,'quota'=>$quota,'issue_count'=>$issue_count,'expiry_date'=>$expiry_date,'prioritysupport_order_type_id'=>$prioritysupport_order_type_id,'active'=>$active,'order_id'=>$order_id);
        }
        return $supports;
    }

    /* UPDATE support method for support API call */
    function update_support($format) {
        if(!($key=$this->requireApiKey()) || !$key->canCreateTickets())
            return $this->exerr(401, __('API key not authorized'));

        $req = $this->getRequest($format);
        if(!isset($req['master_user_id']))
            return $this->exerr(400, __("master_user_id is required field"));
        if(!isset($req['quota']))
            return $this->exerr(400, __("quota is required field"));
        if(!isset($req['expiry_date']))
            return $this->exerr(400, __("expiry_date is required field"));        
        if(!isset($req['active']))
            return $this->exerr(400, __("active is required field"));
        if(!isset($req['order_id']))
            return $this->exerr(400, __("order_id is required field"));
        $response = $this->update_support_func($req);
        if(!$response)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $this->response(201,'{"Code": 200,"Status": "OK"}');
    }

    /* private helper function */
    function update_support_func($data) {
    	$master_user_id = $data['master_user_id'];
        $quota = $data['quota'];
        $expiry_date = "'".$data['expiry_date']."'";
        $active = $data['active'];
        $order_id = "'".$data['order_id']."'";

        $sql = "SELECT * FROM ost_ps_issue_monitor WHERE order_id = ".$order_id;
      	$result = db_query($sql);
      	if(db_num_rows($result) == 0)
      		return false;
        $sql = "UPDATE ost_ps_issue_monitor SET master_user_id = ".$master_user_id.", quota = ".$quota.", expiry_date = ".$expiry_date.", active = ".$active.", order_id = ".$order_id." WHERE order_id = ".$order_id;
      	$res = db_query($sql);
        if($res)
            return $res;
        else
            return false;
    }
    /* UPDATE Issue count Plus method for Ajax call */
    function issue_count_plus($format) {
        $req = $this->getRequest($format);
        $id = $req['id'];
        $comment = $req['comment'];
        //$this->response(201,'{"Code": 200,"Status": "'.$comment.'","id":'.$id.'}');
        if(MaximumTicketsPlugin::issue_count_plus_func($id,$comment))
            $this->response(201,'{"Code": 200,"Status": "OK"}');
        else
            $this->response(201,'{"Code": 400,"Status": "FAIL"}');        
    }
    /* UPDATE Issue count Minus method for Ajax call */
    function issue_count_minus($format) {
        $req = $this->getRequest($format);
        $id = $req['id'];
        $comment = $req['comment'];
        //$this->response(201,'{"Code": 200,"Status": "'.$comment.'","id":'.$id.'}');
        if(MaximumTicketsPlugin::issue_count_minus_func($id,$comment))
            $this->response(201,'{"Code": 200,"Status": "OK"}');
        else
            $this->response(201,'{"Code": 400,"Status": "FAIL"}');        
    }
    /* UPDATE Issue count Minus method for Ajax call */
    function edit_log_comment($format) {
        $req = $this->getRequest($format);
        $log_id = $req['log_id'];
        $comment = $req['comment'];
        //$this->response(201,'{"Code": 200,"Status": "'.$comment.'","id":'.$id.'}');
        if(MaximumTicketsPlugin::edit_log_comment_func($log_id,$comment))
            $this->response(201,'{"Code": 200,"Status": "OK"}');
        else
            $this->response(201,'{"Code": 400,"Status": "FAIL"}');        
    }
    /* Custom Code for delete comment functionality */
    function delete_staff_comment($format) {
        $req = $this->getRequest($format);
        $id = $req['id'];
        //$this->response(201,'{"Code": 200,"Status": "'.$comment.'","id":'.$id.'}');
        if(MaximumTicketsPlugin::delete_staff_comment_func($id))
            $this->response(201,'{"Code": 200,"Status": "OK"}');
        else
            $this->response(201,'{"Code": 400,"Status": "FAIL"}');        
    }
    //
    //API listener functions for  deleting all user data: GDPR complient
    function deleteUserData($id) {
        if(!($key=$this->requireApiKey()))
            return $this->exerr(401, __('API key not authorized'));
        $response = PaidSupportAPIController::deleteUserData_func($id);
        if(!$response)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $response = array(
            "Code" => 200,
            "Status" => "OK"
            );
        $this->response(201,json_encode($response));
    }

    function deleteUserData_func($passport_id){
    	$local_id = MaximumTicketsPlugin::getUserIdByPassportId($passport_id);
    	if(!$local_id)
    		return false;
    	$ost_ps_issue_monitor = $ost_ps_issue_monitor_log = $ost_user = $ost_user_email = $ost_user_account = $ost_ticket_thread = $ost_ticket = "false";
        //Delete paid support data        
        $sql = "DELETE FROM ost_ps_issue_monitor WHERE master_user_id=".$passport_id;
        if(db_query($sql))
        	$ost_ps_issue_monitor = "true";
        //Delete issue monitor log data        
        $sql = "DELETE FROM ost_ps_issue_monitor_log WHERE user_id=".$passport_id;
        if(db_query($sql))
        	$ost_ps_issue_monitor_log = "true";
        //Delete user account
        $sql = "DELETE FROM ost_user WHERE id=".$local_id;
        if(db_query($sql))
        	$ost_user = "true";
        $sql = "DELETE FROM ost_user_email WHERE user_id=".$local_id;
        if(db_query($sql))
        	$ost_user_email = "true";
        $sql = "DELETE FROM ost_user_account WHERE user_id=".$local_id;
        if(db_query($sql))
        	$ost_user_account = "true";
        //Delete tickets attachments        
        $sql = "SELECT ticket_id FROM ost_ticket WHERE user_id=".$local_id;
        $result = db_query($sql);
        $ticket_ids = array();
        if(db_num_rows($result) > 0){
        	while($row=db_fetch_row($result)){
        		$ticket_ids[] = $row[0];
        	}
        }
        $file_ids = array();
        foreach ($ticket_ids as $ticket_id) {
        	//Get all files to be deleted
        	$sql = "SELECT file_id FROM ost_ticket_attachment WHERE ticket_id=".$ticket_id;
	        $result = db_query($sql);	        
	        if(db_num_rows($result) > 0){
	        	while($row=db_fetch_row($result)){
	        		$file_ids[] = $row[0];
	        	}
	        }
        }
        foreach ($file_ids as $file_id) {
        	$sql = "DELETE FROM ost_file_chunk WHERE file_id=".$file_id;
        	db_query($sql);
        }
        //Delete threads/comments
        $sql = "DELETE FROM ost_ticket_thread WHERE user_id=".$local_id;
        if(db_query($sql))
        	$ost_ticket_thread = "true";
        //Delete Tickets
        $sql = "DELETE FROM ost_ticket WHERE user_id=".$local_id;
        if(db_query($sql))
        	$ost_ticket = "true";
        //Add sys logs
        $title = "GDPR logs ".$passport_id;
        $log_type = "Debug";
        $deleted_data = "Deleted Data: ost_ps_issue_monitor :".$ost_ps_issue_monitor.", ost_ps_issue_monitor_log: ".$ost_ps_issue_monitor_log.", ost_user: ".$ost_user.", ost_user_email: ".$ost_user_email.", ost_user_account: ".$ost_user_account.", ost_ticket_thread: ".$ost_ticket_thread.", ost_ticket: ".$ost_ticket;
        $log = "Following data for Passport id: ".$passport_id." has been deleted as requested by user. ".$deleted_data;
        $key=$this->requireApiKey();
        $ip_address = $key->ht['ipaddr'];
        $sql='INSERT INTO '.SYSLOG_TABLE.' SET created=NOW(), updated=NOW() '
            .',title="'.$title
            .'",log_type="'.$log_type
            .'",log="'.$log
            .'",ip_address="'.$ip_address
            .'"';
        db_query($sql);
        //Add sys logs
        return true;
    }

    //API listener functions for  exporting all user data: GDPR complient
    function exportUserData($id) {
        if(!($key=$this->requireApiKey()))
            return $this->exerr(401, __('API key not authorized'));
        $response = PaidSupportAPIController::exportUserData_func($id);
        if(!$response)
            $this->response(400,'{"Code": 400,"Status": "FAIL"}');
        $response = array(
            "Code" => 200,
            "Status" => "OK",
            "data" => $response
            );
        $this->response(201,json_encode($response,JSON_HEX_TAG));
    }

    function exportUserData_func($passport_id){
    	$local_id = MaximumTicketsPlugin::getUserIdByPassportId($passport_id);
    	//Get Ticket data
 		$sql = "SELECT ticket_id FROM ost_ticket WHERE user_id=".$local_id;
        $result = db_query($sql);
        $ticket_ids = array();
        if(db_num_rows($result) > 0){
        	while($row=db_fetch_row($result)){
        		$ticket_ids[] = $row[0];
        	}
        }
        $data = array();
        foreach ($ticket_ids as $ticket_id) {
    		$sql = "SELECT subject FROM ost_ticket__cdata WHERE ticket_id=".$ticket_id;
    		$result = db_query($sql);
    		$row = db_fetch_row($result);
       		$subject = $row[0];

       		$comments = array();
       		$sql = "SELECT body FROM ost_ticket_thread WHERE user_id=".$local_id." AND ticket_id=".$ticket_id;
	        $result = db_query($sql);
	        if(db_num_rows($result) > 0){
	        	while($row=db_fetch_row($result)){
	        		$comments[] = $row[0];		
	        	}
	        }
       		$detail = $comments[0];
       		array_shift($comments);
       		$ticket = array(
       			"Summary" => $subject,
       			"Detail" => $detail,
       			"Comments" => $comments
       			);
       		$data[] = $ticket;
       	}   	
    	return $data;
    }
    //API listener functions for  exporting all user data: GDPR complient
}