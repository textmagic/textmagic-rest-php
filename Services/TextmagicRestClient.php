<?php

/**
 * This file is part of the TextmagicRestClient package.
 *
 * Copyright (c) 2015 TextMagic Ltd. All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Textmagic\Services;

use Textmagic\Services\RestException;
use Textmagic\Services\HttpClient\HttpCurl;
use Textmagic\Services\HttpClient\HttpStream;

/**
 * @author Denis <denis@textmagic.biz>
 */

spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__ . '\\';

    // process only classes with same namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = str_replace('\\', '/',  __DIR__ . DIRECTORY_SEPARATOR . $relativeClass) . '.php';
    
    // if the file exists, require it
    if (file_exists($file)) {
        require_once($file);
    }
});

class TextmagicRestClient {
    
    /**
     * Http client instance
     * @var object
     */
    protected $http;
    
    /**
     * Used version
     * @var string
     */
    protected $version;
    
    /**
     * Allowed versions
     * @var array
     */
    protected $versions = array('v2');

    /**
     * User agent
     * @var string
     */
    protected $userAgent = 'textmagic-rest-php';
    
    /**
     * Previous request time for prevent limit exceed error
     * @var integer
     */    
    protected $previousRequestTime = 0;
    
    /**
     * Get the API URI for this client.
     *
     * @return string
     */
    private function getApiUri() {
        return 'https://rest.textmagic.com/api/' . $this->version;
    }
    
    /**
     * Full user agent with the current PHP Version.
     *
     * @param string $userAgent Application user agent
     * @return string
     */
    private function getFullUserAgent() {
        return $this->userAgent . '/'  . $this->version . ' (php ' . phpversion() . ')';
    }
    
    /**
     * TextmagicRestClient constructor
     *
     * @param string $username API username
     * @param string $token API token
     * @param string $version API version
     * @param object $http Custom http object
     */
    public function __construct(
        $username,
        $token,
        $version = null,
        $http = null
    ) {
        $this->version = in_array($version, $this->versions) ? $version : end($this->versions);
        $this->http = $http;
        if (null === $this->http) {
            if (!in_array('openssl', get_loaded_extensions())) {
                throw new \ErrorException('The OpenSSL extension is required but not currently enabled. For more information, see http://php.net/manual/en/book.openssl.php');
            }
            if (in_array('curl', get_loaded_extensions())) {
                $this->http = new HttpCurl(
                    $this->getApiUri(),
                    array(
                        'curl_options' => array(
                            CURLOPT_USERAGENT => $this->getFullUserAgent(),
                            CURLOPT_HTTPHEADER => array(
                                'Accept-Charset: utf-8',
                                'Accept-Language: en-US'
                            )
                        )
                    )
                );
            } else {
                $this->http = new HttpStream(
                    $this->getApiUri(),
                    array(
                        'http_options' => array(
                            'http' => array(
                                'user_agent' => $this->getFullUserAgent(),
                                'header' => array(
                                    'Accept-Charset: utf-8',
                                    'Accept-Language: en-US'
                                )
                            ),
                            'ssl' => array(
                                'verify_peer' => true,
                                'verify_depth' => 5
                            )
                        )
                    )
                );
            }
        }
        
        $this->http->authenticate($username, $token);
    }

    /**
     * Overload method for access to models
     * 
     * @param string $name Model name
     * @return object
     */
    public function __get($name) {
        $name = strtolower($name);
        if (!isset($this->$name)) {
            $className = __NAMESPACE__ . '\\Models\\' . ucfirst($name);
            $this->$name = new $className($this);
        }
        
        return $this->$name;
    }
    
    /**
     * POST to resource at the specified path
     *
     * @param string $path Path to resource
     * @param array  $params Query string parameters
     * @return array
     */
    public function createData($path, $params = array()) {
        return $this->makeRequest(
            'POST', 
            $path, 
            array('Content-Type' => 'application/x-www-form-urlencoded'),
            http_build_query($params)
        );
    }

    /**
     * DELETE resource at the specified path
     *
     * @param string $path Path to resource
     * @param array  $params Query string parameters
     * @return array
     */
    public function deleteData($path, $params = array()) {
        return $this->makeRequest(
            'DELETE', 
            $path, 
            array('Content-Type' => 'application/x-www-form-urlencoded'), 
            http_build_query($params)
        );
    }

    /**
     * GET resource at the specified path
     *
     * @param string $path Path to resource
     * @param array  $params Query string parameters
     * @return array
     */
    public function retrieveData($path, $params = array()) {
        return $this->makeRequest(
            'GET', 
            $path, 
            array(), 
            http_build_query($params)
        );
    }

    /**
     * PUT resource at the specified path
     *
     * @param string $path Path to resource
     * @param array  $params Query string parameters
     * @return array
     */    
    public function updateData($path, $params = array()) {
        return $this->makeRequest(
            'PUT', 
            $path, 
            array('Content-Type' => 'application/x-www-form-urlencoded'),
            http_build_query($params)
        );
    }
    
    /**
     * Method for implementing request retry logic
     *
     * @param string $method HTTP request method
     * @param string $path Path to resource
     * @return array
     */
    private function makeRequest($method, $path, $headers = array(), $params = '') {
        if (time() - $this->previousRequestTime < 1) {
            sleep(1);
        }        
        $response = call_user_func_array(array($this->http, $method), array($path, $headers, $params));
        $this->previousRequestTime = time();
        list($status, $headers, $body) = $response;
        return $this->processResponse($response);
    }

    /**
     * Convert the JSON encoded response into a PHP object.
     *
     * @param array $response JSON encoded server response
     * @return array
     */
    private function processResponse($response) {
        list($status, $headers, $body) = $response;
        // if empty response just return boolean
        if ($status == 204) {
            return true;
        }
        
        $decoded = json_decode($body, true);
        if ($decoded === null) {
            throw new RestException('Could not decode response body as JSON.', $status);
        }
        if (200 <= $status && $status < 300) {
            return $decoded;
        }

        throw new RestException(
            $decoded['message'],
            $decoded['code'],
            (isset($decoded['errors']) ? $decoded['errors'] : null)
        );
    }
}
