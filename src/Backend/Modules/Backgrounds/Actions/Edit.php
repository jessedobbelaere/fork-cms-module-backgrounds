<?php

namespace Backend\Modules\Backgrounds\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Meta;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Backgrounds\Engine\Model as BackendBackgroundsModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Backend\Modules\Users\Engine\Model as BackendUsersModel;
use Backend\Core\Engine\Language as BL;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * This is the edit-action, it will display a form with the item data to edit
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Edit extends ActionEdit
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->loadData();
        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Load the item data
     */
    protected function loadData()
    {
        $this->id = $this->getParameter('id', 'int', null);
        if ($this->id == null || !BackendBackgroundsModel::exists($this->id)) {
            $this->redirect(
                Model::createURLForAction('index') . '&error=non-existing'
            );
        }

        $this->record = BackendBackgroundsModel::get($this->id);
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        // create form
        $this->frm = new Form('edit');

        $this->frm->addText('title' ,$this->record['title'], null, 'inputText title', 'inputTextError title');
        $this->frm->addImage('image');

        // build array with options for the hidden Dropdown
        $RadiobuttonHiddenValues[] = array('label' => Language::lbl('Hidden'), 'value' => 'Y');
        $RadiobuttonHiddenValues[] = array('label' => Language::lbl('Published'), 'value' => 'N');
        $this->frm->addRadioButton('hidden', $RadiobuttonHiddenValues, $this->record['hidden']);

        // Background options
        $backgroundSizeValues = array(
            'auto' => 'auto',
            'cover' => 'cover',
            'contain' => 'contain'
        );
        $backgroundPositionHorizontalValues = array(
            'left' => 'left',
            'center' => 'center',
            'right' => 'right',
        );
        $backgroundPositionVerticalValues = array(
            'top' => 'top',
            'center' => 'center',
            'bottom' => 'bottom',
        );
        $backgroundRepeatValues = array(
            'no-repeat' => 'no-repeat',
            'repeat' => 'repeat',
            'repeat-x' => 'repeat-x',
            'repeat-y' => 'repeat-y'
        );
        $this->frm->addDropdown('background_size', $backgroundSizeValues, $this->record['background_size']);
        $this->frm->addDropdown('background_position_horizontal', $backgroundPositionHorizontalValues, $this->record['background_position_horizontal']);
        $this->frm->addDropdown('background_position_vertical', $backgroundPositionVerticalValues, $this->record['background_position_vertical']);
        $this->frm->addDropdown('background_repeat', $backgroundRepeatValues, $this->record['background_repeat']);

        // meta
        $this->meta = new Meta($this->frm, $this->record['meta_id'], 'title', true);
        $this->meta->setUrlCallBack('Backend\Modules\Backgrounds\Engine\Model', 'getUrl', array($this->record['id']));

    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        parent::parse();

        // get url
        $url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
        $url404 = BackendModel::getURL(404);

        // parse additional variables
        if ($url404 != $url) {
            $this->tpl->assign('detailURL', SITE_URL . $url);
        }
        $this->record['url'] = $this->meta->getURL();


        $this->tpl->assign('item', $this->record);
    }

    /**
     * Validate the form
     */
    protected function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            $this->frm->cleanupFields();

            // validation
            $fields = $this->frm->getFields();

            $fields['title']->isFilled(Language::err('FieldIsRequired'));
            if ($fields['image']->isFilled()) {
                $fields['image']->isAllowedExtension(
                    array('jpg', 'png', 'gif', 'jpeg'),
                    Language::err('JPGGIFAndPNGOnly')
                );
                $fields['image']->isAllowedMimeType(
                    array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'),
                    Language::err('JPGGIFAndPNGOnly')
                );
            }

            // validate meta
            $this->meta->validate();

            if ($this->frm->isCorrect()) {
                $item['id'] = $this->id;
                $item['language'] = NULL;

                $item['title'] = $fields['title']->getValue();
                $item['image'] = $this->record['image'];

                // the image path
                $imagePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/images';

                // create folders if needed
                $fs = new Filesystem();
                if (!$fs->exists($imagePath . '/source')) {
                    $fs->mkdir($imagePath . '/source');
                }
                if (!$fs->exists($imagePath . '/1600x1200')) {
                    $fs->mkdir($imagePath . '/1600x1200');
                }
                if (!$fs->exists($imagePath . '/1024x768')) {
                    $fs->mkdir($imagePath . '/1024x768');
                }
                if (!$fs->exists($imagePath . '/300x')) {
                    $fs->mkdir($imagePath . '/300x');
                }
                if (!$fs->exists($imagePath . '/75x75')) {
                    $fs->mkdir($imagePath . '/75x75');
                }

                // new image given?
                if ($this->frm->getField('image')->isFilled()) {
                    $filename = $imagePath . '/source/' . $this->record['image'];
                    if (is_file($filename)) {
                        $fs->remove($filename);
                        BackendModel::deleteThumbnails($imagePath, $this->record['image']);
                    }

                    // build the image name
                    $item['image'] = $this->meta->getURL() . '.' . $this->frm->getField('image')->getExtension();

                    // upload the image & generate thumbnails
                    $this->frm->getField('image')->generateThumbnails($imagePath, $item['image']);
                } elseif ($item['image'] != null) {
                    // rename the old image
                    $image = new File($imagePath . '/source/' . $item['image']);
                    $newName = $this->meta->getURL() . '.' . $image->getExtension();

                    // only change the name if there is a difference
                    if ($newName != $item['image']) {
                        // loop folders
                        foreach (BackendModel::getThumbnailFolders($imagePath, true) as $folder) {
                            // move the old file to the new name
                            $fs->rename($folder['path'] . '/' . $item['image'], $folder['path'] . '/' . $newName);
                        }

                        // assign the new name to the database
                        $item['image'] = $newName;
                    }
                }

                $item['hidden'] = $fields['hidden']->getValue();
                $item['extra_id'] = $this->record['extra_id'];
                $item['meta_id'] = $this->meta->save();

                // background options
                $item['background_size'] = $fields['background_size']->getValue();
                $item['background_position_horizontal'] = $fields['background_position_horizontal']->getValue();
                $item['background_position_vertical'] = $fields['background_position_vertical']->getValue();
                $item['background_repeat'] = $fields['background_repeat']->getValue();

                BackendBackgroundsModel::update($item);
                $item['id'] = $this->id;

                BackendModel::triggerEvent(
                    $this->getModule(), 'after_edit', $item
                );
                $this->redirect(
                    BackendModel::createURLForAction('index') . '&report=edited&highlight=row-' . $item['id']
                );
            }
        }
    }
}
