<?php
/**
 * contains the F51InfosysCollaborator class
 *
 * @category
 * @package
 * @author   Peter <pel@intern1.dk>
 */

require_once F51_DIR . 'wpinfosysconnector.php';

/**
 * F51 Infosys Collaboration class
 *
 * @category
 * @package
 * @author   Peter <pel@intern1.dk>
 */
class F51InfosysCollaborator
{
    /**
     * instance of the InfosysConnector class
     *
     * @var InfosysConnector
     */
    private $infosys_connector;

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
        add_action('parse_request', array($this, 'parseRequest'));
        add_action('query_vars', array($this, 'queryVars'));
    }

    /**
     * handles request parsing to cut in on requests
     * for this plugin
     *
     * @access public
     * @return void
     */
    public function parseRequest($wp)
    {
        if (isset($wp->query_vars['f51-ajax'])) {
            switch($wp->query_vars['f51-ajax']) {
            case 'activity-structure':
                $this->presentActivityAsJSON(!empty($wp->query_vars['f51-activity-id']) ? $wp->query_vars['f51-activity-id'] : 0);
                exit;

            case 'create-activity':
                $this->createActivity();
                exit;

            }

        }
    }

    /**
     * registers 'f51' as a recognized query var
     *
     * @param array $query_vars Array of recognized query vars
     *
     * @access public
     * @return array
     */
    public function queryVars($query_vars)
    {
        $query_vars[] = 'f51-ajax';
        $query_vars[] = 'f51-activity-id';
        return $query_vars;
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
                'authentication-user' => 'Infosys auth user',
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
            'taxonomies'          => array(
                'category',
                'post_tag',
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
        $infosys_url = get_option('infosys-url');
        if (substr($infosys_url, -1) !== '/') {
            $infosys_url .= '/';
        }

        if (!preg_match('*^https?://*i', $infosys_url)) {
            $infosys_url = 'http://' . $infosys_url;
        }

        require F51_TEMPLATES_DIR . 'metaboxes.phtml';
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
        register_setting('f51_infosys_options', 'authentication-user');
        register_setting('f51_infosys_options', 'authentication-code');

        wp_enqueue_style('f51-stylesheet', plugins_url('f51-infosys/css/f51.css'));
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
        require F51_TEMPLATES_DIR . 'settings_page.phtml';
    }

    /**
     * returns an infosys connector
     *
     * @access protected
     * @return InfosysConnector
     */
    protected function getInfosysConnector()
    {
        if (empty($this->infosys_connector)) {
            $this->infosys_connector = new WPInfosysConnector(get_option('infosys-url'), get_option('authentication-user'), get_option('authentication-code'));
        }

        return $this->infosys_connector;
    }

    /**
     * fetches the activity structure from infosys and
     * returns it to the ajax request
     *
     * @param int $activity_id Optional ID of activity being worked on
     *
     * @access protected
     * @return void
     */
    protected function presentActivityAsJSON($activity_id)
    {
        $activity = $this->retrieveActivityStructure($activity_id);
        require F51_TEMPLATES_DIR . 'ajax/metaboxes.phtml';
    }

    /**
     * fetches the activity structure from infosys and
     * returns it to the ajax request
     *
     * @param int $activity_id Optional ID of activity being worked on
     *
     * @access public
     * @return void
     */
    public function retrieveActivityStructure($activity_id)
    {
        $connector          = $this->getInfosysConnector();
        $activity_structure = $connector->getActivityStructure();
        $activity           = $connector->findActivity('wp_link', $activity_id);

        return $activity;
    }

    /**
     * calls the infosys service to create an activity
     * or update it
     *
     * @access protected
     * @return void
     */
    protected function createActivity()
    {
        if (empty($_POST['id'])) {
            throw new Exception('Lacking activity data');
        }

        $data              = $_POST;
        $data['wp_link']   = $data['id'];
        $data['foromtale'] = empty($_POST['foromtale']) ? '' : strip_tags($_POST['foromtale']);
        unset($data['id']);

        $connector = $this->getInfosysConnector();
        $connector->saveActivity($data, $connector->findActivity('wp_link', $data['wp_link']));
    }
}
