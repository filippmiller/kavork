// Global settings
CKEDITOR.disableAutoInline = true;

$(function () {
  //Анимация формы авторизации
  $(".login-form").css({
    opacity: 1,
    "-webkit-transform": "scale(1)",
    "transform": "scale(1)",
    "-webkit-transition": ".5s",
    "transition": ".5s"
  });

  if ($('#clock').length > 0) {
    setInterval(upd_clock, 1000);
  }

  //фильтры на нажатие клавиш
  //$('body').on('keypress', '.letters', only_letters_in_input);
  //$('body').on('keypress', 'input,textarea', only_no_foreign_letters_in_input);
  $('body').on('keypress', '.num', no_letters_in_input);
  //$('body').on('keypress', '.canadian_zip_key_control', canadian_zip_key_control);
  $('body').on('keypress', '.onlyFloat', float_in_input);
  $('body').on('keyup', '.onlyFloat', function () {
    this.value = this.value.replace(',', '.');
  });

  //рабоат фильтра в таблице
  //$('body').on('click', '.stopEvent', stopEvent);
  //$('body').on('click', '.showControl', showControl);
  //$('body').on('click', function () {
  //  $('.temp_show.active').removeClass("active");
  //});

  $('body').on('click', ".print,.modal_open", function () {
    var $this = $(this);
    var href = $this.data('link');

    if (!href) return;
    var type = $this.data('type');

    var f = $this.hasClass('modal_open') ? modal_open : print_href;

    if (type) {
      if (type == "editor") {
        $.post(href, {data: JSON.stringify(editor.getData())}, function () {
          var data = this;
          data.f(data.href);
        }.bind({
          href: href,
          f: f
        }));
        return true;
      }
      if (type == "send") {
        $.post(href, {data: JSON.stringify(editor.getData())});
        return;
      }
      if (type == "test") {
        href += '&' + $('.modal-body form').serialize();
        $('.modal-header .close').click();
      }
    }
    f(href);
  });
//фикс скрытия прокрутки тела
  $('.modal').on('shown.bs.modal', function () {
    $('html').css('overflow', 'hidden');
  }).on('hidden.bs.modal', function () {
    $('html').css('overflow', 'auto');
  });


  //работа бутстраповских табов
  $('body.page_list').on('click', '[data-toggle=tab]', tab_select);
  $('.dropdown-toggle').dropdown()
  //работа бутстраповских Popover
  $(document).on('click', '[data-toggle="popover"]', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    var $this = $(this);
    var popover = $this.data('bs.popover');
    if (typeof (popover) != "undefined" && $this.data('bs.popover').$tip.is(':visible')) {
      $(this).popover('hide');
    } else {
      $('[data-toggle="popover"]').not(this).popover('hide');
      $this.popover({html: true, trigger: "manual"}).popover('show');
    }
  });
  $(document).on('click', function (e) {
    if ($('[data-toggle="popover"]').length) {
      $('[data-toggle="popover"]').popover('hide');
    }
  });

  //работа бутстраповских радио кнопок
  //$('.btn-group-toggle')
  $('body').on('change', '[type=radio]', function () {
    var parent = $(this).parent();
    if (!parent.hasClass('btn')) return true;
    parent.closest('.btn-group').find('.active').removeClass('active');
    parent.addClass('active');
  });

  //фикс для календаря
  if (typeof moment !== 'undefined') {
    moment.langData()._meridiemParse = /[ap]\.?m?\.?/i
    moment.langData().meridiem = function (hours, minutes, isLower) {
      if (hours > 11) {
        return isLower ? 'pm' : 'PM';
      } else {
        return isLower ? 'am' : 'AM';
      }
    }
  }

  $('body').on('click', '.testChechedRow', function (e) {
    $sel = $('table .kv-row-checkbox:checked');
    if ($sel.length == 0) {
      e.preventDefault();
      modal.show();
      modal.setTitle(app.i18n.no_selection);
      modal.setContent(app.i18n.you_must_select_items);
      modal.addFooterButton(app.i18n.close, '', 'btn btn-default', function () {
        this.hide()
      });
      return false;
    }
  })
});

var print_href = function (href) {
  var ifr = document.createElement('iframe');
  ifr.src = href;
  ifr.onload = function () {
    this.contentWindow.focus();
    this.contentWindow.print();
    this.remove()
  }.bind(ifr);
  document.body.append(ifr);
};

var modal_open = function (href) {
  //если еще не загрузили модель млжалок то делаем паузу
  if (typeof(modal) == "undefined") {
    setTimeout(modal_open, 100, href);
    return;
  }

  //На странице шаблонов с POST не работает печать
  modal.doRemote(
    href,
    'GET',
    null
  );

  /*$.get(href, function (data) {
    var modal = $('#ajaxCrudModal').modal('show');

    var head = modal.find('.modal-header');
    if (head.find('.modal-title').length == 0) {
      head.append('<h4 class="modal-title"/>')
    }
    modal.find('.modal-body').html(data.content);
    modal.find('.modal-footer').html(data.footer);
    modal.find('.modal-title').html(data.title);
  }, 'json');*/
};

var tab_select = function (e) {
  e.preventDefault();
  var href = this.href.split("#");
  if (href.length != 2) return;
  href = href[1];
  var tab_content = $('#' + href);
  if (tab_content.length == 0) return;

  if (tab_content.hasClass('active')) return;

  var wrap = tab_content.closest('.tab-content');
  wrap.find('.tab-pane.active').removeClass('active').removeClass('in');
  tab_content.addClass('in').addClass('active');

  var $this = $(this);
  $this.closest('.nav-tabs')
    .find('li.active')
    .removeClass('active');
  $(this).closest('li').addClass('active');

  return false;
};

//превью загрузки картинки
function testImgPrew() {
  $('input[type=file]').on('change', function (evt) {
    var file = evt.target.files; // FileList object
    var f = file[0];
    // Only process image files.
    if (!f.type.match('image.*')) {
      return false;
    }
    var reader = new FileReader();

    data = {
      'el': this,
      'f': f
    };
    reader.onload = (function (data) {
      return function (e) {
        img = $('[for="' + data.el.name + '"]');
        if (img.length > 0) {
          img.attr('src', e.target.result)
        }
      };
    })(data);
    // Read in the image file as a data URL.
    reader.readAsDataURL(f);
  });

  $('.file_select input').on('change', function (evt) {
    var img = $('[for="' + this.name + '"]');
    if (img.length > 0) {
      img.attr('src', this.value)
    }
  })
}

