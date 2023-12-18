var records = 1;

function abbrevlookup( input ) {
	$.getJSON( 'traublookupapi.php', {
		abbrev: input
	})
	.done( function( data ) {
		if ( data ) {
			$( "#fleaname" ).html( data.abbrev + ': <span id="sciname">' + data.name + '</span> <span id="author">' + data.authority + '</span>' );
			if ( data.notes ) {
				$( "#fleaname" ).append( '<br><span class="notes">Notes: ' + data.notes + '</span>' );
			}
			if ( data.status ) {
				$( "#fleaname" ).append( '<br><span class="status">Name status: ' + data.status.toLowerCase() + '</span>' );
			}
			$( "p#fillbuttons" ).show();
		} else {
			$( "#fleaname" ).html( '' );
			$( "p#fillbuttons" ).hide();
		}
	});
}

function namelookup( input ) {
	$.getJSON( 'fleanameapi.php', {
		name: input
	})
	.done( function( data ) {
		if ( data ) {
			$( "#fleaname" ).html( '<span id="sciname">' + data.name + '</span> <span id="author">' + data.authority + '</span><br><span class="status">Name status: ' + data.status + '</span>' );
			if ( data.validName ) {
				$( "#fleaname" ).append( '<br><span class="status">Valid name: ' + data.validName + '</span>' );
			}
			$( "p#fillbuttons" ).show();
		} else {
			$( "#fleaname" ).html( '' );
			$( "p#fillbuttons" ).hide();
		}
	});
}

function closeRecord( recordNumber ) {
	$( "table#record"+recordNumber ).remove();
	$( "#fill"+recordNumber ).remove();
}

function addRecord() {
	arrayindex = records;
	records++;
	outputrecord = `<table class="record" id="record`+records+`">
	<tr>
		<td rowspan="2" class="recordlabel">`+records+`</td>
		<td>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Host</label><br/><input type="text" name="fleadata[`+arrayindex+`][host]" size="25"/></td>
					<td><label>Flea taxon (only 1)</label><br/><input type="text" name="fleadata[`+arrayindex+`][sciname]" size="35"/></td>
					<td><label>Taxon author <a href="#" onclick="dwcDoc('scientificNameAuthorship')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[`+arrayindex+`][scientificnameauthorship]" size="25"/></td>
					<td><label>Sex <a href="#" onclick="dwcDoc('sex')" class="info">&#9432;</a></label><br/><select name="fleadata[`+arrayindex+`][sex]"><option value="">&nbsp;</option><option value="male">male</option><option value="female">female</option><option value="male | female">male | female</option></select></td>
					<td><label>Quant.</label><br/><input type="text" name="fleadata[`+arrayindex+`][individualcount]" size="4"/></td>
				</tr>
			<table>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Date</label><br/><input type="date" name="fleadata[`+arrayindex+`][date]" size="10"/></td>
					<td><label>Country <a href="#" onclick="dwcDoc('country')" class="info">&#9432;</a></label><br/><input type="text" class="country" name="fleadata[`+arrayindex+`][country]" size="24"/></td>
					<td><label>State/Province <a href="#" onclick="dwcDoc('stateProvince')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[`+arrayindex+`][stateprovince]" size="24"/></td>
					<td><label>Elevation <a href="#" onclick="dwcDoc('minimumElevationInMeters')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[`+arrayindex+`][elevation]" size="8"/></td>
					<td><label>Associated Collectors</label><br/><input type="text" name="fleadata[`+arrayindex+`][associatedcollectors]" size="30"/></td>
				</tr>
			</table>
			<table class="output" border="0" cellspacing="0">
				<tr>
					<td><label>Locality <a href="#" onclick="dwcDoc('locality')" class="info">&#9432;</a></label><br/><input type="text" name="fleadata[`+arrayindex+`][locality]" size="116"/></td>
				</tr>
			</table>
		</td>
		<td rowspan="3" class="recordclose"><a class="close" onclick="closeRecord(`+records+`);return false;">×</a></td>
	</tr>
</table>`;
	$( "#records" ).append( outputrecord );
	$( "input[name='fleadata["+arrayindex+"][host]']" ).val( $( "input[name='fleadata[0][host]']" ).val() );
	$( "input[name='fleadata["+arrayindex+"][date]']" ).val( $( "input[name='fleadata[0][date]']" ).val() );
	$( "input[name='fleadata["+arrayindex+"][country]']" ).val( $( "input[name='fleadata[0][country]']" ).val() );
	$( "input[name='fleadata["+arrayindex+"][stateprovince]']" ).val( $( "input[name='fleadata[0][stateprovince]']" ).val() );
	$( "input[name='fleadata["+arrayindex+"][elevation]']" ).val( $( "input[name='fleadata[0][elevation]']" ).val() );
	$( "input[name='fleadata["+arrayindex+"][associatedcollectors]']" ).val( $( "input[name='fleadata[0][associatedcollectors]']" ).val() );
	$( "input[name='fleadata["+arrayindex+"][locality]']" ).val( $( "input[name='fleadata[0][locality]']" ).val() );
	$( "p#fillbuttons" ).append( '<input type="submit" value="Fill '+records+'" onclick="filldata('+arrayindex+');return false;" class="fill" id="fill'+records+'"/>' );
}

