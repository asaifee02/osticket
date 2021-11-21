<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class OauthAuthPlugin extends Plugin {
    var $config_class = "OauthPluginConfig";

    function bootstrap() {
        $config = $this->getConfig();

        define('dp_api_url', $config->get('dp-api-url'));
        define('dp_api_client_key', $config->get('dp-api-client-key'));
        define('dp_api_signing_key', $config->get('dp-api-signing-key'));
        # ----- Dynabic.Passport ---------------------
        $dynabicPassport = $config->get('dp-enabled');
        if (in_array($dynabicPassport, array('all', 'staff'))) {
            require_once('dynabic-passport.php');
            StaffAuthenticationBackend::register(
                new DynabicPassportStaffAuthBackend($this->getConfig()));
        }
        if (in_array($dynabicPassport, array('all', 'client'))) {
            require_once('dynabic-passport.php');
            UserAuthenticationBackend::register(
                new DynabicPassportClientAuthBackend($this->getConfig()));
        }
    }
}

require_once(INCLUDE_DIR.'UniversalClassLoader.php');
use Symfony\Component\ClassLoader\UniversalClassLoader_osTicket;
$loader = new UniversalClassLoader_osTicket();
$loader->registerNamespaceFallbacks(array(
    dirname(__file__).'/lib'));
$loader->register();
