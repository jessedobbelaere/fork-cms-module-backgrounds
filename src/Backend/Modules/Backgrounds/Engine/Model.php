<?php

namespace Backend\Modules\Backgrounds\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language;

/**
 * In this file we store all generic functions that we will be using in the Backgrounds module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Model
{
    const QRY_DATAGRID_BROWSE =
        'SELECT i.id, i.title, UNIX_TIMESTAMP(i.created_on) AS created_on, i.hidden
         FROM backgrounds AS i';

    /**
     * Delete a certain item
     *
     * @param int $id
     */
    public static function delete($id)
    {
        $db = BackendModel::get('database');
        $item = self::get($id);

        if (!empty($item)) {
            $db->delete('meta', 'id = ?', array($item['meta_id']));
            $db->delete('backgrounds', 'id = ?', array((int) $id));
            BackendModel::deleteExtraById($item['extra_id']);
            BackendModel::invalidateFrontendCache('Backgrounds', Language::getWorkingLanguage());
        }
    }

    /**
     * Checks if a certain item exists
     *
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        return (bool) BackendModel::get('database')->getVar(
            'SELECT 1
             FROM backgrounds AS i
             WHERE i.id = ?
             LIMIT 1',
            array((int) $id)
        );
    }

    /**
     * Fetches a certain item
     *
     * @param int $id
     * @return array
     */
    public static function get($id)
    {
        return (array) BackendModel::get('database')->getRecord(
            'SELECT i.*
             FROM backgrounds AS i
             WHERE i.id = ?',
            array((int) $id)
        );
    }

    /**
     * Retrieve the unique URL for an item
     *
     * @param string $url
     * @param int[optional] $id    The id of the item to ignore.
     * @return string
     */
    public static function getURL($url, $id = null)
    {
        $url = \SpoonFilter::urlise((string) $url);
        $db = BackendModel::get('database');

        // new item
        if ($id === null) {
            // already exists
            if ((bool) $db->getVar(
                'SELECT 1
                 FROM backgrounds AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE m.url = ?
                 LIMIT 1',
                array($url))) {
                $url = BackendModel::addNumber($url);
                return self::getURL($url);
            }
        } else {
            // current item should be excluded
            if ((bool) $db->getVar(
                'SELECT 1
                 FROM backgrounds AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE m.url = ? AND i.id != ?
                 LIMIT 1',
                array($url, $id))) {
                $url = BackendModel::addNumber($url);
                return self::getURL($url, $id);
            }
        }

        return $url;
    }

    /**
     * Insert an item in the database
     *
     * @param array $item
     * @return int
     */
    public static function insert(array $item)
    {
        $item['created_on'] = BackendModel::getUTCDate();
        $item['edited_on'] = BackendModel::getUTCDate();
        $db = BackendModel::get('database');

        // insert extra
        $item['extra_id'] = BackendModel::insertExtra(
            'widget',
            'Backgrounds',
            'Background'
        );

        $item['id'] = $db->insert('backgrounds', $item);

        // update extra (item id is now known)
        BackendModel::updateExtra(
            $item['extra_id'],
            'data',
            array(
                'id' => $item['id'],
                'extra_label' => \SpoonFilter::ucfirst(Language::lbl('Background', 'Backgrounds')) . ': ' . $item['title'],
//                'language' => $item['language'],
                'edit_url' => BackendModel::createURLForAction(
                        'Edit',
                        'Backgrounds',
                        null
//                        $item['language']
                    ) . '&id=' . $item['id']
            )
        );

        BackendModel::invalidateFrontendCache('Backgrounds', Language::getWorkingLanguage());

        return $item['id'];
    }

    /**
     * Updates an item
     *
     * @param array $item
     */
    public static function update(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();

        BackendModel::get('database')->update(
            'backgrounds', $item, 'id = ?', (int) $item['id']
        );

        // update extra
        BackendModel::updateExtra(
            $item['extra_id'],
            'data',
            array(
                'id' => $item['id'],
                'extra_label' => \SpoonFilter::ucfirst(Language::lbl('Background', 'Backgrounds')) . ': ' . $item['title'],
//                'language' => $item['language'],
                'edit_url' => BackendModel::createURLForAction('Edit') . '&id=' . $item['id']
            )
        );

        // invalidate menu
        BackendModel::invalidateFrontendCache('Backgrounds', Language::getWorkingLanguage());
    }

    public static function getImageThumb($id, $module)
    {
        $imageFilename = (string) BackendModel::getContainer()->get('database')->getVar(
            'SELECT i.image
            FROM backgrounds AS i
            WHERE id = ?
            LIMIT 1',
            array((int) $id)
        );

        $image = FRONTEND_FILES_URL . '/' . $module . '/images/75x75/' . $imageFilename;
        return '<img src="' . $image . '" width="50" height="50" />';
    }
}
