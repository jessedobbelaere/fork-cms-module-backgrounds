<?php

namespace Backend\Modules\Backgrounds\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Authentication;
use Backend\Core\Engine\DataGridDB;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model;
use Backend\Modules\Backgrounds\Engine\Model as BackendBackgroundsModel;

/**
 * This is the index-action (default), it will display the overview of Backgrounds posts
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Index extends ActionIndex
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->loadDataGrid();

        $this->parse();
        $this->display();
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGrid()
    {
        $this->dataGrid = new DataGridDB(
            BackendBackgroundsModel::QRY_DATAGRID_BROWSE,
            Language::getWorkingLanguage()
        );

        // reform date
        $this->dataGrid->setColumnFunction(
            array('Backend\Core\Engine\DataGridFunctions', 'getLongDate'),
            array('[created_on]'), 'created_on', true
        );

        // add preview
        $this->dataGrid->addColumn('cover', \SpoonFilter::ucfirst(Language::lbl('Cover')));
        $this->dataGrid->setColumnURL('cover', Model::createURLForAction('Edit') . '&amp;id=[id]');
        $this->dataGrid->setColumnFunction(array(new BackendBackgroundsModel(), 'getImageThumb'), array('[id]', $this->getModule()), 'cover', true);
        $this->dataGrid->setColumnAttributes('cover', array('width' => '50px'));

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $this->dataGrid->addColumn(
                'edit', null, Language::lbl('Edit'),
                Model::createURLForAction('Edit') . '&amp;id=[id]',
                Language::lbl('Edit')
            );
            $this->dataGrid->setColumnURL(
                'title', Model::createURLForAction('Edit') . '&amp;id=[id]'
            );
        }

        // Set sequence of the columns
        $this->dataGrid->setColumnsSequence(array('cover','title','created_on','edit'));
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        // parse the dataGrid if there are results
        $this->tpl->assign('dataGrid', (string) $this->dataGrid->getContent());
    }
}
