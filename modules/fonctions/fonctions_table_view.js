jQuery(document).ready(function($) {

  // --- Custom DataTables order plugin ---
  $.fn.dataTable.ext.order['dom-data-order'] = function(settings, col) {
    return this.api().column(col, { order: 'index' }).nodes().map(function(td, i) {
      var $elements = $('input, textarea', td);
      if ($elements.length) {
        var values = $elements.map(function() {
          return $(this).attr('data-order') || $(this).val() || '';
        }).get().join(' ').trim();

        return $.isNumeric(values) ? parseFloat(values) : values.toLowerCase();
      }
      return $(td).text().trim().toLowerCase();
    });
  };

  // --- Initialize DataTable ---
  if (!$.fn.DataTable.isDataTable('#ccpfa_fonctions_table_view')) {
    $('#ccpfa_fonctions_table_view').DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      columnDefs: [
        { targets: '_all', orderDataType: 'dom-data-order' }
      ],
      columns: [
        { searchable: true },   // Fonction
        { searchable: false },  // filiere
        { searchable: false },  // Category
        { searchable: true },  // Description
        { searchable: false },  // Salaire Brut Mensuel
        { searchable: false },   // Salaire Brut Journalier
        { searchable: false }   // Page Number
      ]
    });
  }

  // --- Live update sorting when user edits ---
  $('#ccpfa_fonctions_table_view').on('input change', 'input, textarea', function() {
    $(this).attr('data-order', $(this).val());
    $('#ccpfa_fonctions_table_view').DataTable().cell($(this).closest('td')).invalidate().draw(false);
  });

});
