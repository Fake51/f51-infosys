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
 * plugin setup/install
 */
register_activation_hook(__FILE__, 'f51_infosys_plugin_activation');
register_deactivation_hook(__FILE__, 'f51_infosys_plugin_deactivation');

/**
 * setting up hooks
 */
add_action('wp_footer', 'f51_infosys_wp_footer');

/**
 * handles plugin install settings/options
 *
 * @return void
 */
function f51_infosys_plugin_activation()
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
 * handles plugin install settings/options
 *
 * @return void
 */
function f51_infosys_plugin_deactivation()
{
}

/**
 * hooks
 */

/**
 * called on WP rendering the footer
 *
 * @return void
 */
function f51_infosys_wp_footer()
{
    echo "hehehe ...";
}
