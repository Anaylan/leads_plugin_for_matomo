<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Leads;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Leads\Emails\LeadMail;
use Piwik\View;

/**
 * A controller lets you for example create a page that can be added to a menu. For more information read our guide
 * http://developer.piwik.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Controller and
 * http://developer.piwik.org/api-reference/Piwik/View
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        // Piwik::checkUserIsNotAnonymous();
        $view = new View('@Leads/index.twig');

        return $view->render();
    }
}
