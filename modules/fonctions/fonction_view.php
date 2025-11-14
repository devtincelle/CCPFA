<div class="col-md-6 mb-4">
    <div class="card shadow-sm" style="width: auto;">
        <div class="card-body">
            <h5 class="card-title"><?= esc_html($row->fonction); ?></h5>
            <h5 class="card-title text-muted"><?= esc_html($row->version_feminisee); ?></h5>
            
            <!--Category-->
            <p><strong>Category:</strong> <?= esc_html($row->_category ?? '-'); ?></p>

            <!--Description-->
            <p><strong>Description:</strong><br><?= nl2br(esc_html($row->_definition ?? '-')); ?></p>

            <!--FILIERE-->
            <p><strong>Filière:</strong><br>
              <?php echo ccpfa_render_filiere_href($row);?>
            </p>
          
          <!--SALAIRES-->
          <h6>Salaire Brut</h6>
          <table class="table table-sm table-bordered w-auto mb-2">
            <thead class="table-light">
              <tr>
                <th>Mensuel</th>
                <th>Journalier</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= esc_html($row->salaire_brut_mensuel ?? '-'); ?> €</td>
                <td><?= esc_html($row->salaire_brut_journalier ?? '-'); ?> €</td>
              </tr>
            </tbody>
          </table>
          <!--PAGE NUMBER-->
          <p><strong>Numero de page :</strong>
            <?php echo ccpfa_generate_page_ahref($row->page_number);?>
          </p>
        </div>
    </div>
</div>
