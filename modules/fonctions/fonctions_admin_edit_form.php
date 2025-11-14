<form method="post">
    <table class="form-table">
        <tr>
            <th>Fonction</th>
            <td><input type="text" name="fonction" value="<?php echo esc_attr($job['fonction']); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th>Version Féminisée</th>
            <td><input type="text" name="version_feminisee" value="<?php echo esc_attr($job['version_feminisee']); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th>Catégorie</th>
            <td><input type="text" name="_category" value="<?php echo esc_attr($job['_category']); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th>Filière</th>
            <td>
                <select name="filiere_id" class="regular-text">
                    <option value="">-- Select Filière --</option>
                    <?php
                    global $wpdb;
                    $filieres_table = $wpdb->prefix . CCPFA_TABLE_FILIERES;
                    $filieres = $wpdb->get_results("SELECT id, nom FROM $filieres_table ORDER BY nom ASC", ARRAY_A);

                    foreach ($filieres as $filiere) {
                        $selected = ($job['filiere_id'] == $filiere['id']) ? 'selected' : '';
                        echo '<option value="' . esc_attr($filiere['id']) . '" ' . $selected . '>' . esc_html($filiere['nom']) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th>Définition</th>
            <td><textarea name="_definition" rows="5" class="large-text"><?php echo esc_textarea($job['_definition']); ?></textarea></td>
        </tr>

        <tr>
            <th>Salaire Brut Mensuel (€)</th>
            <td><input type="number" name="salaire_brut_mensuel" value="<?php echo esc_attr($job['salaire_brut_mensuel']); ?>" step="0.01"></td>
        </tr>

        <tr>
            <th>Salaire Brut Journalier (€)</th>
            <td><input type="number" name="salaire_brut_journalier" value="<?php echo esc_attr($job['salaire_brut_journalier']); ?>" step="0.01"></td>
        </tr>

        <tr>
            <th>External ID</th>
            <td><input type="text" name="external_id" value="<?php echo esc_attr($job['external_id']); ?>" class="regular-text"></td>
        </tr>
    </table>

    <p>
        <input type="submit" name="ccpfa_save_job" class="button button-primary" value="Save Changes">
        <a href="<?php echo admin_url('admin.php?page=ccpfa'); ?>" class="button">Cancel</a>
    </p>
</form>
