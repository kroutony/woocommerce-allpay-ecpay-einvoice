"use strict";
jQuery(function($){
    var prefix_allpay_e_invoice_billing_receipt='allpay_e_invoice_billing_receipt';
    var $invoice_title=$('#invoice_title');
    var $tax_id_alert=$("#tax_id_alert");
    var $carruer_cdc_number_alert=$('#carruer_cdc_number_alert');
    var $carruer_pbc_number_alert=$('#carruer_pbc_number_alert');
    var $allpay_e_invoice_billing_receipt_company_tax_id=$('#'+prefix_allpay_e_invoice_billing_receipt+'_company_tax_id');
    var $input_name_allpay_e_invoice_billing_receipt_invoice_donate_mark=$('input[name='+prefix_allpay_e_invoice_billing_receipt+'_invoice_donate_mark]');
    var $input_name_allpay_e_invoice_billing_receipt_invoice_print_mark=$('input[name='+prefix_allpay_e_invoice_billing_receipt+'_invoice_print_mark]');
    var $allpay_e_invoice_billing_receipt_invoice_print_mark_No=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_print_mark_No');
    var $label_for_allpay_e_invoice_billing_receipt_invoice_print_mark_No=$('label[for='+prefix_allpay_e_invoice_billing_receipt+'_invoice_print_mark_No]');
    var $allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_donate_mark_Yes');
    var $label_for_allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes=$('label[for='+prefix_allpay_e_invoice_billing_receipt+'_invoice_donate_mark_Yes]');
    var $allpay_e_invoice_billing_receipt_lovecode_field=$('#'+prefix_allpay_e_invoice_billing_receipt+'_lovecode_field');
    var $allpay_e_invoice_billing_receipt_buyer_field=$('#'+prefix_allpay_e_invoice_billing_receipt+'_buyer_field');
    var $allpay_e_invoice_billing_receipt_buyer=$('#'+prefix_allpay_e_invoice_billing_receipt+'_buyer');
    var $allpay_e_invoice_billing_receipt_invoice_carruer_type_field=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_carruer_type_field');
    var $allpay_e_invoice_billing_receipt_invoice_carruer_type_option_value_0=$("#"+prefix_allpay_e_invoice_billing_receipt+"_invoice_carruer_type option[value='0']");
    var $allpay_e_invoice_billing_receipt_invoice_carruer_num_field=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_carruer_num_field');
    var $allpay_e_invoice_billing_receipt_invoice_carruer_num=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_carruer_num');
    var $allpay_e_invoice_billing_receipt_invoice_carruer_type=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_carruer_type');
    var $allpay_e_invoice_billing_receipt_invoice_print_mark_Yes=$('#'+prefix_allpay_e_invoice_billing_receipt+'_invoice_print_mark_Yes');
    var $label_for_allpay_e_invoice_billing_receipt_invoice_print_mark_Yes=$('label[for='+prefix_allpay_e_invoice_billing_receipt+'_invoice_print_mark_Yes]');
    var $allpay_e_invoice_billing_receipt_company_tax_id_field=$('#'+prefix_allpay_e_invoice_billing_receipt+'_company_tax_id_field');
    var place_order_button_selector='input[name=woocommerce_checkout_place_order]';
    var Regxp={
        taxId:"^[0-9]{8}$",
        pbcNumber:"^\/{1}[0-9a-zA-Z+-.]{7}$",
        cdcNumber:"^[a-zA-Z]{2}[0-9]{14}$"
    }
    $(window).load(function(){
       $('.allpay-e-invoice-fields-group').each(function(){
            $invoice_title.insertBefore($allpay_e_invoice_billing_receipt_company_tax_id_field);
            $tax_id_alert.insertAfter($allpay_e_invoice_billing_receipt_company_tax_id_field);
            $carruer_cdc_number_alert.insertAfter($allpay_e_invoice_billing_receipt_invoice_carruer_num_field);
            $carruer_pbc_number_alert.insertAfter($allpay_e_invoice_billing_receipt_invoice_carruer_num_field);
            $invoice_title.removeAttr('style');
            $tax_id_alert.removeAttr('style');
            $carruer_cdc_number_alert.removeAttr('style');
            $carruer_pbc_number_alert.removeAttr('style');
            $tax_id_alert.dn();
            $carruer_cdc_number_alert.dn();
            $carruer_pbc_number_alert.dn();
            $(this).removeClass('allpay-e-invoice-fields-group');
       }); 
    });
    $(document).ready(function(){
        $.fn.extend({
            dn:function(){
                this.addClass('displayNone');
            },
            dd:function(){
                this.removeClass('displayNone');
            },
            db:function(){
                this.addClass('displayBlock');
            },
            dat:function(){
                this.attr('disabled',true);
            },
            daf:function(){
                this.attr('disabled',false);
            },
            ss:function(){
                this.attr('selected',true);
            }
        });
        //統一編號欄位輸入事件
        $allpay_e_invoice_billing_receipt_company_tax_id.on('input',function(){   
            var val=$(this).val();
            //如統一編號有值
            if(val!=''){     
                //符合統編規則，八碼數字
                if(val.match(Regxp.taxId)){ 
                    //取消結帳按鈕鎖定
                    $(place_order_button_selector).daf();
                    //隱藏統編提示
                    $tax_id_alert.dn();
                //不符合統編規則
                }else{   
                    //結帳按鈕鎖定
                    $(place_order_button_selector).dat();
                    //顯示統編提示
                    $tax_id_alert.dd();
                }
                //捐贈設為-否
                $input_name_allpay_e_invoice_billing_receipt_invoice_donate_mark[1].checked=true; 
                //列印設為-是
                $input_name_allpay_e_invoice_billing_receipt_invoice_print_mark[0].checked=true; 
                //隱藏列印選項No，只顯示Yes
                $allpay_e_invoice_billing_receipt_invoice_print_mark_No.dn();  
                $label_for_allpay_e_invoice_billing_receipt_invoice_print_mark_No.dn();
                //隱藏捐贈選項Yes，只顯示No
                $allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dn();
                $label_for_allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dn();
                //隱藏捐贈單位選項
                $allpay_e_invoice_billing_receipt_lovecode_field.dn();
                //顯示買受人欄位
                $allpay_e_invoice_billing_receipt_buyer_field.dd();
                $allpay_e_invoice_billing_receipt_buyer.val('');
                //隱藏載具選項
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_field.dn();
                //選擇無載具
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_option_value_0.ss();
                //隱藏載具編號欄位
                $allpay_e_invoice_billing_receipt_invoice_carruer_num_field.dn();
                $allpay_e_invoice_billing_receipt_invoice_carruer_num.val('None');
                //隱藏載具編號提示
                $carruer_pbc_number_alert.dn();
                $carruer_cdc_number_alert.dn();
            //如統一編號沒有值
            }else{
                //取消結帳按鈕鎖定
                $(place_order_button_selector).daf();
                //顯示列印選項No，Yes與No都顯示
                $allpay_e_invoice_billing_receipt_invoice_print_mark_No.dd()
                $label_for_allpay_e_invoice_billing_receipt_invoice_print_mark_No.dd();
                //顯示捐贈選項Yes，Yes與No都顯示
                $allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dd();
                $label_for_allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dd();
                //顯示隱藏買受人欄位，設值為None
                $allpay_e_invoice_billing_receipt_buyer_field.dn();
                $allpay_e_invoice_billing_receipt_buyer.val('None');
                //隱藏統編警示
                if(!$tax_id_alert.hasClass('displayNone')){
                    $tax_id_alert.addClass('displayNone');
                }
                //顯示載具選項
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_field.dd();
                //列印設為-否
                $input_name_allpay_e_invoice_billing_receipt_invoice_print_mark[1].checked=true; 
            }
        }); 
        //統一編號欄位移出事件
        $allpay_e_invoice_billing_receipt_company_tax_id.focusout(function(){
            var val=$(this).val();
            //有值，且不符合統編規則，八碼數字
            if(val.match(Regxp.taxId)==null && val!=''){
                //結帳按鈕鎖定
                $(place_order_button_selector).dat();
                //顯示統編警示
                $tax_id_alert.dd();
            //無值，或符合統編規則
            }else{
                //取消結帳按鈕鎖定
                $(place_order_button_selector).daf();
                //隱藏統編警示
                $tax_id_alert.dn();
            }
            //有值
            if(val!=''){
                //隱藏載具選項
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_field.dn();
                //選擇無載具
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_option_value_0.ss();
                //隱藏載具編號欄位
                $allpay_e_invoice_billing_receipt_invoice_carruer_num_field.dn();
                $allpay_e_invoice_billing_receipt_invoice_carruer_num.val('None');
            //無值
            }else{
                //顯示載具選項
                // $allpay_e_invoice_billing_receipt_invoice_carruer_type_field.dd();
            }
        });
        //載具編號輸入事件
        $allpay_e_invoice_billing_receipt_invoice_carruer_num.on('input',function(){
            var val=$(this).val();
            var type=$allpay_e_invoice_billing_receipt_invoice_carruer_type.val();
            //如果類型為手機條碼
            if(type=='3'){
                if(!val.match(Regxp.pbcNumber) && val!=''){
                    //顯示警示
                    $carruer_pbc_number_alert.dd();
                    $(place_order_button_selector).dat();
                }else{
                    //隱藏警示
                    $carruer_pbc_number_alert.dn();
                    $(place_order_button_selector).daf();
                }
            //如果類型為自然人憑證
            }else if(type=='2'){
                if(!val.match(Regxp.cdcNumber) && val!=''){
                    //顯示警示
                    $carruer_cdc_number_alert.dd();
                    $(place_order_button_selector).dat();
                }else{
                    //隱藏警示
                    $carruer_cdc_number_alert.dn();
                    $(place_order_button_selector).daf();
                }
            }
        });
        //列印選項變更事件
        $input_name_allpay_e_invoice_billing_receipt_invoice_print_mark.on('change',function(){
            if($(this).val()=='Yes'){
                //隱藏載具選項
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_field.dn();
                //選擇無載具
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_option_value_0.ss();
                //隱藏載具編號欄位
                $allpay_e_invoice_billing_receipt_invoice_carruer_num_field.dn();
                $allpay_e_invoice_billing_receipt_invoice_carruer_num.val('None');
                //捐贈設為-否
                $input_name_allpay_e_invoice_billing_receipt_invoice_donate_mark[1].checked=true; 
                //隱藏捐贈選項Yes，只顯示No
                $allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dn();
                $label_for_allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dn();
            }else{
                //顯示載具選項
                $allpay_e_invoice_billing_receipt_invoice_carruer_type_field.dd();
                //顯示捐贈選項Yes，都顯示
                $allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dd();
                $label_for_allpay_e_invoice_billing_receipt_invoice_donate_mark_Yes.dd();
            }
        });
        //捐贈選項變更事件
        $input_name_allpay_e_invoice_billing_receipt_invoice_donate_mark.on('change',function(){
            //如捐贈為Yes
            if($(this).val()=='Yes'){
                //顯示捐贈單位選項
                $allpay_e_invoice_billing_receipt_lovecode_field.dd();
                //列印設為-否
                $input_name_allpay_e_invoice_billing_receipt_invoice_print_mark[1].checked=true;
                //隱藏列印選項Yes，只顯示No
                $allpay_e_invoice_billing_receipt_invoice_print_mark_Yes.dn();  
                $label_for_allpay_e_invoice_billing_receipt_invoice_print_mark_Yes.dn();
                //隱藏買受人欄位
                $allpay_e_invoice_billing_receipt_buyer_field.dn();
                $allpay_e_invoice_billing_receipt_buyer.val('None');
                //鎖定統編欄位
                $allpay_e_invoice_billing_receipt_company_tax_id.dat();
                $allpay_e_invoice_billing_receipt_company_tax_id.val('');
            //如捐贈為No
            }else{
                //隱藏捐贈單位選項
                $allpay_e_invoice_billing_receipt_lovecode_field.dn();
                //顯示列印選項Yes，兩者都顯示
                $allpay_e_invoice_billing_receipt_invoice_print_mark_Yes.dd();  
                $label_for_allpay_e_invoice_billing_receipt_invoice_print_mark_Yes.dd();
                //取消鎖定統編欄位
                $allpay_e_invoice_billing_receipt_company_tax_id.daf();
            }
        });
        //載具類型選項變更事件
        $allpay_e_invoice_billing_receipt_invoice_carruer_type.on('change',function(){
            var val=$(this).val();
            //如為手機載具或自然人憑證載具
            if(['2','3'].indexOf(val)!=-1){
                //顯示載具編號欄位
                $allpay_e_invoice_billing_receipt_invoice_carruer_num_field.dd();
                $allpay_e_invoice_billing_receipt_invoice_carruer_num.val('');
            //如為無載具
            }else if(['0'].indexOf(val)!=-1){
                //隱藏載具編號欄位
                $allpay_e_invoice_billing_receipt_invoice_carruer_num_field.dn();
                $allpay_e_invoice_billing_receipt_invoice_carruer_num.val('None');
            }
            $carruer_pbc_number_alert.dn();
            $carruer_cdc_number_alert.dn();
        });
    });
   
});