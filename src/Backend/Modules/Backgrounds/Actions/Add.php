<?php

namespace Backend\Modules\Backgrounds\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Meta;
use Backend\Core\Engine\Model;
use Backend\Modules\Backgrounds\Engine\Model as BackendBackgroundsModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Backend\Modules\Users\Engine\Model as BackendUsersModel;
use Backend\Core\Engine\Language as BL;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Add extends ActionAdd
{
    /**
     * Execute the actions
     */
    public function execute()
    {
        parent::execute();

        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        $this->frm = new Form('add');

        $this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
        $this->frm->addImage('image');

        // build array with options for the hidden Dropdown
        $RadiobuttonHiddenValues[] = array('label' => Language::lbl('Hidden'), 'value' => 'Y');
        $RadiobuttonHiddenValues[] = array('label' => Language::lbl('Published'), 'value' => 'N');
        $this->frm->addRadioButton('hidden', $RadiobuttonHiddenValues, 'N');

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
        $this->frm->addDropdown('background_size', $backgroundSizeValues, 'cover');
        $this->frm->addDropdown('background_position_horizontal', $backgroundPositionHorizontalValues, 'center');
        $this->frm->addDropdown('background_position_vertical', $backgroundPositionVerticalValues, 'center');
        $this->frm->addDropdown('background_repeat', $backgroundRepeatValues, 'repeat');

        // meta
        $this->meta = new Meta($this->frm, null, 'title', true);

    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        parent::parse();

        // get url
        $url = Model::getURLForBlock($this->URL->getModule(), 'detail');
        $url404 = Model::getURL(404);

        // parse additional variables
        if ($url404 != $url) {
            $this->tpl->assign('detailURL', SITE_URL . $url);
        }
        $this->record['url'] = $this->meta->getURL();

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
            else $fields['image']->addError(Language::err('FieldIsRequired'));

            // validate meta
            $this->meta->validate();

            if ($this->frm->isCorrect()) {
                // build the item
                $item['language'] = NULL;
                $item['title'] = $fields['title']->getValue();

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

                // image provided?
                if ($fields['image']->isFilled()) {
                    // build the image name
                    $item['image'] = $this->meta->getURL() . '.' . $fields['image']->getExtension();

                    // upload the image & generate thumbnails
                    $fields['image']->generateThumbnails($imagePath, $item['image']);
                }

                $item['hidden'] = $fields['hidden']->getValue();
                $item['meta_id'] = $this->meta->save();

                // background options
                $item['background_size'] = $fields['background_size']->getValue();
                $item['background_position_horizontal'] = $fields['background_position_horizontal']->getValue();
                $item['background_position_vertical'] = $fields['background_position_vertical']->getValue();
                $item['background_repeat'] = $fields['background_repeat']->getValue();

                // insert it
                $item['id'] = BackendBackgroundsModel::insert($item);

                Model::triggerEvent(
                    $this->getModule(), 'after_add', $item
                );
                $this->redirect(
                    Model::createURLForAction('index') . '&report=added&highlight=row-' . $item['id']
                );
            }
        }
    }
}
