<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
?>
<!-- Custom change to update UI design -->
<style type="text/css">
    #contentstart{
        background-image: -webkit-linear-gradient( -45deg, rgb(123,38,145) 0%, rgb(11,58,92) 100%);
    }
    h1,h2,h3,h4,h5,h6,p{
        color: white !important;
    }
    #new_ticket p{
        color: black !important;
    }
    #check_status p{
        color: black !important;
    }
    .sub-heading-list{
        font-size: 18px;
        margin-top: 50px;
        font-family: 'Open Sans',sans-serif;
        font-weight: lighter!important;
        color: rgb(237, 229, 229);
        line-height: 1;
    }
    #psDetail {
        font-size: 13px;
        font-weight: 300;
        font-family: "Open Sans", sans-serif;
        color: rgba(255, 255, 255, 0.502) !important;
        line-height: 1.538;
        padding: 25px 0px 20px 0px;
        width: 70%;
    }
    .sub-heading-list li{
        margin-top: 8px;
    }
    .btn-hdr{
        border: 1px solid white;
        border-radius: 0;
        background: transparent;
        height:46px;
        color: white!important;
        font-family: 'Open Sans', sans-serif!important;
        font-size: 16px!important;
        font-weight: 400!important;
        line-height:1.875;
        margin:10px 20px 0px 0px;
        padding:8px 10px!important; 
        text-shadow: none;
    }
    .btn-hdr:hover{
        background-color: rgb(255, 255, 255);
        color: rgb(123,38,145)!important;
    }
    .btn-hdr:focus{
        text-decoration: none;
        outline: 0!important;
    }
    ul{
        padding: 0 0 0 18px;
    }
    .footernote{
        color: rgb(237, 229, 229);
    }
</style>
<!-- Custom change to update UI design -->

<div class="container-fluid supportheader">
<div class="container">
    <?php
    if($cfg && ($page = $cfg->getLandingPage()))
        echo $page->getBodyWithImages();
    else
        echo  '<h1>'.__('Welcome to the Support Center').'</h1>';
    ?>
</div>
</div>
<!-- Custom change to update UI design -->
<?php
$url = $_SERVER['SERVER_NAME'];
$site = "/kb/faq/How-to-obtain-Paid-Support";
?>
<div class="container-fluid helpdesk-head header2 productfamilyheader minify-header">
    <div class="container">
        <div class="row page-header">
            <div class="col-lg-6 order-lg-2">
                <div class="tr pull-right">
                    <img class="totalimg lazyloaded" src="assets/default/images/paid-support.png" alt="Aspose Free Consulting" id="headerImg">        
                </div>
            </div>
            <div class="col-lg-6">
                <div class="sub-heading-list">
                    <ul>
                        <li>Comprehensive support on a priority basis</li>
                        <li>Direct access to Paid Support management team</li>
                        <li>Use of our issue tracking system</li>
                    </ul>
                </div>
                <h6 id="psDetail">Paid Support subscription is purchased as a separate product and is not a part of product license.</h6>
                <div class="btn-container">
                    <a href="<?php echo $site; ?>"><button class="btn-hdr">Get Paid Support</button></a>
                    <?php if(!is_object($thisclient)){ ?>
                        <a href="login.php?do=ext&bk=dynabic.passport.client"><button class="btn-hdr">Sign In</button></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Custom change to update UI design -->
<div id="new_ticket" class="col-lg-6 col-md-6 pull-left">
<div class="new-ticket">
<div class="icon-wrap"><a href="<?php if(is_object($thisclient)){ echo 'open.php';} else {echo 'login.php?do=ext&bk=dynabic.passport.client';}?>"><i class="fa fa-plus plus fa-2x"></i></a></div>
<h3><a href="<?php if(is_object($thisclient)){ echo 'open.php';} else {echo 'login.php?do=ext&bk=dynabic.passport.client';}?>"><?php echo __('Open a New Ticket');?></a></h3>
<p><?php echo __('Please provide as much detail as possible so we can best assist you. To update a previously submitted ticket, please login.');?></p>
<a href="<?php if(is_object($thisclient)){ echo 'open.php';} else {echo 'login.php?do=ext&bk=dynabic.passport.client';}?>" class="btn btn-success btn-lg"><?php echo __('Open a New Ticket');?></a>
</div></div>
<div id="check_status" class="col-lg-6 col-md-6 pull-left">
<div class="check-status">
<div class="icon-wrap"><a href="<?php if(is_object($thisclient)){ echo 'tickets.php';} else {echo 'login.php?do=ext&bk=dynabic.passport.client';}?>"><i class="fa fa-hourglass-3 plus fa-2x"></i></a> </div>
<h3><a href="<?php if(is_object($thisclient)){ echo 'tickets.php';} else {echo 'login.php?do=ext&bk=dynabic.passport.client';}?>"><?php echo __('Check Ticket Status');?></a></h3>
<p><?php echo __('We provide archives and history of all your current and past support requests complete with responses.');?></p>
<a href="<?php if(is_object($thisclient)){ echo 'tickets.php';} else {echo 'login.php?do=ext&bk=dynabic.passport.client';}?>" class="btn btn-warning btn-lg"><?php echo __('Check Ticket Status');?></a>
</div></div>
<div class="spacer2 col-lg-12 hidden-xs  pull-left">&nbsp;</div>
<div class="col-lg-12 pull-left footernote">
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
//FIXME: provide ability to feature or select random FAQs ??
?>
<?php echo sprintf(
__('Be sure to browse our %s before opening a ticket'),
sprintf('<a href="kb/index.php">%s</a>',
__('Frequently Asked Questions (FAQs)')
)); ?>
<?php
} ?>
</div></div></div>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>