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
 
class Stats extends BaseModel {

    protected $resourceName = 'stats';

    protected $allowMethods = array('spending', 'messaging');

    public function spending($params = array()) {
        $this->checkPermissions('spending');
        
        return $this->client->retrieveData($this->resourceName . '/spending', $params);
    }
    
    public function messaging($params = array()) {
        $this->checkPermissions('messaging');
        
        return $this->client->retrieveData($this->resourceName . '/messaging', $params);
    }
    
}
