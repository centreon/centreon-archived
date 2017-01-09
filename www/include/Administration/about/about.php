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
                    <h3>Centreon <?php print $release["value"]; ?></h3> <br>

                    </td>
                    <td>
                        Developed by <a href="http://www.centreon.com">Centreon</a>
                    </td>
                    <td style="text-align: center" rowspan="3"><img src="./img/centreon.png" alt="logo centreon" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">
                        <h3><?php echo _("Project Leaders"); ?></h3>
                    </td>
                    <td>
                        -&nbsp;<a href="mailto:jmathis@centreon.com">Julien Mathis</a> <br>
                        -&nbsp;<a href="mailto:rlemerlus@centreon.com">Romain Le Merlus</a> <br>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;padding-top :10px;">
                        <h3><?php echo _("Developers"); ?></h3>
                    </td>
                    <td style="padding-top :10px;">
                        <p>Lionel Assepo</p>
                        <p>Maximilien Bersoult</p>
                        <p>Kevin Duret</p>
                        <p>Toufik Mechouet</p>
                        <p>Rabaa Ridene</p>
                        <p>Loïc Laurent</p>
                        <p>Benoît Sauveton</p>
                        <p>Romain Bertholon</p>
                        <p>Christophe Coraboeuf</p>
                        <p>Nicolas Cordier</p>
                        <p>Damien Duponchelle</p>
                        <p>Cedrick Facon</p>
                        <p>Nikolaus Filus</p>
                        <p>Quentin Garnier</p>
                        <p>Dorian Guillois</p>
                        <p>Sylvestre Ho</p>
                        <p>Matthieu Kermagoret</p>
                        <p>Antoine Nguyen</p>
                        <p>Laurent Pinsivy</p>
                        <p>David Porte</p>
                        <p>Mathavarajan Sugumaran</p>
                        <p>Cedric Temple</p>
                        <p>Alexandru Vilau</p>
                        <p>Guillaume Watteeux</p>
                        <p>Remi Werquin</p>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td  style="vertical-align: top;padding-top :10px;"><h3 style='text-align:left;'><?php echo _("Contributors"); ?></h3></td>
                    <td style="padding-top: 10px;" colspan="2">
                        <table class="table">
                            <tr>
                                <td>Marisa Belijar</td>
                                <td>Tobias Boehnert</td>
                            </tr>
                            <tr>
                                <td>Duy-Huan BUI</td>
                                <td>Gaetan Lucas de Couville</td>
                            </tr>
                            <tr>
                                <td>Sebastien Boulianne</td>
                                <td>Bertrand Cournaud</td>
                            </tr>
                            <tr>
                                <td>Vincent Carpentier</td>
                                <td>Christoph Ziemann</td>
                            </tr>
                            <tr>
                                <td>Mathieu Chateau</td>
                                <td>Eric Coquard</td>
                            </tr>
                            <tr>
                                <td>Luiz Gustavo Costa</td>
                                <td>Guillaume Halbitte</td>
                            </tr>
                            <tr>
                                <td>Thomas Fisher</td>
                                <td>Loïc Fontaine</td>
                            </tr>
                            <tr>
                                <td>Jean Gabès</td>
                                <td>Claire Gizard</td>
                            </tr>
                            <tr>
                                <td>Jean Marc Grisar</td>
                                <td>Florin Grosu</td>
                            </tr>
                            <tr>
                                <td>Jay Lopez</td>
                                <td>Jan Kuipers</td>
                            </tr>
                            <tr>
                                <td>Ira Janssen</td>
                                <td>Thomas Johansen</td>
                            </tr>
                            <tr>
                                <td>Peeters Jan</td>
                                <td>Jan Kuipers</td>
                            </tr>
                            <tr>
                                <td>Danil Makeyev</td>
                                <td>Camille N&eacute;ron</td>
                            </tr>
                            <tr>
                                <td>Maxime Peccoux</td>
                                <td>Patrick Proy</td>
                            </tr>
                            <tr>
                                <td>Matthieu Robin</td>
                                <td>Joerg Steinlechner</td>
                            </tr>
                            <tr>
                                <td>Silvio Rodrigo Damasceno de Souza</td>
                                <td>Thierry Van Acker</td>
                            </tr>
                            <tr>
                                <td>Felix Zingel</td>
                                <td>Massimiliano Ziccardi</td>
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
