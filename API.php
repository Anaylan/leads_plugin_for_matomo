<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Leads;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\Leads\Emails\LeadMail;
use Piwik\Site;
use Piwik\Notification;
use Piwik\Plugins\UserCountry\API as UserCountryAPI;
use Piwik\Plugins\UserCountry\UserCountry;

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

    private $settings;
    /**
     * @var string
     */
    private $email;

    public function __construct(Db $db)
    {
        $this->db = $db;
        $this->settings = new SystemSettings();
        $this->email = $this->settings->leadsEmail->getValue();
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

        if ($this->email !== '') {
            if ($this->sendByMail(array(
                'name' => Common::sanitizeInputValues($who),
                'phone' => Common::sanitizeInputValues($phone),
                'siteid' => $this->getSiteId($header['Origin'], $header['Referer']),
                // 'city' => UserCountry.
            )) == true) {
                $send = true;
            } else {
                $send = false;
            }
        } else {
            $messageId = 'Leads_EmailForLeadsIsEmpty';
            $errorMessage = new Notification('Lead did not send, because email for leads is empty');
            $errorMessage->type = Notification::TYPE_PERSISTENT;
            $errorMessage->context = Notification::CONTEXT_ERROR;
            Notification\Manager::notify($messageId, $errorMessage);
        }

        try {
            $dataTable = new DataTable();

            $ip = IP::getIpFromHeader(); // your ip address here
            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));

            $data = array(
                'name' => Common::sanitizeInputValues($who),
                'phone' => Common::sanitizeInputValues($phone),
                'siteid' => $this->getSiteId($header['Origin'], $header['Referer']),
                'send' => $send,
                'city' => $query['city']
            );
            $dataTable->addRowFromSimpleArray($data);

            $sql = sprintf('INSERT INTO ' . Common::prefixTable('leaders') . ' (`name`, `phone`, `siteId`, `send`, `city`) VALUES (?,?,?,?,?)');
            $this->db->query($sql, $data);

            // $country = new UserCountryAPI();



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
                // $rn->setColumn('name', $row['name']);
                $rn->setColumn('phone', $row['phone']);
                $rn->setColumn('hostname', Site::getNameFor($row['siteId']));
                $rn->setColumn('city', $row['city']);
                $rn->setColumn('label', $row['name']);

                $table->addRow($rn);
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        // $table->addRowFromArray(array(Row::COLUMNS => array('phone' => 5)));

        return $table;
    }

    private function sendByMail($data)
    {
        // $login = Piwik::getCurrentUserLogin();

        $city = 'тест';

        $leadData = array(
            "date" => Date::now(),
            "city" => $city,
            "name" => $data['name'],
            "phone" => $data['phone'],
            "href" => '#'
        );

        $mail = new LeadMail($this->email, $data['siteid'], $leadData);
        return $mail->send();
    }

    public function getSiteId($origin, $referer)
    {
        $row = $this->db->fetchRow('SELECT idsite FROM ' . Common::prefixTable('site') . ' where main_url = ? or main_url = ?', [$origin, $referer]);
        return $row['idsite'];
    }
}
