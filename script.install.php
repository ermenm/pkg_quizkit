<?php

/**
 * @package     QuizKit
 * @version     1.1.1
 * @author      Michelle Ermen
 * @copyright   Copyright © 2023 MSE Digital All Rights Reserved
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Error\Error;

/**
 * Installer script
 * 
 * Creates a new table on install
 * 
 * Updates table on install
 * 
 */

class pkg_QuizKitInstallerScript
{
  /**
   * Method to install the extension
   *
   * @return void
   */
  function install($parent)
  {
    $db = Factory::getDBO();
    $query = "CREATE TABLE IF NOT EXISTS #__quizkit_submissions (
      id int(11) NOT NULL AUTO_INCREMENT,
      email varchar(50) NOT NULL,
      params TEXT NOT NULL,
      visitor_id int(11) NOT NULL DEFAULT 0,
      score float NOT NULL,
      submission_time datetime NOT NULL,
      PRIMARY KEY (id)
    )";

    $db->setQuery($query);
    try {
      $db->execute();
      echo '<p>The module has been installed.</p>';
    } catch (Exception $e) {
      Error::raiseWarning(500, $e->getMessage());
      return false;
    }
    echo '<p>The module has been installed.</p>';
  }

  /**
   * Method to uninstall the extension
   * $parent is the class calling this method
   *
   * @return void
   */
  function uninstall($parent)
  {
    $db = Factory::getDBO();

    try {
      $db->execute();
      echo '<p>The module has been uninstalled and the table has been removed.</p>';
    } catch (Exception $e) {
      Error::raiseWarning(500, $e->getMessage());
      return false;
    }
  }

  /**
   * Method to update the extension
   * $parent is the class calling this method
   *
   * @return void
   */
  function update($parent)
  {
    $db = Factory::getDBO();

    // Controleer of de kolom 'params' niet al van het type TEXT is en wijzig deze indien nodig
    $query = $db->getQuery(true)
      ->select('COLUMN_TYPE')
      ->from('INFORMATION_SCHEMA.COLUMNS')
      ->where('TABLE_SCHEMA = DATABASE()')
      ->where('TABLE_NAME = ' . $db->quote($db->getPrefix() . 'quizkit_submissions'))
      ->where('COLUMN_NAME = ' . $db->quote('params'));

    $db->setQuery($query);
    $columnType = $db->loadResult();

    if ($columnType !== 'text') {
      $query = 'ALTER TABLE ' . $db->quoteName('#__quizkit_submissions') . ' MODIFY COLUMN ' . $db->quoteName('params') . ' TEXT';
      $db->setQuery($query);
      $db->execute();
    }

    // set default to 0 on column visitor_id
    $query = 'ALTER TABLE ' . $db->quoteName('#__quizkit_submissions') . ' MODIFY COLUMN ' . $db->quoteName('visitor_id') . ' int(11) NOT NULL DEFAULT 0';
    $db->setQuery($query);
    $db->execute();

    $query = "CREATE TABLE IF NOT EXISTS #__quizkit_submissions (
        id int(11) NOT NULL AUTO_INCREMENT,
        email varchar(50) NOT NULL,
        params TEXT NOT NULL,
        visitor_id int(11) NOT NULL DEFAULT 0,
        score float NOT NULL,
        submission_time datetime NOT NULL,
        PRIMARY KEY (id)
    )";

    $db->setQuery($query);

    $db->setQuery($query);
    try {
      $db->execute();

      // Load the manifest file to get the version
      $manifest = $parent->getParent()->manifest;
      $version = (string) $manifest->version;

      echo '<p>The module has been updated to version ' . $version . '.</p>';
    } catch (Exception $e) {
      Error::raiseWarning(500, $e->getMessage());
      return false;
    }
  }

  /**
   * Method to run before an install/update/uninstall method
   * $parent is the class calling this method
   * $type is the type of change (install, update or discover_install)
   *
   * @return void
   */
  function preflight($type, $parent)
  {
  }

  /**
   * Method to run after an install/update/uninstall method
   * $parent is the class calling this method
   * $type is the type of change (install, update or discover_install)
   *
   * @return void
   */
  function postflight($type, $parent)
  {
  }
}
