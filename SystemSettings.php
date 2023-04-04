<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Leads;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NotEmpty;
use Piwik\Validators\Email;
/**
 * Defines Settings for Leads.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $leadsEmail;


    protected function init()
    {
        // Email address for Leads
        $this->leadsEmail = $this->createLeadsEmailSetting();
    }

    private function createLeadsEmailSetting()
    {
        return $this->makeSetting('leadsEmail', $default='', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Email for leads';
            $field->description = 'Нou must specify the email where the leads will come';
            $field->uiControlAttributes = array('type' => 'email');
            $field->validators[] = new NotEmpty(); 
            $field->validators[] = new Email();
        });
    }
}
