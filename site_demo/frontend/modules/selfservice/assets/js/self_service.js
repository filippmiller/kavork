
function setHeiHeight() {
      var self_h = $('.header_self').outerHeight(true);
	  //$('.header_self').css({height: self_h + 'px'});
      var gig = ($(window).outerHeight(true) - self_h);
      //$('.content_self').css({height: gig + 'px'});
	   $('.content_self').outerHeight( gig + 'px');
	   $('.content_self').css('line-height', gig +'px');
    }
   setHeiHeight(); // устанавливаем высоту окна при первой загрузке страницы
   $(window).resize(setHeiHeight); 
   
/**
 * Created by acid on 19.10.18.
 */


// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        }, wait);
        if (immediate && !timeout) func.apply(context, args);
    };
}

function typeahead_existing_user_select(event, suggestion) {
    $('.existing_user_visitor_id').val(suggestion.id);
    $('.existing_user_email').val(suggestion.email);
    $('#' + event.target.id).prop('readonly', true);
    //$('#' + event.target.id).typeahead('close');
}

function touchspin_new_user_mature_change() {
    var width = (this.value-1) * 91;
    $('._indicator_guest_').css('width', width);
    var val = parseInt($("#selfservicenewuser-guest_m").val()) + parseInt($("#selfservicenewuser-guest_chi").val());

    //$('#result4').html('<div class="col-md-3 padding-off-right">Person :</div><div class="col-md-9 padding-off-left">'+val+'</div>')
}

