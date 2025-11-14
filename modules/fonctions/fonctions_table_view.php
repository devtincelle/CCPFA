<div class="container mt-4">
    <table id="ccpfa_fonctions_table_view" class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Fonction</th>
                <th>Filière</th>
                <th>Category</th>
                <th>Description</th>
                <th>Salaire Brut Mensuel</th>
                <th>Salaire Brut Journalier</th>
                <th>Numéro de page</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row) : ?>
                <tr>
                    <td style="width: 25%;">
                        <?= esc_html($row->fonction); ?>
                        <?php if(!empty($row->version_feminisee)) : ?>
                            <br><em><?= esc_html($row->version_feminisee); ?></em>
                        <?php endif; ?>
                    </td>
                    <td><?= ccpfa_render_filiere_href($row); ?></td>
                    <td><?= esc_html($row->_category ?? ''); ?></td>
                    <td style="width: 35%;"><?= nl2br(esc_html($row->_definition ?? '')); ?></td>
                    <td><?= esc_html($row->salaire_brut_mensuel ?? ''); ?></td>
                    <td><?= esc_html($row->salaire_brut_journalier ?? ''); ?></td>
                    <td><?= ccpfa_generate_page_ahref($row->page_number); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
