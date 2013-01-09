<xsl:stylesheet xmlns:its="http://www.w3.org/2005/11/its" xmlns:datc="http://example.com/datacats"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:XSL="http://www.w3.org/1999/XSL/TransformAlias" exclude-result-prefixes="xsl"
	version="2.0">
	<xsl:output method="xml" indent="yes" encoding="utf-8"/>
	<xsl:strip-space elements="*"/>
	<xsl:namespace-alias stylesheet-prefix="XSL" result-prefix="xsl"/>
	<xsl:param name="inputDocUri">notest</xsl:param>
	<xsl:param name="inputDatacats">all</xsl:param>
	<xsl:variable name="dataCatDoc" select="." as="node()*"/>
	<xsl:variable name="inputDoc" select="doc('inputfile.xml')" as="node()*"/>
	<xsl:variable name="datacategories">
		<xsl:choose>
			<xsl:when test="$inputDatacats='all'">
				<xsl:copy-of select="//datc:datacat"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:for-each select="tokenize($inputDatacats,'\s+')">
					<xsl:variable name="datacatName">
						<xsl:value-of select="."/>
					</xsl:variable>
					<xsl:copy-of select="$dataCatDoc//datc:datacat[@name=$datacatName]"/>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="locallyAddedMarkup">
		<xsl:for-each select="$datacategories//datc:localAdding/@addedMarkup">
			<xsl:value-of select="."/>
			<xsl:text> | </xsl:text>
		</xsl:for-each>
	</xsl:variable>
	<xsl:template match="/">
		<!-- Write general stylesheet stuff, including path generation template-->
		<XSL:stylesheet version="2.0" xmlns:its="http://www.w3.org/2005/11/its"
			xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<XSL:output method="xml" indent="yes" encoding="utf-8"/>
			<XSL:strip-space elements="*"/>
			<XSL:template match="*|@*" mode="get-full-path">
				<XSL:apply-templates select="parent::*" mode="get-full-path"/>
				<XSL:text>/</XSL:text>
				<XSL:if test="count(. | ../@*) = count(../@*)">@</XSL:if>
				<XSL:value-of select="name()"/>
				<XSL:if test="self::element() and parent::element()">
					<XSL:text>[</XSL:text>
					<XSL:number/>
					<!-- <XSL:value-of select="1+count(preceding-sibling::*[name()=name(current())])"/> not necessary, see mail from Sebastian from 2006/07/07 23:50.-->
					<XSL:text>]</XSL:text>
				</XSL:if>
			</XSL:template>
			<!-- Write root template. This creates nodeList for one data category and calls the recursion template. -->
			<XSL:template name="writeOutput">
				<XSL:param name="outputType">no-value</XSL:param>
				<XSL:param name="outputValue" as="node()*">
					<output>no-value</output>
				</XSL:param>
				<XSL:element name="node">
					<XSL:attribute name="path">
						<XSL:apply-templates mode="get-full-path" select="."/>
					</XSL:attribute>
					<XSL:attribute name="outputType">
						<XSL:value-of select="$outputType"/>
					</XSL:attribute>
					<XSL:copy-of select="$outputValue"/>
				</XSL:element>
			</XSL:template>
			<XSL:template match="/">
				<nodeList xmlns:its="http://www.w3.org/2005/11/its">
					<xsl:for-each select="$datacategories//datc:datacat">
						<XSL:element name="nodeList">
							<XSL:attribute name="datacat">
								<XSL:text>
									<xsl:value-of select="@name"/>
								</XSL:text>
							</XSL:attribute>
							<!-- The call of the recursion template used for local and inheritance stuff. Takes care of (possible non-existing) default values. -->
							<XSL:apply-templates mode="{@name}">
								<XSL:with-param name="existingDataCatValue">
									<xsl:choose>
										<xsl:when test="datc:defaults/datc:*">
											<xsl:text>default-value</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>no-value</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</XSL:with-param>
							</XSL:apply-templates>
						</XSL:element>
					</xsl:for-each>
				</nodeList>
			</XSL:template>
			<xsl:for-each select="$datacategories//datc:datacat">
				<!--
	    The template below is not used to achieve comparibility with other ITS implementations.
	    <XSL:template match="{concat($locallyAddedMarkup, '@xyzAttr')}" mode="{@name}"/>-->
				<!-- Just for readability the following is separated.-->
				<xsl:if test="datc:localAdding">
					<xsl:call-template name="localMarkupTemplate"/>
				</xsl:if>
				<xsl:call-template name="recursionTemplate"/>
				<xsl:if test="datc:rulesElement">
					<xsl:call-template name="globalRulesTemplate"/>
				</xsl:if>
			</xsl:for-each>
		</XSL:stylesheet>
	</xsl:template>
	<xsl:template name="gatherRules">
		<xsl:param name="dataCat"/>
		<xsl:param name="docWithRules"/>
		<xsl:for-each select="$docWithRules//its:rules">
			<xsl:if test="@xlink:href" xmlns:xlink="http://www.w3.org/1999/xlink">
				<xsl:call-template name="gatherRules">
					<xsl:with-param name="dataCat" select="$dataCat"/>
					<xsl:with-param name="docWithRules"
						select="doc(concat(substring-before(base-uri($docWithRules),tokenize(base-uri($docWithRules), '(/)|(\\)')[last()]),@xlink:href))"
					/>
				</xsl:call-template>
				<!--<xsl:call-template name="gatherRules">
				<xsl:with-param name="dataCat" select="$dataCat"/>
				<xsl:with-param name="docWithRules" select="doc('/home/leroy/Desktop/rules.xml')"/>
			      </xsl:call-template>-->
			</xsl:if>
			<xsl:for-each
				select=".//*[local-name()=$dataCat/*/datc:rulesElement/@name and
		  namespace-uri()='http://www.w3.org/2005/11/its']">
				<xsl:copy-of select="."/>
			</xsl:for-each>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="globalRulesTemplate">
		<xsl:variable name="dataCat">
			<xsl:copy-of select="."/>
		</xsl:variable>
		<xsl:variable name="globalRules">
			<xsl:call-template name="gatherRules">
				<xsl:with-param name="dataCat" select="$dataCat"/>
				<xsl:with-param name="docWithRules" select="$inputDoc"/>
			</xsl:call-template>
		</xsl:variable>
		<xsl:for-each select="$globalRules/*">
			<xsl:variable name="priority">
				<xsl:number/>
			</xsl:variable>
			<xsl:variable name="globalMarkup" as="node()*">
				<xsl:copy-of
					select="@*[not(name()='selector')][not(contains(name(),'Pointer'))] |
		    node()"
				/>
			</xsl:variable>
			<xsl:variable name="pointerMarkup" as="node()*" select="@*[contains(name(),'Pointer')]|@*[contains(name(),'idValue')]"/>
			<xsl:for-each select="$pointerMarkup">
				<XSL:template mode="{concat($dataCat/*/@name,name(.))}" match="element() | @*">
					<xsl:attribute name="priority" select="$priority"/>
					<XSL:copy>
						<XSL:apply-templates select="@* | node()"
							mode="{concat($dataCat/*/@name,name(.))}"/>
					</XSL:copy>
				</XSL:template>
			</xsl:for-each>
			<!--            </xsl:if>-->
			<XSL:template mode="{$dataCat/*/@name}" match="{@selector}">
				<xsl:variable name="currentRuleEl" select="."/>
				<xsl:attribute name="priority">
					<xsl:number/>
				</xsl:attribute>
				<xsl:for-each select="in-scope-prefixes(.)[not(.='xml')]">
					<xsl:namespace name="{.}" select="namespace-uri-for-prefix(.,$currentRuleEl)"/>
				</xsl:for-each>
				<XSL:call-template name="writeOutput">
					<XSL:with-param name="outputType">new-value-global</XSL:with-param>
					<XSL:with-param name="outputValue" as="node()*">
						<output>
							<xsl:if test="not(empty($globalMarkup))">
								<xsl:copy-of select="$globalMarkup"/>
							</xsl:if>
							<!--                             <xsl:if test="not(empty($pointerMarkup))">-->
							<xsl:for-each select="$pointerMarkup">
								<xsl:element name="{name(.)}">
									<XSL:apply-templates select="{.}"
										mode="{concat($dataCat/*/@name,name(.))}"/>
								</xsl:element>
							</xsl:for-each>
							<!--                            </xsl:if>-->
						</output>
					</XSL:with-param>
				</XSL:call-template>
				<XSL:apply-templates mode="{$dataCat/*/@name}" select="@* | element()">
					<XSL:with-param name="existingDataCatValue" as="node()*">
						<xsl:choose>
							<xsl:when test="datc:inheritance/@appliesTo='none'">
								<xsl:choose>
									<xsl:when test="datc:defaults/datc:*">
										<xsl:text>default-value</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>no-value</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
							<xsl:otherwise>
								<output>
									<xsl:if test="not(empty($globalMarkup))">
										<xsl:copy-of select="$globalMarkup"/>
									</xsl:if>
									<!--                            <xsl:if test="not(empty($pointerMarkup))">-->
									<xsl:for-each select="$pointerMarkup">
										<xsl:element name="{name(.)}">
											<XSL:apply-templates select="{.}"
												mode="{concat($dataCat/*/@name,name(.))}"/>
										</xsl:element>
									</xsl:for-each>
									<!--                            </xsl:if>-->
								</output>
							</xsl:otherwise>
						</xsl:choose>
					</XSL:with-param>
				</XSL:apply-templates>
			</XSL:template>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="localMarkupTemplate">
		<XSL:template match="{datc:localAdding/@datcatSelector}" mode="{@name}" priority="+1000">
			<XSL:call-template name="writeOutput">
				<XSL:with-param name="outputType">new-value-local</XSL:with-param>
				<XSL:with-param name="outputValue" as="node()*">
					<output>
						<XSL:for-each select="{datc:localAdding/@addedMarkup}">
							<XSL:copy-of select="."/>
						</XSL:for-each>
					</output>
				</XSL:with-param>
			</XSL:call-template>
			<XSL:apply-templates mode="{@name}" select="@* | element()">
				<XSL:with-param name="existingDataCatValue" as="node()*">
					<xsl:choose>
						<xsl:when test="datc:inheritance/@appliesTo='none'">
							<xsl:choose>
								<xsl:when test="datc:defaults/datc:*">
									<xsl:text>default-value</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>no-value</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<output>
								<XSL:for-each select="{datc:localAdding/@addedMarkup}">
									<XSL:copy-of select="."/>
								</XSL:for-each>
							</output>
						</xsl:otherwise>
					</xsl:choose>
				</XSL:with-param>
			</XSL:apply-templates>
		</XSL:template>
	</xsl:template>
	<xsl:template name="iterateWithNoValue">
		<XSL:if test="$existingDataCatValue='no-value'">
			<XSL:call-template name="writeOutput">
				<XSL:with-param name="outputType">no-value</XSL:with-param>
				<XSL:with-param name="outputValue" as="node()*">
					<output/>
				</XSL:with-param>
			</XSL:call-template>
			<XSL:apply-templates mode="{@name}" select="@* | element()">
				<XSL:with-param name="existingDataCatValue" as="node()*">no-value</XSL:with-param>
			</XSL:apply-templates>
		</XSL:if>
	</xsl:template>
	<xsl:template name="recursionTemplate">
		<XSL:template match="*" mode="{@name}" priority="-1000">
			<XSL:param name="existingDataCatValue" as="node()*">no-value</XSL:param>
			<!-- warning: this needs to be changed for the categories which don't have local markup.-->
			<xsl:if test="datc:defaults/datc:defaultsElements">
				<XSL:if test="$existingDataCatValue='default-value'">
					<XSL:call-template name="writeOutput">
						<XSL:with-param name="outputType">default-value</XSL:with-param>
						<XSL:with-param name="outputValue" as="node()*">
							<output>
								<xsl:copy-of
									select="datc:defaults/datc:defaultsElements/@* |
			    datc:defaults/datc:defaultsElement/*"
								/>
							</output>
						</XSL:with-param>
					</XSL:call-template>
					<XSL:apply-templates mode="{@name}" select="@* | element()">
						<XSL:with-param name="existingDataCatValue" as="node()*"
							>default-value</XSL:with-param>
					</XSL:apply-templates>
				</XSL:if>
			</xsl:if>
			<xsl:if test="not(datc:inheritance/@appliesTo='none')">
				<XSL:if
					test="not($existingDataCatValue='default-value') and
		  not($existingDataCatValue='no-value')">
					<XSL:call-template name="writeOutput">
						<XSL:with-param name="outputType">inherited</XSL:with-param>
						<XSL:with-param name="outputValue" as="node()*">
							<XSL:copy-of select="$existingDataCatValue"/>
						</XSL:with-param>
					</XSL:call-template>
					<XSL:apply-templates mode="{@name}" select="@* | element()">
						<XSL:with-param name="existingDataCatValue" as="node()*"
							select="$existingDataCatValue"/>
					</XSL:apply-templates>
				</XSL:if>
			</xsl:if>
			<xsl:call-template name="iterateWithNoValue"/>
		</XSL:template>
		<XSL:template match="@*" mode="{@name}" priority="-1000">
			<XSL:param name="existingDataCatValue" as="node()*">no-value</XSL:param>
			<xsl:if
				test="datc:inheritance/@appliesTo='onlyElements' or datc:inheritance/@appliesTo='none'">
				<XSL:call-template name="writeOutput">
					<xsl:choose>
						<xsl:when test="datc:defaults">
							<XSL:with-param name="outputType">default-value</XSL:with-param>
							<XSL:with-param name="outputValue" as="node()*">
								<output>
									<xsl:copy-of
										select="datc:defaults/datc:defaultsAttributes/@* |
			      datc:defaults/datc:defaultsAttributes/*"
									/>
								</output>
							</XSL:with-param>
						</xsl:when>
						<xsl:otherwise>
							<XSL:with-param name="outputType">no-value</XSL:with-param>
							<XSL:with-param name="outputValue" as="node()*">
								<output/>
							</XSL:with-param>
						</xsl:otherwise>
					</xsl:choose>
				</XSL:call-template>
			</xsl:if>
			<xsl:if test="datc:inheritance/@appliesTo='elementsAndAttributes'">
				<xsl:if test="datc:localAdding">
					<XSL:if test="{concat('parent::',datc:localAdding/@datcatSelector)}">
						<XSL:call-template name="writeOutput">
							<XSL:with-param name="outputType">new-value-local</XSL:with-param>
							<XSL:with-param name="outputValue" as="node()*">
								<output>
									<XSL:copy-of
										select="{concat('parent::*/',datc:localAdding/@addedMarkup)}"
									/>
								</output>
							</XSL:with-param>
						</XSL:call-template>
					</XSL:if>
				</xsl:if>
				<xsl:variable name="inheritanceTest">
					<xsl:text>not($existingDataCatValue='default-value') and not($existingDataCatValue='no-value')</xsl:text>
					<xsl:if test="datc:localAdding">
						<xsl:text>  and not(</xsl:text>
						<xsl:value-of
							select="concat('parent::',datc:localAdding[1]/@datcatSelector)"/>
						<xsl:text>)</xsl:text>
					</xsl:if>
				</xsl:variable>
				<XSL:if test="{$inheritanceTest}">
					<XSL:call-template name="writeOutput">
						<XSL:with-param name="outputType">inherited</XSL:with-param>
						<XSL:with-param name="outputValue" as="node()*">
							<XSL:copy-of select="$existingDataCatValue"/>
						</XSL:with-param>
					</XSL:call-template>
				</XSL:if>
				<xsl:variable name="defaultTest">
					<xsl:text>$existingDataCatValue='default-value'</xsl:text>
					<xsl:if test="datc:localAdding">
						<xsl:text>  and not(</xsl:text>
						<xsl:value-of
							select="concat('parent::',datc:localAdding[1]/@datcatSelector)"/>
						<xsl:text>)</xsl:text>
					</xsl:if>
				</xsl:variable>
				<XSL:if test="{$defaultTest}">
					<XSL:call-template name="writeOutput">
						<XSL:with-param name="outputType">default-value</XSL:with-param>
						<XSL:with-param name="outputValue" as="node()*">
							<output>
								<xsl:copy-of
									select="datc:defaults/datc:defaultsAttributes/@* |
			    datc:defaults/datc:defaultsAttributes/*"
								/>
							</output>
						</XSL:with-param>
					</XSL:call-template>
				</XSL:if>
				<XSL:if test="$existingDataCatValue='no-value'">
					<XSL:call-template name="writeOutput">
						<XSL:with-param name="outputType">no-value</XSL:with-param>
						<XSL:with-param name="outputValue" as="node()*">
							<output/>
						</XSL:with-param>
					</XSL:call-template>
				</XSL:if>
			</xsl:if>
		</XSL:template>
	</xsl:template>
</xsl:stylesheet>
