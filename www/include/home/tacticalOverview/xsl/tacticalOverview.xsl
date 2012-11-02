<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html"/>
<xsl:template match="/">
<xsl:for-each select="//root">
<div id="hostStats">
	<table>
		<tr>
			<td style="font-weight:bold;background-color:#ffffff;">
				&#160;::&#160;<xsl:value-of select="//main/str_hosts"/>
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top;">
				<table class="SubTableTactical">
					<tr>
						<td class="SubTacticalDT">
							<table>
								<xsl:choose>
									<xsl:when test="hostDown = (hostDownAck + hostDownInact) and (hostdown &gt; 0)">								
									<tr>
										<td style="background-color:#ee9572;">
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_hostPb'/>
												</xsl:attribute>											
												<xsl:value-of select='hostDown'/>&#160;<xsl:value-of select='//main/str_down'/>
											</xsl:element>
										</td>
									</tr>
									</xsl:when>
									<xsl:when test="hostDown &gt; 0">
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_down'/>;font-weight:bold;</xsl:attribute>							
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_hostPb'/>
												</xsl:attribute>											
												<xsl:value-of select='hostDown'/>&#160;<xsl:value-of select='//main/str_down'/>
											</xsl:element>
										</xsl:element>
									</tr>
									</xsl:when>
									<xsl:otherwise>
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;</xsl:attribute>										
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_hostPb'/>
												</xsl:attribute>
												<xsl:value-of select='hostDown'/>&#160;<xsl:value-of select='//main/str_down'/>
											</xsl:element>
										</xsl:element>
									</tr>
									</xsl:otherwise>
								</xsl:choose>
								<xsl:if test="hostDownAck &gt; 0">								
								<tr>
									<td class="SubTacticalDT" style="background-color:#ee9572;">
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_hostPb'/>
											</xsl:attribute>
											<xsl:value-of select='hostDownAck'/>&#160;<xsl:value-of select='//main/str_acknowledged'/>
										</xsl:element>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test="hostDownInact &gt; 0">
								<tr>
									<td class="SubTacticalDT" style="background-color:#ee9572;">
										<xsl:value-of select='hostDownInact'/><xsl:value-of select='//main/str_disabled'/>
									</td>
								</tr>
								</xsl:if>								
								<xsl:if test="hostDownUnhand &gt; 0">								
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_down'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_host_unhand'/>
											</xsl:attribute>											
											<xsl:value-of select='hostDownUnhand'/>&#160;<xsl:value-of select='//main/str_unhandledpb'/>
										</xsl:element>									
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>
								<xsl:choose>
									<xsl:when test="hostUnreach = (hostUnreachAck + hostUnreachInact) and (hostUnreach &gt; 0)">								
									<tr>
										<td style="background-color:#339999;">
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_hostPb'/>
												</xsl:attribute>											
												<xsl:value-of select='hostUnreach'/>&#160;<xsl:value-of select='//main/str_unreachable'/>
											</xsl:element>
										</td>
									</tr>
									</xsl:when>
									<xsl:when test="hostUnreach &gt; 0">
									<tr>
										<xsl:element name='td'>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unreachable'/>;font-weight:bold;</xsl:attribute>										
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_hostPb'/>
												</xsl:attribute>											
												<xsl:value-of select='hostUnreach'/>&#160;<xsl:value-of select='//main/str_unreachable'/>
											</xsl:element>						
										</xsl:element>
									</tr>
									</xsl:when>
									<xsl:otherwise>								
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;</xsl:attribute>
											<xsl:value-of select='hostUnreach'/>&#160;<xsl:value-of select='//main/str_unreachable'/>
										</xsl:element>
									</tr>
									</xsl:otherwise>
								</xsl:choose>
								<xsl:if test="hostUnreachAck &gt; 0">								
									<tr>
										<td class="SubTacticalDT" style="background-color:#339999;">
											<xsl:value-of select='hostUnreachAck'/>&#160;<xsl:value-of select='//main/str_acknowledged'/>
										</td>
									</tr>
								</xsl:if>
								<xsl:if test="hostUnreachInact &gt; 0">
								<tr>
									<td class="SubTacticalDT" style="background-color:#339999;">
										<xsl:value-of select='hostUnreachInact'/>&#160;<xsl:value-of select='//main/str_disabled'/>
									</td>
								</tr>
								</xsl:if>								
								<xsl:if test="hostUnreachUnhand &gt; 0">
								<tr>
									<xsl:element name='td'>
									<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
									<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unreachable'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_host_unhand'/>
											</xsl:attribute>											
											<xsl:value-of select='hostUnreachUnhand'/>&#160;<xsl:value-of select='//main/str_unhandledpb'/>
										</xsl:element>									
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTableTactical</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_up'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_hostOK'/>
											</xsl:attribute>											
											<xsl:value-of select='hostUp'/>&#160;<xsl:value-of select='//main/str_up'/>
										</xsl:element>									
									</xsl:element>
								</tr>
								<xsl:if test="hostUpInactive &gt; 0">								
								<tr>
									<td class="SubTacticalDT" style="background-color:#ccff99;">
										<xsl:value-of select='hostUpInactive'/>&#160;<xsl:value-of select='str_disabled'/>
									</td>
								</tr>
								</xsl:if>								
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>								
								<xsl:choose>
									<xsl:when test="hostPending &gt; 0">
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_pending'/>;font-weight:bold;</xsl:attribute>
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_hostPb'/>
												</xsl:attribute>											
												<xsl:value-of select='hostPending'/>&#160;<xsl:value-of select='//main/str_pending'/>
											</xsl:element>
										</xsl:element>
									</tr>
									</xsl:when>
									<xsl:otherwise>								
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;</xsl:attribute>
											<xsl:value-of select='hostPending'/>&#160;<xsl:value-of select="//main/str_pending"/>										
										</xsl:element>
									</tr>				
									</xsl:otherwise>
								</xsl:choose>
								<xsl:if test="hostPendingInact &gt; 0">								
								<tr>
									<td class="SubTacticalDT" style="background-color:#99cccc;">
										<xsl:value-of select='hostPendingInact'/>&#160;<xsl:value-of select="//main/str_disabled"/>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test="hostPendingUnhand &gt; 0">
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_pending'/>;font-weight:bold;</xsl:attribute>
										<xsl:value-of select='hostPendingUnhand'/>&#160;<xsl:value-of select="//main/str_unhandledpb"/>
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
<div id="unhandledproblems">
	<table>
		<tr>
			<td style="font-weight:bold;background-color:#ffffff;">
				&#160;::&#160;<xsl:value-of select="//main/str_hostprobunhandled"/>
			</td>
		</tr>
		<tr>			
			<xsl:choose>
			<xsl:when test="nbHostPb = 0">			
				<td align="center"><xsl:value-of select='//main/str_hostprobno_unhandled'/></td>
			</xsl:when>
			<xsl:otherwise>			
			<td>
				<table class="tactical_light_table">
					<tr style="font-weight:bold;" class="tactical_light_header">
                        <td align='center'><xsl:value-of select='//main/str_hostprobcriticality'/></td>
						<td align='center' colspan="2"><xsl:value-of select='//main/str_hostprobhostname'/></td>
						<td align='center'><xsl:value-of select='//main/str_hostprobstatus'/></td>
						<td align='center'><xsl:value-of select='//main/str_hostprobip'/></td>
						<td align='center'><xsl:value-of select='//main/str_hostprobduration'/></td>
						<td align='center'><xsl:value-of select='//main/str_hostproblastcheck'/></td>
						<td align='center' style="width:500px;"><xsl:value-of select='//main/str_hostproboutput'/></td>
					</tr>					
					<xsl:for-each select='//root/unhandledHosts'>
					<xsl:element name='tr'>
						<xsl:attribute name='class'><xsl:value-of select='class'/></xsl:attribute>
                        <xsl:element name='td'>
                            <xsl:attribute name='class'>ListColCenter</xsl:attribute>
                            <xsl:attribute name='align'>center</xsl:attribute>
                            <xsl:attribute name='style'>font-weight:bold;white-space:nowrap;</xsl:attribute>
                            <xsl:element name='img'>
                                <xsl:attribute name='src'>
                                    <xsl:value-of select='hostcriticality'/>
                                </xsl:attribute>
                            </xsl:element>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='class'>ListColLeft</xsl:attribute>
							<xsl:attribute name='style'>white-space:nowrap;</xsl:attribute>
							<xsl:if test='icon != ""'>
								<xsl:element name='img'>
									<xsl:attribute name='src'>
										./img/media/<xsl:value-of select='icon'/>
									</xsl:attribute>
									<xsl:attribute name='width'>16</xsl:attribute>
									<xsl:attribute name='height'>16</xsl:attribute>
								</xsl:element>
							</xsl:if>
							<xsl:element name='a'>
								<xsl:attribute name='href'><xsl:value-of select='//main/url_hostdetail'/><xsl:value-of select='hostname'/></xsl:attribute>
								<xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
								<xsl:attribute name="id">host-<xsl:value-of select="hid"/></xsl:attribute>
								<xsl:value-of select='hostname'/>
							</xsl:element>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='style'>border-left:0;width:auto;</xsl:attribute>
							<xsl:if test='host_notesurl != ""'>
								<xsl:element name='a'>
									<xsl:attribute name='href'>
										<xsl:value-of select='host_notesurl'/>
									</xsl:attribute>
									<xsl:attribute name='title'>
										<xsl:value-of select='host_notes'/>
									</xsl:attribute>
									<xsl:attribute name='target'>_new</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name='src'>./img/icones/15x7/weblink.gif</xsl:attribute>
									</xsl:element>
								</xsl:element>	
							</xsl:if>
							<xsl:if test='host_actionurl != ""'>
								<xsl:element name='a'>
									<xsl:attribute name='href'>
										<xsl:value-of select='host_actionurl'/>
									</xsl:attribute>									
									<xsl:attribute name='target'>_new</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name='src'>./img/icones/16x16/star_yellow.gif</xsl:attribute>
									</xsl:element>
								</xsl:element>	
							</xsl:if>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='class'>ListColCenter</xsl:attribute>
							<xsl:attribute name='style'>background-color:<xsl:value-of select='bgcolor'/>;font-weight:bold;white-space:nowrap;</xsl:attribute>
							<xsl:attribute name='align'>center</xsl:attribute>
							<xsl:value-of select='state'/>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='class'>ListColCenter</xsl:attribute>
							<xsl:attribute name='align'>center</xsl:attribute>
							<xsl:attribute name='style'>white-space:nowrap;</xsl:attribute>
							<xsl:value-of select='ip'/>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='class'>ListColRight</xsl:attribute>
							<xsl:attribute name='align'>right</xsl:attribute>
							<xsl:attribute name='style'>white-space:nowrap;</xsl:attribute>
							<xsl:value-of select='duration'/>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='class'>ListColCenter</xsl:attribute>
							<xsl:attribute name='align'>center</xsl:attribute>
							<xsl:attribute name='style'>white-space:nowrap;</xsl:attribute>
							<xsl:value-of select='last'/>
						</xsl:element>
						<xsl:element name='td'>
							<xsl:attribute name='class'>ListColNoWrap</xsl:attribute>							
							<xsl:attribute name='style'>width:500px;</xsl:attribute>
							<xsl:value-of select='output'/>
						</xsl:element>
					</xsl:element>					
					</xsl:for-each>
				</table>
				</td>
			</xsl:otherwise>
			</xsl:choose>		
		</tr>		
	</table>
