<?php
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
            if($item['variation_id']!=0)
                $product=new WC_Product_Variation($item['variation_id']);
            else
                $product=new WC_Product($item['product_id']);
            $Item=array();
            $Item['ItemName']=$item['name'];
            $Item['ItemCount']=$item['qty'];
            if($item['line_total']==0)
                continue;
            $Item['ItemAmount']=$item['line_total'];
            $Item['ItemPrice']=$item['line_total']/$item['qty'];
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
        if(preg_match('/^[0-9]{8}$/',$tax_id))
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
            if(preg_match('/^\/{1}[0-9a-zA-Z+-.]{7}$/',$carruer_num)||
            preg_match('/^[a-zA-Z]{2}[0-9]{14}$/',$carruer_num))
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