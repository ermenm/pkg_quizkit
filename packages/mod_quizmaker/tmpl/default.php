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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::stylesheet(Uri::base() . 'modules/mod_quizmaker/tmpl/mod_quizmaker.css', array('media' => 'screen'));

$startImage = $params->get('start_image') ? $params->get('start_image') : '';
$spinner = $params->get('spinner_image') ? $params->get('spinner_image') : '';
$optinText = $params->get('user_agreement_text') ? $params->get('user_agreement_text') : '';

?>
<form id="qz-form-quiz" class="qz-control-group" onsubmit="submitForm(event); return false;">

  <div class="qz-quiz-container">
    <div class="qz-controls">
      <div class="qz-start qz-section-container">
        <div class="qz-start-image qz-question-image">
          <?php if ($startImage) : ?>
            <img src="<?php echo $startImage; ?>" alt="<?php echo $start_image_alt; ?>">
          <?php endif; ?>
        </div>
        <div class="qz-question-container">
          <h3 class="qz-question-text" tabindex="0">
            <?php echo $params->get('start_title'); ?>
          </h3>
          <p class="qz-subtext" tabindex="0">
            <?php echo $params->get('start_subtitle'); ?>
          </p>
        </div>
      </div>
      <div class="qz-button-wrapper qz-start">
        <div class="qz-button-container">
          <button type="button" id="qz-start-button" class="qz-button">Start de quiz</button>
        </div>
      </div>
      <div class="qz-top-bar-score qz-hidden">
        <div class="qz-progress-container">
          <div class="qz-progress-text-container">
            <label class="qz-progress-text" for="qz-progressbar"></label>
          </div>
          <div class="qz-progress-bar" id="qz-progressbar" role="progressbar" aria-valuemin="0" aria-valuemax="100" tabindex="0">
            <div class="qz-progress"></div>
          </div>
        </div>
        <div class="qz-score" tabindex="0">
          <label class="qz-score-label">score</label>
          <div class="qz-score qz-section-container qz-questions">
            <div class="qz-score-container">
              <div class="qz-score-wrapper">
                <div class="qz-score-section qz-hidden">
                  <p id="qz-score"></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="qz-questions" class="qz-questions">
        <?php foreach ($subform_data as $i => $data) : ?>
          <?php $questionIndex = substr($i, -1);
          $questionImage = $data->image ? $data->image : '';
          $imageAlt = $data->image_question_alt ? $data->image_question_alt : '';
          ?>
          <div class="qz-question qz-question-list qz-section-container" id="qz-question-<?php echo $i; ?>" data-question-index="<?php echo $questionIndex ?>">
            <div class="qz-question-container">
              <input type="hidden" name="questionIndex" value="<?php echo $questionIndex ?>">
              <input type="hidden" class="qz-field-questions" name="field-question-<?php echo $questionIndex; ?>" value="" />
              <h3 class="qz-question-text" tabindex="0">
                <?php echo Text::_($data->question); ?>
              </h3>
              <div role="radiogroup" aria-checked="false" aria-labelledby="question-<?php echo $i; ?>">
                <?php foreach ($data->answers as $j => $answer) {
                  $index = substr($j, -1);
                ?>
                  <div class="qz-choice-container" role="radio" tabindex="0" onclick="enableHandleChoiceClick(event)" onkeydown="enableHandleChoiceKeyDown(event)">
                    <p class="qz-choice-text" aria-describedby="<?php echo Text::_($data->question); ?>" data-question="<?php echo Text::_($data->question); ?>" data-choice-index="<?php echo $index; ?>">
                      <?php echo Text::_($answer->answer); ?>
                    </p>
                    <input type="hidden" name="choiceIndex" value="<?php echo $index ?>">
                  </div>
                <?php
                }
                ?>
              </div>
              <div class="qz-explanation-section" aria-live="assertive">
                <div id="qz-right-answer-context" class="qz-context qz-hidden" aria-checked="true">
                  <p>
                    <?php echo Text::_('MOD_QUIZMAKER_SUCCESS'); ?>
                  </p>
                </div>
                <div id="qz-wrong-answer-context" class="qz-context qz-hidden" aria-checked="false">
                  <p>
                    <?php echo Text::_('MOD_QUIZMAKER_FAULTY'); ?>
                  </p>
                </div>
              </div>
            </div>
            <div class="qz-question-image">
              <?php if ($questionImage) : ?>
                <img src="<?php echo $questionImage; ?>" alt="<?php echo $imageAlt; ?>">
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="qz-button-wrapper qz-hidden">
        <div class="qz-button-container qz-next">
          <div id="qz-spinner" class="qz-hidden">
            <?php if ($spinner) : ?>
              <img src="<?php echo $spinner; ?>" aria-hidden="true">
            <?php endif; ?>
          </div>
          <button type="button" id="qz-next-button" class="qz-button qz-hidden">Volgende vraag <i class="qz-icon">›</i></button>
        </div>
      </div>
    </div>
  </div>
  <div class="qz-email-submission-form ">
    <div name="qz-email-submission-form" id="esf">
      <div class="qz-fields">
        <div class="qz-control-group qz-email-input">
          <div class="qz-control-input">
            <input type="email" name="email" id="qz-email" required placeholder="Vul je email in" class="qz-input">
          </div>
        </div>
        <div class="qz-control-input">
          <p><?php echo Text::_($optinText); ?></p>
          <div class="qz-optin-wrapper">
            <input type="checkbox" name="email" id="qz-email-optin" required="" class="qz-input" tabindex="0">
            <label for="qz-email-optin"><?php echo Text::_('MOD_QUIZMAKER_AGREE'); ?></label>
          </div>
        </div>
        <div class="qz-control-group">
          <div class="qz-control-input">
            <div class="qz-text-left">
              <div class="qz-button-wrapper">
                <div class="qz-button-container">
                  <button type="submit" class="qz-button" id="qz-submit-button" disabled>
                    Aanmelden
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <input type="hidden" name="score" id="qz-score-input" value="">
      </div>
    </div>
    <div class="qz-response qz-response-success">
      <span id="qz-success-message"></span>
    </div>
    <div class="qz-response qz-response-error">
      <span id="qz-error-message"></span>
    </div>
    <div id="qz-email-error-message" class="qz-response-error">
      <span id="qz-error-message"></span>
    </div>
  </div>
</form>


<script type="text/javascript">
  const data = <?php echo json_encode($questions_array, JSON_HEX_TAG); ?>;
</script>

<script src="<?php echo Uri::base() . 'modules/mod_quizmaker/tmpl/quizmaker.js'; ?>"></script>