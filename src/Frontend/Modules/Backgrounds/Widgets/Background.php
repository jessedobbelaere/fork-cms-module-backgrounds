<?php

namespace Frontend\Modules\Backgrounds\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Modules\Backgrounds\Engine\Model as FrontendBackgroundsModel;

/**
 * This is a widget with background image
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Background extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        // call parent
        parent::execute();

        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        $this->tpl->assign('item', FrontendBackgroundsModel::get($this->data['id']));
    }
}