$(function () {
  $('body').on('click', 'button.file_select', function (e) {
    var csrfParam = $('meta[name=csrf-param]').attr('content');
    var csrfToken = $('meta[name=csrf-token]').attr('content');
    var customData = {};
    customData[csrfParam] = csrfToken;

    param = {
      //startPathHash: "",
      customData: customData,
      useBrowserHistory: false,
      resizable: false,
      width: 'auto',
      url: "/elfinder/connect?filter=image&lang=" + app.language.split('-')[0].toLowerCase(),
      lang: app.language.split('-')[0].toLowerCase(),
      dialogContained: true,
      getFileCallback: function (file) {
        var $this = $(this);
        var for_el = $this.attr('for');
        var input = $('[name="' + for_el + '"]');
        var img = $('img[for="' + for_el + '"]');
        input.val(file.url);
        if (img.length > 0) {
          img.attr('src', file.url)
        }
        jQuery('#file_select .close').click();
      }.bind(this)
    };

    var modal = '<div id="file_select" class="fade modal new_modal" role="dialog" tabindex="-1">' +
      '<div class="modal-dialog "><div class="modal-content">' +
      '<div class="modal-header">' +
      '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
      '</div>' +
      '<div class="modal-body"></div>' +
      '</div>' +
      '</div>' +
      '</div>';
    model = $(modal);
    $('body').append(modal);

    modal = new ModalRemote('#file_select');
    modal.show();
    modal.setTitle(app.i18n.insert_img);
    jQuery('#file_select .modal-body').elfinder(param).elfinder('instance');

    $('.ui-dialog').css({
      'z-index': 999999,
      //'top':'10vh'
    })
  });
});

//Обновление часов на стартовой
function upd_clock() {
  if (!typeof(difference) == 'undefined') return;
  d = new Date()
  d.setTime(d.getTime() + difference);
  h = d.getHours();

  if (app.time24Hour) {
    pr = "";
  } else {
    if (h > 11) {
      pr = ' PM'
    } else {
      pr = ' AM'
    }
    if (h == 0) h = 12;
    if (h > 12) h -= 12;
  }
  ;

  i = d.getSeconds();
  /* i=(((i%2)==1)?' ':':')*/
  $('#clock h3').html(h + '<span class=clock_razdel>:</span>' + first_null(d.getMinutes()) + pr);
  $('#clock h5').text(d.getDate() + ' ' + GetMonth(d.getMonth()) + ', ' + d.getFullYear());

  $('.clock_razdel').css('opacity', (i % 2))
}

function first_null(i) {
  if (i < 10) return '0' + i;
  return i;
}

function GetMonth(intMonth) {
  return MonthArray[intMonth]
}

function float_in_input(evt) {
  code = evt.keyCode || evt.charCode; // для Chrome || Firefox
  char = String.fromCharCode(code)

  if ((code >= 48 && code <= 57) || (code == 13)) return;
  if ((char == ",") || (char == ".")) {
    val = this.value;
    if (val.indexOf(',') == -1 && val.indexOf('.') == -1) return;
  }
  show_gritter(this);
  evt.preventDefault();

}

function no_letters_in_input(evt) {
  code = evt.keyCode || evt.charCode; // для Chrome || Firefox
  if ((code >= 48 && code <= 57) || (code == 13)) return;
  else {
    show_gritter(this);
    evt.preventDefault();
  }
}

function only_letters_in_input(evt) {
  code = evt.keyCode || evt.charCode;
  if (
    (code >= 97 && code <= 122) || (code == 13) ||    // enter
    (code >= 65 && code <= 90) || (code == 32))
    return;
  else {
    show_gritter(this);
    evt.preventDefault();
  }
}

function only_no_foreign_letters_in_input(evt) {
  code = evt.keyCode || evt.charCode;  // для Chrome || Firefox
  if (
    (code >= 48 && code <= 57) ||
    (code >= 97 && code <= 122) ||
    (code >= 65 && code <= 90) ||
    (code == 44) || (code == 46) ||
    (code == 13) || (code == 32)
    || (code == 33) || (code == 64) || (code == 35) || (code == 36) || (code == 37) || (code == 94) || (code == 38) || (code == 42)
    || (code == 40) || (code == 41) || (code == 95) || (code == 43) || (code == 45) || (code == 61) || (code == 8) || (code == 9) || (code == 39)  // !@#$%^&*()_+-=
  )
    return;
  else {
    show_gritter(this);
    evt.preventDefault();
  }
}

function show_gritter(current_element) { // проверяем показывать ли для данного элемента гриттер или нет.

}

function stopEvent(e) {
  e.preventDefault();
  return false;
}

//показать фильтр в таблице
function showControl(e) {
  $('.temp_show.active').removeClass('active');
  id = $(this).attr('for');
  control_d = eval(id);
  control = $("#" + id + '-wrap');
  control
    .addClass('active')
    .html(control_d);

  e.preventDefault();
  return false;
}


//выпадающий список юеров при поиске
//http://twitter.github.io/typeahead.js/examples/
//https://github.com/twitter/typeahead.js/blob/master/doc/jquery_typeahead.md#datasets
function userAA(ev, suggestion) {
  if (this.id.indexOf('startvisit-') >= 0) {
    if ($('#startvisit-type input:checked').val() == 0) {
      $('#startvisit-type input[value=1][type=radio]')[0].checked = true;
    }
    ;
    if (ev.type == "typeahead:select") {
      $.each(suggestion, function (key, value) {
        $('#startvisit-' + key).val(value);
        //alert( key + ": " + value );
      });
      $('#startvisit-type input[value=2][type=radio]')[0].checked = true;
    }
    return;
  }
  if (this.id.indexOf('visitorlog-certificate_type') >= 0) {
    var type = $('#visitorlog-certificate_type').val();
    var els = $('[id^=visitorlog-certificate_]').not('#visitorlog-certificate_type');
    els.closest('.form-group').removeClass('form-group-invisible');

    if (type > 0) els = els.not('#visitorlog-certificate_number');
    if (type == 2) els = els.not('#visitorlog-certificate_time');
    if (type == 4) els = els.not('#visitorlog-certificate_discount');
    if (type == 5) els = els.not('#visitorlog-certificate_cash');

    els.closest('.form-group').addClass('form-group-invisible');
  }
}

var template = false;

function init_template() {
  if (template) return;
  template = (function () {
    var ready = false;
    var tpls = {};

    $.get('/tpls', function (data) {
      for (index in data) {
        tpls[index] = Twig.twig({
          data: data[index],
        });
      }
      ready = true;
    }, 'json');

    function render(tpl, data) {
      if (!tpls[tpl]) return '';

      data.i18n = app.i18n;
      return tpls[tpl].render(data);
    }

    function isReady() {
      return ready;
    }

    return {
      render: render,
      ready: isReady
    }
  })();
}

function updateMain() {
  var containerId = '#crud-datatable',
      container = $(containerId);
  if (container.length) {
    $.pjax({container: containerId});
  }
}

