<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table class="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td colspan="2" class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"></td>
		<td colspan="3" class="ListColHeaderCenter" style="white-space:nowrap;" id="service_description"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_state_change"></td>
		<xsl:for-each select="//i">
			<xsl:if test="o = 'svc_unhandled' or o = 'svcpb' or o = 'svc_warning' or o = 'svc_critical' or o = 'svc_unknown'">
				<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_hard_state_change">
					<xsl:value-of select="hard_state_label"/>
				</td>
			</xsl:if>
		</xsl:for-each>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_check"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_attempt"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="plugin_output"></td>
		<xsl:for-each select="//i">
			<xsl:if test="nc = 1">
				<td class="ListColHeaderCenter" style="white-space:nowrap;" id="next_check">next check</td>
			</xsl:if>
		</xsl:for-each>
	</tr>
	<xsl:for-each select="//l">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
		<td class="ListColPicker">
			<xsl:element name="input">
				<xsl:attribute name="type">checkbox</xsl:attribute>
				<xsl:attribute name="value">1</xsl:attribute>
				<xsl:attribute name="id"><xsl:value-of select="hn"/>;<xsl:value-of select="sd"/></xsl:attribute>
				<xsl:attribute name="name">select[<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/>]</xsl:attribute>
				<xsl:attribute name="onclick">
					if (this.checked) {
						putInSelectedElem('<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/>');
					} else {
						removeFromSelectedElem('<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/>');
					}
				</xsl:attribute>
			</xsl:element>
		</td>
		<td class="ListColLeft" style="white-space:nowrap;">
			<xsl:if test="hn/@none = 0">
				<xsl:element name="span">
					<xsl:if test="hdtm = 0">
						<xsl:if test="hs = 1">
							<xsl:attribute name="class">host_down</xsl:attribute>
						</xsl:if>
						<xsl:if test="hs = 2">
							<xsl:attribute name="class">host_unreachable</xsl:attribute>
						</xsl:if>
					</xsl:if>
					<xsl:if test="hdtm != 0">
						<xsl:attribute name="class">host_downtime</xsl:attribute>
					</xsl:if>
					<xsl:if test="hico != ''">
						<xsl:element name="img">
							<xsl:attribute name="src">./img/media/<xsl:value-of select="hico"/></xsl:attribute>
							<xsl:attribute name="width">16</xsl:attribute>
							<xsl:attribute name="height">16</xsl:attribute>
							<xsl:attribute name="style">padding-right:5px;</xsl:attribute>
						</xsl:element>
					</xsl:if>
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn/@hnl"/></xsl:attribute>
						<xsl:attribute name="class">infobulle</xsl:attribute>
						<xsl:attribute name="onmouseover">displayPOPUP('<xsl:value-of select="hid"/>');</xsl:attribute>
						<xsl:attribute name="onmouseout">hiddenPOPUP('<xsl:value-of select="hid"/>');</xsl:attribute>
						<xsl:value-of select="hn"/>
						<xsl:element name="span">
							<xsl:attribute name="id">span_<xsl:value-of select="hid"/></xsl:attribute>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:if>
		</td>
		<td class="ListColLeft" style="white-space:nowrap;">
			<xsl:if test="hn/@none = 0">
				<xsl:if test="hnu != 'none'">
					<xsl:element name="a">
					  	<xsl:attribute name="classe">infobulle</xsl:attribute>
					  	<xsl:attribute name="href"><xsl:value-of select="hnu"/></xsl:attribute>
						<xsl:attribute name="target">_blank</xsl:attribute>
						<xsl:element name="img">
						  	<xsl:attribute name="src">./img/icones/15x7/weblink.gif</xsl:attribute>
						  	<xsl:attribute name="title">HTTP Link <xsl:value-of select="hnn"/></xsl:attribute>
						</xsl:element>
					</xsl:element>
				</xsl:if>
				<xsl:if test="hdtm != 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/warning.gif</xsl:attribute>
					  	<xsl:attribute name="title">Host is currently on downtime</xsl:attribute>
					</xsl:element>
				</xsl:if>
				<xsl:if test="ha = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/worker.gif</xsl:attribute>
					  	<xsl:attribute name="title">Problem has been acknowledged</xsl:attribute>
					</xsl:element>
				</xsl:if>
				<xsl:if test="hae = 0 and hpe = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_pause.gif</xsl:attribute>
					  	<xsl:attribute name="title">This host is only check by passiv mode</xsl:attribute>
					</xsl:element>
				</xsl:if>
				<xsl:if test="hae = 0 and hpe = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_stop.gif</xsl:attribute>
					  	<xsl:attribute name="title">This host is never checked</xsl:attribute>
					</xsl:element>
				</xsl:if>
			</xsl:if>
		</td>
		<td class="ListColLeft" style="white-space:nowrap;">
			<xsl:element name="a">
			  	<xsl:attribute name="href">main.php?p=202&amp;o=svcd&amp;host_name=<xsl:value-of select="hn/@hnl"/>&amp;service_description=<xsl:value-of select="sdl"/></xsl:attribute>
				<xsl:attribute name="class">infobulle</xsl:attribute>
				<xsl:attribute name="onmouseover">displayPOPUP_svc('<xsl:value-of select="svc_id"/>');</xsl:attribute>
				<xsl:attribute name="onmouseout">hiddenPOPUP('<xsl:value-of select="svc_id"/>');</xsl:attribute>
				<xsl:value-of select="sd"/>
				<xsl:element name="span">
					<xsl:attribute name="id">span_<xsl:value-of select="svc_id"/></xsl:attribute>
				</xsl:element>
			</xsl:element>
		</td>
		<td class="ListColRight" style="white-space:nowrap;">
			<xsl:if test="snu != 'none'">
				<xsl:element name="a">
				  	<xsl:attribute name="classe">infobulle</xsl:attribute>
				  	<xsl:attribute name="target">_blank</xsl:attribute>
				  	<xsl:attribute name="href"><xsl:value-of select="snu"/></xsl:attribute>
						<xsl:element name="img">
						  	<xsl:attribute name="src">./img/icones/15x7/weblink.gif</xsl:attribute>
						  	<xsl:attribute name="title">HTTP Link <xsl:value-of select="sn"/></xsl:attribute>
						</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test="dtm != 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/warning.gif</xsl:attribute>
					  	<xsl:attribute name="title">Service is currently on Downtime</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="pa = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/worker.gif</xsl:attribute>
					  	<xsl:attribute name="title">Problem has been acknowledged</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ac = 0 and pc = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_pause.gif</xsl:attribute>
					  	<xsl:attribute name="title">This service is checked passive only</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ac = 0 and pc = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_stop.gif</xsl:attribute>
					  	<xsl:attribute name="title">This service is neither active nor passive</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="is = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/flapping.gif</xsl:attribute>
					  	<xsl:attribute name="title">This Service is flapping</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ne = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/noloudspeaker.gif</xsl:attribute>
					  	<xsl:attribute name="title">Notification is disabled</xsl:attribute>
					</xsl:element>
			</xsl:if>
		</td>
		<td class="ListColRight">
			<xsl:if test="ppd &gt; 0">
				<xsl:if test="svc_index &gt; 0">
					<xsl:element name="a">
				  		<xsl:attribute name="href">main.php?p=4&amp;mode=0&amp;svc_id=<xsl:value-of select="hn/@hnl"/>;<xsl:value-of select="sdl"/></xsl:attribute>					
							<xsl:element name="img">
					  			<xsl:attribute name="src">./img/icones/16x16/column-chart.gif</xsl:attribute>					
								<xsl:attribute name="onmouseover">displayIMG('<xsl:value-of select="svc_index"/>','<xsl:value-of select="//sid"/>','<xsl:value-of select="svc_id"/>');</xsl:attribute>
								<xsl:attribute name="onmouseout">hiddenIMG('<xsl:value-of select="svc_id"/>');</xsl:attribute>					
							</xsl:element>					
					</xsl:element>
				</xsl:if>
			</xsl:if>
		</td>
		<td class="ListColCenter">
			<xsl:attribute name="style">
				background-color:<xsl:value-of select="sc"/>;
			</xsl:attribute>
			<xsl:value-of select="cs"/>
		</td>
		<td class="ListColRight" style="white-space:nowrap;">
			<xsl:value-of select="d"/>
		</td>
		<xsl:if test = "//i/o = 'svc_unhandled' or //i/o = 'svcpb' or //i/o = 'svc_warning' or //i/o = 'svc_critical' or //i/o = 'svc_unknown'">
			<td class="ListColRight" style="white-space:nowrap;">
				<xsl:value-of select="last_hard_state_change"/>
			</td>
		</xsl:if>
        <td class="ListColCenter" style="white-space:nowrap;">
        	<xsl:value-of select="lc"/>
        </td>
        <td class="ListColCenter" style="white-space:nowrap;">
        	<xsl:value-of select="ca"/>
        </td>
        <td class="ListColLeft" >
        	<xsl:value-of select="po" disable-output-escaping="yes" />
        </td>
		<xsl:if test="//i/nc = 1">
			<td class="ListColCenter" style="white-space:nowrap;">
				<xsl:value-of select="nc"/>
			</td>
		</xsl:if>
	</tr>
</xsl:for-each>
</table>
<div id="div_img" class="img_volante"></div>
</xsl:template>
</xsl:stylesheet>