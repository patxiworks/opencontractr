var form_schema = {
          "description": {
                    "type":"textarea"
            },
          "rationale": {
                    "type":"textarea"
          },
          "currency":{
                    "type": "select",
                    "value": {
                              "USD":"US Dollars",
                              "GBP":"British Pounds"
                              }
          },
          "procurementMethod": {
                    "type": "select",
                    "value": {
                              "open":"Open",
                              "selective":"Selective",
                              "limited":"Limited",
                              },
          },
          "scheme": [{
                    "type": "select",
                    "value": {
                              "CPV":"EC Common Procurement Vocabulary",
                              "CPVS":"EC Common Procurement Vocabulary - Supplementary Codelists",
                              "GSIN":"Goods and Services Identification Number",
                              "UNSPSC":"United Nations Standard Products and Services Code®"
                    },
                    "alias": [
                              "tender-items-classification-scheme",
                              "tender-items-additionalClassifications-scheme",
                              "awards-items-classification-scheme",
                              "awards-items-additionalClassifications-scheme",
                              "contracts-items-classification-scheme",
                              "contracts-items-additionalClassifications-scheme",
                              ]
                    },{
                    "type": "select",
                    "value": {
                              "AF-CBR":"Afghanistan Central Business Registry",
                              "AF-MOE":"Ministry of Economy",
                              "AU-ABN":"Australian Business Register",
                              "AU-ACNC":"Australian Charities and Not-for-profits Commission",
                              "BD-NAB":"Bangladesh NGO Affairs Bureau",
                              "BE-BCE_KBO":"Banque Carrefour des entreprises / Kruispuntbank van ondernemingen",
                              "CA-CRA_ACR":"Canadian Revenue Agency / Agence du revenu du Canada",
                              "ES-DIR3":"Common Directory of Organizational Units and Offices - DIR3",
                              "ET-MFA":"Ministry of Foreign Affairs",
                              "FI-PRO":"Finnish Patent and Registration office",
                              "FR-RCS":"Registre de Commerce et des Societies /Trade and Companies Register - Commercial Court Registry",
                              "GB-CHC":"Charity Commission",
                              "GB-COH":"Companies House",
                              "GB-GOV":"UK Government Departments Reference Numbers",
                              "GB-GOVUK":"UK Government Departments, Agencies & Public Bodies",
                              "GB-NIC":"The Charity Commission for Northern Ireland",
                              "GB-REV":"HM Revenue and Customs",
                              "GB-SC":"Scottish Charity Register",
                              "GB-UKPRN":"UK Register of Learning Providers",
                              "GH-DSW":"Department of Social Welfare",
                              "ID-KDN":"Ministry Home affairs/ Kementerian Dalam Negeri",
                              "ID-KHH":"Ministry of Justice & Human Rights/ Kementerian Hukum Dan Hak",
                              "ID-KLN":"Ministry of Foreign affairs/ Kementerian Luar Negeri",
                              "ID-PRO":"NGOs registered at Provinicial Level",
                              "IE-CHY":"Irish Register of Charities",
                              "IE-CRO":"Irish CompaniesRegistration Office",
                              "IM-CR":"Isle of Man Companies Registry",
                              "IM-GR":"Isle of Man Index of Registered Charities",
                              "IN-MCA":"Government of India, Minstry of Corporate Affairs",
                              "KE-NCB":"NGO’s Coordination Board",
                              "KE-RCO":"Registar of Companies",
                              "KE-RSO":"Registrar of Societies",
                              "LS-LCN":"Lesotho Council of Non Governmental Organisations",
                              "MM-MHA":"Ministry of Home Affairs - Central Committee for the Registration and Supervision of Organisations",
                              "MW-CNM":"The Council for Non Governmental Organisations in Malawi",
                              "MW-MRA":"Malawi Revenue Authority",
                              "MW-NBM":"NGO Board of Malawi",
                              "MW-RG":"Registrar General, Department of Justice",
                              "MZ-MOJ":"Mozambique Ministry of Justice",
                              "NL-KVK":"Kamer van Koophandel",
                              "NO-BRC":"Brønnøysundregistrene",
                              "NP-CRO":"Company Registrar Office",
                              "NP-SWC":"NGO registration",
                              "PK-PCP":"Pakistan Centre for Philanthropy",
                              "SE-BLV":"Bolagsverket / Swedish Companies Registration Office",
                              "SK-ZRSR":"Slovakia Ministry Of Interior Trade Register",
                              "UA-EDR":"United State Register, Ukraine",
                              "UG-NGB":"NGO Board, Ministry of Internal Affairs",
                              "UG-RSB":"Registration Services Bureau",
                              "US-DOS":"Corporation registration is the responsibility of each state (see link)",
                              "US-EIN":"Internal Revenue Service / Employer Identification Number",
                              "US-USAGOV":"Index of U.S. Government Departments and Agencies",
                              "XI-IATI":"International Aid Transparency Initiative Organisation Identifier",
                              "XM-DAC":"OECD Development Assistance Committee",
                              "XM-OCHA":"United Nations Office for the Coordination of Humanitarian Affairs",
                              "ZA-CIP":"Companies and Intellectual Property Commission (CIPC)",
                              "ZA-NPO":"Association for Non-Profit Organisations",
                              "ZA-PBO":"SA Revenue Service Tax Exemption Unit / Public Benefit Organisations",
                              "ZM-NRB":"Non Governmental Organisation Registration Board",
                              "ZM-PCR":"Patents and Companies Registration Agency",
                              "ZW-PVO":"Private Voluntary Organisations Council",
                              "ZW-ROD":"Registrar of Deeds",
                    },
                    "alias": [
                              "tender-procuringEntity-identifier-scheme",
                              "tender-procuringEntity-additionalIdentifiers-scheme",
                              "tender-tenderers-identifier-scheme",
                              "tender-tenderers-additionalIdentifiers-scheme",
                              "buyer-additionalIdentifiers-scheme",
                              "buyer-identifier-scheme",
                              "contracts-implementation-transactions-receiverOrganization-scheme",
                              "contracts-implementation-transactions-providerOrganization-scheme"
                    ]
          }],
          "awardCriteria": {
                    "type": "select",
                    "value": {
                              "lowestCost":"Lowest Cost",
                              "bestProposal":"Best Proposal",
                              "bestValueToGovernment":"Best Value to Government",
                              "singleBidOnly":"Single Bid Only",
                    },
          },
          "submissionMethod": {
                    "type": "select",
                    "value": {
                              "electronicAuction":"Electronic Auction",
                              "electronicSubmission":"Electronic Submission",
                              "written":"Written",
                              "inPerson":"In Person",
                    },
          },
          "status": [{
                    "type": "select",
                    "value": {
                              "planned":"Planned",
                              "active":"Active",
                              "cancelled":"Cancelled",
                              "unsuccessful":"Unsuccessful",
                              "complete":"Complete",
                    },
                    "alias": ["tender-status"]
          },{
                    "type": "select",
                    "value": {
                              "pending":"Pending",
                              "active":"Active",
                              "cancelled":"Cancelled",
                              "unsuccessful":"Unsuccessful",
                    },
                    "alias": ["awards-status"]
          },{
                    "type": "select",
                    "value": {
                              "pending":"Pending",
                              "active":"Active",
                              "cancelled":"Cancelled",
                              "terminated":"Terminated",
                    },
                    "alias": ["contracts-status"]
          },{
                    "type": "select",
                    "value": {
                              "met":"Met",
                              "notMet":"Not Met",
                              "partiallyMet":"Partially Met",
                    },
                    "alias": ["contracts-implementation-milestones-status"]
          }]
}