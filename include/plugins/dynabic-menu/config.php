<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class DynabicMenuPluginConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('dynabic-menu');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        
        return array(
            'dynabicMenu' => new SectionBreakField(array(
                'label' => $__('Dynabic Menu Configurations')
            )),
            'dynabic-menu-header' => new TextareaField(array(
                'label'=>__('Header Code'),
                'configuration'=>array('html'=>false,'rows'=>15,'cols'=>90),
            )),
            'dynabic-menu-footer' => new TextareaField(array(
                'label' => $__('Footer Code'),
                'configuration'=>array('html'=>false,'rows'=>3,'cols'=>90),
            )),
            'dynabic-menu-favicon' => new TextareaField(array(
                'label' => $__('Favicon Link'),
                'configuration'=>array('html'=>false,'rows'=>3,'cols'=>90),
            )),
        );
    }
}