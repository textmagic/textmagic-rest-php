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
 
class Contacts extends BaseModel {

    protected $resourceName = 'contacts';

    protected $allowMethods = array('getList', 'create', 'get', 'update', 'delete', 'search', 'getLists');

    public function getLists($id) {
        $this->checkPermissions('getLists');
        
        return $this->client->retrieveData($this->resourceName . '/' . $id . '/lists');
    }
    
}
