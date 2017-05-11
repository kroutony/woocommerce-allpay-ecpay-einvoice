<script>
    var $=jQuery.noConflict();
    $(document).ready(function(){
        var ele=$('<option>').val('aeeik_bulk_issue').text("<?php _e('Bulk issue invoices','allpay-e-invoice');?>");
        ele.appendTo('select[name="action"]');
        ele.clone().appendTo('select[name="action2"]');
    });
</script>