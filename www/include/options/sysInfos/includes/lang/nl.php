<?php
//
// phpSysInfo - A PHP System Information Script
// http://phpsysinfo.sourceforge.net/
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// $Id$

if (PHP_OS == 'WINNT') {
  $text['locale']         = 'dutch'; // (windows) 
}
else {	
  $text['locale']         = 'nl-NL'; // (Linux and friends(?))
}

$text['title']          = 'Systeem Informatie';

$text['vitals']         = 'Systeem overzicht';
$text['hostname']       = 'Toegewezen naam';
$text['ip']             = 'IP-adres';
$text['kversion']       = 'Kernelversie';
$text['dversion']       = 'Distributie';
$text['uptime']         = 'Uptime';
$text['users']          = 'Huidige gebruikers';
$text['loadavg']        = 'Gemiddelde belasting';

$text['hardware']       = 'Hardware overzicht';
$text['numcpu']         = 'Processors';
$text['cpumodel']       = 'Model';
$text['cpuspeed']       = 'CPU snelheid';
$text['busspeed']       = 'BUS snelheid';
$text['cache']          = 'Cache grootte';
$text['bogomips']       = 'Systeem Bogomips';

$text['pci']            = 'PCI Apparaten';
$text['ide']            = 'IDE Apparaten';
$text['scsi']           = 'SCSI Apparaten';
$text['usb']            = 'USB Apparaten';

$text['netusage']       = 'Netwerkgebruik';
$text['device']         = 'Apparaat';
$text['received']       = 'Ontvangen';
$text['sent']           = 'Verzonden';
$text['errors']         = 'Err/Drop';

$text['memusage']       = 'Geheugengebruik';
$text['phymem']         = 'Fysiek geheugen';
$text['swap']           = 'Swap geheugen';

$text['fs']             = 'Aangesloten bestandssystemen';
$text['mount']          = 'Mount';
$text['partition']      = 'Partitie';

$text['percent']        = 'Percentage gebruikt';
$text['type']           = 'Type';
$text['free']           = 'Vrij';
$text['used']           = 'Gebruikt';
$text['size']           = 'Grootte';
$text['totals']         = 'Totaal';

$text['kb']             = 'KB';
$text['mb']             = 'MB';
$text['gb']             = 'GB';

$text['none']           = 'geen';

$text['capacity']       = 'Capaciteit';
  
$text['template']       = 'Opmaak-model';
$text['language']       = 'Taal';
$text['submit']         = 'Toepassen';
$text['created']        = 'Gegenereerd door';
$text['gen_time']       = 'op %d %B %Y, om %H:%M';

$text['days']           = 'dagen';
$text['hours']          = 'uren';
$text['minutes']        = 'minuten';
  
$text['temperature']    = 'Temperatuur';
$text['voltage']        = 'Voltage';
$text['fans']           = 'Fans';
$text['s_value']        = 'Waarde';
$text['s_min']          = 'Min';
$text['s_max']          = 'Max';
$text['s_div']          = 'Div';
$text['hysteresis']     = 'Hysterie';
$text['s_limit']        = 'Limiet';
$text['s_label']        = 'Omschrijving';
$text['degree_mark']    = '&ordm;C';
$text['voltage_mark']   = 'V';
$text['rpm_mark']       = 'RPM';

$text['app']		= '- Kernel + applications';
$text['buffers']	= '- Buffers';
$text['cached']		= '- Cached';

?>
