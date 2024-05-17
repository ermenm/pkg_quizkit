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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Helper class for the QuizMaker module.
 *
 */
class ModQuizMakerHelper
{
  /**
   * Array of field names from the QuizMaker module parameters.
   *
   * @var array
   */
  private static $field_names = [];
  /**
   * AJAX endpoint for the quiz.
   *
   * @return void
   */
  public static function getAjax()
  {
    ob_start();

    $input = Factory::getApplication()->input;
    $questionIndex = $input->getInt('questionIndex');
    $choiceIndex = $input->getInt('choiceIndex');
    $score = $input->getInt('score');
    $response = new stdClass();
    $response->data = new stdClass();

    if (empty(self::$field_names)) {
      self::$field_names = self::getFieldNames();
    }

    if ($questionIndex === count(self::$field_names)) {
      $score_details = self::getScoreDetails($score);
      $score_title = $score_details['title'];
      $score_subtitle = $score_details['subtitle'];
      $response->data->finalScoreTitle = isset($score_title) ? $score_title : '';
      $response->data->finalScoreSubtitle = isset($score_subtitle) ? $score_subtitle : '';
    } else {
      $isCorrect = self::checkAnswer($questionIndex, $choiceIndex);
      $correct_answer = self::getCorrectAnswerIndex($questionIndex);
      $explanation = self::$field_names['field-name' . $questionIndex]['answers']['answers' . $correct_answer]['explanation'];

      $response->data->isCorrect = $isCorrect;
      $response->data->correctAnswer = $correct_answer;
      $response->data->explanation = $explanation;
    }

    header('Content-Type: application/json');
    echo json_encode($response);

    ob_end_flush();
  }

  /**
   * Sends quiz data via AJAX post request.
   *
   * @return void
   */
  public static function postQuizDataAjax()
  {
    ob_start();
    date_default_timezone_set('Europe/Amsterdam');

    $response = new stdClass();
    $response->data = new stdClass();
    $email = htmlspecialchars($_POST['email']);
    $score = htmlspecialchars($_POST['score']);
    $answers = json_encode($_POST['answers']);

    if (!self::is_valid_email($email)) {
      $message = "Geen geldig email adres";
      $response->data->error = $message;
    }

    $message = "Bedankt voor het invullen van de quiz!";
    $response->data->thankyou = $message;

    self::addRecord($score, $email, $answers);

    header('Content-Type: application/json');
    echo json_encode($response);

    ob_end_flush();
  }
  /**
   * Adds a quiz record to the database.
   *
   * @param string $score The quiz score.
   * @param string $email The email address.
   * @param string $answers The quiz answers in JSON format.
   *
   * @return void
   */
  public static function addRecord($score, $email, $answers)
  {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $submission_date = Factory::getDate()->toSql();

    $query
      ->insert($db->quoteName('#__quizkit_submissions'))
      ->columns(array('params', 'submission_time', 'email', 'score'))
      ->values($db->quote($answers) . ', ' . $db->quote($submission_date) . ', ' . $db->quote($email) . ', ' . $db->quote($score));

    $db->setQuery($query);
    $db->execute();
  }

  /**
   * Check if email is valid
   *
   * @param string $email
   *
   * @return bool
   */
  public static function is_valid_email(string $email): bool
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }


  /**
   * Returns an array of the module parameters for the "mod_quizmaker" module.
   * 
   * @return array An associative array of the module parameters.
   */
  private static function getModuleParams(): array
  {
    $module = ModuleHelper::getModule('mod_quizmaker');
    $params = $module->params;
    return json_decode($params, true);
  }

  /**
   * Return an array of field names from the QuizMaker module parameters.
   *
   * @return array An array of field names.
   */
  private static function getFieldNames(): array
  {
    $field_names_array = self::getModuleParams()['field-name'];
    return $field_names_array;
  }

  /**
   * Retrieves the score details based on the provided score.
   * 
   * @param int $score The score to retrieve details for.
   * 
   * @return array An associative array containing the title and subtitle for the provided score.
   */
  private static function getScoreDetails($score): array
  {
    $scoreDetails = self::getModuleParams();
    $result = array();

    switch (true) {
      case ($score <= 25):
        $result['title'] = $scoreDetails['low_score_title'];
        $result['subtitle'] = $scoreDetails['low_score_subtitle'];
        break;
      case ($score <= 50):
        $result['title'] = $scoreDetails['mid_score_title'];
        $result['subtitle'] = $scoreDetails['mid_score_subtitle'];
        break;
      case ($score <= 75):
        $result['title'] = $scoreDetails['high_score_title'];
        $result['subtitle'] = $scoreDetails['high_score_subtitle'];
        break;
      case ($score <= 100):
        $result['title'] = $scoreDetails['excellent_score_title'];
        $result['subtitle'] = $scoreDetails['excellent_score_subtitle'];
        break;
      default:
        break;
    }

    return $result;
  }

  /**
   * Check if the given choice index is the correct answer for the given question index.
   *
   * @param int $questionIndex The index of the question to check.
   * @param int $choiceIndex The index of the choice to check.
   * @return bool True if the choice is the correct answer, false otherwise.
   */
  private static function checkAnswer(int $questionIndex, int $choiceIndex): bool
  {
    $question = self::$field_names['field-name' . $questionIndex];
    $answer = $question['answers']['answers' . $choiceIndex];
    return isset($answer['correct']) && $answer['correct'] === '1';
  }

  /**
   * Return the index of the correct answer for the given question index.
   *
   * @param int $questionIndex The index of the question to check.
   * @return int The index of the correct answer for the given question index.
   */
  public static function getCorrectAnswerIndex(int $questionIndex): int
  {
    $question = self::$field_names['field-name' . $questionIndex];
    foreach ($question['answers'] as $choice) {
      if (isset($choice['correct']) && $choice['correct'] == '1') {
        $correctAnswer = $choice['answer'];
      }
    }

    $correctAnswerIndex = array_search($correctAnswer, array_column($question['answers'], 'answer'));

    return $correctAnswerIndex;
  }
}