//операции на стартовом экране
if (typeof(is_main) != "undefined") {
  $('.open_admin_panel').on('click', function () {
    $('#admin_panel').show('slide', {direction: 'left'}, 1000);
    setCookie('showAdminPanel', 1);

    $('html, body').animate({
      scrollTop: $("#admin_panel").offset().top
    }, 1000);

  });
  $('.close_admin_panel').on('click', function () {
    $('#admin_panel').hide('slide', {direction: 'left'}, 1000);
    setCookie('showAdminPanel', 0);
  });
  if (getCookie('showAdminPanel') == 1) {
    $('#admin_panel').show(0);
  } else {
    $('#admin_panel').hide(0);
  }

  $('.visible_control_tile').on('click', function () {
    $this = $(this).closest('.fixed-tiles');
    if ($this.hasClass('closest')) {
      $this.removeClass('closest');
      setCookie('hideControlTile', 0);
    } else {
      $this.addClass('closest');
      setCookie('hideControlTile', 1);
    }
  });
  if (getCookie('hideControlTile') == 1) {
    setTimeout(function () {
      $('.fixed-tiles').addClass('closest');
    }, 3000);
  }

  init_template();

  function updateMain(not_timeout) {
    $.get('/get_cafe_status', function (data) {
      var persons_summary_count = 0;
      var has_update = false;

      for (index in data) {
        if (index == 'wait_pay' || index == 'tasks') {
          var base_select = (index == 'wait_pay') ? 'waitpay' : 'waittask';
          var base_type = (index == 'wait_pay') ? 'warning' : 'white';
          var allow_dismiss = (index == 'wait_pay') ? false : true;

          var $modal = $('#ajaxCrudModal');
          if ($modal.length > 0 && $modal.css('display') != 'none') continue;
          $('.' + base_select).addClass('old');
          for (var i = 0; i < data[index].length; i++) {
            $msg = $('#' + base_select + '_' + data[index][i]['code']);
            if (allow_dismiss && getCookie('wait' + data[index][i]['code'])) {
              continue;
            }

            if ($msg.length == 0) {
              $.notify({
                title: data[index][i]['title'],
                message: data[index][i]['msg']
              }, {
                type: base_type,
                allow_dismiss: allow_dismiss,
                timer: 100000000000,
                onClose: function () {
                  $(this).find('a,button,input').prop('disabled', true);
                  $(this).find('a,button,input').removeAttr('href');
                  $(this).find('a,button,input').removeAttr('role');

                  var code = $(this).find('[code]').attr('code');
                  setCookie('wait' + code, "1", {
                    expires: 60 * 5//в минутах
                  });
                },
              });
            } else {
              $msg.removeClass('old')
            }
          }

          $('.' + base_select + '.old').closest('.alert').find('.close').click();
          continue;
        }

        if (index == 'showTask') {
          if (getCookie('showTask')) {
            continue;
          }
          ;

          var $modal = $('#ajaxCrudModal');
          if ($modal.length > 0 && $modal.css('display') != 'none') continue;
          modal_open('/tasks/default/views');
          setCookie('showTask', "1", {
            expires: 3600 * 12//в часах
          });
          continue;
        }

        if (index == 'modal') {
          modal_open(data[index]);
        }

        var wrap = $('#' + index + "_log");
        if (wrap.length != 1) continue;
        var wrap_start = wrap.find('.flex_title');
        if (wrap_start.length == 0) {
          wrap_start = $('<div/>', {
            'class': 'flex_title',
          });
          wrap.append(wrap_start);
        }

        var els = wrap.find('.tile');
        els.addClass('old');

        //els.remove();
        if (data[index]) {
          for (var i = 0; i < data[index].length; i++) {
            var item_data = data[index][i];
            var pv = wrap.find('[code=' + item_data.id + ']');
            var hash = JSON.stringify(item_data).hashCode();

            if (index == "visitors") {
              // Persons summary count increment
              persons_summary_count += item_data.persons_summary;
            }

            if (pv.length == 1) {
              if (pv.data('hash') == hash) {
                pv.removeClass('old');
                continue;
              }
            }

            has_update = true;

            item_data['f_string'] = (item_data['f_name'] || "") + (item_data['l_name'] || "");
            item_data['f_string'] = clear_for_find(item_data['f_string']);

            out = template.render(index + "_log", item_data);
            out = $(out);
            out.data('hash', hash);
            if (pv.length > 0) {
              pv.after(out);
              pv.remove();
            } else {
              wrap_start.prepend(out);
            }
          }
        }
        var old = wrap.find('.old');
        if (old.length > 0) {
          has_update = true;
          old.remove();
        }
      }

      if (has_update) {
        search_visitor_main.bind(document.getElementById("search_visitor"))();
      }

      //if(!not_timeout)setTimeout(updateMain,2000);

      updatePersonsCounter(persons_summary_count);
    }, 'json');

  }

  setInterval(updateMain, 2000);
  updateMain();

  function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }

  function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
      var d = new Date();
      d.setTime(d.getTime() + expires * 1000);
      expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
      options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
      updatedCookie += "; " + propName;
      var propValue = options[propName];
      if (propValue !== true) {
        updatedCookie += "=" + propValue;
      }
    }

    document.cookie = updatedCookie;
  }

  //для работы покупок
  $(function () {
    //сброс выделенных тоывров
    $('body').on('click', '.reset_select_product', function (e) {
      e.preventDefault();

      var products = $('.shop-modal-list-products-product.is-active');

      products.find('.tile').removeClass('selected');
      products.find('input,select').prop('disabled', true);

      shop_recalculate();
    });

    //Отправить форму
    $('body').on('click', '.shop-pseudo-footer [type="submit"]', function (e) {
      e.preventDefault();
      var el = $(this);
      var url = el.attr('data-url');
      var form = $('#shop-modal-list-products-form');

      if (typeof url !== 'undefined') {
        form.attr('action', url);
      }

      var data;

      // Test if browser supports FormData which handles uploads
      if (window.FormData) {
        data = new FormData($(form)[0]);
      } else {
        // Fallback to serialize
        data = $(form).serializeArray();
      }

      modal.doRemote(
        form.attr('action'),
        form.hasAttr('method') ? form.attr('method') : 'GET',
        data
      );
    });

    //Показать/скрыть форму левого товара
    $('body').on('click', '.__shop_store_front_add_new_item__', function () {
      $('#__shop_default_view__').addClass('hidden');
      $('#__shop_new_item_view__').removeClass('hidden');
      $('#__shop_new_item_view__ form')[0].reset();
    });

    $('body').on('click', '.__shop_store_front_back_to_default_view__', function () {
      $('#__shop_default_view__').removeClass('hidden');
      $('#__shop_new_item_view__').addClass('hidden');
    });

    //пересчет цены от выбранного количества
    $('body').on('click', '.shop-modal-list-products .bootstrap-touchspin', function () {
      var spin = $(this);
      var el = spin.closest('.shop-modal-list-products-product').find('.tile');
      spin.find('input').prop('disabled', false);
      el.addClass('selected');
      shop_recalculate();
    });

    $('body').on('change', '.shop-modal-list-products input', function () {
      shop_recalculate();
    });

    //Смена выбранных тоывров
    $('body').on('click', '.shop-modal-list-products-product.is-active .tile', function (e) {
      e.preventDefault();
      var el = $(this);
      var product = el.closest('.shop-modal-list-products-product');

      if (el.hasClass('selected')) {
        el.removeClass('selected');
        product.find('input,select').prop('disabled', true);
      } else {
        el.addClass('selected');
        product.find('input,select').prop('disabled', false);
      }

      shop_recalculate();
    });
  });

  $('#search_visitor').on('input', search_visitor_main);

}

