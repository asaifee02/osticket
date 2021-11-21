<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class BNTPlugin extends Plugin {
    var $config_class = "BNTPluginConfig";
	 /**
     * The Jira WSDL endpoint.
     */

    function bootstrap() {
        $config = $this->getConfig();

        # ----- Dynabic.Jira credentials ---------------------
        $BNT = json_decode($config->get('BNT-enabled'));
		//if($BNT) {
			define('BNT_ENDPOINT', $config->get('BNT-endpoint'));
			define('BNT_API_KEY', $config->get('BNT-api-key'));
		//}
		$registerClass = new Application();
		$desc = "Bug Notification Tool";
		$href = "bugs.php";
		$registerClass -> registerStaffApp($desc, $href, $info=array());
    }		
}