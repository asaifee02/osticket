<?php
$title=($cfg && is_object($cfg) && $cfg->getTitle())
    ? $cfg->getTitle() : 'osTicket :: '.__('Support Ticket System');
$signin_url = ROOT_PATH . "login.php"
    . ($thisclient ? "?e=".urlencode($thisclient->getEmail()) : "");
//Custom Code for Passport Integration
$whitelist = array('127.0.0.1', '::1');
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){//Localhost
}
else{//Server
    //Custom code to redirect to passport auth on login
    $signin_url = ROOT_PATH . "login.php?do=ext&bk=dynabic.passport.client";
    //Custom code to redirect to passport auth on login
}
//Custom Code for Passport Integration
$signout_url = ROOT_PATH . "logout.php?auth=".$ost->getLinkToken();

header("Content-Type: text/html; charset=UTF-8");
header("X-Frame-Options: SAMEORIGIN");
if (($lang = Internationalization::getCurrentLanguage())) {
    $langs = array_unique(array($lang, $cfg->getPrimaryLanguage()));
    $langs = Internationalization::rfc1766($langs);
    header("Content-Language: ".implode(', ', $langs));
}
?>
<!DOCTYPE html>
<html<?php
if ($lang
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . $lang . '"';
}
?>>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo Format::htmlchars($title); ?></title>
    <meta name="description" content="customer support platform">
    <meta name="keywords" content="osTicket, Customer support system, support ticket system">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/osticket.css?035fd0a" media="screen"/>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/theme.css?035fd0a" media="screen"/>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/print.css?035fd0a" media="print"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>scp/css/typeahead.css?035fd0a"
         media="screen" />
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?035fd0a"
        rel="stylesheet" media="screen" />
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/thread.css?035fd0a" media="screen"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?035fd0a" media="screen"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?035fd0a"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/flags.css?035fd0a"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?035fd0a"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/select2.min.css?035fd0a"/>
    
    <!-- Custom change to update UI design -->
    <script src="<?php echo ASSETS_PATH; ?>js/jquery.min.js"></script>
    <script src="<?php echo ASSETS_PATH; ?>js/popper.min.js"></script>
    <script src="<?php echo ASSETS_PATH; ?>js/bootstrap.js"></script>
    <!-- Custom change to update UI design -->

    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.11.2.min.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-ui-1.10.3.custom.min.js?035fd0a"></script>
    <script src="<?php echo ROOT_PATH; ?>js/osticket.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/filedrop.field.js?035fd0a"></script>
    <script src="<?php echo ROOT_PATH; ?>scp/js/bootstrap-typeahead.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor.min.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-plugins.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-osticket.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/select2.min.js?035fd0a"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/fabric.min.js?035fd0a"></script>
    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }

    // Offer alternate links for search engines
    // @see https://support.google.com/webmasters/answer/189077?hl=en
    if (($all_langs = Internationalization::getConfiguredSystemLanguages())
        && (count($all_langs) > 1)
    ) {
        $langs = Internationalization::rfc1766(array_keys($all_langs));
        $qs = array();
        parse_str($_SERVER['QUERY_STRING'], $qs);
        foreach ($langs as $L) {
            $qs['lang'] = $L; ?>
        <link rel="alternate" href="//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>?<?php
            echo http_build_query($qs); ?>" hreflang="<?php echo $L; ?>" />
<?php
        } ?>
        <link rel="alternate" href="//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"
            hreflang="x-default" />
<?php
    }
    ?>
<!-- Custom change to update UI design -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" type="text/css"/>
    
<link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/bootstrap.css?19292ad" />
<link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/supporttheme.css?8b927a0" media="screen"/>    

