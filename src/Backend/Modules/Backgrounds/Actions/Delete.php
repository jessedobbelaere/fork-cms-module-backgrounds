<?php

namespace Backend\Modules\Backgrounds\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Backgrounds\Engine\Model as BackendBackgroundsModel;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Delete extends ActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendBackgroundsModel::exists($this->id)) {
            parent::execute();
            $this->record = (array) BackendBackgroundsModel::get($this->id);

            // Delete item
            BackendBackgroundsModel::delete($this->id);

            // Delete the image
            BackendModel::deleteThumbnails(FRONTEND_FILES_PATH . '/' . $this->getModule() . '/images', $this->record['image']);

            Model::triggerEvent(
                $this->getModule(), 'after_delete',
                array('id' => $this->id)
            );

            $this->redirect(
                Model::createURLForAction('index') . '&report=deleted&var=' .
                urlencode($this->record['title'])
            );
        }
        else $this->redirect(Model::createURLForAction('index') . '&error=non-existing');
    }
}
