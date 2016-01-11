<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template match="/">
        <table class="ListTable">
            <tr class='ListHeader'>
                <td colspan="2" class="ListColHeaderLeft" style="white-space:nowrap;" id="alias" width="160"></td>
                <xsl:if test="//i/s = 1">
                    <td class="ListColHeaderLeft" style="white-space:nowrap;" id="host_state" width="70">Status</td>
                </xsl:if>
                <td class="ListColHeaderLeft" style="white-space:nowrap;" id="services"></td>
            </tr>
            <xsl:for-each select="//hg">
                <tr class='list_lvl_1'>
                    <xsl:if test="//i/s = 1">
                        <td colspan="4">
                            <xsl:value-of select="hgn"/>
                            <xsl:if test="notes_url != ''">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">
                                        <xsl:value-of select="notes_url"/>
                                    </xsl:attribute>
                                    <xsl:attribute name="target">_blank</xsl:attribute>
                                    <xsl:element name="img">
                                        <xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
                                        <xsl:attribute name="class">ico-14</xsl:attribute>
                                    </xsl:element>
                                </xsl:element>
                            </xsl:if>
                            <xsl:if test="action_url != ''">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">
                                        <xsl:value-of select="action_url"/>
                                    </xsl:attribute>
                                    <xsl:attribute name="target">_blank</xsl:attribute>
                                    <xsl:element name="img">
                                        <xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
                                        <xsl:attribute name="class">ico-16</xsl:attribute>
                                    </xsl:element>
                                </xsl:element>
                            </xsl:if>
                        </td>
                    </xsl:if>
                    <xsl:if test="//i/s = 0">
                        <td colspan="3">
                            <xsl:value-of select="hgn"/>
                            <xsl:if test="notes_url != ''">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">
                                        <xsl:value-of select="notes_url"/>
                                    </xsl:attribute>
                                    <xsl:attribute name="target">_blank</xsl:attribute>
                                    <xsl:element name="img">
                                        <xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
                                        <xsl:attribute name="class">ico-14</xsl:attribute>
                                    </xsl:element>
                                </xsl:element>
                            </xsl:if>
                            <xsl:if test="action_url != ''">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">
                                        <xsl:value-of select="action_url"/>
                                    </xsl:attribute>
                                    <xsl:attribute name="target">_blank</xsl:attribute>
                                    <xsl:element name="img">
                                        <xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
                                        <xsl:attribute name="class">ico-16</xsl:attribute>
                                    </xsl:element>
                                </xsl:element>
                            </xsl:if>
                        </td>
                    </xsl:if>
                </tr>	
                <xsl:for-each select="l">
                    <tr>
                        <xsl:attribute name="id">trStatus</xsl:attribute>
                        <xsl:attribute name="class">
                            <xsl:value-of select="@class" />
                        </xsl:attribute>
                        <td class="ListColLeft"  width="160">
                            <xsl:if test="hico != 'none'">
                                <xsl:element name="img">
                                    <xsl:attribute name="src">./img/media/<xsl:value-of select="hico"/></xsl:attribute>
                                    <xsl:attribute name="width">16</xsl:attribute>
                                    <xsl:attribute name="height">16</xsl:attribute>
                                    <xsl:attribute name="style">padding-right:4px;</xsl:attribute>
                                </xsl:element>
                            </xsl:if>
                            <xsl:element name="a">
                                <xsl:attribute name="href">main.php?p=20202&amp;o=hd&amp;host_name=<xsl:value-of select="hnl"/></xsl:attribute>
                                <xsl:attribute name="class">pop infobulle link_popup_volante</xsl:attribute>
                                <xsl:attribute name="id">host-<xsl:value-of select="hid"/></xsl:attribute>
                                <xsl:value-of select="hn"/>
                            </xsl:element>
                        </td>
                        <td class="ListColLeft" style="white-space:nowrap;width:37px;">
                            <xsl:element name="a">
                                <xsl:attribute name="href">main.php?o=svc&amp;p=20201&amp;host_search=<xsl:value-of select="hnl"/></xsl:attribute>
                                <xsl:element name="img">
                                    <xsl:attribute name="src">./img/icons/view.png</xsl:attribute>
                                    <xsl:attribute name="class">ico-18</xsl:attribute>
                                </xsl:element>
                            </xsl:element>
                            <xsl:element name="a">
                                <xsl:attribute name="href">main.php?p=204&amp;mode=0&amp;svc_id=<xsl:value-of select="hnl"/></xsl:attribute>
                                <xsl:element name="img">
                                    <xsl:attribute name="src">./img/icons/chart.png</xsl:attribute>
                                    <xsl:attribute name="class">ico-18</xsl:attribute>
                                </xsl:element>
                            </xsl:element>
                        </td>
                        <xsl:if test="//i/s = 1">
                            <td class='ListColCenter'>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">badge <xsl:value-of select="hc"/></xsl:attribute>
                                    <xsl:value-of select="hs"/>
                                </xsl:element>
                            </td>
                        </xsl:if>
                        <td class="ListColLeft">
                            <xsl:if test="sc >= 1">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">main.php?o=svc_critical&amp;p=20201&amp;host_search=<xsl:value-of select="hn"/></xsl:attribute>
                                    <xsl:element name="span">
                                        <xsl:attribute name="class">state_badge <xsl:value-of select="scc"/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:value-of select="sc"/>
                                </xsl:element>
                            </xsl:if>
                            <xsl:if test="sw >= 1">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">main.php?o=svc_warning&amp;p=20201&amp;host_search=<xsl:value-of select="hn"/></xsl:attribute>
                                    <xsl:element name="span">
                                        <xsl:attribute name="class">state_badge <xsl:value-of select="swc"/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:value-of select="sw"/>
                                </xsl:element>
                            </xsl:if>
                            
                            <xsl:if test="su >= 1">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">main.php?o=svc_unknown&amp;p=20201&amp;host_search=<xsl:value-of select="hn"/></xsl:attribute>
                                    <xsl:element name="span">
                                        <xsl:attribute name="class">state_badge <xsl:value-of select="suc"/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:value-of select="su"/>
                                </xsl:element>
                            </xsl:if>
                            <xsl:if test="sk >= 1">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">main.php?o=svc_ok&amp;p=20201&amp;host_search=<xsl:value-of select="hn"/></xsl:attribute>
                                    <xsl:element name="span">
                                        <xsl:attribute name="class">state_badge <xsl:value-of select="skc"/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:value-of select="sk"/>
                                </xsl:element>
                            </xsl:if>
                            <xsl:if test="sp >= 1">
                                <xsl:element name="a">
                                    <xsl:attribute name="href">main.php?o=svc_pending&amp;p=20201&amp;host_search=<xsl:value-of select="hn"/></xsl:attribute>
                                    <xsl:element name="span">
                                        <xsl:attribute name="class">state_badge <xsl:value-of select="spc"/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:value-of select="sp"/>
                                </xsl:element>
                            </xsl:if>
                        </td>
                    </tr>
                </xsl:for-each>	
            </xsl:for-each>
        </table>
        <div id="div_popup" class="popup_volante">
            <div class="container-load"></div>
            <div id="popup-container-display"></div>
        </div>
    </xsl:template>
</xsl:stylesheet>
