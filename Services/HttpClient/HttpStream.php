<?php

/**
 * This file is part of the TextmagicRestClient package.
 *
 * Copyright (c) 2015 TextMagic Ltd. All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Textmagic\Services\HttpClient;

 /**
 * @author Denis <denis@textmagic.biz>
 */

class HttpStream {

    /**
     * API usename
     * @var string
     */
    private $username; 
    
    /**
     * API token
     * @var string
     */
    private $token; 
    
    /**
     * Base uri
     * @var string
     */
    private $uri = null;
    
    /**
     * Stream options
     * @var array
     */
    private $options = array();

    public function __construct($uri = '', $args = array()) {
        $this->uri = $uri;
        $defaultOptions = array(
            'http' => array(
                'headers' => '',
                'timeout' => 60,
                'follow_location' => true,
                'ignore_errors' => true
            ),
            'ssl' => array()
        );
        
        if (isset($args['http_options'])) {
            $this->options = $args['http_options'] + $defaultOptions;
        } else {
            $this->options = $defaultOptions;
        }
    }

    /**
     * Overload method for GET, POST, PUT, HEAD, DELETE queries
     * 
     * @param string $name Method name
     * @param array $args Method arguments
     * 
     * @return array
     */    
    public function __call($name, $args) {
        list($res, $requestHeaders, $requestBody) = $args + array('', array(), '');

        // create url for query
		if (strpos($res, 'http') === 0) {
			$url = $res;
		} else {
			$url = $this->uri . '/' . $res;
		}

        // set options
        $requestOptions = $this->options;

        // set content
        if (isset($requestBody) && strlen($requestBody) > 0) {
            $requestOptions['http']['content'] = $requestBody;
        }

        // set credentials
        $requestOptions['http']['header'][] = "X-TM-Username: $this->username";
        $requestOptions['http']['header'][] = "X-TM-Key: $this->token";
        
        // set headers
        foreach ($requestHeaders as $key => $value) {
            $requestOptions['http']['header'][] = "$key: $value";
        }
        
        $requestOptions['http']['header'] = implode("\r\n", $requestOptions['http']['header']);
        $requestOptions['http']['method'] = strtoupper($name);
        $requestOptions['http']['ignore_errors'] = true;

        $context = stream_context_create($requestOptions);
        $body = file_get_contents($url, false, $context);

        if (false === $body) {
            throw new \ErrorException('Unable to connect to service');
        }

        $statusHeader = array_shift($http_response_header);
        if (1 !== preg_match('#HTTP/\d+\.\d+ (\d+)#', $statusHeader, $matches)) {
            throw new \ErrorException('Unable to detect the status code in the HTTP result.');
        }
        $status = intval($matches[1]);
        
        $headers = array();
        foreach($http_response_header as $header) {
            list($key, $value) = explode(":", $header, 2);
            $headers[trim($key)] = trim($value);
        }

        return array($status, $headers, $body);
    }

    /**
     * Set API credentials
     * 
     * @param string $username API username
     * @param string $token API token
     */
    public function authenticate($username, $token) {
        if (empty($username) || empty($token)) {
            throw new \ErrorException('No username or token supplied.');
        }
        
        $this->username = $username;
        $this->token = $token;
    }
}
