(function($, Proud) {
  Proud.behaviors.proud_issue = {
    attach: function(context, settings) {


function changeType() {
  $('#agency_url_wrapper, #post_menu_wrapper, #wr_editor_tabs, .wr-editor-tab-content').hide();
  var type = $('input[name=issue_category_type]:checked').val();
  if (type == 'link' || type == undefined) {
    $('#wp-content-wrap, #post-status-info').hide();
  }
  else {
    $('#wp-content-wrap, #post-status-info').show();
  }

}

changeType();
$('input[name=issue_category_type]').bind('click', changeType);

$('#issue_meta_box').appendTo('#titlediv').css('margin-top', '1em');
$('#wpseo_meta').hide();


    }
  };
})(jQuery, Proud);