function search_visitor_main() {
  var val = clear_for_find(this.value);
  var els = $('#visitors_log .tile');
  els.show();
  if (val.length < 1) return;
  els.not('[title*="' + val + '"]').hide();
}

function clear_for_find(item_data) {
  item_data = item_data.split('.').join('');
  item_data = item_data.split(' ').join('');
  item_data = item_data.toLocaleLowerCase();
  return item_data;
}

function shop_recalculate(visitor_id) {
  var form = $('#shop-modal-list-products-form');
  var tiles_selected = $('.shop-modal-list-products .tile.selected');

  if (tiles_selected.length > 0) {
    $('.shop-pseudo-footer [type="submit"]').prop('disabled', false);
  } else {
    $('.shop-pseudo-footer [type="submit"]').not('.not_disabled').prop('disabled', true);
  }

  var data = form.serialize() + '&' + $.param({method: 'recalculate'});
  if (visitor_id) {
    data += '&' + $.param({'visitor_id': visitor_id});
  }

  $.ajax({
    url: form.attr('action'),
    method: form.attr('method'),
    data: data,
  }).done(function (data) {
    $('.shop-footer-summary').replaceWith(data);
  });
}

function updatePersonsCounter(persons_count) {
  $('.__stats_current_visitors_count__').text(persons_count);
}