</div><br />
<div id="serviceStats">
	<table>
		<tr>
			<td style="font-weight:bold;background-color:#ffffff;">&#160;::&#160;<xsl:value-of select='//main/str_services'/></td>
		</tr>
		<tr>
			<td style="vertical-align:top;">
				<table class="SubTableTactical">
					<tr>
						<td class="SubTacticalDT">
							<table>								
								<xsl:choose>
									<xsl:when test='svcCritical &gt; 0 and svcCriticalUnhand &gt; 0'>
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_critical'/>;font-weight:bold;</xsl:attribute>
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_critical'/>
												</xsl:attribute>
												<xsl:value-of select='svcCritical'/>&#160;<xsl:value-of select='//main/str_critical'/>
											</xsl:element>										
										</xsl:element>
									</tr>
									</xsl:when>								
									<xsl:when test='svcCritical &gt; 0'>
									<tr>
										<td style="background-color:#ee9572;">
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_critical'/>
												</xsl:attribute>											
												<xsl:value-of select='svcCritical'/>&#160;<xsl:value-of select='//main/str_critical'/>
											</xsl:element>
										</td>
									</tr>
									</xsl:when>
									<xsl:otherwise>
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;</xsl:attribute>
											<xsl:value-of select='svcCritical'/>&#160;<xsl:value-of select='//main/str_critical'/>
										</xsl:element>
									</tr>								
									</xsl:otherwise>
								</xsl:choose>								
								<xsl:if test='svcCriticalAck &gt; 0'>
								<tr>
									<td class="SubTacticalDT" style="background-color:#ee9572;">
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_ack'/>
											</xsl:attribute>											
											<xsl:value-of select='svcCriticalAck'/>&#160;<xsl:value-of select='//main/str_acknowledged'/>
										</xsl:element>										
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcCriticalInact &gt; 0'>								
								<tr>
									<td class="SubTacticalDT" style="background-color:#ee9572;">
										<xsl:value-of select='svcCriticalInact'/>&#160;<xsl:value-of select='//main/str_disabled'/>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcCriticalOnpbHost &gt; 0'>								
								<tr>
									<td class="SubTacticalDT" style="background-color:#ee9572;">
										<xsl:value-of select='svcCriticalOnpbHost'/>&#160;<xsl:value-of select='//main/str_pbhost'/>
									</td>
								</tr>								
								</xsl:if>
								<xsl:if test='svcCriticalUnhand &gt; 0'>
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_critical'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_unhand'/>
											</xsl:attribute>											
											<xsl:value-of select='svcCriticalUnhand'/>&#160;<xsl:value-of select='//main/str_unhandledpb'/>
										</xsl:element>
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>
								<xsl:choose>
									<xsl:when test='svcWarning &gt; 0 and svcWarningUnhand &gt; 0'>
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_warning'/>;font-weight:bold;</xsl:attribute>
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_warning'/>
												</xsl:attribute>											
												<xsl:value-of select='svcWarning'/>&#160;<xsl:value-of select='//main/str_warning'/>
											</xsl:element>
										</xsl:element>
									</tr>
									</xsl:when>								
									<xsl:when test='svcWarning &gt; 0'>
									<tr>
										<td style="background-color:#ffcc66;">
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_warning'/>
												</xsl:attribute>											
												<xsl:value-of select='svcWarning'/>&#160;<xsl:value-of select='//main/str_warning'/>
											</xsl:element>										
										</td>
									</tr>
									</xsl:when>
									<xsl:otherwise>								
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/></xsl:attribute>										
											<xsl:value-of select='svcWarning'/>&#160;<xsl:value-of select='//main/str_warning'/>
										</xsl:element>
									</tr>
									</xsl:otherwise>							
								</xsl:choose>								
								<xsl:if test='svcWarningAck &gt; 0'>
								<tr>
									<td class="SubTacticalDT" style="background-color:#ffcc66;">
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_ack'/>
											</xsl:attribute>											
											<xsl:value-of select='svcWarningAck'/>&#160;<xsl:value-of select='//main/str_acknowledged'/>
										</xsl:element>										
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcWarningInact &gt; 0'>								
								<tr>
									<td class="SubTacticalDT" style="background-color:#ffcc66;">
										<xsl:value-of select='svcWarningInact'/>&#160;<xsl:value-of select='//main/str_disabled'/>
									</td>
								</tr>
								</xsl:if>								
								<xsl:if test='svcWarningOnpbHost &gt; 0'>
								<tr>
									<td class="SubTacticalDT" style="background-color:#ffcc66;">
										<xsl:value-of select='svcWarningOnpbHost'/>&#160;<xsl:value-of select='//main/str_pbhost'/>
									</td>
								</tr>
								</xsl:if>								
								<xsl:if test='svcWarningUnhand &gt; 0'>
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_warning'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_unhand'/>
											</xsl:attribute>
											<xsl:value-of select='svcWarningUnhand'/>&#160;<xsl:value-of select='//main/str_unhandledpb'/>
										</xsl:element>
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_ok'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_ok'/>
											</xsl:attribute>											
											<xsl:value-of select='svcOk'/>&#160;<xsl:value-of select='//main/str_ok'/>
										</xsl:element>
									</xsl:element>
								</tr>
								<xsl:if test='svcOkInactive &gt; 0'>
									<tr>
										<td class="SubTacticalDT" style="background-color:#ccff99;">
											<xsl:value-of select='svcOkInactive'/>&#160;<xsl:value-of select='//main/str_disabled'/>
										</td>
									</tr>
								</xsl:if>
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>
								<xsl:choose>								
									<xsl:when test='svcUnknown &gt; 0 and svcUnknownUnhand &gt; 0'>
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;font-weight:bold;</xsl:attribute>
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_unknown'/>
												</xsl:attribute>
												<xsl:value-of select='svcUnknown'/>&#160;<xsl:value-of select='//main/str_unknown'/>
											</xsl:element>
										</xsl:element>
									</tr>
									</xsl:when>
									<xsl:otherwise>								
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color.color_unknown'/>;</xsl:attribute>
											<xsl:element name='a'>
												<xsl:attribute name='href'>
													<xsl:value-of select='//main/url_unknown'/>
												</xsl:attribute>											
												<xsl:value-of select='svcUnknown'/>&#160;<xsl:value-of select='//main/str_unknown'/>
											</xsl:element>
										</xsl:element>
									</tr>
									</xsl:otherwise>								
								</xsl:choose>								
								<xsl:if test='svcUnknownAck &gt; 0'>
								<tr>
									<td class="SubTacticalDT" style="background-color:#cccccc;">
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_ack'/>
											</xsl:attribute>											
											<xsl:value-of select='svcUnknownAck'/>&#160;<xsl:value-of select='//main/str_acknowledged'/>
										</xsl:element>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcUnknownInact &gt; 0'>													
								<tr>
									<td class="SubTacticalDT" style="background-color:#cccccc;">
										<xsl:value-of select='svcUnknownInact'/>&#160;<xsl:value-of select='//main/str_disabled'/>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcUnknownOnpbHost &gt; 0'>								
								<tr>
									<td class="SubTacticalDT" style="background-color:#cccccc;">
										<xsl:value-of select='svcUnknownOnpbHost'/>&#160;<xsl:value-of select='//main/str_pbhost'/>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcUnknownUnhand &gt; 0'>								
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;font-weight:bold;</xsl:attribute>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_unhand'/>
											</xsl:attribute>
											<xsl:value-of select='svcUnknownUnhand'/>&#160;<xsl:value-of select='//main/str_unhandledpb'/>
										</xsl:element>
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
						<td class="SubTacticalDT">
							<table>								
								<xsl:choose>
									<xsl:when test='svcPending &gt; 0'>
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_pending'/>;font-weight:bold;</xsl:attribute>
											<xsl:value-of select='svcPending'/>&#160;<xsl:value-of select='//main/str_pending'/>
										</xsl:element>
									</tr>
									</xsl:when>
									<xsl:otherwise>						
									<tr>
										<xsl:element name='td'>
											<xsl:attribute name='style'>background-color:<xsl:value-of select='color_unknown'/>;</xsl:attribute>
											<xsl:value-of select='svcPending'/>&#160;<xsl:value-of select='//main/str_pending'/>
										</xsl:element>
									</tr>
									</xsl:otherwise>			
								</xsl:choose>
								<xsl:if test='svcPendingInact &gt; 0'>								
								<tr>
									<td class="SubTacticalDT" style="background-color:#99cccc;">
										<xsl:value-of select='svcPendingInact'/>&#160;<xsl:value-of select='//main/str_disabled'/>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcPendingOnpbHost &gt; 0'>								
								<tr>
									<td class="SubTacticalDT" style="background-color:#99cccc;">
										<xsl:value-of select='svcPendingOnpbHost'/>&#160;<xsl:value-of select='//main/str_pbhost'/>
									</td>
								</tr>
								</xsl:if>
								<xsl:if test='svcPendingUnhand &gt; 0'>								
								<tr>
									<xsl:element name='td'>
										<xsl:attribute name='class'>SubTacticalDT</xsl:attribute>
										<xsl:attribute name='style'>background-color:<xsl:value-of select='color_pending'/>;font-weight:bold;</xsl:attribute>									
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='//main/url_svc_unhand'/>
											</xsl:attribute>											
											<xsl:value-of select='svcPendingUnhand'/>&#160;<xsl:value-of select='//main/str_unhandledpb'/>
										</xsl:element>
									</xsl:element>
								</tr>
								</xsl:if>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
