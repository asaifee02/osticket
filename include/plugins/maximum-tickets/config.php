<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class MaximumTicketsPluginConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('maximum-tickets');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        return array(
            'maximumtickets' => new SectionBreakField(array(
                'label' => $__('Maximum Tickets'),
            )),
            'maximum-tickets' => new TextboxField(array(
                'label' => $__('Maximum Tickets'),
                'configuration' => array('size'=>6, 'length'=>100),
                'required' => true,
                'validator' => 'number'
            )),
        );
    }
}
