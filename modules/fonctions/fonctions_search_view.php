<div class="mb-3">
    <label for="fonction-select" class="form-label">Fonction</label>
    <select name="fonction_id" id="fonction-select" class="form-select">
        <option value="">-- Sélectionner un poste --</option>
        <?php
          foreach ($fonction_options as $f) {
              echo '<option value="' . esc_attr($f->id) . '">' . esc_html($f->fonction) . '</option>';
          }
        ?>
    </select>
  </div>
<div id="search_results" class="mt-3">
</div>

