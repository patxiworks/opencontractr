
function updateResult(title, result, error){
  var $ele = $("#result");

  if(!error){
    $ele.find('#result-title').text(title);
    //$ele.find('pre').text(result);
  }else{
    $ele.find('#result-title').text('');
    $ele.find('pre').html("<div class='alert alert-danger'> ERROR:" + error.message + "</div>");
  }

  $ele.fadeOut().fadeIn();
};

function queryHelper(model, onResult){
  $('#query-form').submit(function(e){
    var queryString = $("#query").val();
    var result, formated_json;
    var query;

    try {
      var t1 = new Date(),
          result = eval(queryString),
          time_taken = new Date() - t1;

      if(result.criteria){
        result = result.exec();
        $('#query').val(queryString + '.exec()');
      }
      
      var dynatable = $('#contractslist').data('dynatable');
      var pos = 0;
      $.each($('.filteritem').find('.fields'), function(index, item) {
        field = $(item).val();
        if (field) {
            dynatable.domColumns.remove(field);
            dynatable.domColumns.add($('<th>'+field+'</th>'), 1);
        }
      });
      $('.remove-filter').on('click', function() {
        remfield = $(this).closest('.filteritem').find('.fields').val();
        dynatable.domColumns.remove(remfield);
      })
      dynatable.settings.writers._rowWriter = function(index, record, columns, cellWriter) {
          row = '<tr>'
          for(i=0; i<columns.length; i++) {
            $.each(record, function(key, value) {
              console.log(key, columns[i].id)
                if (key == columns[i].id) {
                    if (key=='planning/budget/description') {
                      console.log('herl')
                      value = '<a href="post.php?post='+record['post-id']+'&action=edit">'+value+'</a>';
                    }
                    row += '<td>'+value+'</td>'
                }
                    
            })
          }
          row += '</tr>'
          return row;
	  }
      dynatable.records.updateFromJson({records: result});
      dynatable.records.init();
      dynatable.process();

      var formated_json = JSON.stringify(result, undefined, 2),
          count;

      if($.isPlainObject(result)){
        count = Object.keys(result).length; 
      }else{
        count = result.length;
      } 

      updateResult("Found a total of " + count +  ' contracts (' + time_taken  + ' ms)', formated_json);

      if(onResult){
        onResult(result);
      }

    }catch(err) {
      updateResult(null, null, err);
      console.log(err);
    }
    
    e.preventDefault();
  });

  //Set Sample model
  $("#all-records").on('click', function(e){
     updateResult('All Contracts', JSON.stringify(model.all, undefined, 2));

     if(onResult){
       onResult(model.all);
     }
     
     e.stopPropagation();
  });

  $("#all-records").trigger('click');

  $('a[data-q]').on('click', function(e){
    var query = QUERIES[$(this).data('q')];

    $('#helpbox-modal').modal('hide')
    $("#query").val(query);
    $('#query-form').submit();

    e.preventDefault();
  });

};
