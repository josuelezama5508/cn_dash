/*!
 * CTRL-NUMBER v.1.2
 * Author: Carlos OrtÃ­z
 * Website: http://desarrolladores.plumillanegra.com/jcarlosortiz/
 * Contact: tec.carlosortiz@gmail.com
 *
 * Copyright 2016-2017 ctrl-number
 * License: MIT
 */
/// CTRL-NUMBER ACTION ////
function ctrlListener() {
  var t = $(this),
    n = $(this).find("button:first-child"),
    i = $(this).find("button:last-child"),
    e = $(this).find("input");
  n.click(function () {
    var n = parseInt(t.attr("min")),
      i = (parseInt(t.attr("max")), t.getValue() - 1);
    (i = n > i ? n : i), t.setValue(i), e.trigger("change");
  }),
    i.click(function () {
      var n = (parseInt(t.attr("min")), parseInt(t.attr("max"))),
        i = t.getValue() + 1;
      (i = i > n ? n : i), t.setValue(i), e.trigger("change");
    }),
    e.on("keypress", function (t) {
      var n = window.event ? window.event.keyCode : t.which;
      return 8 == n || 46 == n ? !0 : /\d/.test(String.fromCharCode(n));
    }),
    e.change(function () {
      var n = parseInt(t.attr("min")),
        i = parseInt(t.attr("max")),
        e = $(this).val();
      (null == e || "" == e) && (e = 0),
        (e = n > e ? n : e),
        (e = e > i ? i : e),
        $(this).val(e),
        t.trigger("change");
    });
}
($.prototype.getValue = function () {
  return $(this).hasClass("ctrl-number")
    ? parseInt($(this).find("input").val())
    : $(this).hasClass("atcomplete")
    ? $(this).attr("data-value")
    : void 0;
}),
  ($.prototype.setValue = function (t) {
    return $(this).hasClass("ctrl-number")
      ? parseInt($(this).find("input").val(t))
      : void ($(this).hasClass("atcomplete") && $(this).attr("data-value", t));
  }),
  ($.prototype.onChange = function (t) {
    $(this).find("button").click(t), $(this).find("input").change(t);
  }),
  ($.prototype.lock = function () {
    $(this).find("button").prop("disabled", !0),
      $(this).find("input").prop("disabled", !0);
  }),
  ($.prototype.unlock = function () {
    $(this).find("button").prop("disabled", !1),
      $(this).find("input").prop("disabled", !1);
  }),
  $(".ctrl-number").each(ctrlListener),
  ($.prototype.ctrlNumber = function () {
    $(this).each(ctrlListener);
  });
