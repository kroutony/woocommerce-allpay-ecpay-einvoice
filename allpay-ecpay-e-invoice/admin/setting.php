<?php
add_action('admin_menu','aeeik_menu_build');
add_action('admin_init','aeeik_register_options');
function aeeik_menu_build(){
    add_submenu_page( 'woocommerce', __('Allpay-Ecpay EInvoice','allpay-e-invoice'), __('Allpay&Ecpay EInvoice','allpay-e-invoice'), 'manage_woocommerce', 'allpay-e-invoice', 'aeeik_setting_page' );
}
function aeeik_register_options(){
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_enabled');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_service_source');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_test_mode');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_merchant_id');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_hash_key');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_hash_iv');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_issue_mode');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_donate_to');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_invoice_type');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_tax_type');
    register_setting('allpay-e-invoice-settings','allpay_e_invoice_tax_shipping_fee_included');
    add_settings_section('allpay_e_invoice_switch','','aeeik_section_switch','allpay-e-invoice');
    add_settings_field('allpay_e_invoice_enabled',__('Enable','allpay-e-invoice'),'aeeik_field_enabled','allpay-e-invoice','allpay_e_invoice_switch');
    add_settings_field('allpay_e_invoice_service_source',__('Service source','allpay-e-invoice'),'aeeik_field_service_source','allpay-e-invoice','allpay_e_invoice_switch');
    add_settings_section('allpay_e_invoice_general_setting',__('General Settings','allpay-e-invoice'),'aeeik_section_general_setting','allpay-e-invoice');
    add_settings_field('allpay_e_invoice_issue_mode',__('Issue Mode','allpay-e-invoice'),'aeeik_field_issue_mode','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_field('allpay_e_invoice_donate_to',__('List of organization to be donated','allpay-e-invoice'),'aeeik_field_donate_to','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_field('allpay_e_invoice_invoice_type',__('Invoice type','allpay-e-invoice'),'aeeik_field_invoice_type','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_field('allpay_e_invoice_tax_type',__('Tax type','allpay-e-invoice'),'aeeik_field_tax_type','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_field('allpay_e_invoice_tax_shipping_fee_included',__('Including shipping fee','allpay-e-invoice'),'aeeik_field_tax_shipping_fee_included','allpay-e-invoice','allpay_e_invoice_general_setting');
    add_settings_section('allpay_e_invoice_api_info',__('API Key Information','allpay-e-invoice'),'aeeik_section_api_info' ,'allpay-e-invoice');
    add_settings_field('allpay_e_invoice_test_mode',__("Test mode","allpay-e-invoice"),'aeeik_field_test_mode','allpay-e-invoice','allpay_e_invoice_api_info');
    add_settings_field('allpay_e_invoice_merchant_id','Merchant ID','aeeik_field_merchant_id','allpay-e-invoice','allpay_e_invoice_api_info');
    add_settings_field('allpay_e_invoice_hash_key','Hash Key','aeeik_field_hash_key','allpay-e-invoice','allpay_e_invoice_api_info');
    add_settings_field('allpay_e_invoice_hash_iv','Hash IV','aeeik_field_hash_iv','allpay-e-invoice','allpay_e_invoice_api_info');

}
function aeeik_generate_option_html($field){
    switch($field['type']){
        case 'checkbox':
            echo "<input type='checkbox' id='".$field['id']."' name='".$field['name']."' value='1'";
            if(get_option($field['name'])=='1') echo "checked='checked'";
            echo ">";
            echo "<label for='".$field['id']."'>".$field['label']."</label>";
            break;
        case 'textarea':
            echo "<textarea rows='".$field['rows']."' cols='".$field['cols']."' id='".$field['id']."' name='".$field['name']."'>";
            echo get_option($field['name']);
            echo "</textarea>";
            break;
        case 'text':
            echo "<input type='text' id='".$field['id']."' name='".$field['name']."' value='".get_option($field['name'])."'>";
            break;
        case 'radio':
            $option=get_option($field['name']);
            foreach($field['option'] as $value=>$label){
                echo "<input type='radio' id='".$field['name'].'_'.$value."' name='".$field['name']."' value='".$value."'";
                if($option==$value) echo "checked='checked'";
                echo ">";
                echo "<label for='".$field['name'].'_'.$value."'>".$label."</label>";
                echo "<br>";
            }
            break;
    }
}
?>
<?php function aeeik_setting_page(){ ?>
<form method="post" action="options.php">
    <h1><?php _e('Allpay-Ecpay EInvoice','allpay-e-invoice');?></h1>
    <?php settings_fields( 'allpay-e-invoice-settings' ); ?>
    <?php do_settings_sections( 'allpay-e-invoice' ); ?>
    <?php submit_button(); ?>
</form>
<?php }?>
<?php
//Section 1 Switch
function aeeik_section_switch(){
    return;
}
//啟用
function aeeik_field_enabled(){
    $field=array(
        'type'=>'checkbox',
        'name'=>'allpay_e_invoice_enabled',
        'id'=>'allpay_e_invoice_enabled',
        'label'=>__('Enable','allpay-e-invoice')
    );
    aeeik_generate_option_html($field);
}
//服務來源
function aeeik_field_service_source(){
    $field=array(
        'type'=>'radio',
        'name'=>'allpay_e_invoice_service_source',
        'option'=>array(
            'allpay'=>__("Allpay","allpay-e-invoice"),
            'ecpay'=>__("Ecpay","allpay-e-invoice")
        )
    );
    aeeik_generate_option_html($field);
}
//Section 2 General Setting
function aeeik_section_general_setting(){
    return;
}
//開立模式
function aeeik_field_issue_mode(){
    $field=array(
        'type'=>'radio',
        'name'=>'allpay_e_invoice_issue_mode',
        'option'=>array(
            'manual'=>__('Manual issue','allpay-e-invoice'),
            'auto'=>__('Automatic issue','allpay-e-invoice')
        )
    );
    aeeik_generate_option_html($field);
}
//捐贈單位
function aeeik_field_donate_to(){
    $field=array(
        'type'=>'textarea',
        'name'=>'allpay_e_invoice_donate_to',
        'id'=>'allpay_e_invoice_donate_to',
        'cols'=>'50',
        'rows'=>'4'
    );
    aeeik_generate_option_html($field);
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
//字軌類別
function aeeik_field_invoice_type(){
    $field=array(
        'type'=>'radio',
        'name'=>'allpay_e_invoice_invoice_type',
        'option'=>array(
            'general'=>__('General Tax Computation','allpay-e-invoice'),
            'special'=>__('Special Tax Computation','allpay-e-invoice')
        )
    );
    aeeik_generate_option_html($field);
}
//課稅類別
function aeeik_field_tax_type(){
    $field=array(
        'type'=>'radio',
        'name'=>'allpay_e_invoice_tax_type',
        'option'=>array(
            'dutiable'=>__('Dutiable','allpay-e-invoice'),
            'free'=>__('Duty free','allpay-e-invoice')
        )
    );
    aeeik_generate_option_html($field);
}
//包含運費
function aeeik_field_tax_shipping_fee_included(){
    $field=array(
        'type'=>'checkbox',
        'name'=>'allpay_e_invoice_tax_shipping_fee_included',
        'id'=>'allpay_e_invoice_tax_shipping_fee_included',
        'label'=>__('Yes','allpay-e-invoice')
    );
    aeeik_generate_option_html($field);
}
//Section 3 API Info
function aeeik_section_api_info(){
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
//測試模式
function aeeik_field_test_mode(){
    $field=array(
        'type'=>'checkbox',
        'name'=>'allpay_e_invoice_test_mode',
        'id'=>'allpay_e_invoice_test_mode',
        'label'=>__('Enable','allpay-e-invoice')
    );
    aeeik_generate_option_html($field);
}
function aeeik_field_merchant_id(){
    $field=array(
        'type'=>'text',
        'name'=>'allpay_e_invoice_merchant_id',
        'id'=>'allpay_e_invoice_merchant_id'
    );
    aeeik_generate_option_html($field);
}
function aeeik_field_hash_key(){
    $field=array(
        'type'=>'text',
        'name'=>'allpay_e_invoice_hash_key',
        'id'=>'allpay_e_invoice_hash_key'
    );
    aeeik_generate_option_html($field);
}
function aeeik_field_hash_iv(){
    $field=array(
        'type'=>'text',
        'name'=>'allpay_e_invoice_hash_iv',
        'id'=>'allpay_e_invoice_hash_iv'
    );
    aeeik_generate_option_html($field);
}
?>
