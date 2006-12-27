<?
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

class Duration
{
	function toString ($duration, $periods = null)
    {
        if (!is_array($duration)) {
            $duration = Duration::int2array($duration, $periods);
        }
        return Duration::array2string($duration);
    }
 
    function int2array ($seconds, $periods = null)
    {        
        // Define time periods
        if (!is_array($periods)) {
            $periods = array (
                    'y'	=> 31556926,
                    'M' => 2629743,
                    'w' => 604800,
                    'd' => 86400,
                    'h' => 3600,
                    'm' => 60,
                    's' => 1
                    );
        }
 
        // Loop
        $seconds = (int) $seconds;
        foreach ($periods as $period => $value) {
            $count = floor($seconds / $value);
 
            if ($count == 0) {
                continue;
            }
 
            $values[$period] = $count;
            $seconds = $seconds % $value;
        }
 
        // Return
        if (empty($values)) {
            $values = null;
        }
 
        return $values;
    }
 
    function array2string ($duration)
    {
        if (!is_array($duration)) {
            return false;
        }
        foreach ($duration as $key => $value) {
            $segment = $value . '' . $key;
            $array[] = $segment;
        }
        $str = implode(' ', $array);
        return $str;
    }
}
 
?>