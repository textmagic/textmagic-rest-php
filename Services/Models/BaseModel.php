<?php

/**
 * This file is part of the TextmagicRestClient package.
 *
 * Copyright (c) 2015 TextMagic Ltd. All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Textmagic\Services\Models; 
 
use Textmagic\Services\TextmagicRestClient;
 
 /**
 * @author Denis <denis@textmagic.biz>
 */
 
class BaseModel {

    /**
     * Http client instance
     * @var object
     */
    protected $client;
    
    /**
     * Resource name
     * @var string
     */
    protected $resourceName = null;
    
    /**
     * Item name for create and update methods
     * @var string
     */
    protected $itemName = null;

    /**
     * Allowed methods for model
     * @var array
     */
    protected $allowMethods = array('getList', 'create', 'get', 'update', 'delete', 'search');
    
    /**
     * Check model method name for allowed execution
     *
     * @param string $operation Operation name
     */
    protected function checkPermissions($operation) {
        if (!in_array($operation, $this->allowMethods)) {
            throw new \ErrorException('Model is not supported this method.');
        }
    }
    
    /**
     * BaseModel constructor
     *
     * @param object $client Http client
     */
    public function __construct(TextmagicRestClient $client) {
        $this->client = $client;
    }
    
    /**
     * Retrive collection of model objects
     *
     * @param array $params Query params
     * @return array
     */
    public function getList($params = array()) {
        $this->checkPermissions('getList');
        
        return $this->client->retrieveData($this->resourceName, $params);
    }

    /**
     * Create new model object
     *
     * @param array $params Object parameters
     * @return boolean
     */
    public function create($params = array()) {
        $this->checkPermissions('create');
        
        return $this->client->createData($this->resourceName, $params);
    }
    
    /**
     * Retrieve model object
     *
     * @param mixed $id Object id
     * @return array
     */
    public function get($id) {
        $this->checkPermissions('get');
        
        return $this->client->retrieveData($this->resourceName . '/' . $id);
    }
    
    /**
     * Update model object
     *
     * @param mixed $id Object id
     * @param array $params Object parameters
     * @return array
     */
    public function update($id, $params = array()) {
        $this->checkPermissions('update');
        
        return $this->client->updateData($this->resourceName . '/' . $id, $params);
    }
    
    /**
     * Delete model object
     *
     * @param mixed $id Object id
     * @return boolean
     */
    public function delete($id) {
        $this->checkPermissions('delete');
        
        return $this->client->deleteData($this->resourceName . '/' . $id);
    }
    
    /**
     * Search model object
     *
     * @param array $params Query params
     * @return array
     */
    public function search($params = array()) {
        $this->checkPermissions('search');
        
        return $this->client->retrieveData($this->resourceName . '/search', $params);
    }
}
