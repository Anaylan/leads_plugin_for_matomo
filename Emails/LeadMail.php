<?php

namespace Piwik\Plugins\Leads\Emails;

use Piwik\Piwik;
use Piwik\Site;
use Piwik\View;

class LeadMail extends \Piwik\Mail
{
    /**
     * @var string
     */
    private $toAddress;

    /**
     * @var string
     */
    private $leadData;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var string
     */
    private $login;

    public function __construct($toAddress, $idSite, $leadData)
    {
        parent::__construct();

        $this->toAddress = $toAddress;
        $this->idSite = $idSite;
        $this->leadData = $leadData;
        // $this->login = $login;

        $this->setUpEmail();
    }

    private function setUpEmail()
    {
        $siteName = Site::getNameFor($this->idSite);

        // $this->setSmtpDebug(true);


        $this->addTo($this->toAddress);
        $this->setDefaultFromPiwik();
        $this->setSubject(Piwik::translate('Leads_LeadsMail', [$siteName]));
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    protected function getDefaultBodyView()
    {
        $view = new View('@Leads/lead_mail');
        $view->site = Site::getNameFor($this->idSite);
        $view->city = $this->leadData['city'];
        $view->date = $this->leadData["date"];
        $view->name = $this->leadData["name"];
        $view->phone = $this->leadData["phone"];
        $view->href = $this->leadData["href"];
        return $view;
    }
}
