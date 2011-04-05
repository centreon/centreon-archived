<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
<xsl:for-each select="//root">
<xsl:for-each select="//input">
<xsl:element name="table">
		<xsl:element name="tbody">
			<xsl:attribute name="id">input_<xsl:value-of select="id" /></xsl:attribute>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_lvl_1</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">ListColLvl1_name</xsl:attribute>
					Input <xsl:value-of select="id" />
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">ListColLvl1_name</xsl:attribute>
					<xsl:attribute name="style">text-align: right</xsl:attribute>
					<xsl:element name="a">
						<xsl:attribute name="href">javascript:deleteRow('input',<xsl:value-of select="id" />);</xsl:attribute>
						<xsl:element name="img">
							<xsl:attribute name="src">./img/icones/16x16/delete.gif</xsl:attribute>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/name"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][name]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="name">
							<xsl:attribute name="value"><xsl:value-of select="name"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_two</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/failover"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][failover]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="failover">
							<xsl:attribute name="value"><xsl:value-of select="failover"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/type"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="select">
						<xsl:attribute name="id">input_<xsl:value-of select="id" />_type</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][type]</xsl:attribute>
						<xsl:attribute name="onchange">changeType('input',<xsl:value-of select="id" />,this.selectedIndex,this);</xsl:attribute>
						<xsl:element name="option">
							<xsl:if test="type='ipv4'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">ipv4</xsl:attribute>
							IP v4
						</xsl:element>
						<xsl:element name="option">
							<xsl:if test="type='ipv6'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">ipv6</xsl:attribute>
							IP v6
						</xsl:element>
						<xsl:element name="option">
							<xsl:if test="type='unix_client'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">unix_client</xsl:attribute>
							<xsl:value-of select="//main/lang/unix_socket_client"/>
						</xsl:element>
						<xsl:element name="option">
							<xsl:if test="type='unix_server'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">unix_server</xsl:attribute>
							<xsl:value-of select="//main/lang/unix_socket_server"/>
						</xsl:element>
						<xsl:element name="option">
							<xsl:if test="type='file'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">file</xsl:attribute>
							<xsl:value-of select="//main/lang/file"/>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_two</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_protocol</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/protocol"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="select">
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][protocol]</xsl:attribute>
						<xsl:element name="option">
							<xsl:if test="protocol='ndo'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">ndo</xsl:attribute>
							Ndo
						</xsl:element>
						<xsl:element name="option">
							<xsl:if test="protocol='xml'">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">xml</xsl:attribute>
							XML
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_host</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/host"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][host]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="host">
							<xsl:attribute name="value"><xsl:value-of select="host"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_two</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_port</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/port"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][port]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="port">
							<xsl:attribute name="value"><xsl:value-of select="port"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_netif</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/netif"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][net_iface]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="net_iface">
							<xsl:attribute name="value"><xsl:value-of select="net_iface"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_two</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_tls</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/use_tls"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="id">input_<xsl:value-of select="id" />_tls_en</xsl:attribute>
						<xsl:attribute name="type">radio</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][tls]</xsl:attribute>
						<xsl:attribute name="value">1</xsl:attribute>
						<xsl:attribute name="onChange">toggleTls('input',<xsl:value-of select="id" />,1);</xsl:attribute>
						<xsl:if test="tls = '1'">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>
					<xsl:value-of select="//main/lang/yes"/> 
					<xsl:element name="input">
						<xsl:attribute name="type">radio</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][tls]</xsl:attribute>
						<xsl:attribute name="value">0</xsl:attribute>
						<xsl:attribute name="onChange">toggleTls('input',<xsl:value-of select="id" />,0);</xsl:attribute>
						<xsl:if test="tls = '0'">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>
					<xsl:value-of select="//main/lang/no"/>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_tls_ca</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/ca"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][ca]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="ca">
							<xsl:attribute name="value"><xsl:value-of select="ca"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_two</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_tls_cert</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/cert"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][cert]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="cert">
							<xsl:attribute name="value"><xsl:value-of select="cert"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_tls_key</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/certkey"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][key]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="key">
							<xsl:attribute name="value"><xsl:value-of select="key"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_two</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_tls_compress</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/tls_compress"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">radio</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][compress]</xsl:attribute>
						<xsl:attribute name="value">1</xsl:attribute>
						<xsl:if test="compress='true'">
						<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>
					<xsl:value-of select="//main/lang/yes"/> 
					<xsl:element name="input">
						<xsl:attribute name="type">radio</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][compress]</xsl:attribute>
						<xsl:attribute name="value">0</xsl:attribute>
						<xsl:if test="compress='0'">
						<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>
					<xsl:value-of select="//main/lang/no"/>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_socket</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/socket_path"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][socket]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="socket">
							<xsl:attribute name="value"><xsl:value-of select="socket"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="tr">
				<xsl:attribute name="class">list_one</xsl:attribute>
				<xsl:attribute name="id">input_<xsl:value-of select="id" />_filename</xsl:attribute>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowField</xsl:attribute>
					<xsl:value-of select="//main/lang/filename"/>
				</xsl:element>
				<xsl:element name="td">
					<xsl:attribute name="class">FormRowValue</xsl:attribute>
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">input[<xsl:value-of select="id" />][filename]</xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:if test="filename">
							<xsl:attribute name="value"><xsl:value-of select="filename"/></xsl:attribute>
						</xsl:if>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
</xsl:element>
</xsl:for-each>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>