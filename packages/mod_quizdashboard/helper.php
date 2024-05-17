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

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

class ModQuizDashboardHelper
{
  /**
   * Exports quiz submissions to a CSV file.
   *
   * @return  void
   *
   */
  public static function exportCSVAjax()
  {
    $app = Factory::getApplication();
    $module = ModuleHelper::getModule('mod_quizdashboard');
    $params = $module->params;

    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id', 'email', 'params', 'score', 'visitor_id', 'submission_time')));
    $query->from($db->quoteName('#__quizkit_submissions'));
    $db->setQuery($query);
    $results = $db->loadObjectList();

    $filename = 'quizkit_submissions.csv';
    $delimiter = ',';
    $content = implode($delimiter, array('ID', 'Email', 'Score', 'Visitor ID', 'Submission Time')) . "\n";
    foreach ($results as $result) {
      $content .= implode($delimiter, array($result->id, $result->email, $result->score, $result->visitor_id, $result->submission_time)) . "\n";
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $content;
    $app->close();
  }
}