<div id="unhandledproblems">
	<table>
		<tr>
			<td style="font-weight:bold;background-color:#ffffff;">&#160;::&#160;<xsl:value-of select='//main/str_unhandled'/></td>
		</tr>
		<tr>			
			<xsl:choose>
				<xsl:when test='nbSvcPb = 0'>
					<td align="center"><xsl:value-of select='//main/str_no_unhandled'/></td>
				</xsl:when>
				<xsl:otherwise>
				<td>
					<table class="tactical_light_table">
						<tr style="font-weight:bold;" class="tactical_light_header">
                            <td align='center'><xsl:value-of select='//main/str_criticality'/></td>
							<td align='center'><xsl:value-of select='//main/str_hostname'/></td>
							<td align='center' colspan="2"><xsl:value-of select='//main/str_servicename'/></td>
							<td align='center'><xsl:value-of select='//main/str_status'/></td>
							<td align='center'><xsl:value-of select='//main/str_ip'/></td>
							<td align='center'><xsl:value-of select='//main/str_duration'/></td>
							<td align='center'><xsl:value-of select='//main/str_lastcheck'/></td>
							<td align='center' style="width:500px;"><xsl:value-of select='//main/str_output'/></td>
						</tr>
						<xsl:for-each select='//root/unhandledServices'>						
							<xsl:element name='tr'>
								<xsl:attribute name='class'><xsl:value-of select='class'/></xsl:attribute>
                                <xsl:element name='td'>
									<xsl:attribute name='class'>ListColCenter</xsl:attribute>
									<xsl:attribute name='style'>white-space: nowrap;font-weight:bold;</xsl:attribute>
									<xsl:attribute name='align'>center</xsl:attribute>
                                    <xsl:element name='img'>
                                        <xsl:attribute name='src'>
                                            <xsl:value-of select='servicecriticality'/>
                                        </xsl:attribute>
                                    </xsl:element>
								</xsl:element>
								<td class="ListColLeft" style="white-space:nowrap;">
									<xsl:if test='icon != ""'>
										<xsl:element name='img'>
											<xsl:attribute name='src'>./img/media/<xsl:value-of select='icon'/></xsl:attribute>
											<xsl:attribute name='width'>16</xsl:attribute>
											<xsl:attribute name='height'>16</xsl:attribute>
											&#160;&#160;
										</xsl:element>
									</xsl:if>						
									<xsl:element name='a'>
										<xsl:attribute name="id">host-<xsl:value-of select="hid"/></xsl:attribute>
										<xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
										<xsl:attribute name='href'>
											<xsl:value-of select='//main/url_hostdetail'/><xsl:value-of select='hostname'/>
										</xsl:attribute>
										<xsl:attribute name='href'><xsl:value-of select='//main/url_hostdetail'/><xsl:value-of select='hostname'/></xsl:attribute>
										<xsl:value-of select='hostname'/>
									</xsl:element>
								</td>					
								<xsl:element name='td'>
									<xsl:attribute name='class'>ListColLeft</xsl:attribute>
									<xsl:attribute name='style'>
										white-space: nowrap;
									</xsl:attribute>									
									<xsl:element name='a'>
										<xsl:attribute name="id">service-<xsl:value-of select="sid"/></xsl:attribute>
										<xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
										<xsl:attribute name='href'>
											<xsl:value-of select='//main/url_svcdetail'/><xsl:value-of select='hostname'/><xsl:value-of select='//main/url_svcdetail2'/><xsl:value-of select='servicename'/>
										</xsl:attribute>
										<xsl:value-of select='servicename'/>
									</xsl:element>
                                </xsl:element>
								<xsl:element name='td'>
                                    <xsl:attribute name='style'>border-left:0;width:auto;</xsl:attribute>
									<xsl:if test='notes_url != ""'>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='notes_url'/>
											</xsl:attribute>
											<xsl:attribute name='title'>
												<xsl:value-of select='notes'/>
											</xsl:attribute>
											<xsl:attribute name='target'>_new</xsl:attribute>											
											<xsl:element name='img'>
												<xsl:attribute name='src'>./img/icones/15x7/weblink.gif</xsl:attribute>
											</xsl:element>
										</xsl:element>	
									</xsl:if>
									<xsl:if test='action_url != ""'>
										<xsl:element name='a'>
											<xsl:attribute name='href'>
												<xsl:value-of select='action_url'/>
											</xsl:attribute>											
											<xsl:attribute name='target'>_new</xsl:attribute>											
											<xsl:element name='img'>
												<xsl:attribute name='src'>./img/icones/16x16/star_yellow.gif</xsl:attribute>
											</xsl:element>
										</xsl:element>	
									</xsl:if>
								</xsl:element>
								<xsl:element name='td'>
									<xsl:attribute name='class'>ListColCenter</xsl:attribute>
									<xsl:attribute name='style'>
										background-color: <xsl:value-of select='bgcolor'/>;white-space: nowrap;font-weight:bold;
									</xsl:attribute>
									<xsl:attribute name='align'>center</xsl:attribute>
									<xsl:value-of select='state'/>
								</xsl:element>
								<td class="ListColCenter" align='center' style="white-space:nowrap;">
									<xsl:value-of select='ip'/>						
								</td>
								<td class="ListColRight" align='right' style="white-space:nowrap;">
									<xsl:value-of select='duration'/>
								</td>
								<td class="ListColCenter" align='center' style="white-space:nowrap;">
									<xsl:value-of select='last'/>
								</td>
								<td class="ListColNoWrap" style="width:500px;">
									<xsl:value-of select='output'/>
								</td>						
							</xsl:element>
						</xsl:for-each>
					</table>
				</td>
				</xsl:otherwise>
			</xsl:choose>			
		</tr>
	</table>
</div>
</xsl:for-each>
<div id="div_img" class="img_volante"></div>
<div id="div_popup" class="popup_volante"><div class="container-load"></div><div id="popup-container-display"></div></div>
</xsl:template>
</xsl:stylesheet>