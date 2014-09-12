<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.jds
 *
 * @copyright   Copyright (C) 2014 DZ Creative Studio. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die;

/**
 * Table class
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @link        http://docs.joomla.org/JTableObserver
 * @since       3.2
 */
class JTableObserverJdswatcher extends JTableObserver
{
    # Store plugin instance
    protected $plugin;

    # Store params instance
    protected $params;

    public static function createObserver(JObservableInterface $observableObject, $params = array())
    {
        $observer = new self($observableObject);
        $observer->plugin = JPluginHelper::getPlugin('system', 'jds');
        $observer->params = new JRegistry($observer->plugin->params);

        return $observer;
    }

    public function onAfterStore(&$result)
    {
        $team_id = $this->table->id;
        $default_season_id = $this->params->get('season_id', 0);
        var_dump($this->table);die;

        if ($team_id && $default_season_id) {
            // Check if this team is belong to default season or not
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select("COUNT(*)");
            $query->from("#__bl_season_teams");
            $query->where("season_id = " . (int) $default_season_id . " AND team_id = " . (int) $team_id);
            $db->setQuery($query);

            $count = $db->loadResult();
            if (!$count) {
              $query = "INSERT INTO #__bl_season_teams(season_id, team_id) VALUES($default_season_id, $team_id)";
              $db->setQuery($query);
              $db->query();
            }
        }
    }
}
