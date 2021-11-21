<?php
/*********************************************************************
    faq.php

    FAQs Clients' interface..

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('kb.inc.php');
require_once(INCLUDE_DIR.'class.faq.php');

$faq=$category=null;
//Custom code to make seo friendly FAQ urls
$url=strtok($_SERVER["REQUEST_URI"],'?');
if($url == "/kb/faq/index.php"){
    $newurl = "/kb/index.php";
    header("Location: ".$newurl);
}
if(isset($_REQUEST['id']) AND strpos($_SERVER["REQUEST_URI"], 'id') AND !strpos($_SERVER["REQUEST_URI"], 'cid')){
    if($url == "/kb/faq.php" OR $url == "/kb/faq/faq.php"){
        $newurl = "/kb/faq/".KnowledgeBasePlugin::getQuestionById($_REQUEST['id']);
        header("Location: ".$newurl);
    }
}
if($_REQUEST['cid']){
    if($url == "/kb/faq/faq.php"){
        $newurl = "/kb/faq.php?cid=".$_REQUEST['cid'];
        header("Location: ".$newurl);
    }
}
$id=KnowledgeBasePlugin::compileURL($_REQUEST['id']);
//Custom code to make seo friendly FAQ urls
if($_REQUEST['id'] && !($faq=FAQ::lookup($id)))
   $errors['err']=sprintf(__('%s: Unknown or invalid'), __('FAQ article'));

if(!$faq && $_REQUEST['cid'] && !($category=Category::lookup($_REQUEST['cid'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid'), __('FAQ category'));


$inc='knowledgebase.inc.php'; //FAQs landing page.
if($faq && $faq->isPublished()) {
    $inc='faq.inc.php';
} elseif($category && $category->isPublic() && $_REQUEST['a']!='search') {
    $inc='faq-category.inc.php';
}
require_once(CLIENTINC_DIR.'header.inc.php');
require_once(CLIENTINC_DIR.$inc);
require_once(CLIENTINC_DIR.'footer.inc.php');
?>
