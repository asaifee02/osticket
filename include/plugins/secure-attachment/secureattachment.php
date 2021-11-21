<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class SecureAttachmentPlugin extends Plugin {
    var $config_class = "SecureAttachmentPluginConfig";

    function bootstrap() {
        $config = $this->getConfig();

        $secureattachment = json_decode($config->get('secureattachment-enabled'));
    }
	
	public static function checkClientSession() {
		return $_SESSION[':token']['client'];
	}
	
	public static function checkStaffSession() {
		return $_SESSION[':token']['staff'];
	}
	
	public static function getAttachmentId($key) {
		$sql='SELECT id FROM '.FILE_TABLE.' WHERE `key`='.db_input($key);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id)=db_fetch_row($res);

        return $id;
	}
	
	public static function checkAttachment($user_id, $key) {
		$master_user_ids = MaximumTicketsPlugin::getMasterUserIdsByUserId($user_id);
		if(isset($master_user_ids)){
			$master_user_ids[] = $user_id;
			$allowed_users = $master_user_ids;
		}
		else
			return "";
		
		foreach ($allowed_users as $user) {
			$fileId = self::getAttachmentId($key);
			$sql='SELECT ticket.ticket_id FROM ost_attachment as attachment, ost_thread as thread, ost_thread_entry as entry, '.TICKET_TABLE.' as ticket 
			      WHERE 
				  	ticket.ticket_id = thread.object_id
				  AND
				  	thread.id = entry.thread_id
				  AND
				  	entry.id = attachment.object_id
				  AND
				  	ticket.user_id = '.db_input($user).'
				  AND
				  	attachment.file_id='.db_input($fileId);
	        if(($res=db_query($sql)) && db_num_rows($res)) {
	            list($ticketId)=db_fetch_row($res);
	            return $ticketId;
			}
		}
		//Allow support owner to access their sub-account attachment created using his support
        $sql='SELECT ticket.ps_id FROM ost_attachment as attachment, ost_thread as thread, ost_thread_entry as entry, '.TICKET_TABLE.' as ticket 
			      WHERE 
				  	ticket.ticket_id = thread.object_id
				  AND
				  	thread.id = entry.thread_id
				  AND
				  	entry.id = attachment.object_id
				  AND
				  	attachment.file_id='.db_input($fileId);
		if(($res=db_query($sql)) && db_num_rows($res)){
	        $ps_id = db_fetch_row($res)[0];
		}else{
			return "";
		}
		$support_ids = MaximumTicketsPlugin::getAllSupportIdsByUserId($user_id);
        if(empty($support_ids))
        	return "";
        if(in_array($ps_id, $support_ids))
        	return true;
		//Allow support owner to access their sub-account attachment created using his support
		return "";
	}
			
}