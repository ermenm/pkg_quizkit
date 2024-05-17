<?php

/**
 * @package     QuizKit
 * @version     1.1.0
 * @author      Michelle Ermen
 * @copyright   Copyright © 2023 MSE Digital All Rights Reserved
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;

/**
 * Installer script
 * 
 * Creates a new table on install
 * 
 * Updates table on install
 * 
 */
class pkg_QuizKitInstallerScript extends InstallerScript
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
      params varchar(255) NOT NULL,
      score float NOT NULL,
      submission_time datetime NOT NULL,
      PRIMARY KEY (id)
    )";

    $db->setQuery($query);
    $result = $db->execute();

    if (!$result) {
      Factory::getApplication()->enqueueMessage($db->stderr(), 'error');
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

    // Verwijder de tabel
    $query = "DROP TABLE IF EXISTS #__quizkit_submissions";
    $db->setQuery($query);
    $result = $db->execute();

    if (!$result) {
      Factory::getApplication()->enqueueMessage($db->stderr(), 'error');
      return false;
    }

    // Verwijder de bestanden van de module
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');

    $modulePaths = [
      JPATH_SITE . '/modules/mod_quizmaker',
      JPATH_ADMINISTRATOR . '/modules/mod_quizdashboard'
    ];

    foreach ($modulePaths as $path) {
      if (JFolder::exists($path)) {
        JFolder::delete($path);
      }
    }

    // Verwijder de vermeldingen uit de `extensions` tabel
    $query = $db->getQuery(true)
      ->delete($db->quoteName('#__extensions'))
      ->where($db->quoteName('element') . ' = ' . $db->quote('mod_quizmaker'))
      ->orWhere($db->quoteName('element') . ' = ' . $db->quote('mod_quizdashboard'));
    $db->setQuery($query);
    $db->execute();

    echo '<p>The module has been uninstalled and the table has been removed.</p>';
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

    // Check if 'params' column is not already TEXT and modify it if necessary
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

    // Create the table if it does not exist
    $query = "CREATE TABLE IF NOT EXISTS #__quizkit_submissions (
            id int(11) NOT NULL AUTO_INCREMENT,
            email varchar(50) NOT NULL,
            params TEXT NOT NULL,
            score float NOT NULL,
            submission_time datetime NOT NULL,
            PRIMARY KEY (id)
        )";

    $db->setQuery($query);
    $result = $db->execute();

    if (!$result) {
      Factory::getApplication()->enqueueMessage($db->stderr(), 'error');
      return false;
    }

    echo '<p>The module has been updated to version ' . $parent->getManifest()->version . '.</p>';
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
