<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Leads;

use Piwik\API\Request as APIRequest;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\Leads\Emails\LeadMail;
use Piwik\Request;
use Piwik\Site;

/**
 * API for plugin Leads
 *
 * @method static \Piwik\Plugins\Leads\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var SettingsProvider
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function getAnswerToLife(bool $truth = true): int
    {
        if ($truth) {
            return 42;
        }

        return 24;
    }


    public function getExampleReport(string $idSite, string $period, string $date, bool $wonderful = false): DataTable
    {
        $table = DataTable::makeFromSimpleArray(array(
            array('label' => 'My Label 1', 'nb_visits' => '1'),
            array('label' => 'My Label 2', 'nb_visits' => '5'),
        ));

        return $table;
    }

    /**
     * Creates a new report and schedules it.
     *
     * @param string $who
     * @param string $phone Phone number in russian format
     *
     * @return int idReport generated
     */
    public function addReport(
        $who,
        $phone
    ) {
        $header = getallheaders();

        if ($this->sendByMail(array(
            'name' => Common::sanitizeInputValues($who),
            'phone' => Common::sanitizeInputValues($phone),
            // 'siteid' => $this->getSiteId($header['Origin'], $header['Referer'])
        ), 'alexanderskobe@yandex.ru') == true) {
            $send = true;
        } else {
            $send = false;
        }



        try {
            $dataTable = new DataTable();
            $data = array(
                'name' => Common::sanitizeInputValues($who),
                'phone' => Common::sanitizeInputValues($phone),
                'siteid' => $this->getSiteId($header['Origin'], $header['Referer']),
                // 'send' => $send,
            );
            $dataTable->addRowFromSimpleArray($data);

            $sql = sprintf('INSERT INTO ' . Common::prefixTable('leaders') . ' (`name`, `phone`, `siteId`, `send`) VALUES (?,?,?,?)');
            $this->db->query($sql, $data);

            // $this->sendByMail(array(
            //     'name' => $who,
            //     'phone' => $phone,
            //     'siteid' => 1
            //     // 'siteid' => $this->getSiteId($header['Origin'], $header['Referer'])
            // ), 'alexanderskobe@yandex.ru');


            return $dataTable;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Another example method that returns a data table.
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getLeaders($period, $date, $segment = false)
    {
        $table = new DataTable();
        $rows = $this->db->fetchAll('SELECT * FROM ' . Common::prefixTable('leaders'));

        foreach ($rows as $row) {
            $rn = new Row();

            try {
                $rn->setColumn('name', $row['name']);
                $rn->setColumn('phone', $row['phone']);
                $rn->setColumn('hostname', Site::getNameFor($row['siteId']));

                $table->addRow($rn);
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        // $table->addRowFromArray(array(Row::COLUMNS => array('phone' => 5)));

        return $table;
    }

    private function sendByMail($data, $email)
    {
        $login = Piwik::getCurrentUserLogin();

        $leadData = array(
            "date" => Date::now(),
            "name" => $data['name'],
            "phone" => $data['phone'],
            "href" => '#'
        );

        $mail = new LeadMail($login, $email, $data['siteid'], $leadData);
        return $mail->send();
    }

    public function getSiteId($origin, $referer)
    {
        $row = $this->db->fetchRow('SELECT idsite FROM ' . Common::prefixTable('site') . ' where main_url = ? or main_url = ?', [$origin, $referer]);
        return $row['idsite'];
    }
}