function filldata( arrayindex ) {
	console.log(arrayindex);
	$( "input[name='fleadata["+arrayindex+"][sciname]']" ).val( $( "#sciname" ).text() );
	$( "input[name='fleadata["+arrayindex+"][scientificnameauthorship]']" ).val( $( "#author" ).text() );
}

function dwcDoc( dcTag ) {
	//dwcWindow=open("https://symbiota.org/symbiota-occurrence-data-fields-2/#"+dcTag,"dwcaid","width=1250,height=300,left=20,top=20,scrollbars=1");
	dwcWindow=open("https://dwc.tdwg.org/terms/#dwc:"+dcTag,"dwcaid","width=1250,height=300,left=20,top=20,scrollbars=1");
	if(dwcWindow.opener == null) dwcWindow.opener = self;
	dwcWindow.focus();
	return false;
}

$( "input.country" ).autocomplete({
source: [
"Afghanistan",
"Albania",
"Algeria",
"Andorra",
"Angola",
"Antigua and Barbuda",
"Argentina",
"Armenia",
"Australia",
"Austria",
"Azerbaijan",
"Bahamas",
"Bahrain",
"Bangladesh",
"Barbados",
"Belarus",
"Belgium",
"Belize",
"Benin",
"Bhutan",
"Bolivia",
"Bosnia and Herzegovina",
"Botswana",
"Brazil",
"Brunei",
"Bulgaria",
"Burkina Faso",
"Burundi",
"Cabo Verde",
"Cambodia",
"Cameroon",
"Canada",
"Central African Republic",
"Chad",
"Chile",
"China",
"Colombia",
"Comoros",
"Congo, Democratic Republic of the",
"Congo, Republic of the",
"Costa Rica",
"Cote d’Ivoire",
"Croatia",
"Cuba",
"Cyprus",
"Czech Republic",
"Denmark",
"Djibouti",
"Dominica",
"Dominican Republic",
"Ecuador",
"Egypt",
"El Salvador",
"Equatorial Guinea",
"Eritrea",
"Estonia",
"Eswatini",
"Ethiopia",
"Fiji",
"Finland",
"France",
"Gabon",
"Gambia",
"Georgia",
"Germany",
"Ghana",
"Greece",
"Grenada",
"Guatemala",
"Guinea",
"Guinea-Bissau",
"Guyana",
"Haiti",
"Honduras",
"Hungary",
"Iceland",
"India",
"Indonesia",
"Iran",
"Iraq",
"Ireland",
"Israel",
"Italy",
"Jamaica",
"Japan",
"Jordan",
"Kazakhstan",
"Kenya",
"Kiribati",
"Korea, Democratic People's Republic of",
"Korea, Republic of",
"Kosovo",
"Kuwait",
"Kyrgyzstan",
"Laos",
"Latvia",
"Lebanon",
"Lesotho",
"Liberia",
"Libya",
"Liechtenstein",
"Lithuania",
"Luxembourg",
"Madagascar",
"Malawi",
"Malaysia",
"Maldives",
"Mali",
"Malta",
"Marshall Islands",
"Mauritania",
"Mauritius",
"Mexico",
"Micronesia, Federated States of",
"Moldova",
"Monaco",
"Mongolia",
"Montenegro",
"Morocco",
"Mozambique",
"Myanmar",
"Namibia",
"Nauru",
"Nepal",
"Netherlands",
"New Zealand",
"Nicaragua",
"Niger",
"Nigeria",
"North Macedonia",
"Norway",
"Oman",
"Pakistan",
"Palau",
"Palestinian Territory",
"Panama",
"Papua New Guinea",
"Paraguay",
"Peru",
"Philippines",
"Poland",
"Portugal",
"Qatar",
"Romania",
"Russia",
"Rwanda",
"Saint Kitts and Nevis",
"Saint Lucia",
"Saint Vincent and the Grenadines",
"Samoa",
"San Marino",
"Sao Tome and Principe",
"Saudi Arabia",
"Senegal",
"Serbia",
"Seychelles",
"Sierra Leone",
"Singapore",
"Slovakia",
"Slovenia",
"Solomon Islands",
"Somalia",
"South Africa",
"South Sudan",
"Spain",
"Sri Lanka",
"Sudan",
"Suriname",
"Sweden",
"Switzerland",
"Syria",
"Taiwan",
"Tajikistan",
"Tanzania",
"Thailand",
"Timor-Leste",
"Togo",
"Tonga",
"Trinidad and Tobago",
"Tunisia",
"Turkey",
"Turkmenistan",
"Tuvalu",
"Uganda",
"Ukraine",
"United Arab Emirates",
"United Kingdom",
"United States",
"Uruguay",
"Uzbekistan",
"Vanuatu",
"Vatican City",
"Venezuela",
"Vietnam",
"Yemen",
"Zambia",
"Zimbabwe"
]
});

