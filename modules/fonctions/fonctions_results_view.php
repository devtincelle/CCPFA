<div class="filiere-cards">
<div class="mb-3">
<?php if (count($results) > 1): ?>
    <p><strong><?php echo esc_html(count($results)) ?> Fonctions</strong></p>

    <table class="table table-striped table-bordered w-100">
        <thead class="table-dark">
            <tr>
                <th>Type de salaire</th>
                <th>BRUT (€)</th>
                <th>NET (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (get_average_monthly_salary($results) != 0): ?>
                <tr>
                    <td>Salaire mensuel moyen</td>
                    <td><?php echo get_average_monthly_salary($results); ?></td>
                    <td><?php echo convert_to_net(get_average_monthly_salary($results)); ?></td>
                </tr>
            <?php endif; ?>

            <?php if (get_average_daily_salary($results) != 0): ?>
                <tr>
                    <td>Salaire journalier moyen</td>
                    <td><?php echo get_average_daily_salary($results); ?></td>
                    <td><?php echo convert_to_net(get_average_daily_salary($results)); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php foreach ($results as $row): ?>

    <div class="card card-filiere mb-4 shadow-sm">
        <div class="card-body d-flex flex-column">
            <!-- Title & Version féminisée equally prominent -->
            <div class="card-header d-flex flex-wrap align-items-baseline mb-3">
                <h5 class="card-fonction"><?= esc_html($row->fonction); ?>
                <?php if (!empty($row->version_feminisee)): ?>
                    <p><?= esc_html($row->version_feminisee); ?></p>
                <?php endif; ?>
                </h5>
            </div>

            
            <!-- Definition with breathing space -->
            <p class="card-definition"><?= nl2br(esc_html($row->_definition ?? '-')); ?></p>


            <!-- Salaire -->
            <div class="card-salaire mb-3">
                <h5>Salaire Brut</h5>
                <table class="table table-sm table-bordered table-filiere mb-0 w-100">
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
            </div>

            <!-- Filière as a  button -->
                 <?= ccpfa_render_filiere_href($row, 'btn btn-outline-primary btn-sm card-filiere-btn'); ?>
            <!-- Category as a button -->
            <?php if (!empty($row->_category)): ?>
                <span class="btn btn-outline-secondary btn-sm card-category-btn">
                        Categorie <?= esc_html($row->_category); ?>
                </span>
            <?php endif; ?>



            <!-- Page link bottom-right -->
            <div class="card-footer mt-auto text-end">
                <?= ccpfa_generate_page_ahref($row->page_number); ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
