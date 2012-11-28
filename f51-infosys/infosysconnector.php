<?php
/**
 * contains the InfosysConnector class
 *
 * @category
 * @package
 * @author   Peter <pel@intern1.dk>
 */

/**
 * Abstract InfosysConnector class
 *
 * @category
 * @package
 * @author   Peter <pel@intern1.dk>
 */
abstract class InfosysConnector
{
    const API_PREFIX = 'api/';

    /**
     * url of the infosys installation
     *
     * @var string
     */
    private $url;

    /**
     * auth code for the infosys installation
     *
     * @var string
     */
    private $auth_code;

    /**
     * auth user for the infosys installation
     *
     * @var string
     */
    private $auth_user;

    /**
     * cookies used for connection with infosys
     *
     * @var array
     */
    private $auth_cookies;

    /**
     * public constructor
     *
     * @param string $url       Url of infosys installation to connect to
     * @param string $auth_user Authentication user to use for connection
     * @param string $auth_code Authentication code to use for connection
     *
     * @access public
     * @return void
     */
    public function __construct($url, $auth_user, $auth_code)
    {
        $this->url       = substr($url, -1) === '/' ? $url : $url . '/';
        $this->auth_code = $auth_code;
        $this->auth_user = $auth_user;
    }

    /**
     * returns the auth code for the infosys installation
     *
     * @access protected
     * @return string
     */
    protected function getAuthCode()
    {
        return $this->auth_code;
    }

    /**
     * returns the auth code for the infosys installation
     *
     * @access protected
     * @return string
     */
    protected function getAuthUser()
    {
        return $this->auth_user;
    }

    /**
     * returns the url of the infosys installation
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * prefixes a given service with the base url of
     * the infosys instance
     *
     * @param string $service Service to make url for
     *
     * @access protected
     * @return string
     */
    protected function makeUrl($service)
    {
        return $this->getUrl() . self::API_PREFIX . $service;
    }

    /**
     * connects to infosys and authenticates
     *
     * @access protected
     * @return void
     */
    protected abstract function handleAuthentication();

    /**
     * performs a get request to the infosys webservice
     *
     * @param string $service Service to connect to
     * @param array  $params  GET parameters to send along, if any
     *
     * @access protected
     * @return mixed
     */
    protected abstract function get($service, array $params = array());

    /**
     * performs a post request to the infosys webservice
     *
     * @param string $service Service to connect to
     * @param array  $params  POST parameters to send along, if any
     *
     * @access protected
     * @return mixed
     */
    protected abstract function post($service, array $params = array());

    /**
     * sets the auth cookies to use for connecting with infosys
     *
     * @param array $cookies Authentication cookies to set
     *
     * @access protected
     * @return $this
     */
    protected function setAuthCookies(array $cookies)
    {
        $this->auth_cookies = $cookies;
        return $this;
    }

    /**
     * returns the array of cookies to use for infosys authentication
     *
     * @access protected
     * @return array
     */
    protected function getAuthCookies()
    {
        return $this->auth_cookies;
    }

    /**
     * calls the activity structure webservice of infosys
     *
     * @access public
     * @return mixed
     */
    public function getActivityStructure()
    {
        $result = $this->get('activity-structure');
        return $result;
    }

    /**
     * saves an activity, by creating or updating it
     *
     * @param array $post     POST data to save for activity
     * @param array $activity Data from existing activity, if any
     *
     * @access public
     * @return void
     */
    public function saveActivity(array $post, $activity = null)
    {
        $activity_id = isset($activity->id) ? $activity->id : '';
        $this->post('activities/' . $activity_id, $post);
    }

    /**
     * finds an activity given a field and value to search for
     *
     * @param string $field Field of activity to search by
     * @param mixed  $value Value to search for
     *
     * @access public
     * @return array
     */
    public function findActivity($field, $value)
    {
        $result = $this->get('activities/' . $field . '/' . $value);
        return $result;
    }
}
