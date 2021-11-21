<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class HelpdeskToForumConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('helpdesk-forum');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        return array(
            'HelpdeskToForum' => new SectionBreakField(array(
                'label' => $__('HelpdeskToForum Credentials')
            )),
            'HelpdeskToForum-host' => new TextboxField(array(
                'label' => $__('Host'),
                'configuration' => array('size'=>60, 'length'=>100),                
            )),
            'HelpdeskToForum-api' => new TextboxField(array(
                'label' => $__('API key'),
                'configuration' => array('size'=>60, 'length'=>100),                
            )),
        );
    }
}
