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
 
class Numbers extends BaseModel {

    protected $resourceName = 'numbers';

    protected $allowMethods = array('getList', 'getAvailable', 'create', 'get', 'delete');
    
    public function getAvailable($params = array()) {
        $this->checkPermissions('getAvailable');
        
        return $this->client->retrieveData($this->resourceName . '/available', $params);
    }
    
}
