var $=jQuery;
$(document).ready(function(){
  $('.issue_button').on('click',function(e){
    var $this=$(this);
    var $loading_icon=$this.parent().children('.invoice_issue_loading');
    $loading_icon.attr('style','');
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/wp-admin/admin-ajax.php',
        timeout:10000,
        data: {
              action:'invoice_trigger_issue',
              order_id:$(this).attr('value')
        },
        success: function(resp){
          var data=JSON.parse(resp);
          if(data.status){
            window.location.reload();
          }else{
            $loading_icon.attr('style','display:none;');
            alert('開立發票錯誤，請聯絡管理員檢查瀏覽器主控台，或訂單備註');
            console.log(data.message);
          }
        },
        error:function(){
          alert('伺服器回應時間過長，請再試一次。');  
        }
    });
  });
  $('.issue_invalid_button').on('click',function(e){
    var $this=$(this);
    var $loading_icon=$this.parent().children('.invoice_issue_loading');
    e.preventDefault();
    var reason=prompt('請輸入作廢原因(必填，限制20字):','');
    if(reason!=null &&reason!=''){
      $loading_icon.attr('style','');
      $.ajax({
        type: "POST",
        url: '/wp-admin/admin-ajax.php',
        timeout:10000,
        data: {
              action:'invoice_invalid_issue',
              order_id:$(this).attr('value'),
              reason:reason
        },
        success: function(resp){
          var data=JSON.parse(resp);
          if(data.status){
            window.location.reload();
          }else{
            $loading_icon.attr('style','display:none;');
            alert('作廢發票發票錯誤，請聯絡管理員檢查瀏覽器主控台，或訂單備註');
            console.log(data.message);
          }
        },
        error:function(){
          alert('伺服器回應時間過長，請再試一次。');  
        }
      });
    }
  });
});
