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
 
class HttpCurl {
    
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
     * Curl port
     * @var integer
     */
    private $port = false;
    
    /**
     * Curl options
     * @var array
     */
    private $options = array();

    public function __construct($uri = '', $args = array()) {
        $res = parse_url($uri);
        $this->uri = $res['scheme'] . '://' . $res['host'] . $res['path'];
        $this->port = isset($res['port']) ? $res['port'] : false;
        $this->options = isset($args['curl_options']) ? $args['curl_options'] : array();
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

        // set default options
        $options = $this->options + array(
          CURLOPT_URL => $url,
          CURLOPT_HEADER => 1,
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_POSTFIELDS => null,
          CURLOPT_TIMEOUT => 60
        );

        // set credentials
        $options[CURLOPT_HTTPHEADER][] = "X-TM-Username: $this->username";
        $options[CURLOPT_HTTPHEADER][] = "X-TM-Key: $this->token";
    
        // set options
        foreach ($requestHeaders as $key => $value) {
            $options[CURLOPT_HTTPHEADER][] = "$key: $value";
        }
        
        // set port
        if ($this->port) {
            $options[CURLOPT_PORT] = $this->port;
        }

        switch (strtoupper($name)) {
            case 'GET':
                $options[CURLOPT_HTTPGET] = 1;
                if ($requestBody) {
                    $options[CURLOPT_URL] .= '?' . $requestBody;
                }
            break;
            case 'POST':
                $options[CURLOPT_POST] = 1;
                $options[CURLOPT_POSTFIELDS] = $requestBody;
            break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $options[CURLOPT_POSTFIELDS] = $requestBody;
            break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $options[CURLOPT_POSTFIELDS] = $requestBody;
            break;
            case 'HEAD':
                $options[CURLOPT_NOBODY] = 1;
            break;
        }
    
        try {
            if ($curl = curl_init()) {
                if (curl_setopt_array($curl, $options)) {
                    if ($response = curl_exec($curl)) {
                        $parts = explode("\r\n\r\n", $response, 3);
                        list($head, $body) = ($parts[0] == 'HTTP/1.1 100 Continue') ? array($parts[1], $parts[2]) : array($parts[0], $parts[1]);
                        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        $header_lines = explode("\r\n", $head);
                        
                        array_shift($header_lines);
                        foreach ($header_lines as $header) {
                            list($key, $value) = explode(":", $header, 2);
                            $headers[trim($key)] = trim($value);
                        }
                        curl_close($curl);
                        
                        return array($status, $headers, $body);
                    } else {
                        throw new \ErrorException(curl_error($curl));
                    }
                } else {
                    throw new \ErrorException(curl_error($curl));
                }
            } else {
                throw new \ErrorException('unable to initialize cURL');
            }
        } catch (\ErrorException $e) {
            if (is_resource($curl)) {
                curl_close($curl);
            }
          
            throw $e;
        }
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
