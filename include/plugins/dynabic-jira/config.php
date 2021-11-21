<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class DynabicJiraPluginConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('dynabic-jira');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        
        $current_url = $_SERVER["HTTP_HOST"];
        $whitelist = explode('.', $current_url);
        if(!in_array('groupdocs', $whitelist)){//For Aspose tenant
            return array(
                'dynabicaucklandJira' => new SectionBreakField(array(
                    'label' => $__('Auckland Credentials')
                )),
                'dynabic-aucklandJira-host' => new TextboxField(array(
                    'label' => $__('Auckland Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-aucklandJira-username' => new TextboxField(array(
                    'label' => $__('Auckland Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-aucklandJira-password' => new PasswordField(array(
                    'label' => $__('Auckland Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-aucklandJira-projects' => new TextboxField(array(
                    'label' => $__('Auckland Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicnanjingJira' => new SectionBreakField(array(
                    'label' => $__('Nanjing Credentials')
                )),
                'dynabic-nanjingJira-host' => new TextboxField(array(
                    'label' => $__('Nanjing Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-nanjingJira-username' => new TextboxField(array(
                    'label' => $__('Nanjing Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-nanjingJira-password' => new PasswordField(array(
                    'label' => $__('Nanjing Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-nanjingJira-projects' => new TextboxField(array(
                    'label' => $__('Nanjing Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                // 'dynabicodessaJira' => new SectionBreakField(array(
                //     'label' => $__('Odessa Credentials')
                // )),
                // 'dynabic-odessaJira-host' => new TextboxField(array(
                //     'label' => $__('Odessa Host'),
                //     'configuration' => array('size'=>60, 'length'=>100),                
                // )),
                // 'dynabic-odessaJira-username' => new TextboxField(array(
                //     'label' => $__('Odessa Username'),
                //     'configuration' => array('size'=>60, 'length'=>100),                
                // )),
                // 'dynabic-odessaJira-password' => new PasswordField(array(
                //     'label' => $__('Odessa Jira Password'),
                //     'configuration' => array('size'=>60, 'length'=>100),                
                // )),

                'dynabicsaltovJira' => new SectionBreakField(array(
                    'label' => $__('Saltov Credentials')
                )),
                'dynabic-saltovJira-host' => new TextboxField(array(
                    'label' => $__('Saltov Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-saltovJira-username' => new TextboxField(array(
                    'label' => $__('Saltov Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-saltovJira-password' => new PasswordField(array(
                    'label' => $__('Saltov Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-saltovJira-projects' => new TextboxField(array(
                    'label' => $__('Saltov Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabiclutskJira' => new SectionBreakField(array(
                    'label' => $__('Lutsk Credentials')
                )),
                'dynabic-lutskJira-host' => new TextboxField(array(
                    'label' => $__('Lutsk Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-lutskJira-username' => new TextboxField(array(
                    'label' => $__('Lutsk Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-lutskJira-password' => new PasswordField(array(
                    'label' => $__('Lutsk Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-lutskJira-projects' => new TextboxField(array(
                    'label' => $__('Lutsk Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicSPBJira' => new SectionBreakField(array(
                    'label' => $__('SPB Credentials')
                )),
                'dynabic-SPBJira-host' => new TextboxField(array(
                    'label' => $__('SPB Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SPBJira-username' => new TextboxField(array(
                    'label' => $__('SPB Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SPBJira-password' => new PasswordField(array(
                    'label' => $__('SPB Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SPBJira-projects' => new TextboxField(array(
                    'label' => $__('SPB Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicRzeszowJira' => new SectionBreakField(array(
                    'label' => $__('Rzeszow Credentials')
                )),
                'dynabic-RzeszowJira-host' => new TextboxField(array(
                    'label' => $__('Rzeszow Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-RzeszowJira-username' => new TextboxField(array(
                    'label' => $__('Rzeszow Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-RzeszowJira-password' => new PasswordField(array(
                    'label' => $__('Rzeszow Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-RzeszowJira-projects' => new TextboxField(array(
                    'label' => $__('Rzeszow Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicKievJira' => new SectionBreakField(array(
                    'label' => $__('Kiev Credentials')
                )),
                'dynabic-KievJira-host' => new TextboxField(array(
                    'label' => $__('Kiev Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-KievJira-username' => new TextboxField(array(
                    'label' => $__('Kiev Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-KievJira-password' => new PasswordField(array(
                    'label' => $__('Kiev Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-KievJira-projects' => new TextboxField(array(
                    'label' => $__('Kiev Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicKharkovJira' => new SectionBreakField(array(
                    'label' => $__('Kharkov Credentials')
                )),
                'dynabic-KharkovJira-host' => new TextboxField(array(
                    'label' => $__('Kharkov Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-KharkovJira-username' => new TextboxField(array(
                    'label' => $__('Kharkov Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-KharkovJira-password' => new PasswordField(array(
                    'label' => $__('Kharkov Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-KharkovJira-projects' => new TextboxField(array(
                    'label' => $__('Kharkov Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicBratislavaJira' => new SectionBreakField(array(
                    'label' => $__('Bratislava Credentials')
                )),
                'dynabic-BratislavaJira-host' => new TextboxField(array(
                    'label' => $__('Bratislava Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-BratislavaJira-username' => new TextboxField(array(
                    'label' => $__('Bratislava Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-BratislavaJira-password' => new PasswordField(array(
                    'label' => $__('Bratislava Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-BratislavaJira-projects' => new TextboxField(array(
                    'label' => $__('Bratislava Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicUgreshaJira' => new SectionBreakField(array(
                    'label' => $__('Ugresha Credentials')
                )),
                'dynabic-UgreshaJira-host' => new TextboxField(array(
                    'label' => $__('Ugresha Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-UgreshaJira-username' => new TextboxField(array(
                    'label' => $__('Ugresha Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-UgreshaJira-password' => new PasswordField(array(
                    'label' => $__('Ugresha Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-UgreshaJira-projects' => new TextboxField(array(
                    'label' => $__('Ugresha Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicSikorskyJira' => new SectionBreakField(array(
                    'label' => $__('Sikorsky Credentials')
                )),
                'dynabic-SikorskyJira-host' => new TextboxField(array(
                    'label' => $__('Sikorsky Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SikorskyJira-username' => new TextboxField(array(
                    'label' => $__('Sikorsky Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SikorskyJira-password' => new PasswordField(array(
                    'label' => $__('Sikorsky Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SikorskyJira-projects' => new TextboxField(array(
                    'label' => $__('Sikorsky Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicSuceavaJira' => new SectionBreakField(array(
                    'label' => $__('Suceava Credentials')
                )),
                'dynabic-SuceavaJira-host' => new TextboxField(array(
                    'label' => $__('Suceava Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SuceavaJira-username' => new TextboxField(array(
                    'label' => $__('Suceava Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SuceavaJira-password' => new PasswordField(array(
                    'label' => $__('Suceava Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-SuceavaJira-projects' => new TextboxField(array(
                    'label' => $__('Suceava Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicNinoJira' => new SectionBreakField(array(
                    'label' => $__('Nino Credentials')
                )),
                'dynabic-NinoJira-host' => new TextboxField(array(
                    'label' => $__('Nino Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-NinoJira-username' => new TextboxField(array(
                    'label' => $__('Nino Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-NinoJira-password' => new PasswordField(array(
                    'label' => $__('Nino Jira Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-NinoJira-projects' => new TextboxField(array(
                    'label' => $__('Nino Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),
            );
        }else{//For Groupdocs tenant
            return array(
                'dynabicaucklandJira_gd' => new SectionBreakField(array(
                    'label' => $__('Auckland Credentials Groupdocs')
                )),
                'dynabic-aucklandJira-host_gd' => new TextboxField(array(
                    'label' => $__('Auckland Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-aucklandJira-username_gd' => new TextboxField(array(
                    'label' => $__('Auckland Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-aucklandJira-password_gd' => new PasswordField(array(
                    'label' => $__('Auckland Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-aucklandJira-projects_gd' => new TextboxField(array(
                    'label' => $__('Auckland Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabicmoscowJira_gd' => new SectionBreakField(array(
                    'label' => $__('Moscow Credentials Groupdocs')
                )),
                'dynabic-moscowJira-host_gd' => new TextboxField(array(
                    'label' => $__('Moscow Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-moscowJira-username_gd' => new TextboxField(array(
                    'label' => $__('Moscow Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-moscowJira-password_gd' => new PasswordField(array(
                    'label' => $__('Moscow Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-moscowJira-projects_gd' => new TextboxField(array(
                    'label' => $__('Moscow Projects'),
                    'configuration' => array('size'=>60, 'length'=>200),                
                )),

                'dynabiclisbonjira_gd' => new SectionBreakField(array(
                    'label' => $__('Lisbon Credentials Groupdocs')
                )),
                'dynabic-lisbonJira-host_gd' => new TextboxField(array(
                    'label' => $__('Lisbon Host'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-lisbonJira-username_gd' => new TextboxField(array(
                    'label' => $__('Lisbon Username'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-lisbonJira-password_gd' => new PasswordField(array(
                    'label' => $__('Lisbon Password'),
                    'configuration' => array('size'=>60, 'length'=>100),                
                )),
                'dynabic-lisbonJira-projects_gd' => new TextboxField(array(
                    'label' => $__('Lisbon Projects'),
                    'configuration' => array('size'=>60, 'length'=>300),                
                )),
            );
        }
    }

    // function pre_save($config, &$errors) {
    //     global $msg;        
    //     $wsdl = '/rpc/soap/jirasoapservice-v2?wsdl';
    //     try{
    //         $soapClient = new SoapClient($config['dynabic-aucklandJira-host'].$wsdl);
    //         $token = $soapClient->login($config['dynabic-aucklandJira-username'], $config['dynabic-aucklandJira-password']);
    //         $msg = 'Successfully updated configuration';
    //         return true;
    //     }
    //     catch(exception $e){
    //         $errors['err'] = __('Invalid Credentials!');
    //         return false;
    //     }        
    // }


}
