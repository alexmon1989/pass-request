function Sort(order_by, method) {
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/temp_requests/sort", 
        {
            'order_by': order_by,
            'method': method,
            'csrf_secure': cct
        },
        function(data) {
             window.location.reload();
        });
}


/**
 * Обработчик нажатия на кнопку "Змінити номер перепустки"
 */
function LostPass(id)
{
    var new_pass_id = $("#new_pass_number").val();
    var password = $("#lost_pass_password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/temp_requests/lost_pass/", 
          {
              'id' : id,
              'new_pass_id' :  new_pass_id,
              'password' : password,
              'csrf_secure': cct
          },
          function(data) {
              if ($.trim(data) == '')
                window.location.reload();
              else
                 $("#error_dialog").html(data);
          });
}

function TakePass(id){
    var password = $("#password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/temp_requests/take_pass/", 
          {
              'id' : id,
              'password' : password,
              'csrf_secure': cct
          },
          function(data) {
              if ($.trim(data) == '')
                window.location.reload();
              else
                 $("#validation_errors").html(data);
          });
}

$(function() {
		$("#dialog_lost_pass").dialog({ autoOpen: false, modal: true, width: 310, resizable: false });
	});
    
$(function(){
    $.datepicker.setDefaults(
        $.extend($.datepicker.regional["uk"])
    );
    $("#date_from").datepicker();
    $("#date_to").datepicker();
});