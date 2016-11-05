<?php
add_action('admin_menu','allpay_e_invoice_menuBuild');
add_action('admin_init','allpay_e_invoice_registerOption');
function allpay_e_invoice_menuBuild(){
    add_submenu_page( 'woocommerce', __('Allpay-Ecpay EInvoice','allpay-e-invoice'), __('Allpay&Ecpay EInvoice','allpay-e-invoice'), 'manage_woocommerce', 'allpay-e-invoice', 'allpay_e_invoice_setting_page' );
}
function allpay_e_invoice_registerOption(){
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_enabled');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_service_source');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_test_mode');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_merchant_id');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_hash_key');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_hash_iv');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_invoice_method');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_donate_to');
    add_settings_section('allpay_e_invoice_switch','','section_allpay_e_invoice_switch','allpay-e-invoice');
    add_settings_field('allpay_e_invoice_enabled',__('Enable','allpay-e-invoice'),'field_allpay_e_invoice_enabled','allpay-e-invoice','allpay_e_invoice_switch');
    add_settings_field('allpay_e_invoice_service_source',__('Service source','allpay-e-invoice'),'field_allpay_e_invoice_service_source','allpay-e-invoice','allpay_e_invoice_switch');
    add_settings_section('allpay_e_invoice_general_setting',__('General Settings','allpay-e-invoice'),'section_allpay_e_invoice_general_setting','allpay-e-invoice');
    add_settings_field('allpay_e_invoice_invoice_method',__('Issue Mode','allpay-e-invoice'),'field_allpay_e_invoice_invoice_method','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_field('allpay_e_invoice_donate_to',__('List of organization to be donated','allpay-e-invoice'),'field_allpay_e_invoice_donate_to','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_section('allpay_e_invoice_api_info',__('API Key Information','allpay-e-invoice'),'section_allpay_e_invoice_api_info' ,'allpay-e-invoice');
    add_settings_field('allpay_e_invoice_test_mode',__("Test mode","allpay-e-invoice"),'field_allpay_e_invoice_test_mode','allpay-e-invoice','allpay_e_invoice_api_info');
    add_settings_field('allpay_e_invoice_merchant_id','Merchant ID','field_allpay_e_invoice_merchant_id','allpay-e-invoice','allpay_e_invoice_api_info');
    add_settings_field('allpay_e_invoice_hash_key','Hash Key','field_allpay_e_invoice_hash_key','allpay-e-invoice','allpay_e_invoice_api_info');
    add_settings_field('allpay_e_invoice_hash_iv','Hash IV','field_allpay_e_invoice_hash_iv','allpay-e-invoice','allpay_e_invoice_api_info');

}
?>
<?php function allpay_e_invoice_setting_page(){ ?>
<form method="post" action="options.php">
    <h1><?php echo __('Allpay-Ecpay EInvoice','allpay-e-invoice');?></h1>
    <?php settings_fields( 'allpay-e-invoice-settings' ); ?>
    <?php do_settings_sections( 'allpay-e-invoice' ); ?>
    <?php submit_button(); ?>
</form>
<?php }?>
<?php
function section_allpay_e_invoice_switch(){
    return;
}
function section_allpay_e_invoice_api_info(){
    echo "<button value='' id='show_api_info'>顯示API設定</button>";
?>
<script>
    jQuery(document).ready(function($){
        var $form=$('#allpay_e_invoice_test_mode').parents('.form-table');
        $form.css('display','none');
        $('#show_api_info').click(function(e){
            e.preventDefault();
            if($form.css('display')=='none'){
                $form.css('display','');
                $(this).html('隱藏API設定');
            }
            else{
                $form.css('display','none');
                $(this).html('顯示API設定');
            }
        });
    });
</script>
<?php
    return;
}
function section_allpay_e_invoice_general_setting(){
    return;
}
function field_allpay_e_invoice_test_mode(){
    echo "<input type='checkbox' id='allpay_e_invoice_test_mode' name='allpay_e_invoice_test_mode' value='1' ";
    if(get_option('allpay_e_invoice_test_mode')==1)
        echo "checked='checked'";
    echo " />";
    echo "<label for='allpay_e_invoice_test_mode'>".__('Enable','allpay-e-invoice')."</label>";
}
function field_allpay_e_invoice_merchant_id(){
    echo "<input type='text' id='allpay_e_invoice_merchant_id' name='allpay_e_invoice_merchant_id' value='".esc_attr( get_option('allpay_e_invoice_merchant_id') )."' />";
}
function field_allpay_e_invoice_hash_key(){
    echo "<input type='text' id='allpay_e_invoice_hash_key' name='allpay_e_invoice_hash_key' value='".esc_attr( get_option('allpay_e_invoice_hash_key') )."' />";
}
function field_allpay_e_invoice_hash_iv(){
    echo "<input type='text' id='allpay_e_invoice_hash_iv' name='allpay_e_invoice_hash_iv' value='".esc_attr( get_option('allpay_e_invoice_hash_iv') )."' />";
}
function field_allpay_e_invoice_invoice_method(){
    $method=get_option('allpay_e_invoice_invoice_method');
    echo "<input type='radio' id='allpay_e_invoice_invoice_method_invoice' name='allpay_e_invoice_invoice_method' value='MANUAL'";
    if($method=="MANUAL") echo "checked=''checked";
    echo " />";
    echo "<label for='allpay_e_invoice_invoice_method_invoice'>".__('Manual issue','allpay-e-invoice')."</label>";
    echo "<br>";
    echo "<input type='radio' id='allpay_e_invoice_invoice_method_delay_trigger' name='allpay_e_invoice_invoice_method' value='AUTO'";
    if($method=="AUTO") echo "checked=''checked";
    echo " />";
    echo "<label for='allpay_e_invoice_invoice_method_delay_trigger'>".__('Automatic issue','allpay-e-invoice')."</label>";
    
}
function field_allpay_e_invoice_donate_to(){
    echo "<textarea rows='4' cols='50' id='allpay_e_invoice_donate_to' name='allpay_e_invoice_donate_to'>";
    echo get_option('allpay_e_invoice_donate_to');
    echo "</textarea>";
    echo "<div>";
    echo "<strong>請至少填入一捐贈單位</strong><br>";
    echo "<strong>格式:</strong><br>";
    echo "愛心碼-單位名稱<br>";
    echo "<strong>例:</strong><br>";
    echo "8455-財團法人台灣兒童暨家庭扶助基金<br>";
    echo "25885-財團法人伊甸社會福利基金會<br>";
    echo "<a href='https://www.einvoice.nat.gov.tw/APMEMBERVAN/XcaOrgPreserveCodeQuery/XcaOrgPreserveCodeQuery' target='_blank'>愛心碼查詢</a>";
    echo "</div>";
    
}
function field_allpay_e_invoice_enabled(){
    echo "<input type='checkbox' id='allpay_e_invoice_enabled' name='allpay_e_invoice_enabled' value='1' ";
    if(get_option('allpay_e_invoice_enabled')==1)
        echo "checked='checked'";
    echo " />";
    echo "<label for='allpay_e_invoice_enabled'>".__('Enable','allpay-e-invoice')."</label>";
}
function field_allpay_e_invoice_service_source(){
    $source=get_option('allpay_e_invoice_service_source');
    echo "<input type='radio' id='allpay_e_invoice_service_source_allpay' name='allpay_e_invoice_service_source' value='allpay'";
    if($source=="allpay") echo "checked=''checked";
    echo " />";
    echo "<label for='allpay_e_invoice_service_source_allpay'>".__("Allpay","allpay-e-invoice")."</label>";
    echo "<br>";
    echo "<input type='radio' id='allpay_e_invoice_service_source_ecpay' name='allpay_e_invoice_service_source' value='ecpay'";
    if($source=="ecpay") echo "checked=''checked";
    echo " />";
    echo "<label for='allpay_e_invoice_service_source_ecpay'>".__("Ecpay","allpay-e-invoice")."</label>";
}
?>