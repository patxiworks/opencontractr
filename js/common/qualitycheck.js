
qualitycheck = {
    flatten: function(data) {
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
                    recurse(cur[p], prop ? prop + "/" + p : p);
                }
                if (isEmpty && prop) result[prop] = {};
            }
        }
        recurse(data, "");
        return result;
    },
    
    unflatten: function(data) {
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
    },
    
    writeoutput: function(table, scheme) {
        var obj = this;
        var schemecount = 0;
        var sn = 1;
        for (var prop in scheme) {
            if (scheme.hasOwnProperty(prop)) {
                var row = $('<tr>');
                var cell_sn = $('<td>');
                var cell_title = $('<td>');
                var cell_count = $('<td>');
                var cell_perc = $('<td>');
                sn++;
                
                // serial numbers
                //row.append(cell_sn.html(sn++));
                stage = prop.split('/')[0];
                checkbox = '<input type="checkbox" class="fieldcheck" data-field="'+prop+'">';
                row.addClass(stage).append(cell_sn.html(checkbox));
                
                // put object key (or field) in cell
                cell_title.addClass('field').html(prop);
                cell_title.attr('data-field', prop.split('/').join('-'));
                row.append(cell_title);
                
                // split value into cells and fill with values
                var value = scheme[prop];
                
                for (val in value) {
                    if (value.hasOwnProperty(val)) {
                        row.append($('<td class="'+val+'">').html(value[val]))
                    }
                }
                
                table.append(row);
                schemecount++;
            }
        }
    },
    
    calculatescores: function(scheme, selected, ocds) {
        
        var output = {};
        var levels = ['Basic', 'Intermediate', 'Advanced'];
        var schemescore, selectedscore, ocdsscore;
        var totalscheme = totalselected = totalocds = 0;
        output['scheme'] = {};
        output['selected'] = {};
        output['ocds'] = {};
        
        // scores for full scheme
        if (scheme) {
            for (item in levels) {
                schemescore = this.sumscores(scheme, levels[item])[0]
                output['scheme'][levels[item]] = schemescore;
                totalscheme += schemescore;
            }
            output['scheme']['total'] = totalscheme;
        }
        
        // scores for selected fields
        if (selected) {
            for (item in levels) {
                selectedscore = this.sumscores(selected, levels[item])[0]
                output['selected'][levels[item]] = selectedscore;
                totalselected += selectedscore;
            }
            output['selected']['total'] = totalselected;
        }
        
        // scores for ocds fields
        if (ocds) {
            scores = this.getscores(selected, ocds)
            for (item in levels) {
                ocdsscore = this.sumscores(scores, levels[item])[1]
                output['ocds'][levels[item]] = ocdsscore;
                totalocds += ocdsscore;
            }
            output['ocds']['total'] = totalocds;
        }
        
        output['fieldscore'] = (parseFloat(totalselected/totalscheme)*100);
        output['completionscore'] = (parseFloat(totalocds/totalselected)*100);
        
        return output;
    },
    
    getscores: function(scheme, ocds) { // 'scheme' may be selected fields, not necessarily the full scheme
        obj = this;
        var data = [];
        if (ocds) {
            var releases = ocds['releases'];
            releases.forEach(function(item, index) {
                data.push(obj.flatten(item))
            });
        }
        var datacount = data.length; // the number of objects (for ocds, number of releases)
        var scores = {};
        var scorecount = 0;
        
        for (var prop in scheme) {
            // split value into cells and fill with values
            var value = scheme[prop];
            
            // fill with data count
            var fieldcount = this.checkfield(prop, data); // frequency
            var fieldperc = parseFloat(fieldcount / datacount); // frequency score = frequency / total count

            if (fieldcount > 0) {
                var fieldvalue, totalvalue = 0;
                scores[prop] = value;
                delete scores[prop]['Description'];
                delete scores[prop]['Type'];
                for (val in scores[prop]) {
                    fieldvalue = parseFloat(scores[prop][val] * fieldperc);
                    scores[prop][val] = fieldvalue;
                    scores[prop].Percentage = fieldperc;
                }
                scorecount++;
            }
        }
        return scores;
    },
    
    checkfield: function(field, data) {
        var count = 0;
        var splitfield = field.split('/');
        // loop through list of objects (release data)
        data.forEach(function(item, index) {
            // loop through fields in each release
            for (var key in item) { if (item.hasOwnProperty(key)) {
                var exitkey = false
                var splitkey = key.split('/');
                if (splitfield.length == splitkey.length) {
                    for (i=0; i<splitfield.length; i++) {
                        //count = [splitfield.length, splitkey.length, i, splitfield[i], splitkey[i], (splitkey[i].indexOf(splitfield[i]))]
                        if (splitkey[i].indexOf(splitfield[i]) == -1) {
                            exitkey = true; break;
                        }
                    }
                    if (exitkey) {
                        continue;
                    } else {
                        count++; break;
                        //count = [splitfield, splitkey]; break;
                    }
                }
                    //console.log(index)
            }}
        });
        return count;
    },
    
    sumscores: function( obj, cat ) {
        var sum = 0, val = 0;
        for( var el in obj ) {
            if( obj.hasOwnProperty( el ) ) {
                if (cat) {
                    if (obj[el]['Percentage']) {
                        sum += parseFloat( obj[el][cat]);
                        val += parseFloat( obj[el][cat] * obj[el]['Percentage'] );
                    } else {
                        sum += parseFloat( obj[el][cat] );
                    }
                }
            }
        }
        return [sum, val];
    }
}

//qualitycheck.writeoutput();
