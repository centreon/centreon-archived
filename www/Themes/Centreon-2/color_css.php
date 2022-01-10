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


header("Content-type: text/css");

?>

.ListTable a:link, #ListTable a:link, .ListTable a:visited, #ListTable a:visited {color:#666666;}
.ListTable, .ListTableMedium, .ListTableSmall {border-color:#BFD0E2;}

.list_lvl_1 { background-color:#D5DFEB; }
.list_lvl_2 { background-color:#CED3ED; }

.ListHeader     { background:#CFEDF9; }
.ListSubHeader  {
    background-color:#A9C5F2;
    font-weight:bold;
    background-position:top left;
}

.list_lvl_2 td, .list_lvl_1 td {
    border-top-color: #AAAAAA;
    border-left-color: #AAAAAA;
    border-bottom-color: #AAAAAA;
    border-right-color: #AAAAAA;
}

.list_one td, .list_two td, .list_three td, .list_four td, .list_up td, .list_down td, .line_ack td, 
.line_downtime td, .list_unreachable td, .row_disabled td {
    border-top-color: #D1DCEB;
    border-left-color: #D1DCEB;
    border-bottom-color: #D1DCEB;
    border-right-color: #D1DCEB;
}

.tab .list_one td, .tab .list_two td, .tab .list_three td, .tab .list_four td, .tab .list_up td, 
.tab .list_down td,.tab .line_ack td,.tab .line_downtime td,.tab .list_unreachable td, .tab .row_disabled td {
    border-top-color: #D1DCEB;
    border-left-color: #D1DCEB;
    border-bottom-color: #D1DCEB;
    border-right-color: #D1DCEB;
}

.tab .list_lvl_2 td, .tab .list_lvl_1 td{
    border-top-color: #AAAAAA;
    border-left-color: #AAAAAA;
    border-bottom-color: #AAAAAA;
    border-right-color: #AAAAAA;
}

.ListHeader td{
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
}

.ListColFooterRight, .ListColFooterLeft, .ListColFooterCenter {
    border-top-color: #AAAAEE;
}

.list_one_fixe      {   background-color: #F8FDFF;}
.list_two_fixe      {   background-color: #F0FBFF;}

.list_one           {   background-color: #F8FDFF;}
.list_one:hover     {   background-color: #CFEDF9;}

.list_two           {   background-color: #F0FBFF;}
.list_two:hover     {   background-color: #CFEDF9;}

.list_three         {   background-color: #fada83;}
.list_three:hover   {   background-color: #bada83;}

.list_four          {   background-color: #fdc11e;}
.list_four:hover    {   background-color: #bdc11e;}

.list_up            {   background-color: #88b917;}
.list_up:hover      {   background-color: #B2A867;}

.list_down          {   background-color: #ffaec1;}
.list_down:hover    {   background-color: #e17790;}

.list_unreachable           {   background-color:#818285;}
.list_unreachable:hover     {   background-color:#818285;}

.line_downtime      {   background-color: #f1dfff;}
.line_downtime:hover{   background-color: #e7c9ff;}

.line_ack           {   background-color: #fefc8e;}
.line_ack:hover     {   background-color: #fcf17f;}


input[type="submit"]:hover, input[type="button"]:hover, input[type="reset"]:hover {
    background: #009FDF;
    color: #FFFFFF;
    border-color: #009FDF;
}

.limitPage, .pageNumber {
    color: #FFFFFF;
    background-position:top left;
    background-repeat:repeat-x;
    background-color:#BFD0E2;
}

.a, .b {
    border-color:#AAAAEE;
}

.msg_loading{
    position:absolute;
    top:20px;
    left:200px;
    width:200px;
    color:blue;
    font-size:18px;
    width:100%;
    height:100%;
}
.msg_isloading{
    font-size:14px;
    position:absolute;
    top:20px;
    left:200px;
    background-color:red;
    color:white;
    width:200px;
}

.Toolbar_TDSelectAction_Top a { 
    font-family:Arial, Helvetica, Sans-Serif;
    font-size:11px;
    color:#5E5E5E;
}
.Toolbar_TDSelectAction_Top a:hover {
    color: #E8AB5C;
}
.Toolbar_TableBottom {
    border-color: #009FDF;
}

.headerTabContainer {
    border-bottom: 1px solid #009FDF;
}

#mainnav li {
    background-color: #009FDF;
    border: 1px solid #009FDF;
}
#mainnav li.a a {
    color: #009FDF;
}
#mainnav li.b a {
    color: #C1ECFF;
}

