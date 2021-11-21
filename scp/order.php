<style>
.error {color: #FF0000;}
</style>
<?php
require_once('staff.inc.php');
$nav->setTabActive('apps');
require_once(STAFFINC_DIR.'header.inc.php');
?>
<h2>Search Order by Order id.</h2>
<form id="searchtickets" action="#" name="searchtickets" method="GET">
    <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>
                <div>
                    <br/><div class="faded" style="padding-left:0.15em">
                    <input type="text" name="order_id" id="order_id" size="100" placeholder="Enter Order id here..." value="<?php echo isset($_GET['order_id']) ? $_GET['order_id'] : '' ?>" required />
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
if(isset($_GET['order_id'])){

    $order_id = $_GET['order_id'];
    if(isset($_GET['action']) && $_GET['action']=='edit'){
        echo "<h3>Update Support Record</h3>";
        echo '
            <script>
                $( function() {
                    $( "#datepicker" ).datepicker();
                } );
            </script>

            <form action="" method="get">
                Date: <input type="text" name="date" id="datepicker">
                <input type="hidden" name="order_id" value="'.$order_id.'">
                <input type="submit" value="Update">
            </form>
            <form action="" method="get">
                Quota: <input type="number" name="quota">
                <input type="hidden" name="order_id" value="'.$order_id.'">
                <input type="submit" value="Update">
            </form>
        ';
    }elseif (isset($_GET['date'])){
        $date = $_GET['date'];

        $sql = "SELECT * FROM ost_ps_issue_monitor WHERE order_id='".$order_id."'";
        $result = db_query($sql);
        if(db_num_rows($result)){
            $row = db_fetch_row($result);          
        }
        $old_date = $row[4];
        $master_user_id = $row[0];

        $input = $date.' 00:00:00'; 
        $date = strtotime($input); 
        $date = date('Y-m-d H:i:s', $date); 



        $sql2 = "UPDATE ost_ps_issue_monitor SET expiry_date = '".$date."' WHERE order_id ='".$order_id."'";
        db_query($sql2);

        $staff_id = $_SESSION['_auth']['staff']['id'];
        $staff_username = $_SESSION['_auth']['staff']['key'];
        $staff_username = explode(":", $staff_username);
        $staff_username = $staff_username[1];

        $comment = "Date has been updated from ".$old_date." to ".$date." for order id: ".$order_id;

        $sql1 = "INSERT INTO `ost_ps_issue_monitor_log`(`user_id`, `action`, `action_date`, `comment`, `asposesupport_user_id`, `asposesupport_username`) VALUES (".$master_user_id.",'Expiry Updated',NOW(),'".$comment."',".$staff_id.",'".$staff_username."')";
        $result1 = db_query($sql1);
        if($result1){
            echo '<div id="msg_notice">Order Updated</div>';
        }
    }elseif (isset($_GET['quota'])){
        $quota = $_GET['quota'];

        $sql = "SELECT * FROM ost_ps_issue_monitor WHERE order_id='".$order_id."'";
        $result = db_query($sql);
        if(db_num_rows($result)){
            $row = db_fetch_row($result);          
        }
        $old_quota = $row[2];
        $master_user_id = $row[0];

        $input = $date.' 00:00:00'; 
        $date = strtotime($input); 
        $date = date('Y-m-d H:i:s', $date); 



        $sql2 = "UPDATE ost_ps_issue_monitor SET quota = '".$quota."' WHERE order_id ='".$order_id."'";
        db_query($sql2);

        $staff_id = $_SESSION['_auth']['staff']['id'];
        $staff_username = $_SESSION['_auth']['staff']['key'];
        $staff_username = explode(":", $staff_username);
        $staff_username = $staff_username[1];

        $comment = "Quota has been updated from ".$old_quota." to ".$quota." for order id: ".$order_id;;

        $sql1 = "INSERT INTO `ost_ps_issue_monitor_log`(`user_id`, `action`, `action_date`, `comment`, `asposesupport_user_id`, `asposesupport_username`) VALUES (".$master_user_id.",'Quota Updated',NOW(),'".$comment."',".$staff_id.",'".$staff_username."')";
        $result1 = db_query($sql1);
        if($result1){
            echo '<div id="msg_notice">Order Updated</div>';
        }
    }

    $order_id = $_GET['order_id'];
    $sql = "SELECT * FROM ost_ps_issue_monitor WHERE order_id='".$order_id."'";
    $result = db_query($sql);
    if(db_num_rows($result)>1){
        echo '<div id="msg_error">There are more than one records available with given Order Id. Kindly delete duplicates!</div>';
    }
    if(db_num_rows($result)){
        echo '<div id="msg_notice">Order Found</div>';
        $row = db_fetch_row($result);
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
                    <th>Update</th>
                </tr>
            </thead>
            <tbody page="1" class="">
        ';
                    $support_type = MaximumTicketsPlugin::getPriorityDescription($row[5]);
                    $expiry_dates = explode(" ",$row[4]);
                    $expiry_date = $expiry_dates[0];
                    $expiry_date = strtotime($expiry_date);
                    $expiry_date = date('F d,Y',$expiry_date);
                    $supportstatus = ($row[6] == 1) ? "Active":"Inactive";
        echo '      
                <tr>                    
                    <th>'. $support_type.'</th>
                    <td>'. $row[2].'</td>
                    <td>'. $row[3].'</td>
                    <td>'. $expiry_date.'</td>
                    <td>'. $supportstatus.'</td>
                    <td>'. $row[1].'</td>
                    <td>'. $row[7].'</td>
                    <td><a href="/scp/order.php?order_id='.$order_id.'&action=edit">Edit</a></td>
                </tr>
        ';
        echo '      
            </tbody>
        </table>
        ';
        $user_id = MaximumTicketsPlugin::getUserIdByPassportId($row[1]);
        if($user_id){
            $path = "/scp/users.php?id=".$user_id;
            $url = $_SERVER['HTTP_HOST'].$path;
            echo "<br/><b>Support Owner:</b> ";
            echo "<a href= '".$path."' >".$url."</a>";
        }else{
            echo "<br/>Support Owner does not exist! ";
        }
    }else{
        echo '<div id="msg_error">There is no Order with given Order Id.</div>';
    }    
}

require_once(STAFFINC_DIR.'footer.inc.php');
?>