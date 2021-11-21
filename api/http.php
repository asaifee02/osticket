<?php
/*********************************************************************
    http.php

    HTTP controller for the osTicket API

    Jared Hancock
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
// Use sessions — it's important for SSO authentication, which uses
// /api/auth/ext
define('DISABLE_SESSION', false);

require 'api.inc.php';

# Include the main api urls
require_once INCLUDE_DIR."class.dispatcher.php";

$dispatcher = patterns('',
        url_post("^/tickets\.(?P<format>xml|json|email)$", array('api.tickets.php:TicketApiController','create')),
        //Custom Code for custom APIS
        url_post("^/message\.(?P<format>xml|json|email)$", array('api.tickets.php:TicketApiController','message')),
        url_post("^/reply\.(?P<format>xml|json|email)$", array('api.tickets.php:TicketApiController','reply')),
        url_post("^/monitor\.(?P<format>xml|json|email)$", array('api.tickets.php:PaidSupportAPIController','monitor')),
        url_post("^/monitor_log\.(?P<format>xml|json|email)$", array('api.tickets.php:PaidSupportAPIController','monitor_log')),
        url_post("^/changeownership\.(?P<format>xml|json|email)$", array('api.tickets.php:PaidSupportAPIController','changeOwnership')),
        url_post("^/transferownership\.(?P<format>xml|json|email)$", array('api.tickets.php:PaidSupportAPIController','transferOwnership')),
        url_post("^/validatesubscription\.(?P<format>xml|json|email)$", array('api.tickets.php:PaidSupportAPIController','validateSubscription')),        
        url_post("^/update_support\.(?P<format>xml|json|email)$", array('api.tickets.php:PaidSupportAPIController','update_support')),
        url_get("^/get_support/(?P<id>\d+)$", array('api.tickets.php:PaidSupportAPIController','get_support')),
        url_get("^/deleteUserData/(?P<id>\d+)$", array('api.tickets.php:PaidSupportAPIController','deleteUserData')),
        url_get("^/exportUserData/(?P<id>\d+)$", array('api.tickets.php:PaidSupportAPIController','exportUserData')),
        //Custom Code for custom APIS
        url('^/tasks/', patterns('',
                url_post("^cron$", array('api.cron.php:CronApiController', 'execute'))
         ))
        );

Signal::send('api', $dispatcher);

# Call the respective function
print $dispatcher->resolve($ost->get_path_info());
?>