<script type"=text/javascript">
jQuery(document).ready(function() {
   jQuery(".menu-toggle").click(function(){
        jQuery(".menu").toggle();
    });
    jQuery(".profilename").click(function(){
        jQuery(".profilemenu").toggleClass('dp-show');
        jQuery(".profilediv").toggleClass('dp-touch');        
    });

});
</script>
<!-- Custom change to update UI design -->
<?php echo DynabicMenuPlugin::getMenuFavicon(); ?><!-- Custom code to integrate Dynabic.Menu favicon-->
</head>
<body>
<?php echo DynabicMenuPlugin::getMenuHeader(); ?><!-- Custom code to integrate Dynabic.Menu -->
 <!-- Custom change to update UI design -->    
 <nav class="navbar navbar-expand-lg navbar-light bg-white pt-2 pb-2">
 <div class="container">
 <span class="navbar-brand" href="/"><?php
if (($all_langs = Internationalization::getConfiguredSystemLanguages())
    && (count($all_langs) > 1)
) {
    $qs = array();
    parse_str($_SERVER['QUERY_STRING'], $qs);
    foreach ($all_langs as $code=>$info) {
        list($lang, $locale) = explode('_', $code);
        $qs['lang'] = $code;
?>
        <a class="flag flag-<?php echo strtolower($locale ?: $info['flag'] ?: $lang); ?>"
            href="?<?php echo http_build_query($qs);
            ?>" title="<?php echo Internationalization::getLanguageDescription($code); ?>">&nbsp;</a>
<?php }
} ?>
            
            <a class="pull-left" id="logo" href="<?php echo ROOT_PATH; ?>index.php"
            title="<?php echo __('Paid Support Helpdesk'); ?>">
                <span class="valign-helper"></span>
                <img src="<?php echo ROOT_PATH; ?>logo.php" border=0 alt="<?php
                echo $ost->getConfig()->getTitle(); ?>">
            </a></span>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  
    <div class="collapse navbar-collapse" id="navbarSupportedContent">                
       <?php    
        if($nav){ ?>
        <ul id="nav" class="flush-left ml-auto mr-auto">
            <?php
            if($nav && ($navs=$nav->getNavLinks()) && is_array($navs)){
                foreach($navs as $name =>$nav) {
                    echo sprintf('<li><a class="%s %s" href="%s">%s</a></li>%s',$nav['active']?'active':'',$name,(ROOT_PATH.$nav['href']),$nav['desc'],"\n");
                }
            } ?>
        </ul>
        <div class="profilediv">
        
        <?php
                if ($thisclient && is_object($thisclient) && $thisclient->isValid()
                && !$thisclient->isGuest()) {
                 ?>
                 
                <div class="dropdown">
<a href="#" class=" profilename text-muted btn btn-success"   id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?php
                 echo Format::htmlchars($thisclient->getName()); ?>  <i class="fa fa-user"></i></a>
               <div class="profilemenu dropdown-menu" aria-labelledby="dropdownMenu"> <a href="<?php echo ROOT_PATH; ?>profile.php"><?php echo __('Profile'); ?></a>
                <a href="<?php echo ROOT_PATH; ?>tickets.php"><?php echo sprintf(__('Tickets <b>(%d)</b>'), $thisclient->getNumTickets()); ?></a> <a href="<?php echo $signout_url; ?>"><?php echo __('Sign Out'); ?></a>
                    </div></div>
                    <?php
            } elseif($nav) {
                /*Don't need "Guest User" option
                if ($cfg->getClientRegistrationMode() == 'public') { ?>
                    <?php echo __('Guest User'); ?> | <?php
                }
                */
                if ($thisclient && $thisclient->isValid() && $thisclient->isGuest()) { ?>
                    <a href="https://helpdesk.aspose.com/login.php?do=ext&bk=dynabic.passport.client"><?php echo __('Sign Out'); ?></a><?php
                }
                elseif ($cfg->getClientRegistrationMode() != 'disabled') { ?>
                    <a href="<?php echo $signin_url; ?>" class="btn btn-success"><?php echo __('Sign In'); ?></a>
<?php
                }
            } ?></div>
            </div>
        <?php
        }else{ ?>
         <hr>
        <?php
        } ?>
         <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
         <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
         <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
         <?php } ?>
</div></div></nav>

<div class="container-fluid" id="contentstart">
<div class="row">
<div class="container">
<div class="col-lg-12 col-md-12  col-sm-12 supportcontent">
<div id="content">