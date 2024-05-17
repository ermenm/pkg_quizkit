<?php

/**
 * @package     QuizKit
 * @subpackage  mod_quizdashboard
 * @version     1.0.0
 * @author      Michelle Ermen
 * @copyright   Copyright © 2023 MSE Digital All Rights Reserved
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

ToolBarHelper::title(Text::_('Quiz Dashboard'), 'quiz-dashboard');

$db = Factory::getDbo();
$query = $db->getQuery(true);
$query->select($db->quoteName(array('id', 'email', 'params', 'score', 'visitor_id', 'submission_time')));
$query->from($db->quoteName('#__quizkit_submissions'));
$db->setQuery($query);
$results = $db->loadObjectList();
$exportUrl = JRoute::_('index.php?option=com_ajax&module=quizdashboard&method=exportCSV&format=raw');
$params = new Registry;
$params->set('header_text', 'Quiz Dashboard');
$params->set('results', $results);
$params->set('export_url', $exportUrl);
$layout = $params->get('layout', 'default');

require ModuleHelper::getLayoutPath('mod_quizdashboard', $layout);
