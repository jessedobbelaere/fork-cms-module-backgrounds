<?php

namespace Frontend\Modules\Backgrounds\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation;

/**
 * In this file we store all generic functions that we will be using in the Backgrounds module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Model
{
    /**
     * Fetches a certain item
     *
     * @param string $id
     * @return array
     */
    public static function get($id)
    {
        $item = (array) FrontendModel::get('database')->getRecord(
            'SELECT i.*
             FROM backgrounds AS i
             WHERE i.id = ?',
            array((string) $id)
        );

        // no results?
        if (empty($item)) {
            return array();
        }

        return $item;
    }

}
