<?php

namespace Frontend\Modules\Backgrounds\Engine;

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
