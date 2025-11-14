<div class="mb-3">
    <select name="filiere" id="filiere-select" class="form-select">
        <option value="">-- Sélectionner une filière --</option>
        <?php foreach ($filieres as $f): ?>
            <option 
                value="<?php echo esc_attr($f->id); ?>" 
                <?php selected($selected_filiere->id, $f->id); ?>>
                <?php echo esc_html($f->nom); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


<div id="filiere_results" class="mt-3"></div>