<?php
/**
 * This file contains the class Languages, this class is responsible for loading the plugin text domain.
 *
 * @namespace KSPlugin
 */

namespace KSPlugin;

/**
 * Class Languages
 */
class Languages
{
    /**
     * Languages constructor.
     */
    public function __construct()
    {
        add_action('plugins_loaded', [ $this, 'ksLoadTextdomain' ]);
    }

    /**
     * This method loads the plugin text domain.
     *
     * @return void
     */
    public function ksLoadTextdomain(): void
    {
        load_plugin_textdomain('ks-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}
