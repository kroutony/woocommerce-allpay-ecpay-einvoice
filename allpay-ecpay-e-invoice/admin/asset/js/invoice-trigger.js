"use strict";
jQuery(function($) {
  $(document).ready(function() {
    $('.issue_button').on('click', issue_invoice_callback);
    $('.allpay_e_invoice_info .invalid_button').on('click',invalid_invoice_callback );
    function issue_invoice_callback(e){
      var $this = $(this);
      var $loading_icon = $this.parent().children('.invoice_issue_loading');
      var order_id=$this.attr('value');
      $loading_icon.attr('style', '');
      e.preventDefault();
      $.ajax({
        type: "POST",
        url: 'admin-ajax.php',
        timeout: 10000,
        data: {
          action: 'aeeik_issue_invoice',
          order_id: order_id
        },
        success: function(resp) {
          var data = JSON.parse(resp);
          if (data.status) {
            // window.location.reload();
            $('#post-'+order_id+' .allpay_e_invoice_column.column-allpay_e_invoice_column').html(data.result);
            $('#post-'+order_id+' .allpay_e_invoice_info .invalid_button').on('click',invalid_invoice_callback);
          }
          else {
            $loading_icon.attr('style', 'display:none;');
            alert(data.message.RtnMsg+",錯誤代碼:"+data.message.RtnCode);
            console.log(data.message);
          }
        },
        error: function() {
          alert('伺服器回應時間過長或開立發票錯誤，請再試一次或聯絡管理員檢查後端錯誤紀錄。');
        }
      });
    }
    function invalid_invoice_callback(e) {
      var $this = $(this);
      var $loading_icon = $this.parent().children('.invoice_issue_loading');
      var order_id=$this.attr('value');
      e.preventDefault();
      var reason = prompt('請輸入作廢原因(必填),限制20字元,中文2字元,英文1字元:', '');
      if (reason != null && reason != '') {
        $loading_icon.attr('style', '');
        $.ajax({
          type: "POST",
          url: 'admin-ajax.php',
          timeout: 10000,
          data: {
            action: 'aeeik_invalid_invoice',
            order_id: order_id,
            reason: reason
          },
          success: function(resp) {
            var data = JSON.parse(resp);
            if (data.status) {
              // window.location.reload();
              $('#post-'+order_id+' .allpay_e_invoice_column.column-allpay_e_invoice_column').html(data.result);
              $('#post-'+order_id+' .allpay_e_invoice_info .issue_button').on('click',issue_invoice_callback);
            }
            else {
              $loading_icon.attr('style', 'display:none;');
              alert(data.message.RtnMsg+",錯誤代碼:"+data.message.RtnCode);
              console.log(data.message);
            }
          },
          error: function() {
            alert('伺服器回應時間過長或作廢發票錯誤，請再試一次或聯絡管理員檢查後端錯誤紀錄。');
          }
        });
      }
    }
  });
});