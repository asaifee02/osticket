<?php
if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid()) die('Access Denied');
//
$user_id = $thisclient->getId();
$master_user_ids = MaximumTicketsPlugin::getMasterUserIdsByUserId($user_id);
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
            $supports[] = array('id'=>$row[0],'master_user_id'=>$row[1],'quota'=>$row[2],'issue_count'=>$row[3],'expiry_date'=>$row[4],'prioritysupport_order_type_id'=>$row[5],'active'=>$row[6],'master_user_id_local'=>$master_user_id_local);
        }
	}
}
?>
<script type="text/javascript" src="scp/js/bootstrap-tab.js"></script>
<link rel="stylesheet" type="text/css" href="scp/css/bootstrap.css"/>
<style type="text/css">
.info-banner {
    margin: 0;
    padding: 5px;
    margin-bottom: 10px;
    color: #3a87ad;
    border: 1px solid #bce8f1;
    background-color: #d9edf7;
}
</style>
<?php 
if($master_user_ids){	
	foreach ($supports as $support) { 
	$expiry_date = new DateTime($support['expiry_date']);
    $current_date = new DateTime (date('Y-m-d H:i:s'));
    $current_date_plus_month = $current_date->modify('+1 month');
    $support_type = MaximumTicketsPlugin::getPriorityDescription($support['prioritysupport_order_type_id']);

	$expiry_dates = explode(" ",$support['expiry_date']);
	$expiry_dat = $expiry_dates[0];
	$expiry_dat = strtotime($expiry_dat);
	$expiry_dat = date('F d,Y',$expiry_dat);

    $current_date_org = new DateTime (date('Y-m-d H:i:s'));
    if($current_date_plus_month >= $expiry_date && $expiry_date >= $current_date_org){
    	echo '
    		<div class="info-banner">
				<strong>Info!</strong> Your '.$support_type.' will be expired on '.$expiry_dat.'. 
				<a href="https://www.aspose.com/purchase/renew-order.aspx"><button>Renew Now!</button></a>.
			</div>
    	';
    }
}
?>	
<?php } ?>
<ul class="nav nav-tabs">
	<li <?php if(!isset($_GET['move_id'])) echo "class = 'active' "; ?> ><a data-toggle="tab" href="#home">Available Supports</a></li>
	<li <?php if(isset($_GET['move_id'])) echo "class = 'active' "; ?> ><a data-toggle="tab" href="#menu1">Stats</a></li>
	<!-- <li><a data-toggle="tab" href="#menu2">Tab 3</a></li> -->
</ul>

<div class="tab-content"> 
    <div id="home" class="tab-pane fade <?php if(!isset($_GET['move_id'])) echo 'active'; ?>">
      	<?php if($master_user_ids){ ?>
		<table class="table table-condensed table-striped">
			<thead>
				<tr>
					<th>Type</th>
					<th>Initial Quota</th>
					<th>Consumed Quota</th>
					<th>Expiry Date</th>
					<th>Status</th>
					<th>Master User Id</th>
					<th>Support Owner</th>
				</tr>
			</thead>
			<tbody page="1" class="">
				<?php foreach ($supports as $support) { 
					$support_type = MaximumTicketsPlugin::getPriorityDescription($support['prioritysupport_order_type_id']);

					$expiry_dates = explode(" ",$support['expiry_date']);
					$expiry_date = $expiry_dates[0];
					$expiry_date = strtotime($expiry_date);
					$expiry_date = date('F d,Y',$expiry_date);
				?>
				<tr>					
					<th><?php echo $support_type; ?></th>
					<td><?php echo $support['quota']; ?></td>
					<td><?php echo $support['issue_count']; ?></td>
					<td><?php echo $expiry_date; ?></td>
					<td><?php echo ($support['active'] == 1) ? "Active":"Inactive"; ?></td>
					<td><?php echo $support['master_user_id']; ?></td>
					<td><?php echo MaximumTicketsPlugin::getUserNameById($support['master_user_id_local']); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>		
		<br/><hr/>
		<?php if (!empty($support_owners_names)){ ?>
			<!-- <p><b>Support owners associated with this account: </b> -->
			<?php 
				$support_owners_names = implode(', ', $support_owners_names);
				//echo $support_owners_names; 
			?>
			<!-- </p> -->
		<?php } ?>
		<?php } 
		else{
			echo '
    		<div class="info-banner">
				<strong>Info!</strong> No Support Avaialable for this account! 
				<a href="https://www.aspose.com/purchase/renew-order.aspx"><button>Purchase Support Now!</button></a>.
			</div>
    		';
		}
		?> 
    </div>
    <div id="menu1" class="tab-pane fade <?php if(isset($_GET['move_id'])) echo 'active'; ?>">
    	<?php
    	if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid()) die('Access Denied');    	
    	$myTicketsStats = MaximumTicketsPlugin::getTicketsStats( $thisclient->getId() );
    	$allTicketsStats =  MaximumTicketsPlugin::getTicketsStats($thisclient->getId(),'yes' );
    	?>
		<table class="table table-condensed table-striped">
			<thead>
				<tr>
					<th>Tickets</th>
					<th>Open</th>
					<th>Resolved</th>
					<th>Closed</th>
					<th>Archived</th>
					<th>Deleted</th>
				</tr>
			</thead>
			<tbody>
				<tr>					
					<th>My Tickets</th>
					<td><?php echo $myTicketsStats['open']; ?></td>
					<td><?php echo $myTicketsStats['resolved']; ?></td>
					<td><?php echo $myTicketsStats['closed']; ?></td>
					<td><?php echo $allTicketsStats['archived']; ?></td>
					<td><?php echo $myTicketsStats['deleted']; ?></td>
				</tr>
				<tr>					
					<th>All Tickets</th>
					<td><?php echo $allTicketsStats['open']; ?></td>
					<td><?php echo $allTicketsStats['resolved']; ?></td>
					<td><?php echo $allTicketsStats['closed']; ?></td>
					<td><?php echo $allTicketsStats['archived']; ?></td>
					<td><?php echo $allTicketsStats['deleted']; ?></td>
				</tr>
			</tbody>
		</table>		
    </div>
    <!-- <div id="menu2" class="tab-pane fade">
		<h3>Menu 2</h3>
		<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
    </div> -->
</div>