<?php
/*********************************************************************
    login.php

    User access link recovery

    TODO: This is a temp. fix to allow for collaboration in lieu of real
    username and password coming in 1.8.2

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once('client.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error');
define('CLIENTINC_DIR',INCLUDE_DIR.'client/');
define('OSTCLIENTINC',TRUE); //make includes happy
//Custom Code for Passport Integration
$whitelist = array('127.0.0.1', '::1');
if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist) && !isset($_GET['do']) && !isset($_SESSION[':oauth']['userInfo']) ){//Server: redirect to passport
    Http::redirect('/login.php?do=ext&bk=dynabic.passport.client');
}
//Custom Code for Passport Integration
require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'class.ticket.php');

if ($cfg->getClientRegistrationMode() == 'disabled'
        || isset($_POST['lticket']))
    $inc = 'accesslink.inc.php';
else
    $inc = 'login.inc.php';

$suggest_pwreset = false;

// Check the CSRF token, and ensure that future requests will have to use a
// different CSRF token. This will help ward off both parallel and serial
// brute force attacks, because new tokens will have to be requested for
// each attempt.
if ($_POST) {
    // Check CSRF token
    if (!$ost->checkCSRFToken())
        Http::response(400, __('Valid CSRF Token Required'));

    // Rotate the CSRF token (original cannot be reused)
    $ost->getCSRF()->rotate();
}

if ($_POST && isset($_POST['luser'])) {
    if (!$_POST['luser'])
        $errors['err'] = __('Valid username or email address is required');
    elseif (($user = UserAuthenticationBackend::process($_POST['luser'],
            $_POST['lpasswd'], $errors))) {
        if ($user instanceof ClientCreateRequest) {
            if ($cfg && $cfg->isClientRegistrationEnabled()) {
                // Attempt to automatically register
                if ($user->attemptAutoRegister())
                    Http::redirect('tickets.php');

                // Auto-registration failed. Show the user the info we have
                $inc = 'register.inc.php';
                $user_form = UserForm::getUserForm()->getForm($user->getInfo());
            }
            else {
                $errors['err'] = __('Access Denied. Contact your help desk administrator to have an account registered for you');
                // fall through to show login page again
            }
        }
        else {
            Http::redirect($_SESSION['_client']['auth']['dest']
                ?: 'tickets.php');
        }
    } elseif(!$errors['err']) {
        $errors['err'] = sprintf('%s - %s', __('Invalid username or password'), __('Please try again!'));
    }
    $suggest_pwreset = true;
}
elseif ($_POST && isset($_POST['lticket'])) {
    if (!Validator::is_email($_POST['lemail']))
        $errors['err'] = __('Valid email address and ticket number required');
    elseif (($user = UserAuthenticationBackend::process($_POST['lemail'],
            $_POST['lticket'], $errors))) {

        // If email address verification is not required, then provide
        // immediate access to the ticket!
        if (!$cfg->isClientEmailVerificationRequired())
            Http::redirect('tickets.php');

        // This will succeed as it is checked in the authentication backend
        $ticket = Ticket::lookupByNumber($_POST['lticket'], $_POST['lemail']);

        // We're using authentication backend so we can guard aganist brute
        // force attempts (which doesn't buy much since the link is emailed)
        $ticket->sendAccessLink($user);
        $msg = sprintf(__("%s - access link sent to your email!"),
            Format::htmlchars($user->getName()->getFirst()));
        $_POST = null;
    } elseif(!$errors['err']) {
        $errors['err'] = sprintf('%s - %s', __('Invalid email or ticket number'), __('Please try again!'));
    }
}
elseif (isset($_GET['do'])) {
    switch($_GET['do']) {
    case 'ext':
        // Lookup external backend
        if ($bk = UserAuthenticationBackend::getBackend($_GET['bk']))
            $bk->triggerAuth();
    }
}
elseif ($user = UserAuthenticationBackend::processSignOn($errors, false)) {
    // Users from the ticket access link
    if ($user && $user instanceof TicketUser && $user->getTicketId())
        Http::redirect('tickets.php?id='.$user->getTicketId());
    // Users imported from an external auth backend
    elseif ($user instanceof ClientCreateRequest) {
        if ($cfg && $cfg->isClientRegistrationEnabled()) {
            // Attempt to automatically register
            //Custom Code for Passport Integration
            if ($user->attemptAutoRegister()){
                //Custom code used when a new user is created after passport auth
                $passport_user_id = $_SESSION[':oauth']['userInfo']->{'PSP.UserId'};
                $user_email = $_SESSION[':oauth']['userInfo']->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                $sql='UPDATE '.USER_EMAIL_TABLE.' SET passport_user_id = '.$passport_user_id.' WHERE address="'.$user_email.'"';
                if(db_query($sql))
                    echo "passport id updated!";
                //Initialize static array of users from passport data
                $parent_count = $_SESSION[':oauth']['userInfo']->{'PSP.MasterAccount.Total'};
                $child_count = $_SESSION[':oauth']['userInfo']->{'PSP.SubAccount.Total'};
                $support_users=array();

                $userEmail = $_SESSION[':oauth']['userInfo']->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
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
                    $userEmail = $_SESSION[':oauth']['userInfo']->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                    $current_users=array();

                    $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $current_user=$row[0];

                    for($i=1;$i<=$parent_count;$i++){
                        $MasterAccount = $_SESSION[':oauth']['userInfo']->{'PSP.MasterAccount.'.$i};
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
                    $userEmail = $_SESSION[':oauth']['userInfo']->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                    $current_users=array();
                    $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $MasterUserId = $row[0];
                    $current_users[]=$MasterUserId;
                    $support_users1 = array(
                        $MasterUserId => $current_users
                    );                

                    $userEmail = $_SESSION[':oauth']['userInfo']->{'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'};
                    $support_users=array();
                    $current_users=array();

                    $sql = "SELECT user_id FROM ost_user_email WHERE address='".$userEmail."'";
                    $result = db_query($sql);
                    $row = db_fetch_row($result);
                    $current_user=$row[0];

                    for($i=1;$i<=$parent_count;$i++){
                        $MasterAccount = $_SESSION[':oauth']['userInfo']->{'PSP.MasterAccount.'.$i};
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
                Http::redirect('index.php');
            }
            //Custom Code for Passport Integration

            // Unable to auto-register. Fill in what we have and let the
            // user complete the info
            $inc = 'register.inc.php';
        }
        else {
            $errors['err'] = __('Access Denied. Contact your help desk administrator to have an account registered for you');
            // fall through to show login page again
        }
    }
    elseif ($user instanceof AuthenticatedUser) {
        Http::redirect($_SESSION['_client']['auth']['dest']
                ?: 'tickets.php');
    }
}

if (!$nav) {
    $nav = new UserNav();
    $nav->setActiveNav('status');
}

// Browsers shouldn't suggest saving that username/password
//Http::response(422);

require CLIENTINC_DIR.'header.inc.php';
require CLIENTINC_DIR.$inc;
require CLIENTINC_DIR.'footer.inc.php';
?>
