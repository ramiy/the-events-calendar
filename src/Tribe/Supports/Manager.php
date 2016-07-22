<?php

/**
 * Class Tribe__Events__Pro__Supports__Manager
 *
 * Loads and manages the third-party plugins support implementations.
 */
class Tribe__Events__Pro__Supports__Manager
{
    /**
     * @var Tribe__Events__Pro__Supports__Manager
     */
    protected static $instance;

    /**
     * The class singleton constructor.
     *
     * @return Tribe__Events__Pro__Supports__Manager
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Loads WPML support classes and event listeners.
     *
     * @return bool
     */
    private function load_wpml_support()
    {
        if (!(class_exists('SitePress') && defined('ICL_PLUGIN_PATH'))) {
            return false;
        }

        Tribe__Events__Pro__Supports__WPML__WPML::instance()->hook();

        return true;
    }

    /**
     * Conditionally loads the classes needed to support third-party plugins.
     *
     * Third-party plugin support classes and methods will be loaded only if
     * supported plugins are activated.
     */
    public function load_supports()
    {
        $this->load_wpml_support();
    }
}