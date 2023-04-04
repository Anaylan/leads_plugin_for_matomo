<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Leads;

use Piwik\Db;
use Piwik\Common;
use \Exception;

class Leads extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'CronArchive.getArchivingAPIMethodForPlugin' => 'getArchivingAPIMethodForPlugin',
        ];
    }

    // support archiving just this plugin via core:archive
    public function getArchivingAPIMethodForPlugin(&$method, $plugin)
    {
        if ($plugin == 'Leads') {
            $method = 'Leads.getExampleArchivedMetric';
        }
    }

    public function install()
    {
        try {
            $sql = "CREATE TABLE " . Common::prefixTable('leaders') . " (
                            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                            name VARCHAR(255) NOT NULL ,
                            phone VARCHAR(30) NOT NULL ,
                            siteId VARCHAR(30) NOT NULL ,
                            send INTEGER(4) ,
                            city VARCHAR(100) NOT NULL ,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                            PRIMARY KEY ( id ),
                            INDEX( siteId )
                    )  DEFAULT CHARSET=utf8 ";
            Db::exec($sql);
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    public function uninstall()
    {
        Db::dropTables(Common::prefixTable('leaders'));
    }
}
