<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:variable name="i" select="//i"/>
    <xsl:template match="/">
        <table class="ListTable">
            <tr class='ListHeader'>
                <td class="ListColHeaderCenter" style="white-space:nowrap;" id="hostGroup_name"></td>
                <td class="ListColHeaderCenter" style="white-space:nowrap;" id="host_status"></td>
                <td class="ListColHeaderCenter" style="white-space:nowrap;" id="service_status"></td>
            </tr>
            <xsl:for-each select="//l">
                <tr>
                    <xsl:attribute name="id">trStatus</xsl:attribute>
                    <xsl:attribute name="class">
                        <xsl:value-of select="@class" />
                    </xsl:attribute>
                    <td class="ListColLeft">
                        <xsl:element name="a">						  	
                            <xsl:attribute name="class">pop</xsl:attribute>
                            <xsl:attribute name="href">
                                <xsl:value-of select="hg_listing_uri"/>
                            </xsl:attribute>
                            <xsl:attribute name="isreact">true</xsl:attribute>
                            <xsl:value-of select="hn"/>
                        </xsl:element>
                    </td>
                    <td class="ListColLeft">
                        <xsl:if test="hd >= 1">	
                            <xsl:element name="a">
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_h_down"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                    <xsl:attribute name="class">margin_right</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="hdc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="hd"/>
                            </xsl:element>
                        </xsl:if>
                        <xsl:if test="hu >= 1">	
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_h_up"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="huc"/>
                                    </xsl:attribute>
                                </span>
                                <xsl:value-of select="hu"/>
                            </xsl:element>    
                        </xsl:if>
                        <xsl:if test="hur >= 1">
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_h_unreachable"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="hurc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="hur"/>
                            </xsl:element>
                        </xsl:if>
                    </td>
                    <td class="ListColLeft">
                        <xsl:if test="sc >= 1">
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_s_critical"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="scc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="sc"/>
                            </xsl:element>
                        </xsl:if>
                        <xsl:if test="sw >= 1">
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_s_warning"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="swc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="sw"/>
                            </xsl:element>
                        </xsl:if>
                        <xsl:if test="su >= 1">
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_s_unknown"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="suc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="su"/>
                            </xsl:element>
                        </xsl:if>
                        <xsl:if test="sk >= 1">
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_s_ok"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="skc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="sk"/>
                            </xsl:element>
                        </xsl:if>
                        <xsl:if test="sp >= 1">
                            <xsl:element name="a">
                                <xsl:attribute name="class">margin_right</xsl:attribute>
                                <xsl:attribute name="href">
                                    <xsl:value-of select="hg_listing_s_pending"/></xsl:attribute>
                                <xsl:attribute name="isreact">true</xsl:attribute>
                                <span>
                                    <xsl:attribute name="class">
                                        state_badge <xsl:value-of select="spc"/>
                                    </xsl:attribute>
                                </span> 
                                <xsl:value-of select="sp"/>
                            </xsl:element>
                        </xsl:if>

                    </td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
</xsl:stylesheet>
