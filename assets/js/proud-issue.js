(function($, Proud) {
  Proud.behaviors.proud_issue = {
    attach: function(context, settings) {
      var issue_settings = settings.proud_issue;
      if (issue_settings && issue_settings.issue_category_type_name) {
        // Grab type field
        var name = 'input[name="' + issue_settings.issue_category_type_name + '"]';
        $(name).bind('click', changeType);
        changeType();
        // Move box
        $('#issue_meta_box').appendTo('#titlediv').css('margin-top', '1em');
        $('#wpseo_meta').hide();

        function changeType() {
          $('#agency_url_wrapper, #post_menu_wrapper, #wr_editor_tabs, .wr-editor-tab-content').hide();
          var type = $(name + ':checked').val();
          if (type == 'link' || type == undefined) {
            $('#wp-content-wrap, #post-status-info').hide();
          }
          else {
            $('#wp-content-wrap, #post-status-info').show();
          }
        }
      }
    }
  };
})(jQuery, Proud);