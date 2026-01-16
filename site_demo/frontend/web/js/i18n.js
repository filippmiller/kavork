$(function () {
  var $table = $('#lg_table tbody');

  var code_list = [];
  var page_limit = 250;
  var total_records = 0;

  function render(e) {
    var render_page_block = false;

    if (e) {
      $this = $(this);
      if ($this.attr('name') == 'category') {
        render_page_block = true;
        $('[name="page"]').html('')
      }
    }

    $table.html('');
    total_records = 0;

    var page = $('[name="page"]').val() * 1;
    if (page > 0) page--;
    var start_item = page * page_limit;

    var update_code_list = (code_list.length == 0);
    if (update_code_list) {
      render_page_block = true;
      code_list = {};
    }

    var filter_category = $('[name="category"]').val();
    if (filter_category.length == 0) filter_category = false;

    for (alies in lg_base) {
      for (file in lg_base[alies][lg_default]) {
        var category = file.substr(0, file.length - 4);
        if (update_code_list) {
          code_list[category] = true;
        }

        if (filter_category && filter_category != category) continue;
        for (code in lg_base[alies][lg_default][file]) {
          total_records++;
          if (total_records < start_item) continue;
          if (total_records > start_item + page_limit) continue;

          var tr = $('<tr/>', {
            'data': {
              'alies': alies,
              'file': file,
              'code': code,
            }
          });
          tr.append($('<td/>', {
            text: category
          }));
          tr.append($('<td/>', {
            text: code
          }));
          for (lg in lg_list) {
            var value = get_lg_value([alies, lg, file, code], code);
            var input = $('<input/>', {
              value: value,
              data: {lg: lg},
            });
            tr.append($('<td/>', {
              'class': 'edit_in_table',
            }).append(input));
          }
          $table.append(tr);
        }
      }
    }

    if (update_code_list) {
      code_list = Object.keys(code_list);

      var $select = $('[name="category"]');
      var options = [];
      for (var i in code_list) {
        code = code_list[i];
        options.push($('<option/>', {
          value: code,
          text: code,
        }))
      }
      $select.append(options);

      console.log('category', code_list);
      console.log('total_records', total_records);
    }

    if (render_page_block) {
      $('[name="page"]').html('');
      var max_pages = Math.ceil(total_records / page_limit);
      var options = [max_pages];
      $('.page_count').text(max_pages);
      for (var i = 1; i <= max_pages; i++) {
        options.push($('<option/>', {
          value: i,
          text: i,
        }))
      }
      $('[name="page"]').append(options);
    }

  }

  render();

  $('[name="page"]').on('change', render);
  $('[name="category"]').on('change', render);

  $table.on('change', 'input', function () {
    var $this = $(this);
    var data = $this.closest('tr').data();
    data['lg'] = $this.data('lg');
    data['value'] = $this.val();
    $this.addClass('sending')
      .removeClass('error')
      .removeClass('done');
    $.post('/i18n/default/update', data, function () {
      var $this = $(this);
      $this.removeClass('sending');
      $this.addClass('done');
    }.bind($this))
      .fail(function () {
        var $this = $(this);
        $this.removeClass('sending');
        $this.addClass('error');
      }.bind($this))
  });

  function get_lg_value(path, def) {
    data = lg_base[path[0]];
    for (i = 1; i < path.length; i++) {
      if (typeof(data[path[i]]) == "undefined") return def;
      data = data[path[i]];
    }
    return data;
  }
});
