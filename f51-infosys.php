<?php
/*
Plugin Name: Infosys Collaboration
Plugin URI: https://github.com/Fake51/Infosys
Description: Connects a WordPress installation with an Infosys installation
Version: 0.1
Author: Peter Lind
Author URI: http://plind.dk
License: FreeBSD

Copyright 2012 Peter Lind. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
THIS SOFTWARE IS PROVIDED BY Peter Lind ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL Peter Lind OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those of the authors and should not be interpreted as representing official policies, either expressed or implied, of the Peter Lind
*/

/**
 * F51 Infosys Collaboration class
 *
 * @category
 * @package
 * @author Peter <pel@intern1.dk>
 */
class F51InfosysCollaborator
{
    /**
     * method docblock
     *
     * @access public
     * @return void
     */
    public function init()
    {
        /**
         * plugin setup/install
         */
        register_activation_hook(__FILE__, array($this, 'pluginActivation'));
        register_deactivation_hook(__FILE__, array($this, 'pluginDeactivation'));

        /**
         * setting up hooks
         */
        add_action('plugins_loaded', array($this, 'setupHooks'));
    }

    /**
     * handles plugin install with default settings/options
     *
     * @return void
     */
    public function pluginActivation()
    {
        if (version_compare(get_bloginfo('version'), '3.4', '<')) {
            deactivate_plugins(basename(__FILE__));
        } else {
            $options = array(
                'infosys-url'         => 'http://infosys-url/',
                'authentication-code' => 'Infosys auth code',
            );

            update_option('f51_infosys_options', $options);
        }
    }

    /**
     * handles plugin uninstall
     *
     * @return void
     */
    public function pluginDeactivation()
    {
        delete_option('f51_infosys_options');
    }

    /**
     * hooks
     */

    /**
     * sets up various hooks needed for functionality
     *
     * @return void
     */
    public function setupHooks()
    {
        add_action('init', array($this, 'initHook'));
        if (is_admin()) {
            add_action('admin_init', array($this, 'adminInitHook'));
            add_action('admin_menu', array($this, 'adminMenuHook'));
            add_action('add_meta_boxes', array($this, 'metaBoxHook'));
        }
    }

    /**
     * runs on wp init
     *
     * @return void
     */
    public function initHook()
    {
        // add con activity type as post
        register_post_type('activity', array(
//            'capability_type'     => 'activity',
            'exclude_from_search' => true,
            'description'         => 'RPG activity',
            'labels'              => array(
                'name' => 'RPG Activities',
                'singular_name' => 'RPG Activity',

            ),
            'publicly_queryable'  => true,
            'rewrite' => array(
                'slug' => 'aktivitet',
                'with_front' => false,

            ),
            'show_ui'             => true,
            'supports'            => array(
                'custom_fields',
                'editor',
                'excerpt',
                'query_var',
                'revisions',
                'title',
            ),
        ));
    }

    /**
     * adds a meta box for allowing editing of infosys data
     *
     * @param object $post Post object representing page viewed
     *
     * @access public
     * @return void
     */
    public function metaBoxHook()
    {
        add_meta_box(
            'f51-infosys-meta',
            'Infosys Collaboration',
            array($this, 'renderMetaBox'),
            'activity',
            'normal',
            'high'
        );
    }

    /**
     * renders the meta box for editing infosys data
     *
     * @param object $post Post object
     *
     * @access public
     * @return void
     */
    public function renderMetaBox($post)
    {
        echo "hejhej";
    }

    /**
     * registers settings and other stuff that needs to
     * take place during the admin init hook
     *
     * @access public
     * @return void
     */
    public function adminInitHook()
    {
        register_setting('f51_infosys_options', 'infosys-url');
        register_setting('f51_infosys_options', 'authentication-code');
    }

    /**
     * runs pre admin menu render and adds settings link
     *
     * @return void
     */
    public function adminMenuHook()
    {
        add_options_page(
            'Infosys Collaboration settings', // title
            'Infosys Collaboration',          // menu link title
            'manage_options',
            'f51_infosys_admin_settings',
            array($this, 'adminSettingsPage')
        );
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function adminSettingsPage()
    {
        require __DIR__ . '/templates/settings_page.phtml';
    }
}

$f51_infosys_collaborator = new F51InfosysCollaborator();
$f51_infosys_collaborator->init();
