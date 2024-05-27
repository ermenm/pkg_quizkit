<?php

/**
 * @package     QuizKit
 * @subpackage  mod_quizmaker
 * @version     1.1.1
 * @author      Michelle Ermen
 * @copyright   Copyright © 2023 MSE Digital All Rights Reserved
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$config = array(
  'editor' => array(
    'format' => 'html',
    'plugins' => 'advlist autolink link image lists charmap print preview hr anchor pagebreak',
    'toolbar' => 'bold italic | link',
    'content_css' => 'css/content.css',
    'width' => '100%',
    'height' => '200',
    'menubar' => false,
    'statusbar' => false,
    'resize' => false,
    'invalid_elements' => 'font',
    'extended_valid_elements' => 'a[href|target=_blank|title|rel],b,i',
    'cleanup' => true,
    'convert_urls' => false,
    'relative_urls' => false,
    'remove_script_host' => false,
    'forced_root_block' => '',
    'force_p_newlines' => false,
    'force_br_newlines' => true,
    'remove_linebreaks' => false,
    'verify_html' => true,
  )
);
