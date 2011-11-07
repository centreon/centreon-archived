<?php
/**
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */


class CentreonIssue
{
    protected $dbb;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     */
    public function __construct($dbb)
    {
        $this->dbb = $dbb;
    }

    /**
     * Get Children
     *
     * @param int $issueId
     * @return array
     */
    public function getChildren($issueId)
    {
        $query = "SELECT tb.issue_id, tb.host_id, tb.service_id, tb.start_time, tb.name, tb.description, tb.state, tb.output
        		  FROM (
					SELECT i.issue_id,
				   		   i.host_id,
				           i.service_id,
				           i.start_time,
				   		   h.name,
				   		   s.description,
				   		   s.state,
				   		   s.output
		    		FROM `hosts` h, `services` s, `issues` i, `issues_issues_parents` iip
					WHERE h.host_id = i.host_id
					AND s.service_id = i.service_id
					AND s.host_id = h.host_id
					AND i.issue_id = iip.child_id
					AND iip.parent_id = ".$this->dbb->escape($issueId)."

					UNION

					SELECT i2.issue_id,
				   		   i2.host_id,
				   		   i2.service_id,
				   		   i2.start_time,
				   		   h2.name,
				   		   NULL,
				   		   h2.state,
				   		   h2.output
					FROM `hosts` h2, `issues` i2, `issues_issues_parents` iip2
					WHERE h2.host_id = i2.host_id
					AND i2.service_id IS NULL
					AND i2.issue_id = iip2.child_id
					AND iip2.parent_id = ".$this->dbb->escape($issueId)."
        		  ) tb ";
        $res = $this->dbb->query($query);
        $childTab = array();
        while ($row = $res->fetchRow()) {
            foreach ($row as $key => $val) {
                $childTab[$row['issue_id']][$key] = $val;
            }
        }
        return $childTab;
    }

    /**
     * Check if issue is parent
     *
     * @param int $issueId
     * @return bool
     */
    public function isParent($issueId)
    {
        $query = "SELECT parent_id FROM issues_issues_parents WHERE parent_id = " . $this->dbb->escape($issueId) . " LIMIT 1";
        $res = $this->dbb->query($query);
        if ($res->numRows()) {
            return true;
        }
        return false;
    }
}