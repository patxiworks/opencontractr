var ocds = {
    "ocid": "string",
    "id": "string",
    "date": "string",
    "tag": "array",
    "initiationType": "tender",
    "planning": {
        "budget": {
            "source": "string",
            "id": "string",
            "description": "string",
            "amount": {
                "amount": "number",
                "currency": "string"
            },
            "project": "string",
            "projectID": "string",
            "uri": "string"
        },
        "rationale": "string",
        "documents": [
            {
                "id": "string",
                "documentType": "string",
                "title": "string",
                "description": "string",
                "url": "string",
                "datePublished": "string",
                "dateModified": "string",
                "format": "string",
                "language": "string"
            }
        ]
    },
    "tender": {
        "id": "string",
        "title": "string",
        "description": "string",
        "status": "planned",
        "items": [
            {
                "id": "string",
                "description": "string",
                "classification": {
                    "scheme": "string",
                    "id": "string",
                    "description": "string",
                    "uri": "string"
                },
                "additionalClassifications": [
                    {
                        "scheme": "string",
                        "id": "string",
                        "description": "string",
                        "uri": "string"
                    }
                ],
                "quantity": "integer",
                "unit": {
                    "name": "string",
                    "value": {
                        "amount": "number",
                        "currency": "string"
                    }
                }
            }
        ],
        "minValue": {
            "amount": "number",
            "currency": "string"
        },
        "value": {
            "amount": "number",
            "currency": "string"
        },
        "procurementMethod": "open",
        "procurementMethodRationale": "string",
        "awardCriteria": "string",
        "awardCriteriaDetails": "string",
        "submissionMethod": "array",
        "submissionMethodDetails": "string",
        "tenderPeriod": {
            "startDate": "string",
            "endDate": "string"
        },
        "enquiryPeriod": {
            "startDate": "string",
            "endDate": "string"
        },
        "hasEnquiries": "boolean",
        "eligibilityCriteria": "string",
        "awardPeriod": {
            "startDate": "string",
            "endDate": "string"
        },
        "numberOfTenderers": "integer",
        "tenderers": [
            {
                "identifier": {
                    "scheme": "string",
                    "id": "string",
                    "legalName": "string",
                    "uri": "string"
                },
                "additionalIdentifiers": [
                    {
                        "scheme": "string",
                        "id": "string",
                        "legalName": "string",
                        "uri": "string"
                    }
                ],
                "name": "string",
                "address": {
                    "streetAddress": "string",
                    "locality": "string",
                    "region": "string",
                    "postalCode": "string",
                    "countryName": "string"
                },
                "contactPoint": {
                    "name": "string",
                    "email": "string",
                    "telephone": "string",
                    "faxNumber": "string",
                    "url": "string"
                }
            }
        ],
        "procuringEntity": {
            "identifier": {
                "scheme": "string",
                "id": "string",
                "legalName": "string",
                "uri": "string"
            },
            "additionalIdentifiers": [
                {
                    "scheme": "string",
                    "id": "string",
                    "legalName": "string",
                    "uri": "string"
                }
            ],
            "name": "string",
            "address": {
                "streetAddress": "string",
                "locality": "string",
                "region": "string",
                "postalCode": "string",
                "countryName": "string"
            },
            "contactPoint": {
                "name": "string",
                "email": "string",
                "telephone": "string",
                "faxNumber": "string",
                "url": "string"
            }
        },
        "documents": [
            {
                "id": "string",
                "documentType": "string",
                "title": "string",
                "description": "string",
                "url": "string",
                "datePublished": "string",
                "dateModified": "string",
                "format": "string",
                "language": "string"
            }
        ],
        "milestones": [
            {
                "id": "string",
                "title": "string",
                "description": "string",
                "dueDate": "string",
                "dateModified": "string",
                "status": "met",
                "documents": [
                    {
                        "id": "string",
                        "documentType": "string",
                        "title": "string",
                        "description": "string",
                        "url": "string",
                        "datePublished": "string",
                        "dateModified": "string",
                        "format": "string",
                        "language": "string"
                    }
                ]
            }
        ],
        "amendment": {
            "date": "string",
            "changes": [],
            "rationale": "string"
        }
    },
    "buyer": {
        "identifier": {
            "scheme": "string",
            "id": "string",
            "legalName": "string",
            "uri": "string"
        },
        "additionalIdentifiers": [
            {
                "scheme": "string",
                "id": "string",
                "legalName": "string",
                "uri": "string"
            }
        ],
        "name": "string",
        "address": {
            "streetAddress": "string",
            "locality": "string",
            "region": "string",
            "postalCode": "string",
            "countryName": "string"
        },
        "contactPoint": {
            "name": "string",
            "email": "string",
            "telephone": "string",
            "faxNumber": "string",
            "url": "string"
        }
    },
    "awards": [
        {
            "id": "string",
            "title": "string",
            "description": "string",
            "status": "pending",
            "date": "string",
            "value": {
                "amount": "number",
                "currency": "string"
            },
            "suppliers": [
                {
                    "identifier": {
                        "scheme": "string",
                        "id": "string",
                        "legalName": "string",
                        "uri": "string"
                    },
                    "additionalIdentifiers": [
                        {
                            "scheme": "string",
                            "id": "string",
                            "legalName": "string",
                            "uri": "string"
                        }
                    ],
                    "name": "string",
                    "address": {
                        "streetAddress": "string",
                        "locality": "string",
                        "region": "string",
                        "postalCode": "string",
                        "countryName": "string"
                    },
                    "contactPoint": {
                        "name": "string",
                        "email": "string",
                        "telephone": "string",
                        "faxNumber": "string",
                        "url": "string"
                    }
                }
            ],
            "items": [
                {
                    "id": "string",
                    "description": "string",
                    "classification": {
                        "scheme": "string",
                        "id": "string",
                        "description": "string",
                        "uri": "string"
                    },
                    "additionalClassifications": [
                        {
                            "scheme": "string",
                            "id": "string",
                            "description": "string",
                            "uri": "string"
                        }
                    ],
                    "quantity": "integer",
                    "unit": {
                        "name": "string",
                        "value": {
                            "amount": "number",
                            "currency": "string"
                        }
                    }
                }
            ],
            "contractPeriod": {
                "startDate": "string",
                "endDate": "string"
            },
            "documents": [
                {
                    "id": "string",
                    "documentType": "string",
                    "title": "string",
                    "description": "string",
                    "url": "string",
                    "datePublished": "string",
                    "dateModified": "string",
                    "format": "string",
                    "language": "string"
                }
            ],
            "amendment": {
                "date": "string",
                "changes": [],
                "rationale": "string"
            }
        }
    ],
    "contracts": [
        {
            "id": "string",
            "awardID": "string",
            "title": "string",
            "description": "string",
            "status": "pending",
            "period": {
                "startDate": "string",
                "endDate": "string"
            },
            "value": {
                "amount": "number",
                "currency": "string"
            },
            "items": [
                {
                    "id": "string",
                    "description": "string",
                    "classification": {
                        "scheme": "string",
                        "id": "string",
                        "description": "string",
                        "uri": "string"
                    },
                    "additionalClassifications": [
                        {
                            "scheme": "string",
                            "id": "string",
                            "description": "string",
                            "uri": "string"
                        }
                    ],
                    "quantity": "integer",
                    "unit": {
                        "name": "string",
                        "value": {
                            "amount": "number",
                            "currency": "string"
                        }
                    }
                }
            ],
            "dateSigned": "string",
            "documents": [
                {
                    "id": "string",
                    "documentType": "string",
                    "title": "string",
                    "description": "string",
                    "url": "string",
                    "datePublished": "string",
                    "dateModified": "string",
                    "format": "string",
                    "language": "string"
                }
            ],
            "amendment": {
                "date": "string",
                "changes": [],
                "rationale": "string"
            },
            "implementation": {
                "transactions": [
                    {
                        "id": "string",
                        "source": "string",
                        "date": "string",
                        "amount": {
                            "amount": "number",
                            "currency": "string"
                        },
                        "providerOrganization": {
                            "scheme": "string",
                            "id": "string",
                            "legalName": "string",
                            "uri": "string"
                        },
                        "receiverOrganization": {
                            "scheme": "string",
                            "id": "string",
                            "legalName": "string",
                            "uri": "string"
                        },
                        "uri": "string"
                    }
                ],
                "milestones": [
                    {
                        "id": "string",
                        "title": "string",
                        "description": "string",
                        "dueDate": "string",
                        "dateModified": "string",
                        "status": "met",
                        "documents": [
                            {
                                "id": "string",
                                "documentType": "string",
                                "title": "string",
                                "description": "string",
                                "url": "string",
                                "datePublished": "string",
                                "dateModified": "string",
                                "format": "string",
                                "language": "string"
                            }
                        ]
                    }
                ],
                "documents": [
                    {
                        "id": "string",
                        "documentType": "string",
                        "title": "string",
                        "description": "string",
                        "url": "string",
                        "datePublished": "string",
                        "dateModified": "string",
                        "format": "string",
                        "language": "string"
                    }
                ]
            }
        }
    ],
    "language": "string"
}


