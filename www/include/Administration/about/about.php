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

if (!isset($centreon)) {
    exit();
}

$DBRESULT = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version' LIMIT 1");
$release = $DBRESULT->fetchRow();

?><center>
    <div style="width:700px;padding:20px;border:1px #CDCDCD solid;">
            <table class="table">
                <tr>
                    <td>
                        <h3>Centreon <?php print $release["value"]; ?></h3>
                        <p><em>(commit <a href="https://github.com/centreon/centreon/tree/@COMMIT@">@COMMIT@</a>)</em></p><br>
                    </td>
                    <td>
                        Developed by <a href="http://www.centreon.com">Centreon</a> and <a href="https://centreon.github.io/">community</a>
                    </td>
                    <td style="text-align: center" rowspan="3"><img src="./img/centreon.png" alt="logo centreon" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        <h3><?php echo _("Project Leaders"); ?></h3>
                    </td>
                    <td>
                        -&nbsp;Julien Mathis<br>
                        -&nbsp;Romain Le Merlus<br>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;padding-top :10px;">
                        <h3><?php echo _("Developers"); ?></h3>
                    </td>
                    <td style="padding-top :10px;">
                        <p>Adrien Gelibert</p>
                        <p>Colin Gagnaire</p>
                        <p>Danijel Halupka</p>
                        <p>David Boucher</p>
                        <p>Etienne Gautier</p>
                        <p>Jérémy Delpierre</p>
                        <p>Jiliang Wang</p>
                        <p>Kevin Duret</p>
                        <p>Laurent Calvet</p>
                        <p>Laurent Pinsivy</p>
                        <p>Loïc Laurent</p>
                        <p>Matthieu Kermagoret</p>
                        <p>Maximilien Bersoult</p>
                        <p>Quentin Garnier</p>
                        <p>Stéphane Chapron</p>
                        <p>Sylvestre Gallon</p>
                        <p>Valentin Hristov</p>
                        <p>Victor Vassilev</p>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td  style="vertical-align: top;padding-top :10px;"><h3 style='text-align:left;'><?php echo _("Contributors"); ?></h3></td>
                    <td style="padding-top: 10px;" colspan="2">
                        <table class="table">
                            <tr>
                                <td>Adrien Morais</td>
                                <td>Loïc Fontaine</td>
                            </tr>
                            <tr>
                                <td>Benjamin Robert</td>
                                <td>Louis Sautier</td>
                            </tr>
                            <tr>
                                <td>btassite</td>
                                <td>Luiz Felipe Aranha</td>
                            </tr>
                            <tr>
                                <td>Cédric Meschin</td>
                                <td>Luiz Gustavo Costa</td>
                            </tr>
                            <tr>
                                <td>Charles Gautier</td>
                                <td>Marie Gallardo</td>
                            </tr>
                            <tr>
                                <td>CPbN</td>
                                <td>Remi Werquin</td>
                            </tr>
                            <tr>
                                <td>Eric Coquard</td>
                                <td>Samuel Mutel</td>
                            </tr>
                            <tr>
                                <td>Fabien Thepaut</td>
                                <td>Sebastien Boulianne</td>
                            </tr>
                            <tr>
                                <td>Guillaume Watteeux</td>
                                <td>Simon Bomm</td>
                            </tr>
                            <tr>
                                <td>Ira Janssen</td>
                                <td>SuL</td>
                            </tr>
                            <tr>
                                <td>Jean Baptiste Borrel</td>
                                <td>Thi Uyên Dang</td>
                            </tr>
                            <tr>
                                <td>Lionel Assepo</td>
                                <td>uncleflo</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <br /><?php print _("And many others..."); ?><br />
                                    <?php print _("You can see the full list by visiting the Centreon's Github"); ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                </tr>
            </table>
        </div>
    </div>
</div>
</center>
