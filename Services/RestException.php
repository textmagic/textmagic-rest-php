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
 
 /**
 * @author Denis <denis@textmagic.biz>
 */
 
class RestException extends \ErrorException {

    /**
     * Errors received from Textmagic API
     * @var array
     */
    protected $errors;

    /**
     * RestException constructor
     * 
     * @param string $message 
     * @param integer $code 
     * @param object $errors 
     * @return object
     */
    public function __construct($message, $code, $errors = null) {
        $this->errors = $errors;
        parent::__construct($message, $code);
    }

    /**
     * Get errors received from Textmagic API
     * 
     * @return array
     */
    public function getErrors() {
        $result = array();

        if (count($this->errors) > 0) {
            if (isset($this->errors['common'])) {
                $result['common'] = $this->errors['common'];
            }
            if (isset($this->errors['fields'])) {
                foreach ($this->errors['fields'] as $key => $value) {
                    $result[$key] = $value;
                }
            }
        }
        
        return $result;
    }

}
