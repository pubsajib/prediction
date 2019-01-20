<form action="<?php echo admin_url('admin.php?import=' . $this->import_page . '&step=2'); ?>" method="post" id="nomap">
    <?php wp_nonce_field('import-woocommerce'); ?>
    <input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
    <?php if ($this->file_url_import_enabled) : ?>
        <input type="hidden" name="import_url" value="<?php echo $this->file_url; ?>" />
    <?php endif; ?>
    <p class="submit">
        <input style="display:none" type="submit" class="button button-primary" value="<?php esc_attr_e('Submit', 'users-customers-import-export-for-wp-woocommerce'); ?>" />
    </p>
</form>
<script type="text/javascript"> 
jQuery(document).ready(function(){
   jQuery("form#nomap").submit();
});
</script>