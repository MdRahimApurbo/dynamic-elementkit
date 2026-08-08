<?php

defined('ABSPATH') || exit;

final class DEK_Module_Loader {

    private $modules = [];

    public function add(DEK_Module_Interface $module) {
        $this->modules[] = $module;
        return $this;
    }

    public function register() {
        foreach ($this->modules as $module) {
            $module->register();
        }
    }
}
