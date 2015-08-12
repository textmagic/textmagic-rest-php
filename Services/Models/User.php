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
 
class User extends BaseModel {

    protected $resourceName = 'user';

    protected $allowMethods = array('get', 'update');

    public function get() {
        $this->checkPermissions('get');
        
        return $this->client->retrieveData($this->resourceName);
    }
    
    public function update($params = array()) {
        $this->checkPermissions('update');
        
        return $this->client->updateData($this->resourceName, $params);
    }
    
}
