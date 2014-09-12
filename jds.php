<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.jds
 *
 * @copyright   Copyright (C) 2014 DZ Creative Studio. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once(dirname(__FILE__) . '/jdswatcher.php');

/**
 * Joomla! JDS Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.JDS
 * @since       1.5
 */
class PlgSystemJds extends JPlugin
{
    /**
     * Set observer to the team table
     *
     * @return  void
     *
     * @since   3.0
     */
    public function onAfterInitialise()
    {
        JObserverMapper::addObserverClassToClass('JTableObserverJdswatcher', 'JTableTeams', array('typeAlias' => 'com_joomsport.team'));
    }

    /**
     * Copy player from default season to newly joined season
     */
    public function onAfterRoute()
    {
        if (JFactory::getApplication()->isAdmin()) {

        } else {
            $task = JRequest::getCmd('task');
            if ($task == 'joinme') {
              $team_id = JRequest::getInt('reg_team');
              $is_team = JRequest::getInt('is_team');
              $season_id = JRequest::getInt('sid');

              if ($team_id && $is_team && $season_id) {
                  $default_season_id = $this->params->get('season_id');
                  if ($default_season_id != $season_id) {
                      $db = JFactory::getDbo();

                      // Check player for current season of this team
                      $query = $db->getQuery(true);
                      $query->select("COUNT(*)");
                      $query->from("#__bl_players_team");
                      $query->where("team_id = " . $team_id . " AND season_id = " . $season_id);
                      $db->setQuery($query);
                      $count = $db->loadResult();

                      // Only copy players from default season if there is no player for this season
                      if ($count == 0) {
                        // Get player from default season for this team
                        $query = $db->getQuery(true);
                        $query->select("player_id");
                        $query->from("#__bl_players_team");
                        $query->where("team_id = " . $team_id . " AND season_id = " . $default_season_id);
                        $db->setQuery($query);
                        $players = $db->loadObjectList();

                        foreach ($players as $player) {
                          $query = $db->getQuery(true);
                          $query->insert('#__bl_players_team');
                          $query->columns($db->quoteName(array('team_id', 'player_id', 'season_id')));
                          $query->values(implode(',', array($team_id, $player->player_id, $season_id)));
                          $db->setQuery($query);
                          $db->query();
                        }
                      }
                  }
              }
            }
        }

    }
}
