<?php
require_once('lib/vendor/autoload.php');
require_once('lib/OpenIDConnectClient.php');

class DynabicPassportAuth {
    var $config;
    var $access_token;

    function __construct($config) {
        $this->config = $config;
    }

    function triggerAuth() {
        $self = $this;
        $oidc = new OpenIDConnectClient(
                                $this->config->get('dp-url'),
                                $this->config->get('dp-client-id'),
                                $this->config->get('dp-client-secret'));
        if($oidc->authenticate()) {
            return $oidc;
        } else {
            return false;
        }
    }
}

class DynabicPassportStaffAuthBackend extends ExternalStaffAuthenticationBackend {
    static $id = "dynabic.passport.staff";
    static $name = "Dynabic.Passport";

    //static $sign_in_image_url = "https://developers.google.com/+/images/branding/sign-in-buttons/White-signin_Long_base_44dp.png";
    static $service_name = "Dynabic.Passport";

    var $config;

    function __construct($config) {
        $this->config = $config;
        $this->dp = new DynabicPassportAuth($config);
    }

    function signOn() {

        // TODO: Check session for auth token
        if (isset($_SESSION[':oauth']['email'])) {
            if (($staff = StaffSession::lookup( $_SESSION[':oauth']['email']))
                && $staff->getId()
            ) {
                if (!$staff instanceof StaffSession) {
                    // osTicket <= v1.9.7 or so
                    $staff = new StaffSession($staff->getId());
                }
                return $staff;
            }
            else{
                header('Location: /noaccount.php');
            }
        }
    }

    static function signOut($user) {
        parent::signOut($user);
        unset($_SESSION[':oauth']);
    }

    function triggerAuth() {
        require_once INCLUDE_DIR . 'class.json.php';
        parent::triggerAuth();
        $dp = $this->dp->triggerAuth();
        if($dp) {
            $userInfo = $dp->requestUserInfo();
            $user_email = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
            //Log in to Dynabic.Menu when user logs into OsTicket
            $user_fname = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'};
            $user_lname = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'};
            $_SESSION['UserInfo'] = "&FullName=".$user_fname." ".$user_lname."&Email=".$user_email;
            $_SESSION[':oauth']['email'] = $user_email;
            //Log in to Dynabic.Menu when user logs into OsTicket
            Http::redirect(ROOT_PATH . 'scp');
        }
        return;
    }
}

class DynabicPassportClientAuthBackend extends ExternalUserAuthenticationBackend {
    static $id = "dynabic.passport.client";
    static $name = "Dynabic.Passport";

    //static $sign_in_image_url = "https://developers.google.com/+/images/branding/sign-in-buttons/Red-signin_Long_base_44dp.png";
    static $service_name = "Dynabic.Passport";

    function __construct($config) {
        $this->config = $config;
        $this->dp = new DynabicPassportAuth($config);
    }

    function supportsInteractiveAuthentication() {
        return false;
    }

    function signOn() {
        // TODO: Check session for auth token
        if (isset($_SESSION[':oauth']['email'])) {
            if (($acct = ClientAccount::lookupByUsername($_SESSION[':oauth']['email']))
                    && $acct->getId()
                    && ($client = new ClientSession(new EndUser($acct->getUser())))){
                return $client;
            }   
            else{
                // TODO: Prepare ClientCreateRequest            
                $master_user_ids = array();
                $master_user_ids[] = $_SESSION[':oauth']['userInfo']->{'PSP.UserId'};
                $total = $_SESSION[':oauth']['userInfo']->{'PSP.MasterAccount.Total'};
                if($total > 0){
                    for ($i=1; $i <= $total; $i++) { 
                        $user_data = explode(',', $_SESSION[':oauth']['userInfo']->{'PSP.MasterAccount.'.$i});
                        $master_user_ids[] = $user_data[0];
                    }
                }
                $found = 0; 
                foreach ($master_user_ids as $master_user_id) {
                    $sql = "SELECT id FROM ost_ps_issue_monitor WHERE master_user_id='".$master_user_id."'";
                    $result = db_query($sql);
                    if(db_num_rows($result)>0){
                        $found = 1;
                    }
                }
                if($found){
                    $name = $_SESSION[':oauth']['fname']." ".$_SESSION[':oauth']['lname'];
                    $info = array(
                        'email' => $_SESSION[':oauth']['email'],
                        'name' => $name,
                    );
                    return new ClientCreateRequest($this, $info['email'], $info);
                }
                else{
                    echo "
                        <script>
                            alert('Only paid support customers can open a new ticket. A Paid Support subscription is a separate purchase and is not included with a regular product license. For more details please check the Paid Support Knowledgebase.');
                            window.location = '/kb/index.php';
                        </script>
                    ";
                }
            }
        }
    }

