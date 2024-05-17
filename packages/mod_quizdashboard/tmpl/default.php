<?php

/**
 * @package     QuizKit
 * @subpackage  mod_quizdashboard
 * @version     1.1.0
 * @author      Michelle Ermen
 * @copyright   Copyright © 2023 MSE Digital All Rights Reserved
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Factory;

/**
 * Render a table with quiz submissions
 *
 */
// Get the URL to export the results to CSV
$exportUrl = $params->get('export_url');

// Create a link to export the results to CSV
$exportButton = HTMLHelper::_('link', $exportUrl, Text::_('EXPORTEREN NAAR CSV'), array(
  'class' => 'btn btn-primary'
));

$jinput = Factory::getApplication()->input;
$limitstart = (int) JFactory::getApplication()->input->get('limitstart', 0);
$limit = $jinput->get('limit', 10, 'INT');

// Bouw de query
$db = Factory::getDbo();
$query = $db->getQuery(true);
$query->select($db->quoteName(array('id', 'email', 'params', 'score', 'visitor_id', 'submission_time')))
  ->from($db->quoteName('#__quizkit_submissions'))
  ->setLimit($limit, $limitstart)
  ->order('submission_time DESC');


// Voer de query uit
$db->setQuery($query);
$results = $db->loadObjectList();

// Totaal aantal resultaten
$queryCount = $db->getQuery(true);
$queryCount->select('COUNT(*)')
  ->from($db->quoteName('#__quizkit_submissions'));
$db->setQuery($queryCount);
$total = $db->loadResult();

// Pagination
$jpagination = new Pagination($total, $limitstart, $limit);
$jpagination->setAdditionalUrlParam('option', 'mod_quizdashboard');
$nextStart = $limitstart + $limit;

?>
<form method="post" name="adminForm" id="adminForm" novalidate>
  <input type="hidden" name="limitstart" value="<?php echo $nextStart; ?>" />
  <table class="table table-striped">
    <thead>
      <tr>
        <th><?php echo Text::_('ID'); ?></th>
        <th><?php echo Text::_('EMAIL'); ?></th>
        <th><?php echo Text::_('SCORE'); ?></th>
        <th><?php echo Text::_('TIJD/DATUM VOLTOOID'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $result) : ?>
        <tr>
          <td><?php echo $result->id; ?></td>
          <td><?php echo $result->email; ?></td>
          <td><?php echo $result->score; ?></td>
          <td><?php echo $result->submission_time; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</form>

<?php

echo $jpagination->getListFooter();
echo $exportButton;

?>