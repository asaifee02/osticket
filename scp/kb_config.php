<?php
require_once('staff.inc.php');
$nav->setTabActive('apps');
require_once(STAFFINC_DIR.'header.inc.php');

$conn = new mysqli(DBHOST,DBUSER,DBPASS,DBSITES);

if(isset($_POST['submit'])){
    $category_list = array();
    foreach ($_POST as $key => $value) {
        if(strpos($key, "cat")!== FALSE){
            $category_list[] = $key;
            $tenants = "";
            foreach ($value as $tenant) {
                $tenants .= $tenant.",";
            }
            $tenants = rtrim($tenants,',');
            $query = "SELECT * FROM ".CONFIG_TABLE." WHERE `key`='".$key."'";
            $res = db_query($query);
            if(db_num_rows($res)==0){
                $query = "INSERT INTO `".CONFIG_TABLE."` (`namespace`,`key`,`value`,`updated`) VALUES('kb_config','".$key."','".$tenants."',NOW())";
                if(db_query($query)){
                    //echo "<br>data inserted";
                }
                else{
                    //echo "<br>Error in sql".$query;
                }
            }else{
                $query = "UPDATE `".CONFIG_TABLE."` SET `value` ='".$tenants."' WHERE `key`='".$key."'";
                if(db_query($query)){
                    //echo "<br>data updated";
                }
                else{
                    //echo "<br>Error in sql".$query;
                }
            }
        }
    }
    $category_list_db = array();
    $query4 = "SELECT * FROM ".FAQ_CATEGORY_TABLE;
    $res4 = db_query($query4);
    while($row4 = db_fetch_row($res4)){
        $category_list_db[] = "cat-".$row4[0];
    }
    foreach ($category_list_db as $key) {
        if(!in_array($key, $category_list)){
            $query = "UPDATE `".CONFIG_TABLE."` SET `value` ='' WHERE `key`='".$key."'";
            if(db_query($query)){
                //echo "<br>data updated";
            }
            else{
                //echo "<br>Error in sql".$query;
            }
        }
    }
}

?>
<h2>Apply Knowledgebase Categories to tenants.</h2>
<form id="searchtickets" action="#" name="searchtickets" method="post">
<?php csrf_token(); ?>
    <?php
    $query = "SELECT * FROM ".FAQ_CATEGORY_TABLE;
    $res = db_query($query);
    while($row = db_fetch_row($res)){
        echo "<h3>".$row[2]."</h3>";
        $sql1 = "select * from ost_sites where status=1";
        $res1 = $conn->query($sql1);

        $query2 = "SELECT `value` FROM `".CONFIG_TABLE."` WHERE `namespace`='kb_config' AND `key`='cat-".$row[0]."'";
        $res2 = db_query($query2);
        if($res2){
            if(db_num_rows($res2)>0){
                $row2 = db_fetch_row($res2);
                $tenants = $row2[0];
                $tenants = explode(',', $tenants);
            }else{
                $tenants=array();
            }
        }
        while($row1 = $res1->fetch_array()){
            if(in_array($row1[2], $tenants)){
                echo '<br><input style="margin-left:40px;" type="checkbox" name="cat-'.$row[0].'[]" value="'.$row1[2].'" checked>'.$row1[2];
            }else{
                echo '<br><input style="margin-left:40px;" type="checkbox" name="cat-'.$row[0].'[]" value="'.$row1[2].'">'.$row1[2];
            }
        }
    }
    ?>
    <br><br><input type="submit" name="submit" value="<?php echo __('Save'); ?>">
</form>
<?php

require_once(STAFFINC_DIR.'footer.inc.php');
?>