<?php

return array(
    'id' =>             'auth:oath2', # notrans
    'version' =>        '0.1',
    'name' =>           /* trans */ 'Dynabic.Passport Authentication',
    'author' =>         'Jared Hancock',
    'description' =>    /* trans */ 'Provides a configurable authentication backend
        for authenticating staff and clients using an OAUTH2 server
        interface.',
    'url' =>            'http://www.osticket.com/plugins/auth/oauth',
    'plugin' =>         'authentication.php:OauthAuthPlugin'
);

?>
