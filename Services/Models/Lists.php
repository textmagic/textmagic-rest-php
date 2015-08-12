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
 
 /**
 * @author Denis <denis@textmagic.biz>
 */
 
class Lists extends BaseModel {

    protected $resourceName = 'lists';

    protected $allowMethods = array('getList', 'create', 'get', 'update', 'delete', 'search', 'getContacts', 'updateContacts', 'deleteContacts');

    public function getContacts($id) {
        $this->checkPermissions('getContacts');
        
        return $this->client->retrieveData($this->resourceName . '/' . $id . '/contacts');
    }
    
    public function updateContacts($id, $params = array()) {
        $this->checkPermissions('updateContacts');
        
        return $this->client->updateData($this->resourceName . '/' . $id . '/contacts', $params);
    }
    
    public function deleteContacts($id, $params = array()) {
        $this->checkPermissions('deleteContacts');
        
        return $this->client->deleteData($this->resourceName . '/' . $id . '/contacts', $params);
    }
}