// Force Modal Close button to show - helps if button was previously hidden
$(function () {
  $('#ajaxCrudModal').on('show.bs.modal', function (e) {
    $('#ajaxCrudModal').find('.modal-header button.close').show();
  });

  //
  // UNITE --- START
  //
  $('.__unite_toggle__').on('click', function (e) {
    var button = $(this);

    if (button.hasClass('selected')) {
      app.unite_disable();
    } else {
      app.unite_enable();
    }
  });

  $(document).on('click', 'button[role="visit-unite"]', function (e) {
    e.preventDefault();
    app.unite_enable();
    var modal = $('#ajaxCrudModal');
    var form = modal.find('form');
    var id = form.find('#visitorlog-id').val();
    modal.modal('hide');

    $('#visitors_log .tile[code="' + id + '"]._selectable').trigger('click');
  });

  $(document).on('click', '#visitors_log .tile._selectable', function (e) {
    e.preventDefault();
    var calculation_button = $('.__unite_calculation__');
    var tile = $(this);

    if (tile.hasClass('selected')) {
      tile.removeClass('selected');
    } else {
      tile.addClass('selected');
    }

    if ($('#visitors_log .tile.selected').length > 1) {
      calculation_button.removeClass('hidden');
    } else {
      calculation_button.addClass('hidden');
    }
  });

  $('.__unite_calculation__').on('click', function (e) {
    e.preventDefault();
    var href = $(this).attr('href');
    var visits_id = [];
    var selected_tiles = $('#visitors_log .tile.selected');

    $.each(selected_tiles, function (index, tile) {
      visits_id.push($(tile).attr('code'));
    });

    modal.doRemote(href, 'POST', visits_id);
  });

  app.unite_enable = function () {
    var button = $('.__unite_toggle__');
    var tiles = $('#visitors_log .tile');

    button.addClass('selected');
    tiles.find('a').removeAttr('role').parent().addClass('_selectable');

    if (app.unite_disable_button_html) {
      app.unite_button_html = button.html();
      button.html(app.unite_disable_button_html);
    }
  };

  app.unite_disable = function () {
    var button = $('.__unite_toggle__');
    var tiles = $('#visitors_log .tile');
    var calculation_button = $('.__unite_calculation__');

    if (app.unite_button_html) {
      button.html(app.unite_button_html)
    }
    button.removeClass('selected');
    tiles.removeClass('selected').find('a').attr('role', 'modal-remote').parent().removeClass('_selectable');
    calculation_button.addClass('hidden');
  };

  //
  // UNITE --- END
  //

  //
  // SHOP MERCHANT --- START
  //
  $('.__shop_merchant_toggle__').on('click', function (e) {
    var button = $(this);
    var mechant_block = $('.__shop_merchant');

    if (button.hasClass('btn-warning')) {
      button.removeClass('btn-warning');
      button.addClass('btn-info');
      $('.__state-enabled').addClass('hidden');
      $('.__state-disabled').removeClass('hidden');
      mechant_block.addClass('hidden');
    } else {
      button.addClass('btn-warning');
      button.removeClass('btn-info');
      $('.__state-enabled').removeClass('hidden');
      $('.__state-disabled').addClass('hidden');
      mechant_block.removeClass('hidden');
    }
  });
  //
  // SHOP MERCHANT --- END
  //

  //
  // FLASH MESSAGES
  //
  app.notify = function (message, type) {
    if (typeof type === 'undefined') {
      type = 'success';
    }

    $.notify({message: message}, {type: type});
  };

  //
  // Check printing START
  //

  app.print_check = function (id) {
    href = "/visits/print_check?id=" + id + '&method=checkPrint';
    var iframe = document.createElement('iframe') ;
    iframe.src = href;
    iframe.onload = function () {
     this.contentWindow.focus();
     this.contentWindow.print();
     this.remove()
	 this.remove()
    }.bind(iframe);
    $('body').append(iframe);
    //document.body.append(iframe);
  };
  app.sale_print_check = function (id) {
    href = "/shop/storefront/print_check?id=" + id + '&method=checkPrint';

    var iframe = document.createElement('iframe');

    iframe.src = href;
    iframe.onload = function () {
    this.contentWindow.focus();
    this.contentWindow.print();
    this.remove()
    }.bind(iframe);
    $('body').append(iframe);
   //document.body.append(iframe);
  };

  app.print_check_by_params = function (id, data) {
    $.ajax({
      url: '/templates/admin/test-print?id=' + id,
      method: 'POST',
      data: {
        data: data
      }
    }).done(function (html) {
      var iframe = document.createElement('iframe');

      iframe.src = 'about:blank';
      iframe.onload = function () {
	  this.contentWindow.document.write(html);
      this.contentWindow.focus();
      this.contentWindow.print();
      this.remove()
      }.bind(iframe);
      //document.body.append(iframe);
	  $('body').append(iframe);
    });
  };

  //
  // CKEDITOR --- START
  //

  app.ckeditor_init = function (container_id) {
    if ($('#' + container_id).data('editor_init')) return;
    $('#' + container_id).data('editor_init', true);
    CKEDITOR.inline(container_id, {
      "height": 100,
      "language": app.language,
      "toolbarGroups": [
        {"name": "undo"},
        {"name": "basicstyles", "groups": ["basicstyles"]},
        {"name": "paragraph", "groups": ["list", "align"]},
        {"name": "links", "groups": ["links"]},
        {"name": "insert", "groups": ["insert"]},
        '/',
        {"name": "styles", "groups": ["styles"]},
        {"name": "colors", "groups": ["colors"]},
        {"name": "mode"}
      ],
      "removeButtons": "Flash,Table,Smiley,SpecialChar,PageBreak,Iframe",
      "removePlugins": "elementspath",
      "extraPlugins": "sourcedialog,token,imageresizerowandcolumn",
      "resize_enabled": false,
      "filebrowserBrowseUrl": "/elfinder/manager",
      "filebrowserImageBrowseUrl": "/elfinder/manager?filter=image",
      "filebrowserFlashBrowseUrl": "/elfinder/manager?filter=flash",
      "on": {
        "instanceReady": function (ev) {
          mihaildev.ckEditor.registerOnChange(container_id);
        }
      }
    });
  };

  app.ckeditor_toggleReadOnly = function (container_id) {
    if (app.ckeditor_isReadOnly(container_id)) {
      app.ckeditor_setReadOnly(container_id, false);
    } else {
      app.ckeditor_setReadOnly(container_id, true);
    }
  };

  app.ckeditor_setReadOnly = function (container_id, isReadOnly) {
    CKEDITOR.instances[container_id].setReadOnly(isReadOnly);
  };

  app.ckeditor_isReadOnly = function (container_id) {
    return CKEDITOR.instances[container_id].readOnly;
  };

  app.ckeditor_destroy = function (container_id) {
    CKEDITOR.instances[container_id].destroy();
  };

  $('[role="ckeditor-inline"]').each(function (index, el) {
    var id = $(el).attr('id');
    if (typeof id !== "undefined") {
      app.ckeditor_init(id);
    }
  });

  //
  // CKEditor - Twig Token support implementation - START
  //
  function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  CKEDITOR.plugins.add('token', {
    requires: 'widget',

    init: function (editor) {
      var tokenStart = '{';
      var tokenEnd = '}';
      var tokenStartNum = tokenStart.length;
      var tokenEndNum = 0 - tokenEnd.length;

      editor.widgets.add('token', {
        requiredContent: 'span(cke_token)',

        init: function () {
          // Note that token markup characters are stripped for the name.
          this.setData('name', this.element.getText().slice(tokenStartNum, tokenEndNum));
        },

        data: function () {
          this.element.setText(tokenStart + this.data.name + tokenEnd);
        },

        downcast: function () {
          return new CKEDITOR.htmlParser.text(tokenStart + this.data.name + tokenEnd);
        }

      });

      // This feature does not have a button, so it needs to be registered manually.
      editor.addFeature(editor.widgets.registered.token);

      // Handle dropping a contact by transforming the contact object into HTML.
      // Note: All pasted and dropped content is handled in one event - editor#paste.
      editor.on('paste', function (evt) {
        var token = evt.data.dataTransfer.getData('token');
        if (!token) {
          return;
        }

        evt.data.dataValue = '<span class="cke_token">' + token + '</span>'
      });
    },

    afterInit: function (editor) {
      var tokenStart = '{';
      var tokenEnd = '}';

      var tokenStartRegex = escapeRegExp(tokenStart);
      var tokenEndRegex = escapeRegExp(tokenEnd);
      var tokenReplaceRegex = new RegExp(tokenStartRegex + '([^' + tokenStartRegex + tokenEndRegex + '])+' + tokenEndRegex, 'g');

      editor.dataProcessor.dataFilter.addRules({
        text: function (text, node) {
          var dtd = node.parent && CKEDITOR.dtd[node.parent.name];

          // Skip the case when token is in elements like <title> or <textarea>
          // but upcast token in custom elements (no DTD).
          if (dtd && !dtd.span) {
            return;
          }

          return text.replace(tokenReplaceRegex, function (match) {
            // Creating widget code.
            var widgetWrapper = null,
              innerElement = new CKEDITOR.htmlParser.element('span', {
                'class': 'cke_token'
              });

            // Adds token identifier as innertext.
            innerElement.add(new CKEDITOR.htmlParser.text(match));
            widgetWrapper = editor.widgets.wrapElement(innerElement, 'token');

            // Return outerhtml of widget wrapper so it will be placed
            // as replacement.
            return widgetWrapper.getOuterHtml();
          });
        }
      });
    }

  });

  // Copy-paste from https://sdk.ckeditor.com/samples/draganddrop.html
  CKEDITOR.on('instanceReady', function () {
    if (document.getElementById('tokensList') !== null) {
      CKEDITOR.document.getById('tokensList').on('dragstart', function (evt) {
        var target = evt.data.getTarget().getAscendant('span', true);

        CKEDITOR.plugins.clipboard.initDragDataTransfer(evt);

        var dataTransfer = evt.data.dataTransfer;

        dataTransfer.setData('token', target.getText());

        dataTransfer.setData('text/html', target.getText());

        if (dataTransfer.$.setDragImage) {
          dataTransfer.$.setDragImage(target.$, 0, 0);
        }
      });
    }
  });

  var csrfParam = $('meta[name=csrf-param]').attr('content');
  var csrfToken = $('meta[name=csrf-token]').attr('content');

  var elfNode, elfInsrance, dialogName,
    elfUrl = '/elfinder/connect', // Your connector's URL
    elfDirHashMap = { // Dialog name / elFinder holder hash Map
      image: '',
      flash: '',
      files: '',
      link: '',
      fb: 'l1_Lw' // Fall back target : `/`
    },
    imgShowMaxSize = 400, // Max image size(px) to show
    customData = {},
    // Set image size to show
    setShowImgSize = function (url, callback) {
      $('<img/>').attr('src', url).on('load', function () {
        var w = this.naturalWidth,
          h = this.naturalHeight,
          s = imgShowMaxSize;
        if (w > s || h > s) {
          if (w > h) {
            h = Math.floor(h * (s / w));
            w = s;
          } else {
            w = Math.floor(w * (s / h));
            h = s;
          }
        }
        callback({width: w, height: h});
      });
    },
    // Set values to dialog of CKEditor
    setDialogValue = function (file, fm) {
      var url = fm.convAbsUrl(file.url),
        dialog = CKEDITOR.dialog.getCurrent(),
        dialogName = dialog._.name,
        tabName = dialog._.currentTabId,
        urlObj;
      if (dialogName == 'image') {
        urlObj = 'txtUrl';
      } else if (dialogName == 'flash') {
        urlObj = 'src';
      } else if (dialogName == 'files' || dialogName == 'link') {
        urlObj = 'url';
      } else if (dialogName == 'image2') {
        urlObj = 'src';
      } else {
        return;
      }
      if (tabName == 'Upload') {
        tabName = 'info';
        dialog.selectPage(tabName);
      }
      dialog.setValueOf(tabName, urlObj, url);
      if (dialogName == 'image' && tabName == 'info') {
        setShowImgSize(url, function (size) {
          dialog.setValueOf('info', 'txtWidth', size.width);
          dialog.setValueOf('info', 'txtHeight', size.height);
          dialog.preview.$.style.width = size.width + 'px';
          dialog.preview.$.style.height = size.height + 'px';
          dialog.setValueOf('Link', 'txtUrl', url);
          dialog.setValueOf('Link', 'cmbTarget', '_blank');
        });
      } else if (dialogName == 'image2' && tabName == 'info') {
        dialog.setValueOf(tabName, 'alt', file.name + ' (' + elfInsrance.formatSize(file.size) + ')');
        setShowImgSize(url, function (size) {
          setTimeout(function () {
            dialog.setValueOf('info', 'width', size.width);
            dialog.setValueOf('info', 'height', size.height);
          }, 100);
        });
      } else if (dialogName == 'files' || dialogName == 'link') {
        try {
          dialog.setValueOf('info', 'linkDisplayText', file.name);
        } catch (e) {
        }
      }
    };

  customData[csrfParam] = csrfToken;

  // Setup upload tab in CKEditor dialog
  CKEDITOR.on('dialogDefinition', function (event) {
    var editor = event.editor,
      dialogDefinition = event.data.definition,
      tabCount = dialogDefinition.contents.length,
      browseButton, uploadButton, submitButton, inputId;

    for (var i = 0; i < tabCount; i++) {
      try {
        browseButton = dialogDefinition.contents[i].get('browse');
        uploadButton = dialogDefinition.contents[i].get('upload');
        submitButton = dialogDefinition.contents[i].get('uploadButton');
      } catch (e) {
        browseButton = uploadButton = null;
      }

      if (browseButton !== null) {
        browseButton.hidden = false;
        browseButton.onClick = function (dialog, i) {
          dialogName = CKEDITOR.dialog.getCurrent()._.name;
          if (dialogName === 'image2') {
            dialogName = 'image';
          }
          if (elfNode) {
            if (elfDirHashMap[dialogName] && elfDirHashMap[dialogName] != elfInsrance.cwd().hash) {
              elfInsrance.request({
                data: {cmd: 'open', target: elfDirHashMap[dialogName]},
                notify: {type: 'open', cnt: 1, hideCnt: true},
                syncOnFail: true
              });
            }
            elfNode.dialog('open');
          }
        }
      }

      if (uploadButton !== null && submitButton !== null) {
        uploadButton.hidden = false;
        submitButton.hidden = false;
        uploadButton.onChange = function () {
          inputId = this.domId;
        }
        // upload a file to elFinder connector
        submitButton.onClick = function (e) {
          dialogName = CKEDITOR.dialog.getCurrent()._.name;
          if (dialogName === 'image2') {
            dialogName = 'image';
          }
          var target = elfDirHashMap[dialogName] ? elfDirHashMap[dialogName] : elfDirHashMap['fb'],
            name = $('#' + inputId),
            input = name.find('iframe').contents().find('form').find('input:file'),
            error = function (err) {
              alert(elfInsrance.i18n(err).replace('<br>', '\n'));
            };

          if (input.val()) {
            var fd = new FormData();
            fd.append('cmd', 'upload');
            fd.append('target', target);
            fd.append('overwrite', 0); // Instruction to save alias when same name file exists
            $.each(customData, function (key, val) {
              fd.append(key, val);
            });
            fd.append('upload[]', input[0].files[0]);
            $.ajax({
              url: editor.config.filebrowserUploadUrl,
              type: "POST",
              data: fd,
              processData: false,
              contentType: false,
              dataType: 'json'
            })
              .done(function (data) {
                if (data.added && data.added[0]) {
                  elfInsrance.exec('reload');
                  setDialogValue(data.added[0]);
                } else {
                  error(data.error || data.warning || 'errUploadFile');
                }
              })
              .fail(function () {
                error('errUploadFile');
              })
              .always(function () {
                input.val('');
              });
          }
          return false;
        }
      }
    }
  });

  // Create elFinder dialog for CKEditor
  CKEDITOR.on('instanceReady', function (e) {
    elfNode = $('<div style="padding:0;">');
    elfNode.dialog({
      autoOpen: false,
      modal: true,
      width: '80%',
      title: app.i18n.file_manager,
      create: function (event, ui) {
        var startPathHash = (elfDirHashMap[dialogName] && elfDirHashMap[dialogName]) ? elfDirHashMap[dialogName] : '';
        // elFinder configure
        elfInsrance = $(this).elfinder({
          startPathHash: startPathHash,
          useBrowserHistory: false,
          resizable: false,
          width: '100%',
          url: elfUrl,
          //lang: app.language,
          dialogContained: true,
          getFileCallback: function (file, fm) {
            setDialogValue(file, fm);
            elfNode.dialog('close');
          }
        }).elfinder('instance');
      },
      open: function () {
        elfNode.find('div.elfinder-toolbar input').blur();
        setTimeout(function () {
          elfInsrance.enable();
        }, 100);
      },
      resizeStop: function () {
        elfNode.trigger('resize');
      }
    }).parent().css({'zIndex': '11000'});

    // CKEditor instance
    var cke = e.editor;

    // Setup the procedure when DnD image upload was completed
    /*cke.widgets.registered.uploadimage.onUploaded = function (upload) {
      var self = this;
      setShowImgSize(upload.url, function (size) {
        self.replaceWith('<img src="' + encodeURI(upload.url) + '" width="' + size.width + '" height="' + size.height + '"></img>');
      });
    }*/

    // Setup the procedure when send DnD image upload data to elFinder's connector
    cke.on('fileUploadRequest', function (e) {
      var target = elfDirHashMap['image'] ? elfDirHashMap['image'] : elfDirHashMap['fb'],
        fileLoader = e.data.fileLoader,
        xhr = fileLoader.xhr,
        formData = new FormData();
      e.stop();
      xhr.open('POST', fileLoader.uploadUrl, true);
      formData.append('cmd', 'upload');
      formData.append('target', target);
      formData.append('upload[]', fileLoader.file, fileLoader.fileName);
      xhr.send(formData);
    }, null, null, 4);

    // Setup the procedure when got DnD image upload response
    cke.on('fileUploadResponse', function (e) {
      var file;
      e.stop();
      var data = e.data,
        res = JSON.parse(data.fileLoader.xhr.responseText);
      if (!res.added || res.added.length < 1) {
        data.message = 'Can not upload.';
        e.cancel();
      } else {
        elfInsrance.exec('reload');
        file = res.added[0];
        if (file.url && file.url != '1') {
          data.url = file.url;
          try {
            data.url = decodeURIComponent(data.url);
          } catch (e) {
          }
        } else {
          data.url = elfInsrance.options.url + ((elfInsrance.options.url.indexOf('?') === -1) ? '?' : '&') + 'cmd=file&target=' + file.hash;
        }
        data.url = elfInsrance.convAbsUrl(data.url);
      }
    });
  });

  //
  // CKEditor - Twig Token support implementation - END
  //

  //
  // CKEDITOR --- END
  //

  //
  // SHOP START
  //

  $(document).on('click', '.shop-open-link', function (e) {
    e.preventDefault();
    var el = $(this);
    var form = $('#__visit-form__');
    modal.doRemote(el.attr('href'), 'POST', form.serialize(), {
      contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
      processData: true
    });
  });
  $(document).on('click', '.cart-open-link', function (e) {
    e.preventDefault();
    var el = $(this);
    var form = $('#__visit-form__');
    modal.doRemote(el.attr('href'), 'POST', form.serialize(), {
      contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
      processData: true
    });
  });
  //
  // SHOP END
  //
});

