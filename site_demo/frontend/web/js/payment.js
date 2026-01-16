var ws;

function init_ws() {
  if (ws && ws.readyState == 1) return;

  try {
    ws = new WebSocket('ws://127.0.0.1:9090');
  } catch (err) {
    return;
  }

  ws.onopen = function () {
    console.log('WebSocket Connect');
  };
  ws.onerror = function (error) {
    console.log('WebSocket Error ', error);
  };
  ws.onclose = function () {
    console.log('WebSocket connection closed');
    //setTimeout(init_ws,2000);
  };
  ws.onmessage = function (e) {
    console.log('Server: ', e.data);
    var data = JSON.parse(e.data);

    if (typeof(data) != "object") return;

    if (data.status == 0) {
      msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FF2E12;" class="msg_notifstyle"></span> ' + lang[16] + '</h4>')
      $('#ajaxCrudModal .modal-footer').removeClass('loading')
      $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[2] + '</div>')
      $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" data-dismiss="modal" class="btn btn-default">' + lang[14] + '</button>')
      $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
      return;
    }

    if (data.status == 1) {
      msg_err('<h4 class="msg_h4"><span style="color:#0455A8;" class="msg_notifstyle"> ' + lang[17] + '</span></h4>')
      $('#ajaxCrudModal .modal-footer').removeClass('loading')
      $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#0455A8;" class="notif_payment">' + lang[5] + '</div>' + '<img src="/img/open_pay.gif" width="150" height="150" class="img_terminal">')
      return;
    }

    if (data.status == 2) {
      msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[19] + '</h4>')
      $('#ajaxCrudModal .modal-footer').removeClass('loading')
      $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment" >' + lang[4] + '</div>' + '<img src="/img/close_pay.gif" width="150" height="110" class="img_terminal">')
      $('#ajaxCrudModal .modal-footer').append('<hr><button type="button"  style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
      $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
      return;
    }
    if (data.status == 3 && last_status_terminal !== data.code) {
      last_status_terminal = data.code;
      if (data.code == "333") {
        //$('#ajaxCrudModal .modal-footer').addClass('loading')
        // $('#ajaxCrudModal .modal-footer').html(lang[7])
        //
        $('.form-group input').val('')
        $('#search_user').text('')
        $.post("/selfservice/default/payment_status", {visit_id: last_code}, function (data) {
          msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-ok" style="color:#7BAD18;" class="msg_notifstyle"></span>&nbsp;&nbsp;' + lang[22] + '</h4>')
          $('#ajaxCrudModal .modal-footer').addClass('loading')
          $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#7BAD18;" class="notif_payment">' + lang[8] + '</div>')
          $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" data-dismiss="modal" class="btn btn-default">' + lang[14] + '</button>')
          setTimeout(function () {
            $('.close').click()
          }, 5000)
          setTimeout(function () {
            $('#ajaxCrudModal .modal-footer').removeClass('loading');
          }, 5000)
        }).fail(function () {
          msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[23] + '</h4>')
          $('#ajaxCrudModal .modal-footer').removeClass('loading')
          $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[9] + '</div>')
          $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" data-dismiss="modal" class="btn btn-default">' + lang[14] + '</button>')
        })
        return;
      }
      if (data.code == '000') {
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#0455A8;margin-bottom:15px;" class="notif_payment">' + lang[21] + '</div>' + '<img src="/img/close_pay.gif" width="150" height="110" class="img_terminal">')
        return;
      }
      if (data.code == '100') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FF2E12;" class="msg_notifstyle"></span> ' + lang[18] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[10] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }
      if (data.code == '110') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[25] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[26] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }
      if (data.code == '120') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[11] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#0455A8;" class="notif_payment">' + lang[11] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }
      if (data.code == '130') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-time" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[24] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[12] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }
      if (data.code == '140') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[27] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[28] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }
      if (data.code == '150') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[29] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[30] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }
      if (data.code == '160') {
        msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FA6800;" class="msg_notifstyle"></span> ' + lang[31] + '</h4>')
        $('#ajaxCrudModal .modal-footer').removeClass('loading')
        $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[32] + '</div>')
        $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
        $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
        return;
      }

      msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#FF2E12;" class="msg_notifstyle"></span> ' + lang[25] + '</h4>')
      $('#ajaxCrudModal .modal-footer').removeClass('loading')
      $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[4] + '</div>')
      $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default btn-lg">' + lang[14] + '</button>')
      $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
    }

    if (data.status == 4) {
      msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-exclamation-sign" style="color:#0455A8;" class="msg_notifstyle"></span> ' + lang[20] + '</h4>')
      $('#ajaxCrudModal .modal-footer').removeClass('loading')
      $('#ajaxCrudModal .modal-footer').html(('<div style="background-color:#0455A8;" class="notif_payment">' + lang[6] + '</div>'))
      $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" style="float:left;" data-dismiss="modal" class="btn btn-default">' + lang[14] + '</button>')
      $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>')
      //setTimeout(function(){$('.close').click()},5000)
      //setTimeout(function(){$('#ajaxCrudModal .modal-footer').removeClass('loading');},5000)
      return;
    }
  };
};
setInterval(init_ws, 10000);
var last_code;

function send_pay_to_terminal() {
  last_code = last_pay.order;
  $('#ajaxCrudModal .modal-footer').addClass('loading')
  // $('#ajaxCrudModal .modal-footer').html(lang[1])
  last_status_terminal = false

  if (ws.readyState != 1) {
    msg_err('<h4 class="msg_h4"><span class="glyphicon glyphicon-ban-circle" style="color:#FF2E12;" class="msg_notifstyle"></span> ' + lang[13] + '</h4>');
    $('#ajaxCrudModal .modal-footer').removeClass('loading');
    $('#ajaxCrudModal .modal-footer').html('<div style="background-color:#FA6800;" class="notif_payment">' + lang[3] + '</div>');
    $('#ajaxCrudModal .modal-footer').append('<hr><button type="button" data-dismiss="modal" style="float:left;" class="btn btn-default btn-lg">' + lang[14] + '</button>');
    $('#ajaxCrudModal .modal-footer').append('<button type="button" style="float:right;" class="btn btn-success estimation btn-lg" onclick="send_pay_to_terminal()"> ' + lang[15] + '</button>');
    return;
  }

  ws.send(JSON.stringify(last_pay));
}

var last_status_terminal = false;
var terminal_timer = false;


var close_modal_timer_max = 10;
var close_modal_timer_cnt = 0;

function close_modal_timer() {
  return;
  $btn = $('.modal .btn-default');
  if ($btn.length > 0 && $btn.parentsUntil('#ajaxCrudModal').parent().css('display') !== 'none') {
    if (close_modal_timer_cnt < close_modal_timer_max) {
      close_modal_timer_cnt++
    } else {
      $btn.click();
      close_modal_timer_cnt = 0;
    }
  } else {
    close_modal_timer_cnt = 0
  }
}

function msg_err(str) {
  $.notify(str, {type: 'white'});
}