$(document).ready(function () {

    console.log('Self service start');

    setInterval(function () {
        $.get('/selfservice/default/keep-alive');
    }, 30000);

    var form_new_user = $('#self-service-new-user-form');
    var form_existing_user = $('#self-service-existing-user-form');
    var form_checkout_user = $('#self-service-checkout-user-form');

    if (form_new_user.length) {
        $('.step:not(.hidden)').find('input:first').focus();

        var submit = false;
        var submitTime = 0;
        var try_next_step = false;

        var guest_m_input = $("#selfservicenewuser-guest_m");
        var guest_chi_input = $("#selfservicenewuser-guest_chi");

        guest_m_input.on('change', function() {
            var width = (this.value) * 91;
            $('._indicator_guest_').css('width', width);
            var count = parseInt(guest_m_input.val()) + parseInt(guest_chi_input.val()) + 1;
            $('._report_persons').text(count);
        });

        guest_chi_input.on('change', function () {
            var width = (this.value) * 91;
            $('._indicator_children_').css('width', width);
            var count = parseInt(guest_m_input.val()) + parseInt(guest_chi_input.val()) + 1;
            $('._report_persons').text(count);
        });

        form_new_user.on('afterValidate', function (event, messages) {
            if (!try_next_step) {
                return;
            }
            try_next_step = false;
            var screenHasError = false;

            $.each(messages, function (fieldId, fieldError) {
                if (fieldError.length) {
                    var field = $('#' + fieldId + ':visible');
                    if (field.length) {
                        screenHasError = true;
                        field.effect('shake', 1000);
                        app.notify(fieldError, 'warning');
                    }
                }
            });

            if (screenHasError == false) {
                if (!isLastStep()) {
                    stepShowNext();
                    submit = false;
                }
                updateConfirmData();
            }
        }).on('beforeSubmit', function () {
            if (!submit && isLastStep()) {
                submit = true;
            } else if (submit && isLastStep()) {
                var time = new Date().getTime();
                if (time - submitTime < 10000) {
                    return false;
                }
                submitTime = time;
                return true;
            }

            return false;
        });

        function updateConfirmData() {
            var first_name = $('#selfservicenewuser-first_name').val();
            var last_name = $('#selfservicenewuser-last_name').val();
            var email = $('#selfservicenewuser-email').val();

            var report_name = $('._report_full_name');
            var report_email = $('._report_email');

            report_name.text(' ' + first_name + ' ' + last_name);

            if (email != '') {
                report_email.closest('span').removeClass('hidden');
                report_email.text(email);
            } else {
                report_email.closest('span').addClass('hidden');
            }
        }

        function isLastStep() {
            var current_step = $('.step:not(.hidden)');
            var current_step_id = current_step.attr('data-step-id');
            var next_step_id = parseInt(current_step_id) + 1;
            var next_step = $('.step[data-step-id="' + next_step_id + '"]');
            return !next_step.length;
        }

        function stepShowNext() {
            var current_step = $('.step:not(.hidden)');
            var current_step_desc = $('#steps .current');
            var current_step_id = current_step.attr('data-step-id');
            var next_step_id = parseInt(current_step_id) + 1;
			
            current_step.addClass('hidden');
			current_step.removeClass('fadeOut');
            var next_step = $('.step[data-step-id="' + next_step_id + '"]');
            next_step.removeClass('hidden');
			next_step.addClass('fadeIn');
            next_step.find('input:first').focus();

            current_step_desc.removeClass('current');
            $('#steps li[data-step-id="' + next_step_id + '"]').addClass('current');
        }

        function stepShowPrev() {
            var current_step = $('.step:not(.hidden)');
            var current_step_desc = $('#steps .current');
            var current_step_id = current_step.attr('data-step-id');
            if (!current_step_id || current_step_id == 1) {
                return;
            }
            var next_step_id = parseInt(current_step_id) - 1;

            current_step.addClass('hidden');
			current_step.removeClass('fadeOut');
            var next_step = $('.step[data-step-id="' + next_step_id + '"]');
            next_step.removeClass('hidden');
			next_step.addClass('fadeIn');
            next_step.find('input:first').focus();

            current_step_desc.removeClass('current');
            $('#steps li[data-step-id="' + next_step_id + '"]').addClass('current');
        }

        $('.step_commands a.next').on('click', function (e) {
            e.preventDefault();
            try_next_step = true;
            form_new_user.yiiActiveForm('validate', true);
        });

        $('.step_commands a.prev').on('click', function (e) {
            e.preventDefault();
            stepShowPrev();
        });

        document.addEventListener("keyup", function(event) {
            if (event.key == "Enter") {
                try_next_step = true;
                form_new_user.yiiActiveForm('validate', true);
            }
        });
    }

    if (form_existing_user.length) {
        var name_input = form_existing_user.find('.__existing_user_name_input__');

        var guest_m_input = $("#selfserviceexistinguser-guest_m");
        var guest_chi_input = $("#selfserviceexistinguser-guest_chi");

        guest_m_input.on('change', function() {
            var width = (this.value) * 91;
            $('._indicator_guest_').css('width', width);
            var count = parseInt(guest_m_input.val()) + parseInt(guest_chi_input.val()) + 1;
            $('._report_persons').text(count);
        });

        guest_chi_input.on('change', function () {
            var width = (this.value) * 91;
            $('._indicator_children_').css('width', width);
            var count = parseInt(guest_m_input.val()) + parseInt(guest_chi_input.val()) + 1;
            $('._report_persons').text(count);
        });

        form_existing_user.on('reset', function () {
            name_input.typeahead('val', '');
            name_input.prop('readonly', false);

            $('._indicator_guest_').css('width', 0);
            $('._indicator_children_').css('width', 0);
            $('._report_persons').text(0);
        });

        form_existing_user.on('afterValidate', function (event, messages, errors) {
            $.each(messages, function (fieldId, fieldError) {
                if (fieldError.length) {
                    app.notify(fieldError, 'warning');
                    return false;
                }
            });

            return true;
        });
    }

    if (form_checkout_user.length) {
        var name_input = form_checkout_user.find('.__checkout_user_name_input__');
        var visits_wrapper = $('#finded_visits_wrapper');

        function findVisits(name) {
            if(visits_wrapper.data('name')==name)return;
            visits_wrapper.data('name',name);

            visits_wrapper.html('');

            $.ajax({
                url: '/selfservice/default/find-active-visits',
                method: 'get',
                data: {term: name},
            }).done(function (visits) {
                if($('.__checkout_user_name_input__').val()!==name) return;
                visits_wrapper.html('')
                $.each(visits, function (index, visit) {
                    var view = template.render('self_service_checkout_visit', visit);
                    visits_wrapper.append(view);
                });
            });
        }

        name_input.on('change keyup', function (e) {
            var el = $(this);
            var value = el.val();
            var value_length = value.trim().length;

            if (value_length >= 3) {
                debounce(findVisits(value))
            } else {
                visits_wrapper.html('');
            }
        });
    }

}); 
