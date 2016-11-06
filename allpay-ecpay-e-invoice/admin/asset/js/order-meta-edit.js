"use strict";
var $=jQuery;
$(document).ready(function(){
    var order_id=$('#post_ID').val();
    $.fn.editable.defaults.mode = 'inline';
    var text_params={
        type: 'text',  
        pk: 1,
        url: 'admin-ajax.php',
        title: '',
        emptytext:'無',
        params:{
            order_id:order_id,
            action:'invoice_update_order_meta'
        }
    };
    var select_yn_params={
        type: 'select',  
        pk: 1,
        url: 'admin-ajax.php',
        title: '',
        params:{
            order_id:order_id,
            action:'invoice_update_order_meta'
        },
        source: [
              {value:'Yes', text: '是'},
              {value: 'No', text: '否'}
           ]
    }
    var donate_to=[];
    $('#donate_to_list span').each(function(){
        var obj={};
        obj.value=$(this).html();
        obj.text=$(this).html().split('-')[1];
        donate_to.push(obj);
    });
    var donate_to_params_options=[{value:'None', text: '無'}];
    for(var i in donate_to){
        donate_to_params_options.push(donate_to[i]);
    }
    var donate_to_params={
        type: 'select',  
        pk: 1,
        url: 'admin-ajax.php',
        title: '',
        emptytext:'無',
        params:{
            order_id:order_id,
            action:'invoice_update_order_meta'
        },
        source:donate_to_params_options
    }
    var carruer_type_params={
        type: 'select',  
        pk: 1,
        url: 'admin-ajax.php',
        title: '',
        params:{
            order_id:order_id,
            action:'invoice_update_order_meta'
        },
        source:[
            {value:'0-無載具', text: '無載具'},
            {value: '3-手機條碼', text: '手機條碼'},
            {value:'2-自然人憑證',text:'自然人憑證'}
             ]
    }
    $('#buyer').editable(text_params);
    $('#company_tax_id').editable(text_params);
    $('#print_mark').editable(select_yn_params);
    $('#donate_mark').editable(select_yn_params);
    $('#donate_to').editable(donate_to_params);
    $('#carruer_type').editable(carruer_type_params);
    $('#carruer_num').editable(text_params);
});