$( "input#abbrev" ).autocomplete({
source: [
"A.",
"ACM.",
"ACM.H.",
"ACN.",
"ACN.A.",
"ACN.B.",
"ACN.B.B.",
"ACN.BI.",
"ACN.DU.",
"ACN.E.",
"ACN.EU.",
"ACN.I.",
"ACN.P.",
"ACN.R.",
"ACN.RO.",
"ACN.RO.RO.",
"ACN.S.T.",
"ACR.",
"ACR.G.",
"ACR.T.",
"ACTN.S.",
"AD.",
"AD.A.",
"AD.B.",
"AEN.",
"AEN.G.",
"AET.",
"AET.TH.",
"AET.W.",
"AFSV.",
"AFSV.AZ.",
"AFSV.NIG.",
"AFSV.P.A.",
"AFSV.S.",
"AFSV.T.",
"AFSV.V.",
"AG.",
"AG.N.",
"AM.",
"AM.A.",
"AM.A.M.",
"AM.AR.",
"AM.CO.",
"AM.J.",
"AM.LO.",
"AM.M.",
"AM.M.P.",
"AM.MA.E.",
"AM.MA.MA.",
"AM.MT.",
"AM.P.",
"AM.PH.LIM.",
"AM.QD.",
"AM.QI.",
"AM.QT.",
"AM.R.",
"AM.S.",
"AM.SH.",
"AM.SIB.SIB.",
"AMA.",
"AMA.AR.",
"AMA.D.",
"AMA.P.K.",
"AMA.P.P.",
"AMB.",
"AMB.D.",
"AMB.D.D.",
"AMB.R.",
"AMD.",
"AML.",
"AML.C.",
"AML.N.",
"AMA.A.A.",
"AN.",
"AN.A.",
"AN.M.",
"AN.N.",
"AN.NID.",
"AN.NU.",
"APH.",
"APH.W.",
"AR.E.",
"ARA.",
"ARA.E.",
"ARA.G.",
"ARA.M.",
"ARA.S.",
"ARA.W.",
"AST.",
"AT.",
"AT.B.",
"AT.E.",
"AT.M.",
"AT.T.",
"AUS.",
"AUS.B.",
"AV.SV.",
"AVSV.",
"AVSV.K.",
"B.",
"B.A.",
"B.S.",
"BIB.",
"BR.E.",
"BRV.",
"C.",
"C.A.",
"C.C.",
"C.CO.",
"C.CR.",
"C.F.",
"C.F.D.",
"C.F.F.",
"C.F.O.",
"C.F.S.",
"C.F.X.",
"C.R.",
"CA.",
"CA.D.",
"CA.T.",
"CAE.",
"CAE.L.",
"CAE.M.",
"CAL.",
"CAL.(CAL.)",
"CAL.(ORN.)",
"CAL.(PCA.)",
"CAL.(TY.)",
"CAL.D.",
"CAL.F.",
"CAL.L.",
"CAL.S.",
"CAL.T.",
"CAL.W.",
"CAR.C.",
"CAR.CL.",
"CD.",
"CD.I.I.",
"CD.I.INT.",
"CD.S.",
"CD.SP.",
"CE.",
"CE.(AMN.)",
"CE.(CE.)",
"CE.(CEL.)",
"CE.(EM.)",
"CE.(MO.)",
"CE.(RCS.)",
"CE.B.",
"CE.C.",
"CE.F.",
"CE.F.C.",
"CE.F.F.",
"CE.FR.",
"CE.G.",
"CE.GAR.",
"CE.H.",
"CE.HA.",
"CE.M.",
"CE.OL.",
"CE.P.",
"CE.PET.",
"CE.R.",
"CE.RI.",
"CE.S.",
"CE.V.I.",
"CG.",
"CG.C.",
"CG.DC.",
"CG.DK.DK.",
"CG.DK.F.",
"CG.I.",
"CG.M.",
"CG.S.",
"CG.ST.",
"CH.",
"CH.F.",
"CH.G.",
"CH.H.",
"CH.H.H.",
"CH.K.",
"CH.L.",
"CH.M.",
"CH.S.",
"CH.T.",
"CH.TB.TB.",
"CH.U.",
"CHI.",
"CHI.N.",
"CHI.N.R.",
"CHI.NA.",
"CHI.P.",
"CHI.R.",
"CHO.",
"CHO.L.",
"CHO.O.",
"CHO.T.",
"CHR.AE.",
"CHR.B.",
"CI.",
"CI.D.",
"CI.J.",
"CI.PR.",
"CI.R.S.",
"CI.T.S.",
"CL.T.",
"CN.",
"CN.N.",
"CN.S.",
"CND.",
"CND.R.",
"CND.S.",
"CND.T.",
"CNP.",
"CNT.",
"CNT.M.",
"CO.",
"CO.B.",
"CO.BI.",
"CO.C.",
"CO.C.C.",
"CO.C.L.",
"CO.C.O.",
"CO.H.",
"CONOTH.OR.",
"COOR.",
"COP.A.",
"COP.B.",
"COP.I.",
"COP.J.",
"COP.L.",
"COP.ME.",
"COP.MO.",
"COP.N.",
"COP.T.",
"COP.W.",
"CORY.K.",
"CORY.O.",
"CPH.",
"CPH.(G.)",
"CPH.A.",
"CPH.A.T.",
"CPH.R.",
"CPH.S.",
"CPH.SUB.",
"CPH.T.",
"CPH.A.A.",
"CR.",
"CR.M.M.",
"CR.M.W.",
"CRAT.",
"CRAT.A.",
"CRAT.B.",
"CRAT.C.",
"CRN.J.",
"CRO.A.",
"CRO.S.",
"CRY.",
"CRY.I.",
"CT.",
"CT.(ETH.)",
"CT.A.",
"CT.AG.",
"CT.AG.AG.",
"CT.AG.P.",
"CT.AN.AN.",
"CT.AR.",
"CT.AS.",
"CT.AS.AS.",
"CT.AV.",
"CT.B.",
"CT.CA.",
"CT.CA.CA.",
"CT.CB.",
"CT.CBL.",
"CT.CN.",
"CT.CN.CN",
"CT.CN.CR.",
"CT.DB.",
"CT.DO.I.",
"CT.DV.",
"CT.ED.",
"CT.EU.",
"CT.EVID.",
"CT.EX.",
"CT.EXP.",
"CT.F.",
"CT.FR.",
"CT.G.",
"CT.H.",
"CT.I.I.",
"CT.I.P.",
"CT.LU.",
"CT.M.M.",
"CT.MACH.",
"CT.MI.",
"CT.O.D.",
"CT.OB.",
"CT.P.",
"CT.PA.",
"CT.PH.",
"CT.PS.",
"CT.PS.MI.",
"CT.PS.PS.",
"CT.R.",
"CT.S.",
"CT.T.",
"CT.TH.",
"CT.TU.",
"CT.U.U.",
"CT.VE.",
"CT.CA.CB.",
"D.",
"D.M.",
"DA.(S.)",
"DA.B.",
"DA.M.",
"DA.NM.",
"DA.P.",
"DA.R.",
"DE.",
"DEM.G.",
"DI.",
"DI.A.",
"DI.AB.",
"DI.B.",
"DI.D.",
"DI.ECH.",
"DI.EL.",
"DI.EL.EL.",
"DI.EL.LY.",
"DI.G.",
"DI.I.",
"DI.K.",
"DI.LO.",
"DI.LY.",
"DI.M",
"DI.S.",
"DI.T.",
"DI.TR.",
"DI.W.",
"DL.S.",
"DO.",
"DO.B.",
"DO.C.",
"DO.D.",
"DO.W.",
"DORC.I.",
"DS.",
"DS.(AV.)",
"DS.(DS.)",
"DS.(NRN.)",
"DS.CO.",
"DS.G.",
"DS.G.G.",
"DS.K.",
"DS.L.L.",
"DS.S.",
"DSV.",
"DSV.M.",
"DY.",
"E.",
"E.A.A.",
"E.A.I.",
"E.AE.",
"E.B.",
"E.G.",
"E.I.",
"E.L.",
"E.LI.",
"E.M.",
"E.MU.",
"E.O.",
"EC.",
"EC.(EC.)",
"EC.I.",
"EP.",
"EP.C.",
"EP.F.",
"EP.N.",
"EP.S.",
"EP.W.",
"EP.W.W.",
"EPR.A",
"ER.C.",
"EU.",
"F.",
"F.(AF.)",
"F.H.",
"F.I.",
"F.I.I.",
"F.M.",
"F.MX.",
"FAR.",
"FR.",
"FR.(FR.)",
"FR.(ORF.)",
"FR.(P.)",
"FR.(PR.)",
"FR.A.",
"FR.E.B.",
"FR.E.E.",
"FR.H.",
"FR.L.",
"FR.LU.LU.",
"FR.M.",
"FR.N.",
"FR.N.T.",
"FR.S.",
"FR.SPX.",
"FR.V.",
"FR.W.",
"GB.",
"GB.ALT.",
"GB.BAL.",
"GB.H.",
"GB.H.H.",
"GB.IR.A.",
"GB.IR.IR.",
"GB.LV.",
"GB.LV.G.",
"GB.PR.",
"GB.TU.",
"GB.TU.AF.",
"GB.TU.TU.",
"GENE.",
"GH.S.",
"GL.",
"GR.",
"GR.H.",
"GRES.A.",
"GSB.",
"GSB.A.",
"H.",
"H.AN.",
"H.G.",
"H.G.AF.",
"H.G.F.",
"H.M.",
"H.P.",
"H.SUA.",
"H.TA.",
"HB.",
"HE.B.",
"HE.PS.",
"HG.",
"HG.N.",
"HG.T.",
"HG.V.",
"HK.O.",
"HK.O.O.",
"HK.O.P.",
"HL.",
"HL.N.",
"HO.",
"HO.F.",
"HY.",
"HY.D.",
"HY.D.D.",
"HY.G.",
"HY.G.G.",
"HY.L.",
"HY.M.",
"HY.MUL.",
"HY.O.",
"HY.OR.",
"HY.ORI.ORI.",
"HY.S.",
"HY.T.",
"HY.T.T.",
"HY.T.O.",
"HYD.T.",
"HYP.",
"HYP.C.",
"HYP.T.",
"I.",
"I.(H.)",
"I.(I.)",
"I.C.",
"I.E.",
"I.H.",
"I.I.",
"I.IN.",
"I.O.",
"I.S.S.",
"ID.",
"ID.I.",
"IG.",
"J.E.",
"J.P.",
"JL.",
"JL.B.",
"JL.BU.",
"JL.G.",
"JL.H.",
"JL.H.B.",
"JL.H.H.",
"JL.I.",
"JL.J.",
"JL.K.",
"JL.K.K.",
"JL.W.",
"JO.",
"JO.A.",
"K.",
"K.B.",
"K.C.",
"K.DELT.",
"K.E.",
"K.ER.",
"K.F.",
"K.G.",
"K.G.R.",
"K.GR.GR.",
"K.K.",
"K.L.",
"K.O.",
"K.P.",
"K.T.",
"K.U.",
"K.W.",
"LA.",
"LA.AN.",
"LA.C.",
"LA.ID.",
"LA.IN.",
"LA.M.",
"LA.MI.",
"LA.S.",
"LB.",
"LB.H.",
"LB.I.",
"LB.I.I.",
"LB.S.",
"LE.",
"LE.AE.",
"LE.AE.AE.",
"LE.AL.",
"LE.AL.AL.",
"LE.N.",
"LE.P.",
"LE.PA.",
"LE.PCP.",
"LE.S.",
"LE.SB.",
"LE.SC.",
"LE.SPB.",
"LE.SX.",
"LE.T.",
"LE.T.CA.",
"LE.T.T.",
"LENT.",
"LI.",
"LI.A.",
"LI.AR.",
"LI.B.",
"LI.D.",
"LIP.",
"LISN.",
"LSV.",
"LSV.(DSV.)",
"LSV.(DSV.)M.",
"LSV.ALN.",
"LSV.ASV.",
"LSV.FE.",
"LSV.I.",
"LSV.VO.",
"LYC.N.",
"MA.",
"MA.E.",
"MA.P.",
"MA.P.A.",
"MA.P.D.",
"MA.P.P.",
"MA.P.S.",
"MA.S.",
"MA.T.",
"MA.VF.",
"MAC.H.",
"MAR.",
"MB.M.",
"MB.N.",
"MC.",
"MC.B.",
"MC.D.",
"MC.E.",
"MC.H.M.",
"MC.H.N.",
"MC.K.K.",
"MC.K.P.",
"MC.L.",
"MC.LI.",
"MC.NK.",
"MC.NO.",
"MC.PIL.",
"MC.PRO.",
"MED.",
"MED.B.",
"MED.C.",
"MED.D.",
"MED.E.",
"MED.G.",
"MED.H.",
"MED.JAV.",
"MED.JM.",
"MED.L.",
"MED.LM.",
"MED.LU.",
"MED.N.",
"MED.O.",
"MED.P.",
"MED.P.P.",
"MED.P.T.",
"MED.Q.",
"MED.R.",
"MED.R.B.",
"MED.R.P.",
"MED.R.R.",
"MED.R.T.",
"MED.RA.",
"MED.TH.",
"MED.TIP.",
"MED.VE.",
"MES.",
"MES.A.",
"MES.EU.",
"MES.EU.A.",
"MES.EU.EU.",
"MES.H.",
"MES.T.",
"MES.T.P.",
"MG.",
"MG.(AMG.)",
"MG.(GEB.)",
"MG.(KU.)",
"MG.A.",
"MG.AC.",
"MG.AD.B.",
"MG.AS.AS.",
"MG.AS.M.",
"MG.CA.GR.",
"MG.CL.",
"MG.G.",
"MG.Q.",
"MG.R.",
"MG.T.",
"MG.W.",
"MGR.",
"MGR.B.",
"MGSV.",
"MGSV.J",
"MGSV.J.J.",
"MGSV.J.P.",
"MGSV.SM.",
"MI.S.G.",
"MI.S.S.",
"MIO.A.A.",
"MIO.A.H.",
"MIO.T.K.",
"MIO.T.T.",
"ML.G.",
"MO.",
"MO.AN.",
"MO.AR.",
"MO.C.P.",
"MO.CY.",
"MO.EU.",
"MO.EU.A.",
"MO.EU.EU.",
"MO.EX.",
"MO.EX.EX.",
"MO.F.",
"MO.H.",
"MO.I.",
"MO.S.",
"MO.T.",
"MO.TH.",
"MO.V.",
"MO.W.",
"MOEO.SJ.",
"MR.",
"MR.A.",
"MR.AL.",
"MR.C.",
"MR.D.",
"MR.DE.",
"MR.H.",
"MR.P.",
"MR.R.",
"MR.S.",
"MSC.",
"MSC.A.",
"MSV.",
"MSV.AB.",
"MSV.AN.",
"MSV.AN.AN.",
"MSV.HU.",
"MSV.LA.",
"MSV.MO.",
"MSV.MO.MO.",
"MSV.MOL.",
"MSV.RE.",
"MSV.RO.",
"MSV.SH.",
"MSV.T.",
"MX.",
"MX.(MIR.)",
"MX.J.",
"MX.L.L.",
"MY.",
"MY.C.",
"MY.I.",
"MY.N.",
"MY.W.",
"N.",
"N.(G.)",
"N.(G.)H.",
"N.(GB.)",
"N.(N.)",
"N.(NS.)",
"N.(P.)G.",
"N.(PN.)",
"N.ALT.",
"N.BAL.",
"N.BAR.",
"N.C.",
"N.F.",
"N.FA.",
"N.H.",
"N.H.H.",
"N.I.",
"N.IR.A.",
"N.IR.IR.",
"N.L.D.",
"N.L.L.",
"N.LV.",
"N.LV.G.",
"N.MIK.",
"N.N.",
"N.P.",
"N.PH.R.",
"N.PR.",
"N.S.",
"N.SA.A.",
"N.SA.P.",
"N.T.",
"N.TU.",
"N.TU.AF.",
"N.TU.AL.",
"N.TU.TU.",
"N.V.",
"NC.",
"NC.BD.",
"NC.BR.",
"NC.G.G.",
"NC.G.H.",
"NC.HM.",
"NC.P.",
"NE.",
"NE.A.",
"NE.AC.",
"NE.AN.",
"NE.ANG.",
"NE.B.",
"NE.D.",
"NE.H.",
"NE.J.",
"NE.L.",
"NE.M.",
"NE.MER.",
"NE.P.",
"NE.P.O.",
"NE.PA.",
"NE.S.",
"NE.SE.",
"NE.SO.",
"NE.ST.",
"NE.T.",
"NO.",
"NO.R.W.",
"NSV.",
"NSV.PO.",
"NT.",
"NT.C.C.",
"NT.R.",
"NTU.I.",
"NYC.",
"NYC.C.",
"NYC.V.",
"O.",
"O.B.",
"O.C.C.",
"O.C.D.",
"O.D.",
"O.F.",
"O.FI.",
"O.H.",
"O.H.H.",
"O.H.T.",
"O.L.",
"O.M.",
"O.N.",
"O.R.",
"O.S.",
"O.S.P.",
"O.S.SCH.",
"OBT.",
"OBT.SIM.",
"OC.",
"OC.H.",
"OC.L.",
"OC.T.C.",
"OC.T.T.",
"OCHOT.",
"OCHOT.R.",
"OD.D.",
"OD.M.",
"OP.",
"OP.(OXL.)",
"OP.(SC.)",
"OP.H.",
"OP.K.",
"OP.N.",
"OP.PS.",
"OP.R.",
"OP.R.M.",
"OP.R.R.",
"OP.V.",
"OPH.",
"OPH.ER.C.",
"OPH.KI.",
"OPH.P.",
"OPH.V.A.",
"OPH.V.I.",
"OR.",
"OR.(D.)",
"OR.(HU.)",
"OR.(OC.)",
"OR.(TH.)",
"OR.A.",
"OR.AL.",
"OR.AS.",
"OR.I.",
"OR.S.",
"ORF.",
"ORN.",
"ORNITH.A.",
"ORTH.",
"ORTH.AB.",
"ORTH.OR.",
"ORTH.OR.OR.",
"OX.I.",
"OC.B.",
"P.",
"P.I.",
"P.P.",
"P.S.",
"P.SIM.",
"PA.R.R.",
"PA.R.W.",
"PA.S.",
"PAC.",
"PAC.A.",
"PAC.G.",
"PAC.K.",
"PAC.PA.",
"PAC.PE.",
"PAC.R.",
"PAC.VA.",
"PAC.VI.",
"PAL.",
"PAL.K.",
"PAL.L.",
"PAL.M.",
"PAL.R.",
"PAL.R.N.",
"PAL.REC.",
"PAL.S.",
"PAL.S.R.",
"PAL.S.S.",
"PAL.S.T.",
"PAL.SE.",
"PAL.SI.",
"PAP.",
"PAP.AL.",
"PAP.BA.",
"PAP.COR.",
"PAP.ES.",
"PAP.LUL.",
"PAP.MI.",
"PAP.PA.",
"PARAP.AUS.",
"PARAP.J.",
"PARAP.L.",
"PARAP.LYN.",
"PARAP.N.",
"PARAP.T.",
"PCS.",
"PCT.",
"PCT.P.",
"PCT.PCP.",
"PDX.",
"PDX.A.",
"PDX.CO.",
"PDX.CUR.",
"PDX.CUS.",
"PDX.DIV.",
"PDX.INTG",
"PDX.INTM.",
"PDX.K.",
"PDX.L.",
"PDX.M.",
"PDX.MAG.",
"PDX.N.",
"PDX.PH.",
"PDX.R.",
"PDX.SC.",
"PDX.SO.",
"PDX.SP.",
"PDX.ST.",
"PDX.T.",
"PE.",
"PE.B.",
"PE.C.",
"PE.D.",
"PE.E.",
"PE.F.",
"PE.H.",
"PE.H.A.",
"PE.H.H.",
"PE.H.Z.",
"PE.HI.",
"PE.HM.C.",
"PE.HM.HM.",
"PE.HM.M.",
"PE.HM.V.",
"PE.O.L.",
"PE.O.O.",
"PE.SC.",
"PE.SE.",
"PE.SI.",
"PE.SI.SI.",
"PE.SI.SP.",
"PE.TK.",
"PG.",
"PG.A.",
"PG.AD.",
"PG.AT.",
"PG.BO.",
"PG.BO.BO.",
"PG.BY.",
"PG.D.",
"PG.DU.",
"PG.G.",
"PG.K.",
"PG.K.S.",
"PG.M.",
"PG.O.O.",
"PG.O.S.",
"PG.OD.",
"PG.P.",
"PG.PL.",
"PG.PR.",
"PG.R.",
"PG.R.R.",
"PG.R.S.",
"PG.RI.",
"PG.S.",
"PG.T.",
"PG.V.",
"PG.VU.",
"PG.W.",
"PH.A.",
"PHN.K.",
"PHN.T.",
"PL.",
"PL.A.",
"PL.AL.",
"PL.AZ.",
"PL.D.",
"PL.E.",
"PL.E.E.",
"PL.EU.",
"PL.L.",
"PL.M.",
"PL.MA.",
"PL.O.",
"PL.P.",
"PL.PM.",
"PL.PO.",
"PL.Q.",
"PL.Q.T.",
"PL.S.J.",
"PL.S.S.",
"PL.SCH.",
"PL.SO.",
"PL.T.",
"PLO.",
"PLO.E.",
"PLO.P.",
"PLO.PA.",
"PLO.PT.",
"PLO.S.",
"PLU.",
"PM.",
"PM.S.",
"PO.",
"PO.C.",
"PP.C.",
"PP.E.",
"PR.",
"PR.A.",
"PR.C.",
"PR.D.",
"PR.I.",
"PRAO.P.",
"PRS.",
"PRS.F.",
"PRS.H.",
"PRS.J.",
"PRS.M.",
"PRS.M.M.",
"PRS.M.S.",
"PRS.P.",
"PRS.S.",
"PSV.",
"PSV.BR.",
"PSV.GRA.",
"PSV.GRE.",
"PSV.NO.",
"PT.D.",
"PTH.AG.",
"PTY.O.",
"PY.",
"PY.A.",
"PY.ARC.",
"PY.C.",
"PY.COL.",
"PY.G.",
"PY.H.",
"PY.I.",
"PY.L.B.",
"PY.L.L.",
"PY.PH.",
"PY.R.",
"PY.S.",
"PY.SIN.",
"PY.SMIT.",
"PY.SP.",
"PY.T.",
"PY.TR.",
"PY.TUN.",
"PY.Z.",
"R.",
"R.A.",
"R.A.A.",
"R.A.T.",
"R.A.TPQ.",
"R.C.",
"R.C.S.",
"R.L.L.",
"R.LU.",
"R.LU.C.",
"R.LU.LU.",
"R.S.",
"RCD.",
"RCD.ANC.",
"RCD.SP.",
"RCD.SPOON.",
"RCD.SZ.",
"RCD.TR.",
"RCD.TR.A.",
"RCD.TR.B.",
"RCD.TR.O.",
"RCD.TR.TR.",
"RD.",
"RD.(A.)",
"RD.(RD.)",
"RD.F.",
"RD.H.",
"RD.I.",
"RD.INT.",
"RD.IOFFI",
"RD.LI",
"RD.LI LI",
"RD.M.",
"RD.MX.",
"RD.P.",
"RD.S.",
"RD.UC.",
"RHI.C.",
"RHI.U.U.",
"RHT.",
"RO.",
"RO.K.",
"RO.S.",
"ROS.",
"ROTP.",
"ROTP.N.",
"RW.",
"RY.M.",
"RY.P.",
"SI.",
"SI.A.",
"SI.C.",
"SI.T.",
"SI.W.",
"SL.C.",
"SMIT.T.",
"SMP.M.",
"SN.A.",
"SN.M.",
"SNG.",
"SNG.C.",
"SNG.C.C.",
"SNG.G.",
"SNP.",
"SNP.E.",
"SNP.F.",
"SP.",
"SP.A.",
"SP.P.",
"SP.POLY.",
"SP.S.",
"SP.ST.",
"SP.T.",
"SP.T.A.",
"SP.T.B.",
"SP.T.M.",
"SP.T.S.",
"SPC.",
"SPH.",
"SPH.A.",
"SPH.I.",
"SPH.T.",
"SPIL.CU.",
"SRN.D.D.",
"SRN.D.S.",
"SRN.D.T.",
"STC.",
"STC.C.",
"STC.D.",
"STC.DO.",
"STC.G.G.",
"STC.G.T.",
"STC.H.",
"STC.P.",
"STC.S.",
"STN.",
"STN.F.",
"STN.M.",
"STR.",
"STR.D.",
"STR.F.",
"STR.M.",
"STR.MA.",
"STR.MR.",
"STR.SCH.",
"STR.T.",
"STR.V.",
"STR.VU.",
"STR.VU.O.",
"STR.VU.VU.",
"STRIO.",
"STRIO.RU.",
"STRIO.VD.",
"SV.",
"SV.A.",
"SV.AB.",
"SV.AL.",
"SV.ALN.",
"SV.AN.",
"SV.AP.",
"SV.AP.AP.",
"SV.AP.R.",
"SV.BR.",
"SV.C.",
"SV.C.B.",
"SV.C.C.",
"SV.C.S.",
"SV.CA.",
"SV.CA.M.",
"SV.COR.",
"SV.D.",
"SV.E.",
"SV.EX",
"SV.F.",
"SV.FE.",
"SV.G.",
"SV.GRA.",
"SV.GRE.",
"SV.H.",
"SV.I.",
"SV.J.",
"SV.J.J.",
"SV.J.P.",
"SV.JAV.",
"SV.JM.",
"SV.K.",
"SV.K.K.",
"SV.L.",
"SV.LM.",
"SV.LU.",
"SV.LUL.",
"SV.M.",
"SV.MO.",
"SV.N.",
"SV.NIG.",
"SV.NO.",
"SV.O.",
"SV.OR.",
"SV.P.P.",
"SV.P.T.",
"SV.PHO.",
"SV.PO.",
"SV.Q.",
"SV.R.",
"SV.R.P.",
"SV.RA.",
"SV.RAHM.",
"SV.RE.",
"SV.ROTH.",
"SV.RU.",
"SV.S.",
"SV.SH.",
"SV.SIM.",
"SV.SM.",
"SV.SZ.",
"SV.T.",
"SV.TA.",
"Sv.TH.",
"SV.TR.",
"SV.VD.",
"SV.VE.",
"SV.VO.",
"SY.C.",
"SY.CA.",
"SY.P.",
"SY.SO.",
"T.G.",
"TAR.O.O.",
"TAY.",
"TB.",
"TB.E.",
"TB.PER.",
"TBL.",
"TE.",
"TE.B.",
"TH.",
"TH.A.A.",
"TH.A.H.",
"TH.AC.",
"TH.AZ.",
"TH.B.B.",
"TH.B.CA.",
"TH.B.CO.",
"TH.B.G.",
"TH.B.J.",
"TH.B.P.",
"TH.B.S.",
"TH.F.",
"TH.FR.",
"TH.G.",
"TH.G.C.",
"TH.H.H.",
"TH.H.U.",
"TH.P.",
"TH.PN.",
"TH.S.",
"TH.ST.",
"TI.",
"TI.C.",
"TM.B.",
"TM.B.B.",
"TM.B.O.",
"TM.D.",
"TM.L.",
"TR.I.",
"TR.I.C.",
"TR.I.I.",
"TRD.",
"TRD.O.",
"TU.",
"TU.H.",
"TU.M.",
"TU.P.",
"TYPH.P.",
"U.T.",
"V.A.",
"W.O.",
"W.Y.",
"WA.S.",
"X.",
"X.A.",
"X.AE.",
"X.AUS.",
"X.B.",
"X.BA.",
"X.BE.",
"X.BU.",
"X.C.",
"X.CO.",
"X.CO.CO.",
"X.CO.M.",
"X.COR.",
"X.CR.",
"X.CRY.",
"X.D.",
"X.DE.",
"X.DI.",
"X.ER.",
"X.ERL.",
"X.F.",
"X.G.",
"X.GEL.",
"X.H.",
"X.HA.",
"X.HP.",
"X.HS.",
"X.HU.",
"X.L.",
"X.M.",
"X.N.",
"X.NE.",
"X.NI.",
"X.NUT.",
"X.P.",
"X.PA.",
"X.PE.",
"X.PH.",
"X.R.",
"X.RO.",
"X.S.",
"X.SA.",
"X.SA.M.",
"X.SA.SE.",
"X.SK.",
"X.T.",
"X.TA.",
"X.TR.",
"X.V.",
"X.V.HA.",
"X.V.M.",
"X.V.V.",
"X.VR.",
"XD.T.",
"XI.",
"XI.F.",
"XI.HI.",
"XI.L.",
"XI.LE."
]
});
// Overrides the default autocomplete filter function to search only from the beginning of the string
$.ui.autocomplete.filter = function (array, term) {
    var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex(term), "i");
    return $.grep(array, function (value) {
        return matcher.test(value.label || value.value || value);
    });
};
