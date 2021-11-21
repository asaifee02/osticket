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

<div class="container-fluid supportheader">
    <div class="container">
        <h4>Admin/agent account doesn't exist!</h4>
        <h3>Please ask your administrator to create an account for you.</h3>
    </div>
</div>


<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>