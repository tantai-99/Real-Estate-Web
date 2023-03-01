(function () {

  var adminApp = window.adminApp = {};

  /**
   * 管理画面用のモーダルオブジェクト
   *
   * @type {{}}
   */
  adminApp.modal = {};

  adminApp.modal.popup = function (options) {
    var modal = app.modal.popup(options);
    modal.$el.find('.modal-contents').addClass('group-setting modal-scroll');
    return modal;
  };

  adminApp.modal.alert = function (title, message, onClose) {
    var modal = app.modal.alert(title, message, onClose);
    modal.$el.find('.modal-contents').addClass('group-setting modal-scroll');
    return modal;
  };
})();