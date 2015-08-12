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
 
class Sessions extends BaseModel {

    protected $resourceName = 'sessions';
    
    protected $allowMethods = array('getList', 'get', 'delete', 'getMessages');

    public function getMessages($id) {
        $this->checkPermissions('getMessages');
        
        return $this->client->retrieveData($this->resourceName . '/' . $id . '/messages');
    }
    
}
