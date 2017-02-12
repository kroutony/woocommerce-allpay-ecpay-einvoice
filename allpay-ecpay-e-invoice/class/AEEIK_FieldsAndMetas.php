<?php
class AEEIK_FieldsAndMetas{
    public function __construct(){
        $this->hooks();
    }
    private function hooks(){
        //自定結帳欄位
        add_filter( 'woocommerce_checkout_fields' ,array($this,'add_custom_checkout_fields'),20);
        //結帳完後更新order_meta
        add_action( 'woocommerce_checkout_update_order_meta',array($this,'update_order_meta_after_checkout'), 20, 2 );
        //顯示電子發票相關資料於thankyou頁面
        add_action( 'woocommerce_thankyou', array($this,'display_custom_meta_to_customer'), 20 );
        //顯示電子發票相關資料於我的帳號內的訂單資料頁面
        add_action( 'woocommerce_view_order', array($this,'display_custom_meta_to_customer'), 20 );
        //顯示電子發票相關資料於後台訂單資料頁面
        add_action( 'woocommerce_admin_order_data_after_billing_address', array($this,'display_custom_meta_to_admin') ,20);
        //於結帳表單後載入css
        add_action( 'woocommerce_before_checkout_form',array($this,'checkout_field_style'));
        //於結帳表單前載入其他額外的隱藏欄位
        add_action( 'woocommerce_after_checkout_form', array($this,'extra_fields'));
    }
    //取得設定內的捐贈單位列表
    private function get_donation_list(){
        $donate_to_option=get_option('allpay_e_invoice_donate_to');
        $donate_to_option=explode("\r\n",$donate_to_option);
        $donate_to=array();
        foreach ($donate_to_option as $key=>$val){
            $donate_to[explode('-',$val)[0]]=explode('-',$val)[1];
        }
        return $donate_to;
    }
    //取得設定內的載具類別
    private function get_carruer_type(){
        $carruer_type=unserialize(get_option('aeeik_carruer_type'));
        return $carruer_type;
    }
    //自定結帳欄位
    public function add_custom_checkout_fields( $fields ) {
        $carruer_type=$this->get_carruer_type();
        $donate_to=$this->get_donation_list();
        $fields['billing']['allpay_e_invoice_billing_receipt_company_tax_id'] = array(
            'label'     => __('Company Tax ID', 'allpay-e-invoice'),
            'placeholder'   => __('8 digits number','allpay-e-invoice'),
            'class'=>array('allpay-e-invoice-fields-group'),
        	'required'  => false,
        	'clear'     => true
        );
    	$fields['billing']['allpay_e_invoice_billing_receipt_buyer'] = array(
            'label'     => __('Receipt Title', 'allpay-e-invoice'),
            'placeholder'   => '',
            'class'=>array('displayNone'),
    		'required'  => true,
        	'clear'     => true,
        	'default'=>'None'
        );
        $fields['billing']['allpay_e_invoice_billing_receipt_invoice_print_mark']=array(
            'type'=>'radio',
            'label'     => __('Print Invoice?', 'allpay-e-invoice'),
            'placeholder'   => '',
            'class'=>array('allpay-e-invoice-fields-group'),
    		'required'  => true,
        	'clear'     => false,
        	'options'=>array(
            	'Yes'=>__('Yes','allpay-e-invoice'),
            	'No'=>__('No','allpay-e-invoice')
        	),
        	'default'=>'No'
        );
        $fields['billing']['allpay_e_invoice_billing_receipt_invoice_donate_mark'] = array(
         	'type'=>"radio",
            'label'     => __('Donate Invoice?', 'allpay-e-invoice'),
            'class'=>array('allpay-e-invoice-fields-group'),
        	'required'  => true,
        	'clear'     => false,
        	'options'=>array(
            	'Yes'=>__('Yes','allpay-e-invoice'),
            	'No'=>__('No','allpay-e-invoice')
        	),
        	'default'=>'No'
        );
        $fields['billing']['allpay_e_invoice_billing_receipt_lovecode']=array(
            'type'=>'select',
            'label'=>__('Donate to','allpay-e-invoice'),
            'class'=>array('displayNone'),
            'options'=>$donate_to
        );
        $fields['billing']['allpay_e_invoice_billing_receipt_invoice_carruer_type']=array(
            'type'=>'select',
            'label'=>__('Carruer Type','allpay-e-invoice'),
            'class'=>array('allpay-e-invoice-fields-group'),
            'required'=>false,
            'options'=>$carruer_type
        );
        $fields['billing']['allpay_e_invoice_billing_receipt_invoice_carruer_num'] = array(
            'label'     => __('Carruer Number', 'allpay-e-invoice'),
            'placeholder'   => __('Please input your Citizen Digital Certificate or Barcode of Phone'),
            'class'=>array('displayNone'),
    		'required'  => true,
        	'clear'     => true,
        	'default'=>'None'
        );
        return $fields;
    }
    //結帳完後更新order_meta
    public function update_order_meta_after_checkout( $order_id, $posted ){
        $carruer_type=$this->get_carruer_type();
        $donate_to=$this->get_donation_list();
        if( isset( $posted['allpay_e_invoice_billing_receipt_company_tax_id'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_company_tax_id',  $posted['allpay_e_invoice_billing_receipt_company_tax_id']  );
        }
        if( isset( $posted['allpay_e_invoice_billing_receipt_buyer'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_buyer',  $posted['allpay_e_invoice_billing_receipt_buyer']  );
        }
        if( isset( $posted['allpay_e_invoice_billing_receipt_invoice_print_mark'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_print_mark',  $posted['allpay_e_invoice_billing_receipt_invoice_print_mark']  );
        }
        if( isset( $posted['allpay_e_invoice_billing_receipt_invoice_donate_mark'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_donate_mark',  $posted['allpay_e_invoice_billing_receipt_invoice_donate_mark']  );
        }
        if( isset( $posted['allpay_e_invoice_billing_receipt_lovecode'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_lovecode',  $posted['allpay_e_invoice_billing_receipt_lovecode'].'-'.$donate_to[$posted['allpay_e_invoice_billing_receipt_lovecode']]  );
        }
        if( isset( $posted['allpay_e_invoice_billing_receipt_invoice_carruer_type'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_carruer_type',  $posted['allpay_e_invoice_billing_receipt_invoice_carruer_type'].'-'.$carruer_type[$posted['allpay_e_invoice_billing_receipt_invoice_carruer_type']]  );
        }
        if( isset( $posted['allpay_e_invoice_billing_receipt_invoice_carruer_num'] ) ) {
            update_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_carruer_num',  $posted['allpay_e_invoice_billing_receipt_invoice_carruer_num'] );
        }
    } 
    //顯示電子發票相關資料於thankyou頁面
    public function display_custom_meta_to_customer( $order_id ){?>
         <table class="shop_table shop_table_responsive additional_info">
            <tbody>
                <tr>
                    <th><?php _e( 'Company Tax ID','allpay-e-invoice' ); ?></th>
                    <td><?php echo get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_company_tax_id',true); ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Receipt Title','allpay-e-invoice' ); ?></th>
                    <td><?php if(get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_buyer', true )!="None")
                                echo get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_buyer', true ); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Print Invoice?', 'allpay-e-invoice'); ?></th>
                    <td><?php _e(get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_print_mark', true ),'allpay-e-invoice' ); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Donate Invoice?', 'allpay-e-invoice'); ?></th>
                    <td><?php _e(get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_donate_mark', true ),'allpay-e-invoice' ); ?></td>
                </tr>
                <?php if(get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_donate_mark', true )=="Yes"): ?>
                <tr>
                    <th><?php _e('Donate to', 'allpay-e-invoice'); ?></th>
                    <td><?php echo explode('-',get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_lovecode', true ))[1]; ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Carruer Type', 'allpay-e-invoice'); ?></th>
                    <td><?php echo explode('-',get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_carruer_type', true ))[1]; ?></td>
                </tr>
                <?php if(explode('-',get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_carruer_type', true ))[0]!='0'): ?>
                <tr>
                    <th><?php _e('Carruer Number', 'allpay-e-invoice'); ?></th>
                    <td><?php if(get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_carruer_num', true )!="None"):
                        echo get_post_meta( $order_id, '_allpay_e_invoice_billing_receipt_invoice_carruer_num', true ); ?></td>
                        <?php endif; ?>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php }
    //顯示電子發票相關資料於後台訂單資料頁面
    public function display_custom_meta_to_admin( $order ){
        $donate_to=$this->get_donation_list();
    ?>
        <div id="donate_to_list" style='display:none;'>
            <?php
            foreach ($donate_to as $key=>$value):
                echo "<span>".$key.'-'.$value."</span>";
            endforeach;
            ?>
        </div>
      	<p class='allpay_e_invoice_admin_order_meta'>
      	    <strong><?php _e( 'Company Tax ID','allpay-e-invoice');?></strong>
      	    <?php if(get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_company_tax_id' ,true)!=''):?>
      	    <a class='order_meta' id='company_tax_id'>
      	        <?php echo get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_company_tax_id' ,true);?>
            </a>
            <?php else: ?>
            <a class='order_meta' id='company_tax_id'></a>
            <?php endif; ?>
        </p>
        <p class='allpay_e_invoice_admin_order_meta'>
            <strong><?php _e( 'Receipt Title','allpay-e-invoice');?></strong>
            <?php if(get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_buyer', true )!='None'):?>
            <a class='order_meta' id="buyer">
                <?php echo get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_buyer', true );?>
            </a>
            <?php else: ?>
            <a class="order_meta" id="buyer"></a>
            <?php endif; ?>
        </p>
        <p class='allpay_e_invoice_admin_order_meta'>
	        <strong><?php _e( 'Print Invoice?','allpay-e-invoice' );?></strong>
	        <a class='order_meta' id='print_mark'><?php _e(get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_invoice_print_mark', true ),'allpay-e-invoice');?> </a>
	    </p>
	    <p class='allpay_e_invoice_admin_order_meta'>
	        <strong><?php _e( 'Donate Invoice?','allpay-e-invoice' );?></strong>
	        <a class='order_meta' id="donate_mark"><?php _e(get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_invoice_donate_mark', true ),'allpay-e-invoice');?></a>
        </p>
	    <p class='allpay_e_invoice_admin_order_meta'>
	        <strong><?php _e( 'Donate to','allpay-e-invoice' );?>:</strong>
	        <?php if(get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_invoice_donate_mark', true )=='Yes') :?>
	        <a class='order_meta' id="donate_to">
	            <?php echo get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_lovecode', true );?>
            </a>
	        <?php else: ?>
	        <a class='order_meta' id="donate_to"></a>
	        <?php endif; ?>
        </p>
	    <p class='allpay_e_invoice_admin_order_meta'>
	        <strong><?php _e( 'Carruer Type','allpay-e-invoice' );?>:</strong>
	        <a class='order_meta' id="carruer_type"><?php echo explode('-',get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_invoice_carruer_type', true ))[1];?></a>
        </p>
        <p class='allpay_e_invoice_admin_order_meta'>
            <strong><?php _e( 'Carruer Number','allpay-e-invoice' );?>:</strong>
            <?php if(explode('-',get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_invoice_carruer_type', true ))[0]!='0'):?>
            <a class='order_meta' id="carruer_num">
                    <?php echo get_post_meta( $order->id, '_allpay_e_invoice_billing_receipt_invoice_carruer_num', true );?>
            </a>
            <?php else: ?>
            <a class='order_meta' id="carruer_num"></a>
            <?php endif; ?>
        </p>
    <?php } 
    //於結帳表單後載入css
    public function checkout_field_style(){ ?>
        <style>
            .displayBlock{
                display:block !important;
            }
            .displayNone{
                display:none !important;
            }
            .allpay-e-invoice-fields-group{
                display:none !important;
            }
            .field_alert{
                    text-align: center;
                    border: 1px solid;
                    border-radius: 3px;
                    margin-bottom: 20px;
            }
            .alert_red{
                border-color: red;
                color: red;
            }
            .alert_blue{
                border-color: rgb(0,0,255);
                color: rgb(0,0,255);
            }
            .alert_green{
                border-color:#69bf29;
                color:#69bf29;
            }
            .alert_orange{
                border-color:#ff6400;
                color:#ff6400;
            }
        </style>
    <?php } 
    //於結帳表單前載入其他額外的隱藏欄位
    public function extra_fields(){
        echo "<h3 class='allpay-e-invoice-fields-group' id='invoice_title' style='display:none;'>".__('Invoice Information','allpay-e-invoice')."</h3>";
        echo "<div id='tax_id_alert' class='field_alert alert_red' style='display:none;'>".__("Company Tax ID should be 8-digits number","allpay-e-invoice")."</div>";
        echo "<div id='carruer_cdc_number_alert' class='field_alert alert_red' style='display:none;'>".__("The number of citizen digital certificate should be 2 english letters combined with 14-digits number","allpay-e-invoice")."</div>";
        echo "<div id='carruer_pbc_number_alert' class='field_alert alert_red' style='display:none;'>".__("The number of barcode should be '/' combined with 7-digits Number,Letter,'+','-' or '.'","allpay-e-invoice")."</div>";
        echo '<script src="'.plugins_url('../asset/js/checkout-field.min.js',__FILE__).'"></script>';
    } 
} 
?>