$(function () {
  $(document).on('click', '.show_out_of_stock', filter_tovar);
});

$(function () {
  $(document).on('change', '#cafe-params_id', function () {
    var url = '/cafe/admin/update-vat?params_id=' + this.value;

    var id = $('[name="cafe_id"]');
    if (id.length > 0) {
      url += "&id=" + id.val();
    }

    var info_line = $('.cafe-vat-accounts-update');
    if (info_line.length != 1) {
      return;
    }

    info_line.html('');
    $.get(url, function (data) {
      info_line.html(data);
      console.log(data);
    })
  });

  $(document).on('change', '#cafe-params_id,#cafe-franchisee_id,#franchiseeregistration-params_id', function () {
    var el = $('#cafe-params_id,#franchiseeregistration-params_id');
    if (el.length == 0) return;
    //var url = '/tariffs/admin/get-regional-tariff?params_id=' + el.val();
    var url = '/cafe/admin-params/view?id=' + el.val();

    var info_line = $(".tariff_infoline");
    if (info_line.length != 1) {
      return;
    }

    /*var franchisee = $('#cafe-franchisee_id');
    if (franchisee.length > 0) {
      url += "&franchiseeId=" + franchisee.val();
    }*/
    info_line.html('');
    $.get(url, function (data) {
      info_line.html(data);
      console.log(data);
    })
  });
});

