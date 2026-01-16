var isMouseDown = false;
var isMouseDownSt = false;
var hs

$('input[name=time_range]').click(function () {
  $(this).parent().find('.tooltip').addClass('show')
  return false;
});

$(document)[0].onmouseup = (function () {
  $('.tooltip.show').removeClass('show');
});
var tooltip = $('.tooltip');
if (tooltip.length > 0) {
  tooltip[0].onmouseup = function (e) {
    e.preventDefault();
    e.stopPropagation();
    return false;
  };
}

function getReport() {
  if ($("#time_all:checked").length > 0) {
    t_r = $("[for=time_all]").text();
  } else {
    t_r = $("[name=begin_time]").val() + " - " + $("[name=end_time]").val()
  }
  $('[name="time_range"]').val(t_r);

  $('#report_result').html("<img src=\"/img/loading.gif\" class=\"loading_gif\" >");
  $('.panel-body').css('overflow-x', 'visible');
  $.post("/report/admin/get", $('#report_filter').serialize(), function (data) {
    $('#report_result').html(data);
  })
}

//getReport();
$('#report_filter').find('input,select').on('change', getReport);

$('#transactions_filter').find('input,select').on('change', getTransaction);

function ChartMouseOver() {
  $('.duration_table  [code=' + this.id + ']').addClass('highlighted_hover')
}

function ChartMouseOut() {
  $('.duration_table  [code=' + this.id + ']').removeClass('highlighted_hover')
}

function clear_sel_dur() {
  $('.duration_table tbody tr.highlighted').removeClass('highlighted')
  calc_durat('<tr>')
}

function calc_durat(el) {
  el = $(el)
  sel = $('.duration_table tbody tr.highlighted')
  if (sel.length > 0) {

    c = 0
    p = 0
    for (i = 0; i < sel.length; i++) {
      t = $(sel[i]).attr('code');
      c += $(sel[i]).attr('cnt') * 1;
      p += $(sel[i]).attr('pers') * 1;
    }
    t = el.position()
    $('.dur_tbl .info_block .person span').text(c)
    $('.dur_tbl .info_block .dolia span').text(p.toFixed(2))
    $('.dur_tbl .info_block').show().css('top', t.top - 70)
  } else {
    $('.dur_tbl .info_block').hide()
  }
}

$(document).mouseup(function () {
  isMouseDown = false;
  /*calc_durat('<tr>')*/
});

function duration_init() {

  $('.duration_table tbody tr').mousedown(function () {
    isMouseDown = true;
    //$('.duration_table tbody tr').removeClass("highlighted");
    if ($(this).hasClass("highlighted")) {
      $(this).removeClass("highlighted");
      isMouseDownSt = false
    } else {
      $(this).addClass("highlighted");
      isMouseDownSt = true
    }
    calc_durat(this)
    //hs.series[0].data[$(this).attr('code')].select();
    return false; // prevent text selection
  })
    .mouseover(function () {
      if (isMouseDown) {
        if (isMouseDownSt)
          $(this).addClass("highlighted");
        else
          $(this).removeClass("highlighted");
        calc_durat(this)
      }
      //hs.series[0].data[$(this).attr('code')].select();
    })
}

function getTransaction() {
  $('#transactions_result').html("<img src=\"/img/loading.gif\" class=\"loading_gif\" >");
  //$('#transactions_result').addClass('loading');
  $.post("/report/admin/transactions", $('#transactions_filter').serialize(), function (data) {
    $('#transactions_result').html(data);
  });
};
