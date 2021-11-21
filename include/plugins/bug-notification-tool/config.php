<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class BNTPluginConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('BNT');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        
        return array(
            'BNT' => new SectionBreakField(array(
                'label' => $__('Bug Notification Tool Credentials')
            )),
            'BNT-endpoint' => new TextboxField(array(
                'label' => $__('API Endpoint'),
                'configuration' => array('size'=>60, 'length'=>100),
                'required' => true
            )),
			'BNT-api-key' => new TextboxField(array(
                'label' => $__('API Key'),
                'configuration' => array('size'=>60, 'length'=>100),
                'required' => true
            )),
        );
    }
}