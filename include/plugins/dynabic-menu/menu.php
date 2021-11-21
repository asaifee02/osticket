<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class DynabicMenuPlugin extends Plugin {
    var $config_class = "DynabicMenuPluginConfig";

    function bootstrap() {
        $config = $this->getConfig();

        # ----- Dynabic.Jira credentials ---------------------
		define('DYNABIC_MENU_HEADER', $config->get('dynabic-menu-header'));
		define('DYNABIC_MENU_FOOTER', $config->get('dynabic-menu-footer'));
        define('DYNABIC_MENU_FAVICON', $config->get('dynabic-menu-favicon'));

        //Log in to Dynabic.Menu when user logs into OsTicket
        if(isset($_SESSION['UserInfo'])){
            $data = $_SESSION['UserInfo'];
            $value = base64_encode($data);
            setcookie("UserInfo", $value, time()+3600);
        }
    }
	
	public static function getMenuHeader() {
		return DYNABIC_MENU_HEADER;
    }

    public static function getMenuFooter() {
    	return DYNABIC_MENU_FOOTER;
    }

    public static function getMenuFavicon() {
        return DYNABIC_MENU_FAVICON;
    }
}
