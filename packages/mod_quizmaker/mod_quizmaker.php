<?php

/**
 * @package     QuizKit
 * @subpackage  mod_quizmaker
 * @version     1.0.0
 * @author      Michelle Ermen
 * @copyright   Copyright © 2023 MSE Digital All Rights Reserved
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

$start_title = $params->get('start_title');
$start_subtitle = $params->get('start_subtitle');
$start_image = $params->get('start_image');
$start_image_alt = $params->get('start_image_alt');
$subform_data = $params->get('field-name', array());

require_once dirname(__FILE__) . '/helper.php';

$questions_array = array();
foreach ($subform_data as $data) {
  $question = array();
  $question['question'] = Text::_($data->question);
  $question['answers'] = array();

  foreach ($data->answers as $answer) {
    $choice = array();
    $choice['answer'] = Text::_($answer->answer);
    $question['answers'][] = $choice;
  }

  $questions_array[] = $question;
}

$layout = $params->get('layout', 'default');
require ModuleHelper::getLayoutPath('mod_quizmaker', $layout);

?>




