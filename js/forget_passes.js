function SendPassData(){ 
    var str = $("#form_pass").serialize();
    $.post("/pass_request/admin/forget_passes/add_pass/", str, function(data) {
            $("#info_pass").html(data);
            $("#modal_body").scrollTop(300);
        });              
}

function Filter(value){
    $.get("/pass_request/admin/forget_passes/set_filter/", {filter: value}, function(data) {
                    window.location.reload();
                });             
}

function TakePass(){
    var forget_pass_id = $("#dialog_take_pass_forget_pass_id").val();
    var password = $("#dialog_take_pass_password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/forget_passes/take_pass/", 
          {
              'forget_pass_id' : forget_pass_id,
              'password' : password,
              'csrf_secure': cct
          },
          function(data) {
              if ($.trim(data) == '')
                window.location.reload();
              else
                 $("#dialog_take_pass_error").html(data);
          });
}

/**
 * Обработчик нажатия на кнопку "Змінити номер перепустки"
 */
function LostPass()
{
    var forget_pass_id = $("#forget_pass_id").val();
    var new_pass_id = $("#new_pass_number").val();
    var password = $("#password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/forget_passes/lost_pass/", 
          {
              'forget_pass_id' : forget_pass_id,
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

$(function() {
		$("#dialog_lost_pass").dialog({ autoOpen: false, modal: true, width: 310, resizable: false });
       $("#dialog_take_pass").dialog({ autoOpen: false, modal: true, width: 310, resizable: false });
	});