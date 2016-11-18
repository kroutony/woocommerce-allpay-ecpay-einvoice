<?php
/*
Plugin Name: allpay-ecpay-e-invoice
Plugin URI: https://github.com/kroutony/woocommerce-allpay-ecpay-einvoice
Description: allpay-ecpay-e-invoice
Version: 1.1.2
Date: 2016-11-18
Author: kroutony
Author URI: http://blog.krypds.com/
Text Domain: allpay-e-invoice
Domain Path: /languages
*/
?>
<?php
require_once('class/AllPay_Invoice.php');
require_once('checkout-fields.php');

abstract class AEEIK_DEFAULT_OPTIONS{
    const TestMode='1';
    const MerchantID='2000132';
    const HashKey='ejCk326UnaZWKisg';
    const HashIV='q9jcZX8Ib9LM8wYk';
    const IssueMode='manual';
    const Enabled='';
    const ServiceSouce='allpay';
    const TaxType='dutiable';
    const ShippingFeeIncluded='';
    const InvoiceType='general';
}
abstract class AEEIK_HOST_URL{
    const ALLPAY='https://einvoice.allpay.com.tw/';
    const ALLPAY_TEST='https://einvoice-stage.allpay.com.tw/';
    const ECPAY='https://einvoice.ecpay.com.tw/';
    const ECPAY_TEST='https://einvoice-stage.ecpay.com.tw/';
}
register_activation_hook(__FILE__, 'aeeik_init_options');
function aeeik_init_options(){
    add_option('allpay_e_invoice_test_mode',AEEIK_DEFAULT_OPTIONS::TestMode);
    add_option('allpay_e_invoice_merchant_id',AEEIK_DEFAULT_OPTIONS::MerchantID);
    add_option('allpay_e_invoice_hash_key',AEEIK_DEFAULT_OPTIONS::HashKey);
    add_option('allpay_e_invoice_hash_iv',AEEIK_DEFAULT_OPTIONS::HashIV);
    add_option('allpay_e_invoice_enabled',AEEIK_DEFAULT_OPTIONS::Enabled);
    add_option('allpay_e_invoice_service_source',AEEIK_DEFAULT_OPTIONS::ServiceSouce);
    add_option('allpay_e_invoice_issue_mode',AEEIK_DEFAULT_OPTIONS::IssueMode);
    add_option('allpay_e_invoice_invoice_type',AEEIK_DEFAULT_OPTIONS::InvoiceType);
    add_option('allpay_e_invoice_tax_type',AEEIK_DEFAULT_OPTIONS::TaxType);
    add_option('allpay_e_invoice_tax_shipping_fee_included',AEEIK_DEFAULT_OPTIONS::ShippingFeeIncluded);
}
add_action('plugins_loaded', 'aeeik_plugin_loaded');
function aeeik_plugin_loaded() {
    include_once('admin/setting.php');
    $plugin_enabled=get_option('allpay_e_invoice_enabled');
    if($plugin_enabled=='1'){
        $method=get_option('allpay_e_invoice_issue_mode');
        if($method=="auto"){
            add_action('woocommerce_order_status_processing','aeeik_issue_invoice');
        }
        add_filter('manage_edit-shop_order_columns','aeeik_add_shop_orders_column');
        add_action('manage_shop_order_posts_custom_column' , 'aeeik_add_shop_orders_column_action' );
        add_action('admin_enqueue_scripts', 'aeeik_admin_enqueue_scripts');
        add_action('wp_ajax_aeeik_issue_invoice', 'aeeik_ajax_issue_invoice' );
        add_action('wp_ajax_aeeik_invalid_invoice', 'aeeik_ajax_invalid_invoice' );
        add_action('wp_ajax_aeeik_edit_invoice_meta', 'aeeik_ajax_edit_invoice_meta' );
        load_plugin_textdomain('allpay-e-invoice');
    }
}
function aeeik_ajax_edit_invoice_meta(){
    if(isset($_POST['order_id'])){
        switch($_POST['name']){
            case 'company_tax_id':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_company_tax_id',  $_POST['value']);
                break;
            case 'buyer':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_buyer',  $_POST['value']);
                break;
            case 'print_mark':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_invoice_print_mark',  $_POST['value']);
                break;
            case 'donate_mark':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_invoice_donate_mark',  $_POST['value']);
                break;
            case 'donate_to':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_lovecode',  $_POST['value']);
                break;
            case 'carruer_type':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_invoice_carruer_type',  $_POST['value']);
                break;
            case 'carruer_num':
                update_post_meta( $_POST['order_id'], '_allpay_e_invoice_billing_receipt_invoice_carruer_num',  $_POST['value']);
                break;
        }
    }
}
function aeeik_ajax_issue_invoice(){
    if(isset($_POST['order_id'])){
        $order_id=$_POST['order_id'];
        include_once('class/AllPay_Invoice.php');
        $allpay_invoice=new WC_Allpay_E_Invoice($order_id);
        $result=$allpay_invoice->Invoice_Issue();
        if(isset($result['RtnCode'])&& $result['RtnCode']==1){
            aeeik_update_post_meta_and_order_note('issue',true,$result,$order_id);
            $response=array(
                "status"=>true
            );
            echo json_encode($response);
        }else{
            aeeik_update_post_meta_and_order_note('issue',false,$result,$order_id);
            $response=array(
                "status"=>false,
                "message"=>$result
            );
            echo json_encode($response);
        }
        wp_die();
    }
}
function aeeik_ajax_invalid_invoice(){
    if(isset($_POST['order_id'])){
        $order_id=$_POST['order_id'];
        include_once('class/AllPay_Invoice.php');
        $allpay_invoice=new WC_Allpay_E_Invoice($order_id);
        $result=$allpay_invoice->Invoice_IssueInvalid($_POST['reason']);
        if(isset($result['RtnCode'])&& $result['RtnCode']==1){
            aeeik_update_post_meta_and_order_note('invalid',true,$result,$order_id);
            $response=array(
                "status"=>true
            );
            echo json_encode($response);
        }else{
            aeeik_update_post_meta_and_order_note('invalid',false,$result,$order_id);
            $response=array(
                "status"=>false,
                "message"=>$result
            );
            echo json_encode($response);
        }
        wp_die();
    }
}
function aeeik_update_post_meta_and_order_note($action,$success,$result,$order_id){
    $order=new WC_Order($order_id);
    $order_note='';
    if($action=='issue'){
        if($success){
            $order_note.=$result['RtnMsg'].'<br>';
            $order_note.=__('Invoice Number：','allpay-e-invoice').$result['InvoiceNumber']."<br>";
            $order_note.=__('Invoice Issue Date：','allpay-e-invoice').$result['InvoiceDate'].'<br>';
            $order_note.=__('Invoice Random Number：','allpay-e-invoice').$result['RandomNumber'].'<br>';
            update_post_meta($order_id,'_allpay_e_invoice_invoice_number',$result['InvoiceNumber']);
            update_post_meta($order_id,'_allpay_e_invoice_invoice_date',$result['InvoiceDate']);
            update_post_meta($order_id,'_allpay_e_invoice_random_number',$result['RandomNumber']);
            update_post_meta($order_id,'_allpay_e_invoice_invoice_status','1');
        }else{
            $order_note.=__('Invoice issue failed!','allpay-e-invoice').'<br>';
            foreach($result as $key=>$value){
                $order_note.=$value.'<br>';
            }
        }
    }else if($action=='invalid'){
        if($success){
            date_default_timezone_set('Asia/Taipei');
            $date = new DateTime();
            $order_note.=$result['RtnMsg']."<br>";
            $order_note.=__('Invoice Number：','allpay-e-invoice').$result['InvoiceNumber']."<br>";
            $order_note.=__('Invoice Invalid Date：','allpay-e-invoice').$date->format('Y-m-d H:i:s')."<br>";
            update_post_meta($order_id,'_allpay_e_invoice_invoice_number',$result['InvoiceNumber']);
            update_post_meta($order_id,'_allpay_e_invoice_invoice_status','4');
            update_post_meta($order_id,'_allpay_e_invoice_invoice_issue_invalid_date',$date->format('Y-m-d H:i:s'));
        }else{
            $order_note.=__('Invoice invalid failed!','allpay-e-invoice').'<br>';
            foreach($result as $key=>$value){
                $order_note.=$value.'<br>';
            }
        }
    }
    $order->add_order_note($order_note);
}
function aeeik_add_shop_orders_column($columns ){
    $columns['allpay_e_invoice_column']=__('E Invoice','allpay-e-invoice');
    return $columns;
}
function aeeik_add_shop_orders_column_action( $column ){
    global $the_order;
    if($column=='allpay_e_invoice_column'){
        $invoice_status=get_post_meta($the_order->id,'_allpay_e_invoice_invoice_status',true);
        $order_status=$the_order->get_status();
        if($invoice_status=='1'){
            echo "<div class='allpay_e_invoice_info'>";
            echo "<div>".__('Invoice Number：','allpay-e-invoice')."</div>";
            echo "<div>".get_post_meta($the_order->id,'_allpay_e_invoice_invoice_number',true)."</div>";
            echo "<div>".__('Invoice Issue Date：','allpay-e-invoice')."</div>";
            echo "<div>".get_post_meta($the_order->id,'_allpay_e_invoice_invoice_date',true)."</div>";
            echo "<a class='button issue_invalid_button' value='".$the_order->id."'>".__('Invalid invoice','allpay-e-invoice')."</a>";
            echo "<img class='invoice_issue_loading' src='".plugin_dir_url( __FILE__ )."/admin/asset/icon/ajax-loader.gif"."' style='display:none;'>";
            echo "</div>";
        }else if($invoice_status=='4'){
            echo "<div class='allpay_e_invoice_info' style='color:red;'>";
            echo "<div>".__('Invalid Invoice：','allpay-e-invoice')."</div>";
            echo "<div>".get_post_meta($the_order->id,'_allpay_e_invoice_invoice_number',true)."</div>";
            echo "<div>".__('Invoice Invalid Date：','allpay-e-invoice')."</div>";
            echo "<div>".get_post_meta($the_order->id,'_allpay_e_invoice_invoice_issue_invalid_date',true)."</div>";
            echo "<a class='button issue_button' value='".$the_order->id."'>".__('Re-issue invoice','allpay-e-invoice')."</a>";
            echo "<img class='invoice_issue_loading' src='".plugin_dir_url( __FILE__ )."/admin/asset/icon/ajax-loader.gif"."' style='display:none;'>";
            echo "</div>";
        }
        else if(in_array($order_status,array('processing','completed'))){
            echo "<div class='allpay_e_invoice_info'>";
            echo "<a class='button issue_button' value='".$the_order->id."'>".__('Issue invoice','allpay-e-invoice')."</a>";
            echo "<img class='invoice_issue_loading' src='".plugin_dir_url( __FILE__ )."/admin/asset/icon/ajax-loader.gif"."' style='display:none;'>";
            echo "</div>";
        }
    }
}
function aeeik_admin_enqueue_scripts($hook){
    if ($hook=='edit.php'&&$_GET['post_type']=='shop_order'){
        wp_enqueue_script( 'allpay_e_invoice_invoice_trigger', plugin_dir_url( __FILE__ ) . '/admin/asset/js/invoice-trigger.js' );
    }else if ($hook=='post.php'&&$_GET['action']=='edit'&&get_post_type(get_the_ID())=='shop_order') {
        wp_enqueue_style( 'allpay_e_invoice_editable_css', plugin_dir_url( __FILE__ ) . '/admin/asset/lib/jquery-editable/css/jquery-editable.css"' );
        wp_enqueue_script( 'allpay_e_invoice_jquery_poshytip', plugin_dir_url( __FILE__ ) . '/admin/asset/lib/jquery.poshytip.min.js','jQuery' );
        wp_enqueue_script( 'allpay_e_invoice_editable_js', plugin_dir_url( __FILE__ ) . '/admin/asset/lib/jquery-editable/js/jquery-editable-poshytip.min.js','jQuery' );
        wp_enqueue_script( 'allpay_e_invoice_invoice_meta_edit', plugin_dir_url( __FILE__ ) . '/admin/asset/js/invoice-meta-edit.js' );
    }else{
        return;
    }
}
function aeeik_issue_invoice($order_id){
    include_once('class/AllPay_Invoice.php');
    $allpay_invoice=new WC_Allpay_E_Invoice($order_id);
    $result=$allpay_invoice->Invoice_Issue();
    if(isset($result['RtnCode'])&& $result['RtnCode']==1){
        aeeik_update_post_meta_and_order_note('issue',true,$result,$order_id);
    }else{
        aeeik_update_post_meta_and_order_note('issue',false,$result,$order_id);
    }
}
class WC_Allpay_E_Invoice extends AllInvoice{
    public $order;
    private $test_mode='';
    private $AEEIK_HOST_URL;
    private $test_AEEIK_HOST_URL;
    private $API=array(
        'Invoice'=>array(
            'Issue'=>'Invoice/Issue',
            'DelayIssue'=>'Invoice/DelayIssue',
            'TriggerIssue'=>'Invoice/TriggerIssue',
            'IssueInvalid'=>'Invoice/IssueInvalid'
        ),
        'Notify'=>array(
            'InvoiceNotify'=>'Notify/InvoiceNotify'
        )
    );
    function __construct($order_id){
        parent::__construct();
        global $woocommerce;
        $this->order=new WC_Order($order_id);
        $this->test_mode=(get_option('allpay_e_invoice_test_mode')==1)?true:false;
        if($this->test_mode){
            $this->MerchantID=AEEIK_DEFAULT_OPTIONS::MerchantID;
            $this->HashKey=AEEIK_DEFAULT_OPTIONS::HashKey;
            $this->HashIV=AEEIK_DEFAULT_OPTIONS::HashIV;
        }else{
            $this->MerchantID=get_option('allpay_e_invoice_merchant_id');
            $this->HashKey=get_option('allpay_e_invoice_hash_key');
            $this->HashIV=get_option('allpay_e_invoice_hash_iv');
        }
        $this->TimeStamp=time();
        $source=get_option('allpay_e_invoice_service_source');
        if($source=='allpay'){
            $this->AEEIK_HOST_URL=AEEIK_HOST_URL::ALLPAY;
            $this->test_AEEIK_HOST_URL=AEEIK_HOST_URL::ALLPAY_TEST;
        }else if($source=='ecpay'){
            $this->AEEIK_HOST_URL=AEEIK_HOST_URL::ECPAY;
            $this->test_AEEIK_HOST_URL=AEEIK_HOST_URL::ECPAY_TEST;
        }
    }
    private function init_info(){
        $RelateNumber="aeeik".substr(time(),-5).'x'.$this->order->id;
    	update_post_meta($this->order->id,'_allpay_e_invoice_relative_number',$RelateNumber);
        $this->Send['RelateNumber']=$RelateNumber;
        $this->Send['NotifyURL']=site_url();
        $this->Send['CustomerEmail']=$this->order->billing_email;
        if($this->Send['CustomerEmail']==''){
            $vowels = array(' ','-','(',')');
            $this->Send['CustomerPhone']=str_replace($vowels, "",$this->order->billing_phone);
        }
        if(get_option('allpay_e_invoice_tax_shipping_fee_included')=='1')
            $this->Send['SalesAmount']=$this->order->get_total();
        else
            $this->Send['SalesAmount']=$this->order->get_total()-($this->order->order_shipping+$this->order->get_shipping_tax());
        $TaxType='';
        switch(get_option('allpay_e_invoice_invoice_type')){
            case 'general':
                $this->Send['InvType']=E_InvType::General;
                break;
            case 'special':
                $this->Send['InvType']=E_InvType::Special;
                break;
        }
        switch(get_option('allpay_e_invoice_tax_type')){
            case 'dutiable':
                $TaxType=E_TaxType::Dutiable;
                break;
            case 'zero':
                $TaxType=E_TaxType::Zero;
                break;
            case 'free':
                $TaxType=E_TaxType::Free;
                break;
        }
        $this->Send['TaxType']=$TaxType;
        $taxIncluded=get_option('woocommerce_prices_include_tax');
        $order_items=$this->order->get_items();
        $this->Send['Items']=array();
        foreach($order_items as $item){
            $product=new WC_Product($item['product_id']);
            $Item=array();
            $Item['ItemName']=$item['name'];
            $Item['ItemCount']=$item['qty'];
            $Item['ItemPrice']=$product->get_price();
            $Item['ItemAmount']=(int)($product->get_price()*$item['qty']);
            $Item['ItemWord']='個';
            $Item['ItemTaxType']=$TaxType;
            array_push($this->Send['Items'],$Item);
        }
        $order_discount=(int)$this->order->get_total_discount(false);
        if($order_discount>0){
            $Item=array();
            $Item['ItemName']="總折扣";
            $Item['ItemCount']='1';
            $Item['ItemPrice']=$order_discount*-1;
            $Item['ItemAmount']=$Item['ItemPrice'];
            $Item['ItemWord']='式';
            $Item['ItemTaxType']=$TaxType;
            array_push($this->Send['Items'],$Item);
        }
        if(get_option('allpay_e_invoice_tax_shipping_fee_included')=='1' && $this->order->order_shipping>0){
            $Item=array();
            $Item['ItemName']="運費";
            $Item['ItemCount']='1';
            $Item['ItemPrice']=$this->order->order_shipping+$this->order->get_shipping_tax();
            $Item['ItemAmount']=$this->order->order_shipping+$this->order->get_shipping_tax();
            $Item['ItemWord']='式';
            $Item['ItemTaxType']=$TaxType;
            array_push($this->Send['Items'],$Item);
        }
        $print_mark=get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_invoice_print_mark', true );
        $donate_mark=get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_invoice_donate_mark', true );
        $buyer_name=get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_buyer', true );
        $love_code=explode('-',get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_lovecode', true ))[0];
        $carruer_type=explode('-',get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_invoice_carruer_type', true ))[0];
        $carruer_num=get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_invoice_carruer_num', true );
        $tax_id=get_post_meta( $this->order->id, '_allpay_e_invoice_billing_receipt_company_tax_id', true );
        $this->Send['CustomerIdentifier']=$tax_id;
        if($print_mark=='Yes'){
            $this->Send['CustomerAddr']=$this->order->shipping_address_1;
            if(in_array($buyer_name,array("None","")))
                $this->Send['CustomerName']=$this->order->billing_last_name.$this->order->billing_first_name;
            else
                $this->Send['CustomerName']=$buyer_name;
        }
        if($print_mark=='Yes'){
            $this->Send['Print']='1';
        }else {
            $this->Send['Print']='0';
        }
        if($donate_mark=='Yes'){
            $this->Send['Donation']='1';
            $this->Send['LoveCode']=$love_code;
        }else{
            $this->Send['Donation']='2';
        }
        if(in_array($carruer_type,array('2','3'))){
            $this->Send['CarruerType']=$carruer_type;
            $this->Send['CarruerNum']=$carruer_num;
        }
    }
    public function Invoice_Issue(){
        $this->init_info();
        $this->Invoice_Method = E_InvoiceMethod::INVOICE;
        if($this->test_mode)
            $this->Invoice_Url=$this->test_AEEIK_HOST_URL.$this->API['Invoice']['Issue'];
        else
            $this->Invoice_Url=$this->AEEIK_HOST_URL.$this->API['Invoice']['Issue'];
        $result=$this->Check_Out();
        return $result;
    }
    public function Invoice_IssueInvalid($reason){
        $this->Invoice_Method=E_InvoiceMethod::INVOICE_VOID;
        $this->Send['InvoiceNumber']=get_post_meta($this->order->id,'_allpay_e_invoice_invoice_number',true);
        $this->Send['Reason']=$reason;
        if($this->test_mode)
            $this->Invoice_Url=$this->test_AEEIK_HOST_URL.$this->API['Invoice']['IssueInvalid'];
        else
            $this->Invoice_Url=$this->AEEIK_HOST_URL.$this->API['Invoice']['IssueInvalid'];
        $result=$this->Check_Out();
        return $result;
    }
}
?>
