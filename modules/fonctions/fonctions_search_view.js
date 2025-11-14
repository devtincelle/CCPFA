jQuery(document).ready(function($) {
  // drop down
  $('#fonction-select').on('change', function() {
      const fonction_id = $(this).val(); // get the selected value
      console.log(fonction_id)
      if (!fonction_id) return; // ignore if no selection
      $.ajax({
            url: ccpfa_fonction_Ajax.ajax_url, // localized object
            type: 'POST',
            data: {
                action: 'fonction_search',  // must match your PHP action
                fonction_id: fonction_id
            },
            success: function(response) {
                console.log(response)
                if (response.success) {
                    $('#search_results').html(response.data);
                } else {
                    alert(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
      });
  });

});