$(function () {
  $(document).on('change', '.__NESTED_CHECKBOX_CHILDREN__ [type="checkbox"]', function (e) {
    var el = $(this);
    var parent = el.closest('.__NESTED_CHECKBOX_PARENT__');
    var children = el.closest('.__NESTED_CHECKBOX_CHILDREN__');

    //var checkboxesCount = children.find('[type="checkbox"]').length;
    var checkboxesCheckedCount = children.find('[type="checkbox"]:checked').length;

    //if (checkboxesCount == checkboxesCheckedCount) {
    if (checkboxesCheckedCount > 0) {
      parent.find('[type="checkbox"]:first').prop('checked', true);
      //console.log('all');
    }
    /*else {
         parent.find('[type="checkbox"]:first').prop('checked', false);
       }*/
  });

  $(document).on('change', '.__NESTED_CHECKBOX_PARENT__ .__main_checkbox__', function (e) {
    var el = $(this);
    var parent = el.closest('.__NESTED_CHECKBOX_PARENT__');
    var ico = parent.find('.children_control i');
    var children = parent.find('.__NESTED_CHECKBOX_CHILDREN__');
    var checked = el.is(':checked');
    children.find('[type="checkbox"]').prop('checked', checked);
    if (checked) {
      ico.removeClass('fa-plus');
      ico.addClass('fa-minus');
      children.slideDown("slow");
    } else {
      //children.hide();
    }
  });

  $(document).on('click', '.__NESTED_CHECKBOX_PARENT__ .children_control', function (e) {
    var el = $(this);
    var ico = el.find('i');
    var parent = el.closest('.__NESTED_CHECKBOX_PARENT__');
    var children = parent.find('.__NESTED_CHECKBOX_CHILDREN__');
    if (ico.hasClass('fa-plus')) {
      ico.removeClass('fa-plus');
      ico.addClass('fa-minus');
      children.slideDown("slow");
    } else {
      ico.addClass('fa-plus');
      ico.removeClass('fa-minus');
      children.slideUp("slow");
    }
  });
});

function filter_tovar(e) {
  $('.shop_item').show();
  if ($('.show_out_of_stock')[0].checked) {
    $('.shop_item.is-not-active').show();
  } else {
    $('.shop_item.is-not-active').hide();
  }

  var els = $('.shop_item:visible');
  var cat = $('[name=filter_cat]');
  if (cat.length > 0 && cat.val().length > 2) {
    els.not('[data-category=' + cat.val() + ']').hide();
    els = els.filter(':visible');
  }

  var name = $('[name=filter_name]');
  if (name.length > 0 && name.val().length > 0) {
    name = clear_for_find(name.val());
    if (name.length > 0)
      for (i = 0; i < els.length; i++) {
        el = els.eq(i);
        t = el.data('title') || "";
        bc = el.data('barcode') || "";

        if (t.toString().indexOf(name) < 0 && bc.toString().indexOf(name) < 0) {
          el.hide();
        }
      }
  }
}

function pruduct_filter_init() {
  $('[name=filter_cat]').on('change', filter_tovar);
  $('[name=filter_name]').on('input', filter_tovar);

  var el = $(".shop-modal-list-products [data-category]");
  var cat_list = [];
  for (i = 0; i < el.length; i++) {
    var n = el.eq(i).data("category");
    if (n.length < 2) continue;
    if (jQuery.inArray(n, cat_list) < 0) {
      cat_list.push(n);
    }
  }
  cat_list.sort();
  if (cat_list.length == 0) {
    $('[name=filter_cat]').parent().hide();
  } else {
    for (i = 0; i < cat_list.length; i++) {
      $('[name=filter_cat]').append($("<option/>", {
        'value': cat_list[i],
        'text': cat_list[i],
      }))
    }
  }
  //console.log(cat_list);
}

function testTaskType() {
  var type = $('#task-type').val();

  $(".control").closest('.form-group').hide(0);


  if (type == 3) {
    $("#task-weekday").closest('.form-group').show(0);
  }
  if (type == 4) {
    $("#task-weekday").closest('.form-group').show(0);
    $("#task-weak_n").closest('.form-group').show(0);
  }

  if (type == 5) {
    $("#task-weak_n").closest('.form-group').show(0);
  }

}