    static function signOut($user) {
        parent::signOut($user);
        unset($_SESSION[':oauth']);
    }

    function triggerAuth() {
        require_once INCLUDE_DIR . 'class.json.php';
        parent::triggerAuth();
        $dp = $this->dp->triggerAuth();
        if($dp) {
            $userInfo = $dp->requestUserInfo();
            //Initialize static array of users from passport data
            $parent_count = $userInfo->{'PSP.MasterAccount.Total'};
            $child_count = $userInfo->{'PSP.SubAccount.Total'};
            $support_users=array();

            $userEmail = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
            $current_users=array();
            $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
            $result = db_query($sql);
            $row = db_fetch_row($result);
            $MasterUserId = $row[0];
            $current_users[]=$MasterUserId;
            $support_users = array(
                $MasterUserId => $current_users
            );

            if($parent_count == 0 && $child_count == 0){//User has no children and no parents, set himself as master user.
            }
            elseif($parent_count == 0){//User has no parents but has children                
            }elseif($child_count==0){//User has no children but has parents
                $userEmail = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                $current_users=array();

                $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
                $result = db_query($sql);
                $row = db_fetch_row($result);
                $current_user=$row[0];

                for($i=1;$i<=$parent_count;$i++){
                    $MasterAccount = $userInfo->{'PSP.MasterAccount.'.$i};
                    $MasterAccount = explode(',', $MasterAccount);
                    $MasterAccount_email = $MasterAccount[1];
                    $sql = "SELECT user_id FROM ost_user_email WHERE address='".$MasterAccount_email."'";
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $MasterUserId = $row[0];
                    $master_support = array(
                        0 => $current_user
                    );
                    $support_users[$MasterUserId] = $master_support;
                }
            }else{//User has both parents and children
                $support_users1 = array();
                $support_users2 = array();
                $userEmail = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                $current_users=array();
                $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
                $result = db_query($sql);
                $row = db_fetch_row($result);
                $MasterUserId = $row[0];
                $current_users[]=$MasterUserId;
                $support_users1 = array(
                    $MasterUserId => $current_users
                );                

                $userEmail = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                $support_users=array();
                $current_users=array();

                $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
                $result = db_query($sql);
                $row = db_fetch_row($result);
                $current_user=$row[0];

                for($i=1;$i<=$parent_count;$i++){
                    $MasterAccount = $userInfo->{'PSP.MasterAccount.'.$i};
                    $MasterAccount = explode(',', $MasterAccount);
                    $MasterAccount_email = $MasterAccount[1];
                    $sql = "SELECT user_id FROM ost_user_email WHERE address='".$MasterAccount_email."'";
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $MasterUserId = $row[0];
                    $master_support = array(
                        0 => $current_user
                    );
                    $support_users[$MasterUserId] = $master_support;
                }

                $support_users2 = $support_users;
                $support_users = $support_users1 + $support_users2;
            }
            $_SESSION['support_users'] = $support_users;
            //Initialize static array of users from passport data
            $user_email = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
            $user_fname = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'};
            $user_lname = $userInfo->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'};
            $_SESSION[':oauth']['email'] = $user_email;
            $_SESSION[':oauth']['fname'] = $user_fname;
            $_SESSION[':oauth']['lname'] = $user_lname;
            $_SESSION[':oauth']['userInfo'] = $userInfo;
            //Log in to Dynabic.Menu when user logs into OsTicket
            $_SESSION['UserInfo'] = "&FullName=".$user_fname." ".$user_lname."&Email=".$user_email;
            //Log in to Dynabic.Menu when user logs into OsTicket
            Http::redirect(ROOT_PATH . 'login.php');
        }
        return;
    }
}