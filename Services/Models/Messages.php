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
 
class Messages extends BaseModel {

    protected $resourceName = 'messages';

    protected $allowMethods = array('getList', 'create', 'get', 'delete', 'search', 'getPrice');

    public function getPrice($params) {
        $this->checkPermissions('getPrice');
        
        return $this->client->retrieveData($this->resourceName . '/price', $params);
    }
    
}