var filterfields = {
  "Metadata": { 
    "date": "date"
  },
  "Planning": { 
    "description": "planning/budget/description",
    "project": "planning/budget/project",
    "amount": "planning/budget/value/amount"
  },
  "Tender": {
    "description": "tender/description",
    "status": "tender/status",
    "end date": "tender/tenderPeriod/endDate",
    "start date": "tender/tenderPeriod/startDate",
    "title": "tender/title",
    "amount": "tender/value/amount",
    "tenderer": "tender/tenderers/name"
  },
  "Buyer": { 
    "name": "buyer/name"
  },
  "Awards": {
    "date": "awards/date",
    "description": "awards/description",
    "status": "awards/status",
    "supplier": "awards/suppliers/name",
    "amount": "awards/value/amount",
    "title": "awards/title"
  },
  "Contracts": { 
    "description": "contracts/description",
    "status": "contracts/status",
    "title": "contracts/title",
    "amount": "contracts/value/amount",
    "end date": "contracts/period/endDate",
    "start date": "contracts/period/startDate"
  },
  "Implementation": { 
    "status": "contracts/implementation/milestones/status",
    "title": "contracts/implementation/milestones/title",
    "amount": "contracts/implementation/transactions/amount/amount",
    "date": "contracts/implementation/transactions/date"
  }
}

