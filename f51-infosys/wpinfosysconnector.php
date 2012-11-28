<?php
/**
 * contains the WPInfosysConnector class
 *
 * @category
 * @package
 * @author   Peter <pel@intern1.dk>
 */

require_once F51_DIR . 'infosysconnector.php';

/**
 * Wordpress specific InfosysConnector class
 *
 * @category
 * @package
 * @author   Peter <pel@intern1.dk>
 */
class WPInfosysConnector extends InfosysConnector
{
    private $authenticated = false;

    private $request;

    /**
     * connects to infosys and authenticates
     *
     * @access protected
     * @return void
     */
    protected function handleAuthentication()
    {
        $wp_http = new WP_Http();
        $result  = $wp_http->request($this->makeUrl('auth'));
        if ($result['response']['code'] != 200 || !($json = json_decode($result['body']))) {
            throw new Exception('Cannot authenticate with infosys: no response');
        }

        if (empty($result['cookies'][0]) || $result['cookies'][0]->name !== 'PHPSESSID') {
            throw new Exception('Cannot authenticate with infosys: bad response');
        }

        $params = array(
            'body' => array(
                'data' => json_encode(
                    array(
                        'token' => md5($this->getAuthCode() . $json->token),
                        'user'  => $this->getAuthUser(),
                    )
                ),
            ),
            'method'  => 'POST',
            'cookies' => array(
                $result['cookies'][0],
            ),
        );

        $cookies   = array($result['cookies'][0]);

        $result = $wp_http->request($this->makeUrl('auth'), $params);
        if ($result['response']['code'] != 200 || !($json = json_decode($result['body']))) {
            throw new Exception('Cannot authenticate with infosys: no response');
        }

        $cookies[] = new WP_Http_cookie(
            array(
                'name'    => 'api-key',
                'value'   => $json->api_key,
                'path'    => $cookies[0]->path,
                'domain'  => $cookies[0]->domain,
                'expires' => $cookies[0]->expires,
            )
        );

        $this->setAuthCookies($cookies);
        $this->request       = $wp_http;
        $this->authenticated = true;
    }

    /**
     * performs a post request to the infosys webservice
     *
     * @param string $service Service to connect to
     * @param array  $params  POST parameters to send along, if any
     *
     * @access protected
     * @return mixed
     */
    protected function post($service, array $params = array())
    {
        if (!$this->authenticated) {
            $this->handleAuthentication();
        }

        $settings = array(
            'cookies' => $this->getAuthCookies(),
            'method'  => 'POST',
            'body'    => $params,
        );

        $result = $this->request->request($this->makeurl($service), $settings);

        if ($result['response']['code'] != 200) {
            throw new Exception('Failed to make request');
        }

        return json_decode($result['body']);
    }

    /**
     * performs a get request to the infosys webservice
     *
     * @param string $service Service to connect to
     * @param array  $params  GET parameters to send along, if any
     *
     * @access protected
     * @return mixed
     */
    protected function get($service, array $params = array())
    {
        if (!$this->authenticated) {
            $this->handleAuthentication();
        }

        $query    = empty($params) ? '' : '?' . http_build_query($params);
        $settings = array('cookies' => $this->getAuthCookies(), 'method' => 'GET');
        $result   = $this->request->request($this->makeurl($service) . $query, $settings);

        if ($result['response']['code'] != 200) {
            throw new Exception('Failed to make request');
        }

        return json_decode($result['body']);
    }
}
