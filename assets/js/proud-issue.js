(function($, Proud) {
  Proud.behaviors.proud_issue = {
    attach: function(context, settings) {


function changeType() {
  $('#agency_url_wrapper, #post_menu_wrapper, #wr_editor_tabs, .wr-editor-tab-content').hide();
  //if (isNewPost) {
  //  window.setTimeout(function(){$('#wr_editor_tabs a[href="#wr_editor_tab2"]').trigger('click');}, 1000);
  //}
  var type = $('input[name=issue_category_type]:checked').val();
  console.log(type);
  console.log($('#wp-content-wrap'));
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