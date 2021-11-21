<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class OauthPluginConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('auth-oauth');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        $modes = new ChoiceField(array(
            'label' => $__('Authentication'),
            'choices' => array(
                '0' => $__('Disabled'),
                'staff' => $__('Agents Only'),
                'client' => $__('Clients Only'),
                'all' => $__('Agents and Clients'),
            ),
        ));
        return array(
            'dynabic-passport' => new SectionBreakField(array(
                'label' => $__('Dynabic.Passport Integration'),
            )),
            'dp-url' => new TextboxField(array(
                'label' => $__('URL'),
                'configuration' => array('size'=>60, 'length'=>100),
            )),
            'dp-client-id' => new TextboxField(array(
                'label' => $__('Client ID'),
                'configuration' => array('size'=>60, 'length'=>100),
            )),
            'dp-client-secret' => new TextboxField(array(
                'label' => $__('Client Secret'),
                'configuration' => array('size'=>60, 'length'=>100),
            )),
            'dp-enabled' => clone $modes,
            //Passport API configurations
            'dynabic-passport-api' => new SectionBreakField(array(
                'label' => $__('Dynabic.Passport API Integration'),
            )),
            'dp-api-url' => new TextboxField(array(
                'label' => $__('API URL'),
                'configuration' => array('size'=>60, 'length'=>100),
            )),
            'dp-api-client-key' => new TextboxField(array(
                'label' => $__('API Client Key'),
                'configuration' => array('size'=>60, 'length'=>100),
            )),
            'dp-api-signing-key' => new TextboxField(array(
                'label' => $__('API Signing Key'),
                'configuration' => array('size'=>60, 'length'=>100),
            )),
            //Passport API configurations
        );
    }
}
