/***********
 **
 ** Converts to OCDSv1.1 from v1.0
 **
 **********/

var ocds_sections = ['parties','planning','buyer','tender','awards','contracts']

JSON.flatten = function (data) {
	var result = {};

	function recurse(cur, prop) {
		if (Object(cur) !== cur) {
			result[prop] = cur;
		} else if (Array.isArray(cur)) {
			for (var i = 0, l = cur.length; i < l; i++)
			recurse(cur[i], prop + "[" + i + "]");
			if (l == 0) result[prop] = [];
		} else {
			var isEmpty = true;
			for (var p in cur) {
				isEmpty = false;
				recurse(cur[p], prop ? prop + "." + p : p);
			}
			if (isEmpty && prop) result[prop] = {};
		}
	}
	recurse(data, "");
	return result;
};
JSON.unflatten = function (data) {
	"use strict";
	if (Object(data) !== data || Array.isArray(data)) return data;
	var regex = /\.?([^.\[\]]+)|\[(\d+)\]/g,
		resultholder = {};
	for (var p in data) {
		var cur = resultholder,
			prop = "",
			m;
		while (m = regex.exec(p)) {
			cur = cur[prop] || (cur[prop] = (m[2] ? [] : {}));
			prop = m[2] || m[1];
		}
		cur[prop] = data[p];
	}
	return resultholder[""] || resultholder;
};

var cleanup = function(obj) {
	obj = JSON.flatten(obj)
	for (var propName in obj) {
		// replace amendment with amendments[0]
		new_propName = propName.replace(/amendment/g, 'amendments[0]');
		if (propName !== new_propName) {
			Object.defineProperty(obj, new_propName, Object.getOwnPropertyDescriptor(obj, propName));
			delete obj[propName]
		}
		if (obj[propName] === null || obj[propName] === undefined || obj[propName] === "" ) {
			delete obj[propName];
		}
	}
	return JSON.unflatten(obj)
}

function pad(n, width, z) {
	z = z || '0';
	n = n + '';
	return n.length >= width ? n : new Array(width-n.length+1).join(z)+n;
}

function search(val, obj) {
	obj.forEach(function(item, i) {
		if (obj.name === val) {
			return obj.id;
		} else {
			return;
		}
	});
}

function addtoParties(field, role, parties) {
	// check for number of objects in parties
	pcount = parties.length;
	parties[pcount] = {};
	role = role.split('-')[0];
	// add corresponding id and role to field
	field['id'] = role+'-'+pad(pcount+1,3);
	field['roles'] = [role]; // check for proper codelist entry, if any

	for (var key in field) {
		if (key != 'name' && key != 'id') {
			if (field.hasOwnProperty(key)) {
				parties[pcount][key] = field[key]
				delete field[key]
			} 
		} else {
			parties[pcount][key] = field[key];
		}
	}
	return parties;
}

function partyAction(item, role, parties) {
	var name = search(item['name'], parties);
	if (!name) {
		// populate parties
		parties = addtoParties(item, role, parties);
	} else {
		
	}
}

function convert(ocds) {
	ocds = cleanup(ocds)
	// define parties array
	var parties = [];
	var organisations = {};
	if (ocds['buyer'])
		organisations['buyer'] = ocds['buyer'];
    if ('tender' in ocds) {
		if ('procuringEntity' in ocds['tender']) {
            organisations['procuringEntity'] = ocds['tender']['procuringEntity'];
        }
		if ('tenderers' in ocds['tender']) {
            organisations['tenderer'] = ocds['tender']['tenderers'];
        }
	}
	// check number of awards and add to organisations array
	var awards = ocds['awards']
	if (awards) {
		awards.forEach(function(item, i) {
			organisations['supplier-'+i] = ocds['awards'][i]['suppliers']
		})
	}
	
	for (var role in organisations) {
		fielditem = organisations[role];
		if (fielditem) {
			if (!!fielditem && fielditem.constructor === Array) {
				fielditem.forEach(function(item, i) {
					if (!!item && item.constructor === Array) {
						item.forEach(function(it, j) {
							partyAction(it, role, parties)
						});
					}
					partyAction(item, role, parties)
				})
			} else {
				partyAction(fielditem, role, parties)
			}
		}
	}
	// add parties to ocds
	ocds['parties'] = parties;
	
	return ocds;
}

//ocds = JSON.parse(document.getElementById('old-ocds').innerHTML)
//convert_ocds(ocds)