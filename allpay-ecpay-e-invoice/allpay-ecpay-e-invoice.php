<?php
/*
Plugin Name: allpay-ecpay-e-invoice
Plugin URI: http://blog.krypds.com/2016/11/07/wordpress-woocommerce-%E6%AD%90%E4%BB%98%E5%AF%B6%E7%B6%A0%E7%95%8C%E7%A7%91%E6%8A%80-%E9%9B%BB%E5%AD%90%E7%99%BC%E7%A5%A8%E4%B8%B2%E6%8E%A5%E5%A4%96%E6%8E%9B/
Description: allpay-ecpay-e-invoice
Version: 1.2.0
Date: 2017-02-03
Author: kroutony
Author URI: http://blog.krypds.com/
Text Domain: allpay-e-invoice
Domain Path: /languages
*/
?>
<?php
require_once('class/AllPay_Invoice.php');
require_once('class/WC_Allpay_E_Invoice.php');
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
    register_uninstall_hook(__FILE__,'aeeik_delete_options');
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
function aeeik_delete_options(){
    delete_option('allpay_e_invoice_test_mode');
    delete_option('allpay_e_invoice_merchant_id');
    delete_option('allpay_e_invoice_hash_key');
    delete_option('allpay_e_invoice_hash_iv');
    delete_option('allpay_e_invoice_enabled');
    delete_option('allpay_e_invoice_service_source');
    delete_option('allpay_e_invoice_issue_mode');
    delete_option('allpay_e_invoice_invoice_type');
    delete_option('allpay_e_invoice_tax_type');
    delete_option('allpay_e_invoice_tax_shipping_fee_included');
}
add_action('plugins_loaded', 'load_AEEIK_Plugin');
function load_AEEIK_Plugin() {
    return AEEIK_Plugin::get_instance();
}
class AEEIK_Plugin{
    private static $instance;
    public function __construct(){
        include_once('admin/setting.php');
        $plugin_enabled=get_option('allpay_e_invoice_enabled');
        if($plugin_enabled=='1'){
            $method=get_option('allpay_e_invoice_issue_mode');
            if($method=="auto"){
                add_action('woocommerce_order_status_processing',array($this,'issue_invoice'));
            }
            add_filter('manage_edit-shop_order_columns',array($this,'add_column_in_orders_page'));
            add_action('manage_shop_order_posts_custom_column',array($this,'add_column_action'));
            add_action('admin_enqueue_scripts',array($this,'enqueue_admin_scripts'));
            add_action('wp_ajax_aeeik_issue_invoice',array($this,'ajax_issue_invoice'));
            add_action('wp_ajax_aeeik_invalid_invoice',array($this,'ajax_invalid_invoice'));
            add_action('wp_ajax_aeeik_edit_invoice_meta',array($this,'ajax_edit_invoice_meta'));
            load_plugin_textdomain('allpay-e-invoice',false,basename(dirname( __FILE__ )).'/languages');
        }
    }
    public static function get_instance(){
        if (is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
    }
    public function ajax_edit_invoice_meta(){
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
    public function ajax_issue_invoice(){
        if(isset($_POST['order_id'])){
            $order_id=$_POST['order_id'];
            include_once('class/AllPay_Invoice.php');
            $allpay_invoice=new WC_Allpay_E_Invoice($order_id);
            $result=$allpay_invoice->Invoice_Issue();
            if(isset($result['RtnCode'])&& $result['RtnCode']==1){
                $this->update_post_meta_and_order_note('issue',true,$result,$order_id);
                $result="<div class='allpay_e_invoice_info'>".
                "<div>".__('Invoice Number：','allpay-e-invoice')."</div>".
                "<div>".get_post_meta($order_id,'_allpay_e_invoice_invoice_number',true)."</div>".
                "<div>".__('Invoice Issue Date：','allpay-e-invoice')."</div>".
                "<div>".get_post_meta($order_id,'_allpay_e_invoice_invoice_date',true)."</div>".
                "<a class='button invalid_button' value='".$order_id."'>".__('Invalid invoice','allpay-e-invoice')."</a>".
                "<img class='invoice_issue_loading' src='".plugin_dir_url( __FILE__ )."/admin/asset/icon/ajax-loader.gif"."' style='display:none;'>".
                "</div>";
                $response=array(
                    "status"=>true,
                    'result'=>$result
                );
                echo json_encode($response);
            }else{
                $this->update_post_meta_and_order_note('issue',false,$result,$order_id);
                $response=array(
                    "status"=>false,
                    "message"=>$result
                );
                echo json_encode($response);
            }
            wp_die();
        }
    }
    public function ajax_invalid_invoice(){
        if(isset($_POST['order_id'])){
            $order_id=$_POST['order_id'];
            include_once('class/AllPay_Invoice.php');
            $allpay_invoice=new WC_Allpay_E_Invoice($order_id);
            $result=$allpay_invoice->Invoice_IssueInvalid($_POST['reason']);
            if(isset($result['RtnCode'])&& $result['RtnCode']==1){
                $this->update_post_meta_and_order_note('invalid',true,$result,$order_id);
                $result="<div class='allpay_e_invoice_info' style='color:red;'>".
                "<div>".__('Invalid Invoice：','allpay-e-invoice')."</div>".
                "<div>".get_post_meta($order_id,'_allpay_e_invoice_invoice_number',true)."</div>".
                "<div>".__('Invoice Invalid Date：','allpay-e-invoice')."</div>".
                "<div>".get_post_meta($order_id,'_allpay_e_invoice_invoice_issue_invalid_date',true)."</div>".
                "<a class='button issue_button' value='".$order_id."'>".__('Re-issue invoice','allpay-e-invoice')."</a>".
                "<img class='invoice_issue_loading' src='".plugin_dir_url( __FILE__ )."/admin/asset/icon/ajax-loader.gif"."' style='display:none;'>".
                "</div>";
                $response=array(
                    "status"=>true,
                    'result'=>$result
                );
                echo json_encode($response);
            }else{
                $this->update_post_meta_and_order_note('invalid',false,$result,$order_id);
                $response=array(
                    "status"=>false,
                    "message"=>$result
                );
                echo json_encode($response);
            }
            wp_die();
        }
    }
    private function update_post_meta_and_order_note($action,$success,$result,$order_id){
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
    public function add_column_in_orders_page($columns ){
        $columns['allpay_e_invoice_column']=__('E Invoice','allpay-e-invoice');
        return $columns;
    }
    public function add_column_action( $column ){
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
                echo "<a class='button invalid_button' value='".$the_order->id."'>".__('Invalid invoice','allpay-e-invoice')."</a>";
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
    public function enqueue_admin_scripts($hook){
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
    function issue_invoice($order_id){
        include_once('class/AllPay_Invoice.php');
        $allpay_invoice=new WC_Allpay_E_Invoice($order_id);
        $result=$allpay_invoice->Invoice_Issue();
        if(isset($result['RtnCode'])&& $result['RtnCode']==1){
            $this->update_post_meta_and_order_note('issue',true,$result,$order_id);
        }else{
            $this->update_post_meta_and_order_note('issue',false,$result,$order_id);
        }
    }
}
?>
