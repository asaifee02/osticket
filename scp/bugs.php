<style>
.error {color: #FF0000;}
</style>
<?php
require_once('staff.inc.php');
$nav->setTabActive('apps');
require_once(STAFFINC_DIR.'header.inc.php');
?>
<h2>Generate forum notification about the bugs closed</h2>
<form id="bugnotification" action="#" name="bugnotification" method="post">
<?php csrf_token(); ?>
    <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>
                <div>
                    <br/><div class="faded" style="padding-left:0.15em"><?php
                    echo __('Enter product download URL and choose/import issue list.'); ?></div>
                    <input type="text" name="download-url" id="download-url" size="100" placeholder="Enter URL here..." value="<?php echo isset($_POST['download-url']) ? $_POST['download-url'] : '' ?>" required />
                    <span class="error">*</span>
                </div>
            </td>
        </tr>
    </table>
    <p style="padding-left:15px;">
    	<span class="buttons">
        	<input type="submit" value="<?php echo __('Search'); ?>">
        </span>
    </p>
</form>
<?php
if(isset($_POST['download-url'])){
	$download_url = $_POST['download-url'];
	$BNTPlugin = new BNTPlugin();
	$config = $BNTPlugin->getConfig();
	$BNT_endpoint = $config->get('BNT-endpoint');
	$BNT_api_key = $config->get('BNT-api-key');

	//
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $BNT_endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{  \n   \"URL\": \"".$download_url."\"  \n }");
	curl_setopt($ch, CURLOPT_POST, 1);

	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Accept: application/json';
	$headers[] = 'Apikey: '.$BNT_api_key;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch);
	} else {
		$issues = json_decode($result);
		
		echo '<form id="updatednotes" action="#" name="updatednotes" method="post">';
			csrf_token();
			echo '<br/><div class="faded" style="padding-left:0.15em">';
                 echo __('Issues found:');
            echo '</div>';
            echo "<input type = 'hidden' name='d_url' value =".$download_url.">";
			echo "<textarea name='issues' rows='15' cols='100'>";
			foreach ($issues as $issue) {
				echo $issue->key.", ";
			}
			echo "</textarea>";
			echo "
				<h3>Custom Notification template.</h3>
				<ul>
					<li><b>{0}</b> stands for the URL you specified above</li>
					<li><b>{1}</b> stands for the list of issues fixed and mentioned in the specified ticket</li>
				</ul>
			";
			echo '
				<textarea name="template" rows="7" cols="100"><p>The issues you have found earlier (filed as {1}) have been fixed in {0}.</p><hr size="1" align="left" width="25%"><span style="text-color:gray">This message was posted using Bug Notification Tool from <a href="https://downloads.aspose.com/">Downloads module</a> by Aspose Notifier.</span>
				</textarea>
			';
			echo '<div class="buttons" style="padding-left:15px;padding-top:15px;">
        			<input type="submit" value="Notify Users">
    			</div>';
		echo '</form>';
	}
}	
if(isset($_POST['issues'])){
	//echo $_POST['issues'];
	$issues_array = explode(",", $_POST['issues']);
	$template = $_POST['template'];
	$d_url = $_POST['d_url'];
	$release = '<a href="'.$d_url.'">this release</a>';
	$template = str_replace("{0}",$release,$template);
	// echo "issues_array<br>";
	// 	print_r($issues_array);
	// echo "<br>issues_array<br>";

	$sql3 = "SELECT firstname,lastname,email FROM ".STAFF_TABLE." WHERE staff_id=".$_SESSION['_auth']['staff']['id'];
	$res3 = db_query($sql3);
	$row3 = db_fetch_row($res3);
	$staff_name = $row3[0]." ".$row3[1];
	$staff_email = $row3[2];

	$ticket_issues = array();
	foreach ($issues_array as $issue) {
		$issue = trim($issue);
		$sql = "SELECT issue_id,ticket_id FROM ".TICKET_JIRA_TABLE." WHERE issue_id LIKE '%".$issue."%'";
		$res = db_query($sql);
		while($row = db_fetch_row($res)) {
			if( (strpos($row[0], $issue)) OR $row[0] == $issue){
				$ticket_id = $row[1];
				$ticket_issues[$ticket_id][] = $issue;
			}
		}
	}	
	foreach ($ticket_issues as $key => $ticket_issue) {
		$issueIds = "";
		foreach ($ticket_issue as $issueId) {
			$issueIds.= $issueId.",";
		}
		$issueIds = rtrim($issueIds,",");
		$ticket_template = $template;
		$ticket_template = str_replace("{1}",$issueIds,$ticket_template);
		$sql1 = "SELECT `user_id`,`number` FROM ".TICKET_TABLE." WHERE ticket_id=".$key;
		$res1 = db_query($sql1);
		if(db_num_rows($res1)){
			$row1 = db_fetch_row($res1);
			$userId = $row1[0];
			$ticket_number = $row1[1];

			$sql2 = "SELECT address FROM ".USER_EMAIL_TABLE." WHERE user_id=".$userId;
			$res2 = db_query($sql2);
			if(db_num_rows($res2)){
				$row2 = db_fetch_row($res2);
				$email2 = $row2[0];

				$to = $email2.",".$staff_email;

				$sql = "SELECT priority FROM ost_ticket__cdata WHERE ticket_id=".$key;
                $res = db_query($sql);
                $row = db_fetch_row($res);
                $priority = $row[0];
                $sql = "SELECT priority_desc FROM ost_ticket_priority WHERE priority_id=".$priority;
                $res = db_query($sql);
                $row = db_fetch_row($res);
                $priority_desc = $row[0];
				$subject = $priority_desc." [#".$ticket_number."]";
				$message = $ticket_template;

				$sql4 = 'SELECT  `value` FROM  `ost_config` WHERE  `key` =  "default_email_id"';
				$res4 = db_query($sql4);
				$row4 = db_fetch_row($res4);
				$default_email_id = $row4[0];
				$email3=null;
			    $email3=Email::lookup($default_email_id);
			    if($email3){
			        $email3->send($to,$subject, Format::sanitize($message), null, array('reply-tag'=>false));
			    }			
			    $sql5 = "SELECT id FROM ost_thread WHERE object_id=".$key;
			    $res5 = db_query($sql5);
				$row5 = db_fetch_row($res5);
				$thread_id = $row5[0];
			
				//echo "<br>Found: issue id: ".$issue." Ticket id: ".$row[1];
				$sql = "INSERT INTO ost_thread_entry (thread_id, staff_id, type, poster, source, title, body, created) VALUES (". $thread_id .", ".$_SESSION['_auth']['staff']['id']." ,'M', '".$staff_name."' ,'BNT' , 'Bug Notification Tool notes', '{$ticket_template}', NOW() )";				
				db_query($sql);
				if(DynabicRedminePlugin::checkIfAllIssuesClosed($key) == "Yes" ){
					$sql = "UPDATE `ost_ticket` SET `status_id`=2 WHERE ticket_id = ".$key;
					db_query($sql);
				}
			}
		}
	}
	if(empty($ticket_issues)){
		echo '<div id="msg_error">No ticket found with given issue ids.</div>';
	}else{
		$ticket_ids = "";
		foreach ($ticket_issues as $key => $ticket_issue) {
			$ticket_ids .= $key.", ";
		}
		echo '<div id="msg_notice">Added notes to following tickets successfully. Ticket ids: '.$ticket_ids.'</div>';
	}	
}

require_once(STAFFINC_DIR.'footer.inc.php');
?>