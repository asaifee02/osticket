<style>
.error {color: #FF0000;}
</style>
<?php
require_once('staff.inc.php');
$nav->setTabActive('apps');
require_once(STAFFINC_DIR.'header.inc.php');
?>
<h2>Search tickets by Issue ids.</h2>
<form id="searchtickets" action="#" name="searchtickets" method="post">
<?php csrf_token(); ?>
    <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>
                <div>
                    <br/><div class="faded" style="padding-left:0.15em">
                    <input type="text" name="issue_id" id="issue_id" size="100" placeholder="Enter Issue id here..." value="<?php echo isset($_POST['issue_id']) ? $_POST['issue_id'] : '' ?>" required />
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
if(isset($_POST['issue_id'])){
	$issue_id = $_POST['issue_id'];
	$ticket_id = SearchTicketsPlugin::SearchTicketsByIssueId($issue_id);
    if($ticket_id){        
        echo '<div id="msg_notice">Ticket Found.</div>';
        echo $ticket_id;
    }else{
        echo '<div id="msg_error">There is no ticket with given Issue key.</div>';
    }
}

require_once(STAFFINC_DIR.'footer.inc.php');
?>