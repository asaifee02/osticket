<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class SearchTicketsPlugin extends Plugin {
    var $config_class = "SearchTicketsPluginConfig";
	 /**
     * The Jira WSDL endpoint.
     */

    function bootstrap() {
        $config = $this->getConfig();

        # ----- Dynabic.Jira credentials ---------------------
        $BNT = json_decode($config->get('SearchTickets'));
		//if($BNT) {
		//}
		$registerClass = new Application();
		$desc = "Search Tickets";
		$href = "search.php";
		$registerClass -> registerStaffApp($desc, $href, $info=array());
    }

    public static function SearchTicketsByIssueId($issueKey){
    	$siteURL = $_SERVER['SERVER_NAME'];
    	$sql = "SELECT ticket_id FROM ".TICKET_JIRA_TABLE." WHERE issue_id LIKE '%".$issueKey."%'";
		$res = db_query($sql);
		if(db_num_rows($res)>0) {
			$ticket_list = '
				<table class="list" border="0" cellspacing="1" cellpadding="2" width="940">
				    <thead>
				        <tr>
				            <th width="100">
				                Ticket Id
				            </th>
				            <th width="300">
				                Ticket URL
				            </th>
				        </tr>
				    </thead>
				    <tbody>
			';
			while($row = db_fetch_row($res)){
				$pageURL = "http://".$siteURL."/scp/tickets.php?id=".$row[0];
				$ticket_list.='			
				        <tr>
				            <td nowrap="">'.$row[0].'</td>
				            <td nowrap=""><a href='.$pageURL.'>'.$pageURL.'</a></td>
				        </tr>
				';				    
			}
			$ticket_list.='
					</tbody>
				</table>    
			';
			return $ticket_list;
		}	
		else
			return 0;
    }
}
