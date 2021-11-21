<?php
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class HelpdeskToForum extends Plugin {
    var $config_class = "HelpdeskToForumConfig";

    function bootstrap() {
        $config = $this->getConfig();
        # ----- Dynabic.Discourse credentials ---------------------
        define('HTF_HOST', $config->get('HelpdeskToForum-host'));
        define('HTF_API', $config->get('HelpdeskToForum-api'));
    }

    //Custom code to downgrade tickets 
    public static function move_ticket_to_discourse($ticket_id){
        $sql = "SELECT user_id,created FROM ".TICKET_TABLE." WHERE ticket_id=".$ticket_id;
        $result = db_query($sql);
        $row = db_fetch_row($result);
        $user_id = $row[0];
        $topic_date = $row[1];

        $sql = "SELECT address FROM ".USER_EMAIL_TABLE." WHERE user_id=".$user_id;
        $result = db_query($sql);
        $row = db_fetch_row($result);
        $topic_user_email = $row[0];

        $sql = "SELECT subject FROM ost_ticket__cdata WHERE ticket_id=".$ticket_id;
        $result = db_query($sql);
        $row = db_fetch_row($result);
        $topic_title = $row[0];

        $sql = "SELECT body FROM ".TICKET_THREAD_TABLE." WHERE ticket_id=".$ticket_id." AND thread_type='M' ";
        $result = db_query($sql);
        $row = db_fetch_row($result);
        $topic_details = $row[0];

        $sql = "SELECT staff_id,user_id,body,created FROM ".TICKET_THREAD_TABLE." WHERE ticket_id=".$ticket_id." AND (thread_type='M' OR thread_type='R' )";
        $result_replies = db_query($sql);
        
        $count = 0;
        $count2 = 0;
        $replies_arrays = array();
        while($row = db_fetch_row($result_replies)){
            if($count==0)
                $count++;
            else{
                $message_body = $row[2];
                $message_date = $row[3];
                if($row[0] == 0){
                    $sql = "SELECT address FROM ".USER_EMAIL_TABLE." WHERE user_id=".$row[1];
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $user_email = $row[0];                      
                }
                elseif($row[1] == 0){
                    $sql = "SELECT email FROM ".STAFF_TABLE." WHERE staff_id=".$row[0];
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $user_email = $row[0];                      
                }
                $reply= array(
                    "user_email" => $user_email,
                    "message_body" => $message_body,
                    "message_date" => $message_date
                );
                $replies_arrays[$count2] = $reply;
                $count2++;
            }       
        }
        
        $request_body = array(
            "topic_title" => $topic_title,
            "topic_details" => $topic_details,
            "topic_user_email" => $topic_user_email,
            "topic_date" => $topic_date,
            "topic_messages" => $replies_arrays
        );

        $request_body = json_encode((object) $request_body);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => HTF_HOST,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 100,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request_body,
            CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "osticket-import-api-key: ".HTF_API
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          return $response;
        }
    }

    public static function remove_ticket($ticket_id){
        $sql = "DELETE FROM ".TICKET_TABLE." WHERE ticket_id=".$ticket_id;
        $result1 = db_query($sql);

        $sql = "DELETE FROM ".TICKET_THREAD_TABLE." WHERE ticket_id=".$ticket_id;
        $result2 = db_query($sql);
        if($result1 AND $result2)
            return true;
        else
            return false;
    }
    //Custom code to downgrade tickets    
}