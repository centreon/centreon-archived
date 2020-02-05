<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

/**
 * Manage new feature
 *
 * Format:
 * $availableFeatures = array( array(
 * 'name' => 'Header',
 * 'version' => 2,
 * 'description' => 'New header design user experience',
 * 'visible' => true))
 *
 */
class CentreonFeature
{
    protected $db;
    protected static $availableFeatures = array();

    /**
     * Constructor
     *
     * @param CentreonDB $db - The centreon database
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Return the list of new feature to test
     *
     * @param int $userId - The user id
     * @return array - The list of new feature to ask at the user
     */
    public function toAsk($userId)
    {
        if (!is_numeric($userId)) {
            throw new Exception('The user id is not numeric.');
        }
        $result = array();
        if (count(self::$availableFeatures) != 0) {
            $query = 'SELECT feature, feature_version FROM contact_feature WHERE contact_id = ' . $userId;
            $res = $this->db->query($query);
            $toAsk = array();
            foreach (self::$availableFeatures as $feature) {
                if ($feature['visible']) {
                    $version = $feature['name'] . '__' . $feature['version'];
                    $toAsk[$version] = $feature;
                }
            }
            while ($row = $res->fetchRow()) {
                $version = $row['feature'] . '__' . $row['feature_version'];
                unset($toAsk[$version]);
            }
            foreach ($toAsk as $feature) {
                $result[] = $feature;
            }
        }
        return $result;
    }

    /**
     * Return the list of features to test
     *
     * @return array
     */
    public function getFeatures()
    {
        $result = array();
        foreach (self::$availableFeatures as $feature) {
            if ($feature['visible']) {
                $result[] = $feature;
            }
        }

        return $result;
    }

    /**
     * Return the list of feature for an user and the activated value
     *
     * @param int $userId - The user id
     * @return array
     */
    public function userFeaturesValue($userId)
    {
        if (!is_numeric($userId)) {
            throw new Exception('The user id is not numeric.');
        }
        $query = 'SELECT feature, feature_version, feature_enabled FROM contact_feature WHERE contact_id = ' . $userId;
        $res = $this->db->query($query);
        $result = array();
        while ($row = $res->fetchRow()) {
            $result[] = array(
                'name' => $row['feature'],
                'version' => $row['feature_version'],
                'enabled' => $row['feature_enabled']
            );
        }
        return $result;
    }

    /**
     * Save the user choices for feature flipping
     *
     * @param int $userId - The user id
     * @param array $features - The list of features
     */
    public function saveUserFeaturesValue($userId, $features)
    {
        if (!is_numeric($userId)) {
            throw new Exception('The user id is not numeric.');
        }
        foreach ($features as $name => $versions) {
            foreach ($versions as $version => $value) {
                $query = 'DELETE FROM contact_feature WHERE contact_id = ' . $userId . ' AND feature = "' .
                    $this->db->escape($name) . '" AND feature_version = "' . $this->db->escape($version) . '"';
                $this->db->query($query);
                $query = 'INSERT INTO contact_feature VALUES (' . $userId . ', "' . $this->db->escape($name) . '", "' .
                    $this->db->escape($version) . '", ' . (int)$value . ')';
                $this->db->query($query);
            }
        }
    }

    /**
     * Get if a feature is active for the application or an user
     *
     * @param string $name - The feature name
     * @param string $version - The feature version
     * @param int|null $userId - The user id if check for an user
     * @return bool
     */
    public function featureActive($name, $version, $userId = null)
    {
        foreach (self::$availableFeatures as $feature) {
            if ($feature['name'] === $name && $feature['version'] === $version && !$feature['visible']) {
                return false;
            }
        }
        if (is_null($userId)) {
            return true;
        }
        if (!is_numeric($userId)) {
            throw new Exception('The user id is not numeric.');
        }
        $query = 'SELECT feature_enabled FROM contact_feature
            WHERE contact_id = ' . $userId . ' AND feature = "' . $this->db->escape($name) . '"
                AND feature_version = "' . $this->db->escape($version) . '"';
        try {
            $res = $this->db->query($query);
        } catch (\Exception $e) {
            return false;
        }
        if ($res->rowCount() === 0) {
            return false;
        }
        $row = $res->fetch();
        return $row['feature_enabled'] == 1;
    }
}
