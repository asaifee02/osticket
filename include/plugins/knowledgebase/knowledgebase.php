<?php
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class KnowledgeBasePlugin extends Plugin {
    var $config_class = "KnowledgeBasePluginConfig";
	 /**
     * The Jira WSDL endpoint.
     */

    function bootstrap() {
        $config = $this->getConfig();

        # ----- Dynabic.Jira credentials ---------------------
        $GFP = json_decode($config->get('KnowledgeBase'));
		//if($BNT) {
		//}
        $current_url = $_SERVER["HTTP_HOST"];
        $whitelist = explode('.', $current_url);
        if(in_array('admin', $whitelist)){//For main tenant only
    		$registerClass = new Application();
    		$desc = "Knowledgebase Config";
    		$href = "kb_config.php";
    		$registerClass -> registerStaffApp($desc, $href, $info=array());
        }
    }

    public static function KnowledgeBaseGetName(){
    	return "KnowledgeBase";
    }

    public static function compileFAQ($text) {//Compile shortcodes and return text according to specific tenant
        $current_url = $_SERVER["HTTP_HOST"];
        $whitelist = explode('.', $current_url);
        if(in_array('aspose', $whitelist) AND in_array('cloud', $whitelist) ){//For Aspose tenant
            $text = str_replace("{asposecloud}", "", $text);
            $text = str_replace("{/asposecloud}", "", $text);
            $text = preg_replace('#{aspose}.*?{/aspose}#si', '', $text);
            $text = preg_replace('#{groupdocs}.*?{/groupdocs}#si', '', $text);
            $text = preg_replace('#{conholdate}.*?{/conholdate}#si', '', $text);
        }elseif(in_array('aspose', $whitelist)){//For Aspose tenant
            $text = str_replace("{aspose}", "", $text);
            $text = str_replace("{/aspose}", "", $text);
            $text = preg_replace('#{groupdocs}.*?{/groupdocs}#si', '', $text);
            $text = preg_replace('#{asposecloud}.*?{/asposecloud}#si', '', $text);
            $text = preg_replace('#{conholdate}.*?{/conholdate}#si', '', $text);
        }elseif(in_array('groupdocs', $whitelist)){//For GroupDocs tenant
            $text = str_replace("{groupdocs}", "", $text);
            $text = str_replace("{/groupdocs}", "", $text);
            $text = preg_replace('#{aspose}.*?{/aspose}#si', '', $text);
            $text = preg_replace('#{asposecloud}.*?{/asposecloud}#si', '', $text);
            $text = preg_replace('#{conholdate}.*?{/conholdate}#si', '', $text);
        }elseif(in_array('conholdate', $whitelist)){//For GroupDocs tenant
            $text = str_replace("{conholdate}", "", $text);
            $text = str_replace("{/conholdate}", "", $text);
            $text = preg_replace('#{aspose}.*?{/aspose}#si', '', $text);
            $text = preg_replace('#{asposecloud}.*?{/asposecloud}#si', '', $text);
            $text = preg_replace('#{groupdocs}.*?{/groupdocs}#si', '', $text);
        }
        return $text;
    }

    public static function cleanString($string){
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = str_replace('/', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = preg_replace("/(-)\\1+/", "$1", $string);
        return $string;
    }

    public static function compileURL($string){
        $string = str_replace('-', '%', $string);
        $sql = "SELECT faq_id FROM ost_faq WHERE question LIKE '%".$string."%' ";
        $result = db_query($sql);
        $row = db_fetch_row($result);
        return $row[0];
    }

    public static function getQuestionById($id){
        $sql = "SELECT question FROM ost_faq WHERE faq_id=".$id;
        $result = db_query($sql);
        $row = db_fetch_row($result);
        $question = $row[0];
        $question = KnowledgeBasePlugin::cleanString($question);
        return $question;
    }
}