<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table class="ListTable">
    <tr class='ListHeader'>
        <td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>      
        <xsl:if test = "//i/use_criticality = 1">
            <td class="ListColHeaderCenter" style="white-space:nowrap;width:17px;" id="criticality_id"></td>
        </xsl:if>
        <td colspan="2" class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"></td>
        <td colspan="3" class="ListColHeaderCenter" style="white-space:nowrap;" id="service_description"></td>
        <td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state"></td>
        <td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_state_change"></td>
        <xsl:for-each select="//i">
            <xsl:if test="o = 'svc_unhandled' or o = 'svcpb' or o = 'svc_warning' or o = 'svc_critical' or o = 'svc_unknown' or o = 'svc_unhandled_warning' or o = 'svc_unhandled_critical' or o = 'svc_unhandled_unknown'">
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
                <xsl:attribute name="name">select[<xsl:value-of select="hnl"/>;<xsl:value-of select="sdl"/>]</xsl:attribute>
                <xsl:attribute name="onclick">
                    if (this.checked) {
                        putInSelectedElem('<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/>');
                    } else {
                        removeFromSelectedElem('<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/>');
                    }
                </xsl:attribute>
            </xsl:element>
        </td>
        <xsl:if test = "//i/use_criticality = 1">
            <td class="ListColCenter">
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
        <td class="ListColLeft" style="white-space:nowrap;">
            <xsl:if test="hn/@none = 0">
                <xsl:if test="hnu != 'none'">
                    <xsl:element name="a">
                        <xsl:attribute name="class">infobulle</xsl:attribute>
                        <xsl:attribute name="href"><xsl:value-of select="hnu"/></xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                        <xsl:element name="img">
                            <xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
                            <xsl:attribute name="class">ico-14</xsl:attribute>
                            <xsl:attribute name="title">
                                <xsl:if test="hnn = ''">
                                    <xsl:value-of select="//i/http_link"/>&#160;:&#160;<xsl:value-of select="hnu"/>
                                </xsl:if>
                                <xsl:if test="hnn != ''">
                                    <xsl:value-of select="hnn"/>
                                </xsl:if>
                            </xsl:attribute>
                        </xsl:element>
                    </xsl:element>
                </xsl:if>
                <xsl:if test="hau != 'none'">
                    <xsl:element name="a">
                        <xsl:attribute name="class">infobulle</xsl:attribute>
                        <xsl:attribute name="href"><xsl:value-of select="hau"/></xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                        <xsl:element name="img">
                            <xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
                            <xsl:attribute name="class">ico-16</xsl:attribute>
                            <xsl:attribute name="title">
                                <xsl:value-of select="//i/http_action_link"/>&#160;:&#160;<xsl:value-of select="hau"/></xsl:attribute>
                        </xsl:element>
                    </xsl:element>
                </xsl:if>
                <xsl:if test="hdtm != 0">
                    <xsl:element name="a">
                        <xsl:attribute name="class">infobulle</xsl:attribute>
                        <xsl:attribute name="title">Downtime</xsl:attribute>
                        <xsl:element name="img">
                            <xsl:attribute name="src">./img/icons/warning.png</xsl:attribute>
                            <xsl:attribute name="class">link_generic_info_volante ico-18</xsl:attribute>
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
                        <xsl:attribute name="title">
                            <xsl:value-of select='//i/host_passive_mode'/>
                        </xsl:attribute>
                    </xsl:element>
                </xsl:if>
                <xsl:if test="hae = 0 and hpe = 0">
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/icons/never_checked.png</xsl:attribute>
                        <xsl:attribute name="class">ico-16</xsl:attribute>
                        <xsl:attribute name="title">
                            <xsl:value-of select='//i/host_never_checked'/>
                        </xsl:attribute>
                    </xsl:element>
                </xsl:if>
            </xsl:if>
        </td>
        <td class="ListColLeft" style="white-space:nowrap;">
            <xsl:if test="hn/@none = 0">
                <xsl:if test="hdtm = 0">
                    <xsl:if test="hs = 1">
                        <xsl:element name="span">
                            <xsl:attribute name="class">state_badge host_down</xsl:attribute>
                        </xsl:element>
                    </xsl:if>
                    <xsl:if test="hs = 2">
                        <xsl:element name="span">
                            <xsl:attribute name="class">state_badge host_unreachable</xsl:attribute>
                        </xsl:element>
                    </xsl:if>
                </xsl:if>
                <xsl:if test="hico != ''">
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/media/<xsl:value-of select="hico"/></xsl:attribute>
                        <xsl:attribute name="width">16</xsl:attribute>
                        <xsl:attribute name="height">16</xsl:attribute>
                        <xsl:attribute name="style">vertical-align:middle;</xsl:attribute>
                        <xsl:attribute name="class">margin_right</xsl:attribute>
                    </xsl:element>
                </xsl:if>
                <xsl:choose>
                    <xsl:when test="isMeta = 0">
                        <xsl:element name="a">
                            <xsl:attribute name="href">main.php?p=20202&amp;o=hd&amp;host_name=<xsl:value-of select="hnl" /></xsl:attribute>
                            <xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
                            <xsl:attribute name="id">host-<xsl:value-of select="hid"/></xsl:attribute>
                            <xsl:value-of select="hdn"/>
                        </xsl:element>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="hdn"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
        </td>
        <td class="ListColLeft" style="white-space:nowrap;">
            <xsl:if test="sico != ''">
                <xsl:element name="img">
                    <xsl:attribute name="src">./img/media/<xsl:value-of select="sico"/></xsl:attribute>
                    <xsl:attribute name="width">16</xsl:attribute>
                    <xsl:attribute name="height">16</xsl:attribute>
                    <xsl:attribute name="style">padding-right:5px;</xsl:attribute>
                </xsl:element>
            </xsl:if>
            <xsl:element name="a">
                <xsl:attribute name="href">main.php?p=20201&amp;o=svcd&amp;host_name=<xsl:value-of select="hn"/>&amp;service_description=<xsl:value-of select="sdl"/></xsl:attribute>
                <xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
                <xsl:attribute name="id">svc-<xsl:value-of select="hid"/>-<xsl:value-of select="svc_id"/></xsl:attribute>
                <xsl:value-of select="sdn"/>
            </xsl:element>
        </td>
        <td class="ListColRight" style="white-space:nowrap;">
            <xsl:if test="snu != 'none'">
                <xsl:element name="a">
                    <xsl:attribute name="class">infobulle</xsl:attribute>
                    <xsl:attribute name="target">_blank</xsl:attribute>
                    <xsl:attribute name="href"><xsl:value-of select="snu"/></xsl:attribute>
                        <xsl:element name="img">
                            <xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
                            <xsl:attribute name="class">ico-14</xsl:attribute>
                            <xsl:attribute name="title">
                                <xsl:if test="snn = 'none'">
                                    <xsl:value-of select='//i/http_link'/>&#160;:&#160;<xsl:value-of select="snu"/>
                                </xsl:if>
                                <xsl:if test="snn != 'none'">
                                    <xsl:value-of select="snn"/>
                                </xsl:if>
                            </xsl:attribute>
                        </xsl:element>
                </xsl:element>
            </xsl:if>
            <xsl:if test="sau != 'none'">
                <xsl:element name="a">
                    <xsl:attribute name="class">infobulle</xsl:attribute>
                    <xsl:attribute name="target">_blank</xsl:attribute>
                    <xsl:attribute name="href"><xsl:value-of select="sau"/></xsl:attribute>
                        <xsl:element name="img">
                            <xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
                            <xsl:attribute name="class">ico-16</xsl:attribute>
                            <xsl:attribute name="title">
                                <xsl:value-of select='//i/http_action_link'/>&#160;:&#160;<xsl:value-of select="sau"/>
                            </xsl:attribute>
                        </xsl:element>
                </xsl:element>
            </xsl:if>
            <xsl:if test="dtm != 0">                    
                <xsl:element name="a">
                    <xsl:attribute name="class">infobulle</xsl:attribute>
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/icons/warning.png</xsl:attribute>
                        <xsl:attribute name="class">link_generic_info_volante ico-18</xsl:attribute>
                        <xsl:attribute name="id">dtmspan_<xsl:value-of select="hid"/>_<xsl:value-of select="svc_id"/></xsl:attribute>
                        <xsl:attribute name="name"><xsl:value-of select="dtmXml"/>|<xsl:value-of select="dtmXsl"/></xsl:attribute>              
                    </xsl:element>
                </xsl:element>
            </xsl:if>
            <xsl:if test="pa = 1">
                <xsl:element name="a">
                    <xsl:attribute name="class">infobulle</xsl:attribute>
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/icons/technician.png</xsl:attribute>
                        <xsl:attribute name="class">infobulle link_generic_info_volante ico-20</xsl:attribute>
                        <xsl:attribute name="id">ackspan_<xsl:value-of select="hid"/>_<xsl:value-of select="svc_id"/></xsl:attribute>
                        <xsl:attribute name="name"><xsl:value-of select="ackXml"/>|<xsl:value-of select="ackXsl"/></xsl:attribute>
                    </xsl:element>
                </xsl:element>
            </xsl:if>
            <xsl:if test="ac = 0 and pc = 1">
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/icons/passive_check.png</xsl:attribute>
                        <xsl:attribute name="class">ico-16</xsl:attribute>
                        <xsl:attribute name="title">                        
                            <xsl:value-of select='//i/service_passive_mode'/>
                        </xsl:attribute>
                    </xsl:element>
            </xsl:if>
            <xsl:if test="ac = 0 and pc = 0">
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/icons/never_checked.png</xsl:attribute>
                        <xsl:attribute name="class">ico-16</xsl:attribute>
                        <xsl:attribute name="title">                            
                            <xsl:value-of select='//i/service_not_active_not_passive'/>
                        </xsl:attribute>
                    </xsl:element>
            </xsl:if>
            <xsl:if test="is = 1">
                    <xsl:element name="img">
                        <xsl:attribute name="src">./img/icones/16x16/flapping.gif</xsl:attribute>
                        <xsl:attribute name="title">                            
                            <xsl:value-of select='//i/service_flapping'/>
                        </xsl:attribute>
                    </xsl:element>
            </xsl:if>
            <xsl:if test="ne = 0">
                    <xsl:element name="img">
                        <xsl:attribute name="class">ico-18</xsl:attribute>
                        <xsl:attribute name="src">./img/icons/notifications_off.png</xsl:attribute>
                        <xsl:attribute name="title">                            
                            <xsl:value-of select='//i/notif_disabled'/>
                        </xsl:attribute>
                    </xsl:element>
            </xsl:if>
        </td>
        <td class="ListColRight">
            <xsl:if test="svc_index &gt; 0">
                <xsl:element name="a">
                    <xsl:attribute name="href">main.php?p=204&amp;mode=0&amp;svc_id=<xsl:value-of select="hnl"/>;<xsl:value-of select="sdl"/></xsl:attribute>                   
                        <xsl:element name="img">
                            <xsl:attribute name="id"><xsl:value-of select="hid"/>_<xsl:value-of select="svc_id"/></xsl:attribute>
                            <xsl:attribute name="class">graph-volant ico-18</xsl:attribute>
                            <xsl:attribute name="src">./img/icons/chart.png</xsl:attribute>
                        </xsl:element>                  
                </xsl:element>
            </xsl:if>
        </td>
        <td class="ListColCenter">
            <xsl:element name="span">
                <xsl:attribute name="style">
                    <xsl:if test="ssc = 3">
                        color: #818285;
                        font-weight: normal;
                    </xsl:if>
                </xsl:attribute>
                <xsl:attribute name="class">badge <xsl:value-of select="sc"/></xsl:attribute>
                <xsl:value-of select="cs"/>
            </xsl:element>
        </td>
        <td class="ListColRight" style="white-space:nowrap;">
            <xsl:value-of select="d"/>
        </td>
        <xsl:if test = "//i/o = 'svc_unhandled' or //i/o = 'svcpb' or //i/o = 'svc_warning' or //i/o = 'svc_critical' or //i/o = 'svc_unknown' or //i/o = 'svc_unhandled_warning' or //i/o = 'svc_unhandled_critical' or //i/o = 'svc_unhandled_unknown'">
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
<div id="div_popup" class="popup_volante"><div class="container-load"></div><div id="popup-container-display"></div></div>
</xsl:template>
</xsl:stylesheet>