function openPoll(visitor_id, mode) {
  $.get('/polls/default/make', {'visitor_id': visitor_id, 'mode': mode}, function (data) {
    var modal = new ModalRemote('#secondModal');
    //var modal = $('#secondModal').modal('show');
    modal.show();

    var head = modal.modal.find('.modal-header');
    if (head.find('.modal-title').length == 0) {
      head.append('<h4 class="modal-title"/>')
    }
    modal.setContent(data.content);
    modal.setFooter(data.footer);
    modal.modal.find('.modal-title').html(data.title);

    if ($(modal.content).find("form")[0] == "undefined") {
      setTimeout(function () {
        modal.setupFormSubmit(
          $(modal.content).find("form")[0],
          $(modal.content, modal.footer).find('[type="submit"]')[0]
        );
      }, 100);
    }
  }, 'json');
}

$(document).ready(function () {
  $('body').on('beforeSubmit', '#secondModal form', function () {
    var form = $(this);
    if (form.find('.has-error').length) {
      return false;
    }

    $.post(form.attr('action'), form.serialize(), function (data) {
      var modal = $('#secondModal').modal('show');

      var head = modal.find('.modal-header');
      if (head.find('.modal-title').length == 0) {
        head.append('<h4 class="modal-title"/>')
      }
      modal.find('.modal-body').html(data.content);
      modal.find('.modal-footer').html(data.footer);
      modal.find('.modal-title').html(data.title);
    }, 'json');

    return false;
  });
});

function make_time_table_drag_list(resources) {
  $('#external-events').html("");
  for (var i = 0; i < resources.length; i++) {
    var dr_user = $("<div/>");
    dr_user.addClass("fc-event");
    dr_user.html(resources[i].name);
    resources[i].title = resources[i].name;

    //dr_user.css("background", resources[i].color || '#000');
    dr_user.css("background", resources[i].color || '#006ac1');

    $('#external-events').append(dr_user);

    //id=> $u['id'],
    //$r['eventColor']=$u['color'];


    // store data so the calendar knows to render an event upon drop
    dr_user.data('event', {
      title: resources[i].title, // use the element's text as the event title
      stick: true // maintain when user navigates (see docs on the renderEvent method)
    });


    // make the event draggable using jQuery UI
    dr_user.draggable({
      zIndex: 999,
      revert: true,      // will cause the event to go back to its
      revertDuration: 0,  //  original position after the drag
      eventColor: resources[i].eventColor,
    });
    dr_user.data('user', resources[i]);
  }
}

function tt_Render(event, element, view) {
  if (event.allDay === 'true' || event.allDay === true) {
    event.allDay = true;
  } else {
    event.allDay = false;
  }
}

function tt_evetToPost(event, is_new, type) {
  var data = {
    id: event.id || event._id,
    start: event.start.toString().split('GMT')[0]
  };
  if (event.end) {
    data.end = event.end.toString().split('GMT')[0]
  }
  if (is_new && type == "month") {
    var d = new Date(event.start);
    d.setSeconds(0);
    d.setMinutes(0);
    d.setHours(8);

    data.start = d.toString().split('GMT')[0];

    d.setMinutes(0);
    d.setHours(16);
    data.end = d.toString().split('GMT')[0];
  }
  return data;
}

function tt_updateEv(event) {
  var data = tt_evetToPost(event);
  $.post("/timetable/admin/update?id=" + data['id'], data, function (data) {

  }, "json");
}

function tt_eventDragStop(event, jsEvent) {
  if (event.editable === false) return;
  var trashEl = jQuery('#calendarTrash');
  var ofs = trashEl.offset();

  var x1 = ofs.left;
  var x2 = ofs.left + trashEl.outerWidth(true);
  var y1 = ofs.top;
  var y2 = ofs.top + trashEl.outerHeight(true);

  if (jsEvent.pageX >= x1 && jsEvent.pageX <= x2 &&
    jsEvent.pageY >= y1 && jsEvent.pageY <= y2) {

    /*   var decision = confirm("Do you really want to do that?");
     if (decision) {*/
    $.post("/timetable/admin/delete?id=" + event.id);
    $('#calendar').fullCalendar('removeEvents', event.id);

    /*} else {
     }*/
    //$('#calendario').fullCalendar('removeEvents', event.id);
  }
}

function tt_eventReceive(event) {
  event.editable = false;
  event.droppable = false;
  event.className.push("loading");
  event.color = user.color;
  event.id = event._id;
  $('#calendar').fullCalendar('updateEvent', event);

  var data = tt_evetToPost(event, true, this.type);
  data.user = user.id;
  $.post("/timetable/admin/create", data, function (data) {
    var event = tt_getEvantByID(data._id);

    $('#calendar').fullCalendar('renderEvent', {
      title: event.title,
      id: data.id,
      start: data.start,
      end: data.end,
      color: data.color,
      allDay: false,
    });

    $('#calendar').fullCalendar('removeEvents', event.id)
  }, "json");
}

function tt_drop(date, jsEvent, ui, resourceId) {
  var $this = $(this);
  user = $this.data('user');
}

function tt_getEvantByID(id) {
  var events = $('#calendar').fullCalendar('clientEvents');
  for (var i = 0; i < events.length; i++) {
    if (events[i].id == id || events[i]._id == id) return events[i];
  }
  return false;
}

function ajaxForm(el) {
  el.find('.ajaxForm')
    .off('submit')
    .on('submit', function (e) {
      e.preventDefault()
      var form=$(this)

      if (form.yiiActiveForm) {
        form.off('afterValidate');
        form.on('afterValidate', yiiValidation.bind(form));

        form.yiiActiveForm('validate', true);
        var d = form.data('yiiActiveForm');
        if (d) {
          d.validated = true;
          form.data('yiiActiveForm', d);
          form.yiiActiveForm('validate');
          isValid = d.validated;
        }
        e.stopImmediatePropagation();
        e.stopPropagation();
        return false
      }

      isValid = isValid && (form.find('.has-error').length == 0);

      if (!isValid) {
        return false;
      } else {
        e.stopImmediatePropagation();
        e.stopPropagation();

        sendForm(form);
      }

    })

  function yiiValidation(e) {
    var form = this;

    if(form.find('.has-error').length == 0){
      sendForm(form);
    }
    return true;
  }

  function sendForm(form){
    if(form.hasClass('loading'))return;
    form.addClass('loading')
    var data = form.serializeArray();

    form.html('');
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: data,
      success: function(data){
        var res=$('<div>'+data+'</div>').find('form')
        form.after(res.length?res:data);
        form.remove()
        ajaxForm($('body'));
      }
    });
    return
  }
}

$(function () {
  ajaxForm($('body'));
});

function printElem(div) {
    var content = div.innerHTML;
    var mywindow = window.open('', 'Print', 'height=600,width=800');

    mywindow.document.write('<html><head><title>Print</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write(content);
    mywindow.document.write('</body></html>');

    mywindow.document.close();
    mywindow.focus();
    mywindow.print();
    mywindow.close();
    return true;
}

$(function () {
    $(document).on('click', '.print-card', function(){
        var card = $('.visitor-card');
        if (card.length) {
          printElem(card[0]);
        }
    });
});
