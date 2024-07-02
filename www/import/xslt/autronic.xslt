<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:math="http://www.w3.org/2005/xpath-functions/math" xmlns:array="http://www.w3.org/2005/xpath-functions/array" xmlns:map="http://www.w3.org/2005/xpath-functions/map" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:err="http://www.w3.org/2005/xqt-errors" exclude-result-prefixes="array fn map math xhtml xs err" version="3.0">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" byte-order-mark="no" indent="yes"/>

	<xsl:param name="outputFileName" select="'default.xml'"/>

	<xsl:template match="/">
		<xsl:variable name="output">
			<xsl:element name="S5Data">
				<xsl:apply-templates select="VelkoobchodniCenik/Sortiment"/>
			</xsl:element>
		</xsl:variable>

		<!-- Uložení výstupního dokumentu do souboru -->
		<xsl:result-document href="{$outputFileName}">
			<xsl:copy-of select="$output"/>
		</xsl:result-document>
	</xsl:template>

	<xsl:template match="VelkoobchodniCenik/Sortiment">
		<xsl:element name="ArtiklList">
			<xsl:apply-templates select="Zbozi" mode="Artikl"/>
		</xsl:element>
		<xsl:element name="PolozkaCenikuList">
			<xsl:apply-templates select="Zbozi" mode="Cenik"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="Zbozi" mode="Artikl">
		<xsl:variable name="name" select="Nazev"/>
		<xsl:variable name="uniqCode" select="MatID"/>
		<xsl:variable name="status" select="Dostupnost"/>
		<xsl:variable name="companyID" select="'ad15b39b-2f90-427a-b0aa-3faf62293ece'"/>
		<xsl:variable name="unitCodeID" select="'e604a0fa-c14a-40ca-97ab-c92b2ce618ef'"/>
		<xsl:variable name="warrantyPeriod" select="'24'"/>
		<xsl:variable name="deliveryPeriod" select="'5'"/>
		<xsl:variable name="dimensionCode" select="'98ce94c1-bca8-4e05-a8fd-80574aecb5b0'"/>
		<xsl:variable name="volumeCode" select="'1'"/>
		<xsl:variable name="weightCode" select="'c604bc2d-dd55-413b-b21c-fbfa82b0faed'"/>
		<xsl:variable name="packageCoeficient" select="20"/>
		<xsl:variable name="typeIdentification" select="'aebf5f9b-c7d2-4153-aa49-d49e0c725d55'"/>

		<xsl:element name="Artikl">
			<xsl:element name="Katalog">
				<xsl:value-of select="$uniqCode"/>
			</xsl:element>
			<xsl:element name="PLU">
				<xsl:value-of select="GTIN"/>
			</xsl:element>
			<xsl:element name="Popis">
				<xsl:value-of select="$name"/>
			</xsl:element>
			<xsl:element name="Poznamka">
				<xsl:value-of select="Popis"/>
			</xsl:element>
			<xsl:element name="Kod">
				<xsl:value-of select="fn:concat('A05-', $uniqCode)"/>
			</xsl:element>
			<xsl:element name="VlastniHmotnost">
				<xsl:value-of select="Hmotnost"/>
			</xsl:element>
			<xsl:element name="Nazev">
				<xsl:value-of select="$name"/>
			</xsl:element>
			<xsl:if test="Parametr[Nazev='Počet balení']/Hodnota">
				<xsl:element name="Balenikoeficient_UserData">
					<xsl:value-of select="xs:double(Parametr[Nazev='Počet balení'][0]/Hodnota) * $packageCoeficient"/>
				</xsl:element>
			</xsl:if>
			<xsl:if test="Baleni">
				<xsl:element name="Baleni">
					<xsl:value-of select="Baleni"/>
				</xsl:element>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="$status = 'SKL'">
					<xsl:element name="DostupnostUDodavat_UserData">
						<xsl:value-of select="'Skladem'"/>
					</xsl:element>
				</xsl:when>
				<xsl:when test="$status = 'Nedostupné'">
					<xsl:element name="DostupnostUDodavat_UserData">
						<xsl:value-of select="'Nedostupné'"/>
					</xsl:element>
				</xsl:when>
				<xsl:otherwise>
					<xsl:element name="DostupnostUDodavat_UserData">
						<xsl:value-of select="'Čeká na naskladnění'"/>
					</xsl:element>
				</xsl:otherwise>

			</xsl:choose>
			<xsl:choose>
				<xsl:when test="matches($status, '^\d{2}\.\d{2}\.\d{4}$')">
					<xsl:variable name="oldDate" select="xs:date(replace($status,'(\d{2})\.(\d{2})\.(\d{4})', '$3-$2-$1'))"/>
					<xsl:variable name="newDate" select="$oldDate + xs:dayTimeDuration(concat('P', $deliveryPeriod, 'D'))"/>
					<xsl:variable name="deliveryDate" select="fn:format-date($newDate, '[D01].[M01].[Y]')"/>
					<xsl:variable name="pocet" select="fn:days-from-duration($newDate - fn:current-date())"/>

					<xsl:element name="PlanNaskladneni_UserData">
						<xsl:value-of select="$deliveryDate"/>
					</xsl:element>
					<xsl:element name="DodaciLhuta">
						<xsl:element name="Doba">
							<xsl:value-of select="$pocet"/>
						</xsl:element>
					</xsl:element>
					<xsl:element name="SklademIDodavatele_UserData">
						<xsl:value-of select="PriblizneMnozstviSkladem"/>
					</xsl:element>
				</xsl:when>
				<xsl:when test="$status = 'SKL'">
					<xsl:element name="PlanNaskladneni_UserData"/>
					<xsl:element name="DodaciLhuta">
						<xsl:element name="Doba">
							<xsl:value-of select="$deliveryPeriod"/>
						</xsl:element>
					</xsl:element>
					<xsl:element name="SklademIDodavatele_UserData">
						<xsl:value-of select="PriblizneMnozstviSkladem"/>
					</xsl:element>
				</xsl:when>
				<xsl:when test="$status = 'Nedostupné'">
					<xsl:element name="PlanNaskladneni_UserData"/>
					<xsl:element name="DodaciLhuta">
						<xsl:element name="Doba"/>
					</xsl:element>
					<xsl:element name="SklademIDodavatele_UserData">0</xsl:element>
				</xsl:when>
			</xsl:choose>
			<xsl:element name="Dodavatele">
				<xsl:element name="SeznamDodavatelu">
					<xsl:element name="ArtiklDodavatel">
						<xsl:element name="Firma_ID">
							<xsl:value-of select="$companyID"/>
						</xsl:element>
						<xsl:element name="Jednotka_ID">
							<xsl:value-of select="$unitCodeID"/>
						</xsl:element>
						<xsl:element name="DodavatelskeOznaceni">
							<xsl:element name="Kod">
								<xsl:value-of select="$uniqCode"/>
							</xsl:element>
							<xsl:element name="Nazev">
								<xsl:value-of select="$name"/>
							</xsl:element>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="HTMLPopisy">
				<xsl:element name="HTMLPopis1">
					<xsl:element name="Popis">
						<xsl:value-of select="$name"/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="RozsirenePopisy">
				<xsl:element name="Popis1">
					<xsl:element name="Popis">
						<xsl:value-of select="$name"/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<xsl:element name="Zaruka">
				<xsl:element name="Doba">
					<xsl:value-of select="$warrantyPeriod"/>
				</xsl:element>
			</xsl:element>
			<xsl:element name="Identifikace">
				<xsl:element name="ArtiklIdentifikace">
					<xsl:element name="Kod">
						<xsl:value-of select="GTIN"/>
					</xsl:element>
				</xsl:element>
				<xsl:element name="DruhIdentifikace">
					<xsl:attribute name="ID" select="$typeIdentification"/>
				</xsl:element>
			</xsl:element>
			<xsl:element name="Rozmery">
				<xsl:element name="SeznamRozmeru">
					<!-- Délka -->
					<xsl:element name="ArtiklRozmer">
						<xsl:element name="Hodnota">
							<xsl:value-of select="Parametr[Nazev='Hloubka (cm)']/Hodnota"/>
						</xsl:element>
						<xsl:element name="Jednotka_ID">
							<xsl:value-of select="$dimensionCode"/>
						</xsl:element>
						<xsl:element name="Velicina_ID">fbee7b95-0e62-424a-b8b1-78ffd52a5f7e</xsl:element>
					</xsl:element>
					<!-- Šířka -->
					<xsl:element name="ArtiklRozmer">
						<xsl:element name="Hodnota">
							<xsl:value-of select="Parametr[Nazev='Šířka (cm)']/Hodnota"/>
						</xsl:element>
						<xsl:element name="Jednotka_ID">
							<xsl:value-of select="$dimensionCode"/>
						</xsl:element>
						<xsl:element name="Velicina_ID">63701f8a-1914-4214-90dc-2c7bb770ec76</xsl:element>
					</xsl:element>
					<!-- Výška -->
					<xsl:element name="ArtiklRozmer">
						<xsl:element name="Hodnota">
							<xsl:value-of select="Parametr[Nazev='Výška (cm)']/Hodnota"/>
						</xsl:element>
						<xsl:element name="Jednotka_ID">
							<xsl:value-of select="$dimensionCode"/>
						</xsl:element>
						<xsl:element name="Velicina_ID">3b65c9b6-0c96-4571-ad83-cae6d3bdd570</xsl:element>
					</xsl:element>
					<!-- Hmotnost -->
					<xsl:element name="ArtiklRozmer">
						<xsl:element name="Hodnota">
							<xsl:value-of select="Hmotnost"/>
						</xsl:element>
						<xsl:element name="Jednotka_ID">
							<xsl:value-of select="$weightCode"/>
						</xsl:element>
						<xsl:element name="Velicina_ID">76abc699-1574-48ef-acca-b070b3665279</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="Zbozi" mode="Cenik">
		<xsl:variable name="pricelistMOID" select="'49a87e2d-f13b-4bd2-a615-ff1c6741f65f'"/>
		<xsl:variable name="pricelistVOID" select="'821beaf9-0ad7-44f0-8625-5c84e97f9636'"/>
		<xsl:variable name="uniqCode" select="MatID"/>

		<xsl:element name="PolozkaCeniku">
			<xsl:element name="Cena">
				<xsl:value-of select="fn:format-number(DoporucenaCenaSDph div (1 + xs:double(DPH)), '0.####')"/>
			</xsl:element>
			<xsl:element name="Cenik_ID">
				<xsl:value-of select="$pricelistMOID"/>
			</xsl:element>
			<xsl:element name="Kod">
				<xsl:value-of select="$uniqCode"/>
			</xsl:element>
		</xsl:element>
		<xsl:element name="PolozkaCeniku">
			<xsl:element name="Cena">
				<xsl:value-of select="fn:format-number(VOCenaBezDph, '0.####')"/>
			</xsl:element>
			<xsl:element name="Cenik_ID">
				<xsl:value-of select="$pricelistVOID"/>
			</xsl:element>
			<xsl:element name="Kod">
				<xsl:value-of select="$uniqCode"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
