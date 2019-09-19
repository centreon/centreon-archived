<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table class="ListTable">
    <tr class='ListHeader'>
        <td class="ListColHeaderPicker">
            <div class="md-checkbox md-checkbox-inline">
                <input type="checkbox" id="checkall" name="checkall" onclick="checkUncheckAll(this);"/>
                <label class="empty-label" for="checkall"></label>
            </div>
        </td>
        <xsl:if test = "//i/use_criticality = 1">
            <td class="ListColHeaderCenter" style="white-space:nowrap;width:17px;" id="criticality_id"></td>
        </xsl:if>
		<td colspan="2" class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="ip"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_check"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_state_change"></td>
		<xsl:for-each select="//i">
			<xsl:if test="o = 'h_unhandled' or o = 'hpb'">
				<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_hard_state_change">
					<xsl:value-of select="hard_state_label"/>
				</td>		
			</xsl:if>
		</xsl:for-each>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_check_attempt"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="plugin_output"></td>	
	</tr>
	<xsl:for-each select="//l">
	<xsl:if test = "//i/o = 'h_unhandled' or //i/o = 'hpb' and //i/sort_type = ''">
		<xsl:if test="parenth = 1">
			<tr class='list_lvl_1'><td colspan="10"><xsl:value-of select="//i/parent_host_label" /></td></tr>
		</xsl:if>
		<xsl:if test="delim = 1">			
			<tr class='list_lvl_1'><td colspan="10"><xsl:value-of select="//i/regular_host_label" /></td></tr>
		</xsl:if>
	</xsl:if>
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>  		
        <td class="ListColPicker">
            <div class="md-checkbox md-checkbox-inline">
                <xsl:element name="input">
                    <xsl:attribute name="type">checkbox</xsl:attribute>
                    <xsl:attribute name="value">1</xsl:attribute>
                    <xsl:attribute name="id"><xsl:value-of select="hn"/></xsl:attribute>
                    <xsl:attribute name="name">select[<xsl:value-of select="hnl"/>]</xsl:attribute>
                    <xsl:attribute name="onclick">
                        if (this.checked) {
                            putInSelectedElem('<xsl:value-of select="hn"/>');
                        } else {
                            removeFromSelectedElem('<xsl:value-of select="hn"/>');
                        }
                    </xsl:attribute>
                </xsl:element>
                <xsl:element name="label">
                    <xsl:attribute name="class">empty-label</xsl:attribute>
                    <xsl:attribute name="for"><xsl:value-of select="hn"/></xsl:attribute>
                </xsl:element>
            </div>
        </td>
            <xsl:if test = "//i/use_criticality = 1">
                <td class="ListColCenter" style="white-space:nowrap;width:17px;">
                <xsl:if test = "hci = 1">
                    <xsl:element name="img">
                        <xsl:attribute name="src">img/media/<xsl:value-of select="ci"/></xsl:attribute>
                        <xsl:attribute name="width">16</xsl:attribute>
                        <xsl:attribute name="height">16</xsl:attribute>
                        <xsl:attribute name="title"><xsl:value-of select='cih'/></xsl:attribute>
                    </xsl:element>
                </xsl:if>                
                </td>
            </xsl:if>
		<td class="ListColLeft">
			<xsl:element name="span">
				<xsl:if test="ico != ''">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/media/<xsl:value-of select="ico"/></xsl:attribute>
					  	<xsl:attribute name="width">16</xsl:attribute>
						<xsl:attribute name="height">16</xsl:attribute>
						<xsl:attribute name="style">padding-right:5px;</xsl:attribute>
					</xsl:element>
				</xsl:if>
				<xsl:element name="a">
				  	<xsl:attribute name="href">main.php?p=20202&amp;o=hd&amp;host_name=<xsl:value-of select="hnl"/></xsl:attribute>
					<xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
				    <xsl:attribute name="id">host-<xsl:value-of select="hid"/></xsl:attribute>
					<xsl:if test="//i/o = 'h_unhandled' or //i/o = 'hpb'">
						<xsl:if test="isp = 1 and s != 0">
							<xsl:attribute name="style">
	              				<xsl:text>font-weight: bold;</xsl:text>
	            			</xsl:attribute>
						</xsl:if>
					</xsl:if>
						<xsl:value-of select="hn"/>
				</xsl:element>
			</xsl:element>
		</td>
		<td class="ListColRight" style="white-space:nowrap;">
			<xsl:if test="hnu != 'none'">
				<xsl:element name="a">
					<xsl:attribute name="href"><xsl:value-of select="hnu"/></xsl:attribute>
						<xsl:attribute name="target">_blank</xsl:attribute>
						<xsl:element name="img">
							<xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
							<xsl:attribute name="class">ico-14</xsl:attribute>
							<xsl:if test="hnn = 'none'">
								<xsl:attribute name="title">HTTP Link <xsl:value-of select="hnu"/></xsl:attribute>
							</xsl:if>
							<xsl:if test="hnn != 'none'">
								<xsl:attribute name="title"><xsl:value-of select="hnn"/></xsl:attribute>
							</xsl:if>
						</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test="hau != 'none'">
				<xsl:element name="a">
					<xsl:attribute name="href"><xsl:value-of select="hau"/></xsl:attribute>
						<xsl:attribute name="target">_blank</xsl:attribute>
						<xsl:element name="img">
							<xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
							<xsl:attribute name="class">ico-16</xsl:attribute>
                             <xsl:attribute name="title">
						  		<xsl:value-of select='//i/http_action_link'/>&#160;:&#160;<xsl:value-of select="hau"/>
						  	</xsl:attribute>
						</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test="isf != 0">
				<xsl:element name="img">
				  	<xsl:attribute name="src">./img/icones/16x16/flapping.gif</xsl:attribute>
				  	<xsl:attribute name="title">Host is flapping</xsl:attribute>
				</xsl:element>			
			</xsl:if>
			<xsl:if test="hdtm != 0">
				<xsl:element name="a">
					<xsl:attribute name="class">infobulle</xsl:attribute>
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icons/warning.png</xsl:attribute>
					  	<xsl:attribute name="class">infobulle link_generic_info_volante ico-18</xsl:attribute>
						<xsl:attribute name="id">dtmspan_<xsl:value-of select="hid"/></xsl:attribute>
						<xsl:attribute name="name"><xsl:value-of select="hdtmXml"/>|<xsl:value-of select="hdtmXsl"/></xsl:attribute>
					</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test="ha = 1">
				<xsl:element name="a">
					<xsl:attribute name="class">infobulle</xsl:attribute>
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icons/technician.png</xsl:attribute>
					  	<xsl:attribute name="class">infobulle link_generic_info_volante ico-20</xsl:attribute>
						<xsl:attribute name="id">ackspan_<xsl:value-of select="hid"/></xsl:attribute>
						<xsl:attribute name="name"><xsl:value-of select="hackXml"/>|<xsl:value-of select="hackXsl"/></xsl:attribute>					
					</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test="hae = 0 and hpe = 1">
				<xsl:element name="img">
				  	<xsl:attribute name="src">./img/icons/passive_check.png</xsl:attribute>
					<xsl:attribute name="class">ico-16</xsl:attribute>
				</xsl:element>
			</xsl:if>
			<xsl:if test="hae = 0 and hpe = 0">
				<xsl:element name="img">
				  	<xsl:attribute name="src">./img/icons/never_checked.png</xsl:attribute>
					<xsl:attribute name="class">ico-16</xsl:attribute>
				</xsl:element>
			</xsl:if>
			<xsl:if test="ne = 0">
				<xsl:element name="img">
				  	<xsl:attribute name="src">./img/icons/notifications_off.png</xsl:attribute>
					<xsl:attribute name="title">
						<xsl:value-of select='//i/notif_disabled'/>
					</xsl:attribute>
					<xsl:attribute name="class">ico-18</xsl:attribute>
				</xsl:element>
			</xsl:if>
			<xsl:element name="a">
				<xsl:attribute name="href">./main.php?p=204&amp;mode=0&amp;svc_id=<xsl:value-of select="hnl"/></xsl:attribute>
				<xsl:element name="img">
					<xsl:attribute name="src">./img/icons/chart.png</xsl:attribute>
					<xsl:attribute name="class">ico-18</xsl:attribute>
					<xsl:attribute name="title">See Graphs of this host</xsl:attribute>
				</xsl:element>
			</xsl:element>
			
		</td>
		<td class="ListColCenter">
          <xsl:element name="span">
            <xsl:attribute name="class">badge <xsl:value-of select="hc"/></xsl:attribute>
            <xsl:value-of select="cs"/>
          </xsl:element>
		</td>
		<td class="ListColRight"><xsl:value-of select="a"/></td>
	    <td class="ListColRight"><xsl:value-of select="lc"/></td>
	    <td class="ListColRight">
                <xsl:value-of select="lsc"/>
            </td>
		<xsl:if test = "//i/o = 'h_unhandled' or //i/o = 'hpb'">
			<td class="ListColRight" style="white-space:nowrap;">
				<xsl:value-of select="lhs"/>
			</td>
		</xsl:if>
	    <td class="ListColCenter"><xsl:value-of select="tr"/></td>
		<td class="ListColNoWrap"><xsl:value-of select="ou"/></td>
	</tr>
</xsl:for-each>
</table>
<div id="div_popup" class="popup_volante"><div class="container-load"></div><div id="popup-container-display"></div></div>
</xsl:template>
</xsl:stylesheet>
