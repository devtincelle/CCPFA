jQuery(document).ready(function($) {
  console.log("HELLO")
  // drop down
  // on load 
  load_filiere_fonctions($('#filiere-select').val())
  // on change 
  $('#filiere-select').on('change', function() {
      load_filiere_fonctions($(this).val())
  });

  function load_filiere_fonctions(filiere_id){
      if (!filiere_id) return; // ignore if no selection

      $.ajax({
            url: ccpfa_filiere_Ajax.ajax_url, // localized object
            type: 'POST',
            data: {
                action: 'search_filiere_fonctions',  // must match your PHP action
                filiere_id: filiere_id
            },
            beforeSend: function() {
              $('#filiere_results').html('<p>Chargement...</p>');
            },
            success: function(response) {
                console.log(response)
                if (response.success) {
                    $('#filiere_results').html(response.data);
                    $('#filiere_title').html(filiere_id);
                } else {
                    alert(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
      });
  }

});
