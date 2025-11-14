[Database]
  ├─ wp_ccpfa_filieres (id, slug, nom, description)
  └─ wp_ccpfa_fonctions (id, fonction, filiere_id, slug, _category, _definition, page_number, ...)

        |
        |  JOIN filiere_id → filieres.id
        v

[PHP Functions]
  ├─ ccpfa_get_filiere_by_slug($slug)   → fetch single filiere
  ├─ ccpfa_get_fonctions_by_filiere($id) → fetch fonctions with filiere info
  ├─ ccpfa_render_filiere_href($row)    → generates <a href> for filiere
  └─ ccpfa_generate_page_ahref($page)   → generates PDF #page link

        |
        |  Include Template
        v

[Templates]
  ├─ filiere_view.php (filiere table)
  └─ fonctions_results_view.php (AJAX results)
      └─ output <table> with <thead> + <tbody> rows

        |
        |  Enqueue JS & CSS
        v

[Frontend JS]
  ├─ DataTables initialization
  └─ AJAX calls (for search/filter)

        |
        |  Optional PDF Link Click
        v

[PDF Serving Endpoint]
  └─ template_redirect → readfile() → inline PDF with #page
