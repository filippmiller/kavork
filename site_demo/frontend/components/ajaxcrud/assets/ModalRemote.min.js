/*!
 * Modal Remote
 * =================================
 * Use for johnitvn/yii2-ajaxcrud extension
 * @author John Martin john.itvn@gmail.com
 */
function ModalRemote(modalId, parent_modal) {

  if (parent_modal && $(parent_modal).hasClass('modal')) {
    this.parentModal = parent_modal;
  }

  if (typeof($.fn.hasAttr) == 'undefined') {
    $.fn.hasAttr = function (name) {
      return this.attr(name) !== undefined;
    };
  }

  this.defaults = {
    okLabel: app.i18n.ok || "OK",
    executeLabel: app.i18n.execute || "Execute",
    cancelLabel: app.i18n.cancel || "Cancel",
    loadingTitle: app.i18n.loading || "Loading"
  };

  this.modal = $(modalId);

  if (this.modal.length == 0) {
    var modal = '<div id="' + modalId.substr(1) + '" class="fade modal new_modal" role="dialog" tabindex="-1">' +
      '<div class="modal-dialog "><div class="modal-content">' +
      '<div class="modal-header">' +
      '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
      '</div>' +
      '<div class="modal-body"></div>' +
      '<div class="modal-footer"></div>' +
      '</div>' +
      '</div>' +
      '</div>';
    $('body').append(modal);
    this.modal = $(modalId);
  }

  this.dialog = $(modalId).find('.modal-dialog');

  this.header = $(modalId).find('.modal-header');

  this.content = $(modalId).find('.modal-body');

  this.footer = $(modalId).find('.modal-footer');

  this.loadingContent = '<div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>';


  /**
   * Show the modal
   */
  this.show = function () {
    this.clear();
    $(this.modal).modal('show');
  };

  /**
   * Hide the modal
   */
  this.hide = function () {
    $(this.modal).modal('hide');
  };

  /**
   * Toogle show/hide modal
   */
  this.toggle = function () {
    $(this.modal).modal('toggle');
  };

  /**
   * Clear modal
   */
  this.clear = function () {
    $(this.modal).find('.modal-title').remove();
    $(this.content).html("");
    $(this.footer).html("");
  };

  /**
   * Set size of modal
   * @param {string} size large/normal/small
   */
  this.setSize = function (size) {
    $(this.dialog).removeClass('modal-lg');
    $(this.dialog).removeClass('modal-sm');
    if (size == 'large')
      $(this.dialog).addClass('modal-lg');
    else if (size == 'small')
      $(this.dialog).addClass('modal-sm');
    else if (size !== 'normal')
      console.warn("Undefined size " + size);
  };

  /**
   * Set modal header
   * @param {string} content The content of modal header
   */
  this.setHeader = function (content) {
    $(this.header).html(content);
  };

  /**
   * Set modal content
   * @param {string} content The content of modal content
   */
  this.setContent = function (content) {
    $(this.content).html(content);
  };

  /**
   * Set modal footer
   * @param {string} content The content of modal footer
   */
  this.setFooter = function (content) {
    $(this.footer).html(content);
  };

  /**
   * Set modal footer
   * @param {string} title The title of modal
   */
  this.setTitle = function (title) {
    // remove old title
    $(this.header).find('h4.modal-title').remove();
    // add new title
    $(this.header).append('<h4 class="modal-title">' + title + '</h4>');
  };

  /**
   * Hide close button
   */
  this.hidenCloseButton = function () {
    //$(this.modal).modal({keyboard: false });
    $(this.header).find('button.close').hide();
    var params = this.modal.data('bs.modal');
    if (!params) return;
    params.options.keyboard = false;
    this.modal.off('keydown.dismiss.bs.modal');
  };

  /**
   * Show close button
   */
  this.showCloseButton = function () {
    //$(this.modal).modal({ keyboard: true });
    var params = this.modal.data('bs.modal');
    if (!params) return;
    params.options.keyboard = true;
    $(this.header).find('button.close').show();
    params.escape();
  };

  /**
   * Show loading state in modal
   */
  this.displayLoading = function () {
    this.setContent(this.loadingContent);
    this.setTitle(this.defaults.loadingTitle);
  };

  /**
   * Add button to footer
   * @param string label The label of button
   * @param string classes The class of button
   * @param callable callback the callback when button click
   */
  this.addFooterButton = function (label, type, classes, callback) {
    buttonElm = document.createElement('button');
    buttonElm.setAttribute('type', type === null ? 'button' : type);
    buttonElm.setAttribute('class', classes === null ? 'btn btn-primary' : classes);
    buttonElm.innerHTML = label;
    var instance = this;
    $(this.footer).append(buttonElm);
    if (callback !== null) {
      $(buttonElm).click(function (event) {
        callback.call(instance, this, event);
      });
    }
  };

  /**
   * Send ajax request and wraper response to modal
   * @param {string} url The url of request
   * @param {string} method The method of request
   * @param {object} data of request
   * @param {object} params of ajax request
   */
  this.doRemote = function (url, method, data, params) {
    var instance = this;

    this.displayLoading();
    this.setFooter('');

    var ajaxParams = $.extend({
      url: url,
      method: method,
      data: data,
      async: false,
      beforeSend: function () {
        beforeRemoteRequest.call(instance);
      },
      error: function (response) {
        errorRemoteResponse.call(instance, response);
      },
      success: function (response) {
        successRemoteResponse.call(instance, response);
      },
      contentType: false,
      cache: false,
      processData: false
    }, params);
    setTimeout($.ajax, 50, ajaxParams);
  };

  /**
   * Before send request process
   * - Ensure clear and show modal
   * - Show loading state in modal
   */
  function beforeRemoteRequest() {
    this.show();
    this.displayLoading();
  }


  /**
   * When remote sends error response
   * @param {string} response
   */
  function errorRemoteResponse(response) {
    this.setTitle(response.status + response.statusText);
    this.setContent(response.responseText);
    this.addFooterButton('Close', 'button', 'btn btn-default', function (button, event) {
      this.hide();
    })
  }

  /**
   * When remote sends success response
   * @param {string} response
   */
  function successRemoteResponse(response) {

    // Reload datatable if response contain forceReload field
    if (response.forceReload !== undefined && response.forceReload) {
      var pjax_options = {
        container: response.forceReload == 'true' ? '#crud-datatable' : response.forceReload,
      };

      if (response.pjax) {
        pjax_options = Object.assign(pjax_options, response.pjax);
      }

      if ($(pjax_options.container).length > 0) {
        var url = $(pjax_options.container).data('url');
        if (url) {
          pjax_options.url = url;
          pjax_options.history = false;
        }
        $.pjax.reload(pjax_options);
      }
    }

    if (response.content !== undefined)
      this.setContent(response.content);

    if(response.reloadPageOnClose){
      this.reloadPageOnClose = true;
    }
    // Close modal if response contains forceClose field
    if (response.forceClose !== undefined && response.forceClose) {
      this.hide();
      return;
    }

    if (response.modalRedirect !== undefined && response.modalRedirect) {
      this.doRemote(
        response.modalRedirect,
        'GET',
        null
      );
      return;
    }

    if (response.redirect !== undefined && response.redirect) {
      location.href = response.redirect;
      return;
    }

    if (typeof (response.closeButton)!= 'undefined' && !response.closeButton) {
      this.hidenCloseButton();
    } else {
      this.showCloseButton();
    }

    if (response.size !== undefined) {
      this.setSize(response.size);
    }

    if (response.title !== undefined) {
      this.setTitle(response.title);

    }

    if (response.footer !== undefined)
      this.setFooter(response.footer);

    if ($(this.content).find("form")[0] !== undefined) {
      this.setupFormSubmit(
        $(this.content).find("form")[0],
        $(this.footer).find('[type="submit"]')[0]
      );
    }
  }

  /**
   * Prepare submit button when modal has form
   * @param {string} modalForm
   * @param {object} modalFormSubmitBtn
   */
  this.setupFormSubmit = function (modalForm, modalFormSubmitBtn) {

    if (modalFormSubmitBtn === undefined) {
      // If submit button not found throw warning message
      console.warn('Modal has form but does not have a submit button');
    } else {
      var instance = this;

      // Submit form when user clicks submit button
      $(modalFormSubmitBtn).click(function (e) {
        var data;

        // Test if browser supports FormData which handles uploads
        if (window.FormData) {
          data = new FormData($(modalForm)[0]);
        } else {
          // Fallback to serialize
          data = $(modalForm).serializeArray();
        }

        instance.doRemote(
          $(modalForm).attr('action'),
          $(modalForm).hasAttr('method') ? $(modalForm).attr('method') : 'GET',
          data
        );
      });
    }
  };

  /**
   * Show the confirm dialog
   * @param {string} title The title of modal
   * @param {string} message The message for ask user
   * @param {string} okLabel The label of ok button
   * @param {string} cancelLabel The class of cancel button
   * @param {string} size The size of the modal
   */
  this.submitConfirmModal = function (title, message, okLabel, cancelLabel, size) {
    var form = $(this.modal).find('form');
    var formAction = form.attr('action');
    var formMethod = form.attr('method');
    var formData = form.serialize();

    this.show();
    this.setSize(size);

    if (title !== undefined) {
      this.setTitle(title);
    }
    // Add form for user input if required
    this.setContent('<form id="ModalRemoteConfirmForm">' + message);

    var instance = this;
    this.addFooterButton(
      okLabel === undefined ? this.defaults.okLabel : okLabel,
      'submit',
      'btn btn-primary',
      function (e) {
        instance.doRemote(
          formAction,
          formMethod,
          formData,
          {
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            processData: true
          }
        );
      }
    );

    this.addFooterButton(
      cancelLabel === undefined ? this.defaults.cancelLabel : cancelLabel,
      'button',
      'btn btn-default pull-left',
      function (e) {
        this.hide();
      }
    );

  }

  /**
   * Show the confirm dialog
   * @param {string} title The title of modal
   * @param {string} message The message for ask user
   * @param {string} okLabel The label of ok button
   * @param {string} cancelLabel The class of cancel button
   * @param {string} size The size of the modal
   * @param {string} dataUrl Where to post
   * @param {string} dataRequestMethod POST or GET
   * @param {number[]} selectedIds
   */
  this.confirmModal = function (title, message, okLabel, cancelLabel, size, dataUrl, dataRequestMethod, selectedIds) {
    this.show();
    this.setSize(size);

    if (title !== undefined) {
      this.setTitle(title);
    }
    // Add form for user input if required
    this.setContent('<form id="ModalRemoteConfirmForm">' + message);

    var instance = this;
    this.addFooterButton(
      okLabel === undefined ? this.defaults.okLabel : okLabel,
      'submit',
      'btn btn-primary',
      function (e) {
        var data;

        // Test if browser supports FormData which handles uploads
        if (window.FormData) {
          data = new FormData($('#ModalRemoteConfirmForm')[0]);
          if (typeof selectedIds !== 'undefined' && selectedIds)
            data.append('pks', selectedIds.join());
        } else {
          // Fallback to serialize
          data = $('#ModalRemoteConfirmForm');
          if (typeof selectedIds !== 'undefined' && selectedIds)
            data.pks = selectedIds;
          data = data.serializeArray();
        }

        instance.doRemote(
          dataUrl,
          dataRequestMethod,
          data
        );
      }
    );

    this.addFooterButton(
      cancelLabel === undefined ? this.defaults.cancelLabel : cancelLabel,
      'button',
      'btn btn-default pull-left',
      function (e) {
        this.hide();
      }
    );

  }

  /**
   * Open the modal
   * HTML data attributes for use in local confirm
   *   - href/data-url         (If href not set will get data-url)
   *   - data-request-method   (string GET/POST)
   *   - data-confirm-ok       (string OK button text)
   *   - data-confirm-cancel   (string cancel button text)
   *   - data-confirm-title    (string title of modal box)
   *   - data-confirm-message  (string message in modal box)
   *   - data-modal-size       (string small/normal/large)
   * Attributes for remote response (json)
   *   - forceReload           (string reloads a pjax ID)
   *   - forceClose            (boolean remote close modal)
   *   - closeButton            (boolean show/hide header close button)
   *   - modalRedirect         (string url to redirect modal)
   *   - size                  (string small/normal/large)
   *   - title                 (string/html title of modal box)
   *   - content               (string/html content in modal box)
   *   - footer                (string/html footer of modal box)
   * @params {elm}
   */
  this.open = function (elm, bulkData) {
    /**
     * Show either a local confirm modal or get modal content through ajax
     */
    if ($(elm).hasAttr('data-submit-confirm-title') || $(elm).hasAttr('data-submit-confirm-message')) {
      this.submitConfirmModal(
        $(elm).attr('data-submit-confirm-title'),
        $(elm).attr('data-submit-confirm-message'),
        $(elm).attr('data-confirm-ok'),
        $(elm).attr('data-confirm-cancel'),
        $(elm).hasAttr('data-modal-size') ? $(elm).attr('data-modal-size') : 'normal'
      )
    } else if ($(elm).hasAttr('data-confirm-title') || $(elm).hasAttr('data-confirm-message')) {
      this.confirmModal(
        $(elm).attr('data-confirm-title'),
        $(elm).attr('data-confirm-message'),
        $(elm).attr('data-confirm-ok'),
        $(elm).attr('data-confirm-cancel'),
        $(elm).hasAttr('data-modal-size') ? $(elm).attr('data-modal-size') : 'normal',
        $(elm).hasAttr('href') ? $(elm).attr('href') : $(elm).attr('data-url'),
        $(elm).hasAttr('data-request-method') ? $(elm).attr('data-request-method') : 'GET',
        bulkData
      )
    } else {
      this.doRemote(
        $(elm).hasAttr('href') ? $(elm).attr('href') : $(elm).attr('data-url'),
        $(elm).hasAttr('data-request-method') ? $(elm).attr('data-request-method') : 'GET',
        bulkData
      );
    }
  }

  this.modal.on('hidden.bs.modal', function () {
    if(this.reloadPageOnClose){
      location.reload()
    }
    if (this.modal.hasClass('new_modal')) {
      this.modal.nextAll().remove();
      this.modal.remove();
    }

    /*if(this.parentModal && this.parentModal){

    }*/
    if ($('.modal:visible').length > 0) {
      $('body').addClass('modal-open');
    }
  }.bind(this));

} // End of Object
