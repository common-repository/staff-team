jQuery("document").ready(function () {
  elementor.hooks.addAction( 'panel/open_editor/widget/team-elementor', function( panel, model, view ) {
    var twd_obj = jQuery('select[data-setting="twd_single_contact"]',window.parent.document);
    twd_edit_single_contact_link(twd_obj);
  });
  jQuery('body').on('change', 'select[data-setting="twd_single_contact"]',window.parent.document, function (){
    twd_edit_single_contact_link(jQuery(this));
  });
});

function twd_edit_single_contact_link(el) {
  var twd_el = el;
  var twd_id = twd_el.val();
  var a_link = twd_el.closest('.elementor-control-content').find('.elementor-control-field-description').find('a');
  var new_link = 'edit.php?post_type=contact';
  if(twd_id !== '0'){
    new_link = 'post.php?post=' + twd_el.val() + '&action=edit';
  }
  a_link.attr( 'href', new_link);
}
