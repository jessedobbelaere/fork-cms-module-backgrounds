<?php

namespace Backend\Modules\Backgrounds\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the Backgrounds module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Installer extends ModuleInstaller
{
    public function install()
    {
        // import the sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // install the module in the database
        $this->addModule('Backgrounds');

        // install the locale, this is set here beceause we need the module for this
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        $this->setModuleRights(1, 'Backgrounds');

        $this->setActionRights(1, 'Backgrounds', 'Index');
        $this->setActionRights(1, 'Backgrounds', 'Add');
        $this->setActionRights(1, 'Backgrounds', 'Edit');
        $this->setActionRights(1, 'Backgrounds', 'Delete');

        // add extra's
        //$subnameID = $this->insertExtra('Backgrounds', 'block', 'Backgrounds', null, null, 'N', 1000);
        //$this->insertExtra('Backgrounds', 'block', 'BackgroundsDetail', 'Detail', null, 'N', 1001);

        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $navigationclassnameId = $this->setNavigation(
            $navigationModulesId,
            'Backgrounds',
            'backgrounds/index',
            array('backgrounds/add','backgrounds/edit')
        );

    }
}
