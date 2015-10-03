function SendContractData() {
    var str = $("#form_contract").serialize();
    $.post("/pass_request/admin/contracts/add/", str, function(data) {
        $("#info_pass").html(data);
        });
}

function FillContractForm() {
        $("#form_contract").trigger("reset");
        $("#info_pass").html("");
}

function Sort(order_by, method) {
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/contracts/sort", 
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
function LostPass()
{
    var contract_id = $("#contract_id").val();
    var new_pass_id = $("#new_pass_number").val();
    var password = $("#lost_pass_password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/contracts/lost_pass/", 
          {
              'contract_id' : contract_id,
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

/**
 * Обработчик нажатия на кнопку "Редагувати"
 */
function EditContract(){
    var contract_id = $("#contract_id").val();
    var date_to = $("#date_to").val();
    var visitor = $("#visitor").val();
    var applicant_id = $("#applicant_id").val();
    var password = $("#password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/contracts/edit_contract/", 
          {
              'contract_id' : contract_id,
              'date_to' :  date_to,
              'visitor' : visitor,
              'applicant_id' : applicant_id,
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

function TakePass(){
    var contract_id = $("#contract_id").val();
    var password = $("#password").val();
    var cct = $.cookie("csrf_cookie_name");
    
    $.post("/pass_request/admin/contracts/take_pass/", 
          {
              'contract_id' : contract_id,
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
    
    // Изображение
    var img_url = $("#photo_filename").val();
    if (img_url != ""){
        img_url ="/pass_request/uploads/" + img_url;
        $("#photo_img").attr("src", img_url);
        $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-primary" onclick="DoNotUseThisPhoto(); return false"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
    }
});

function ajaxFileUpload()
{ 
    $("#loading")
    .ajaxStart(function(){
        $(this).show();
    })
    .ajaxComplete(function(){
        $(this).hide();
    });
    
    $.ajaxFileUpload
    (
        {
            url:'/pass_request/admin/requests/doAjaxFileUpload',
            secureuri:false,
            fileElementId:'photo',
            dataType: 'json',
            data:{name:'logan', id:'id'},
            success: function (data, status)
            {
                if (data.url.length > 0){
                    $("#photo_img").attr("src", data.url);
                    $("#photo_filename").val(data.file_name);
                    $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-primary" onclick="DoNotUseThisPhoto(); return false"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
                    $("#cancel_photo_btn_div").show();
                } else {
                    alert(data);
                    }  
            },
            error: function (data, status, e)
            {
                alert('Невірне розширення файлу!');
            }
        }
    )
    return false;
}

function DoNotUseThisPhoto()
{
    // request_id
    var contract_id = $("#contract_id").val();
    
    // Ajax запрос на получение id фото
    $.get('/pass_request/admin/contracts/get_photo_id/' + contract_id, 
         function(data){
             var photo_id = data * 1;
             // Назначаем URL изображения
             if (photo_id != 0)
                var img_url = '/pass_request/photos/get_image/' + photo_id;
             else
                var img_url = '/pass_request/images/photo_missed.jpeg';
             $("#photo_img").attr("src", img_url);
             // Очищаем photo_filename
             $('#photo_filename').val('');
             $("#cancel_photo_btn_div").hide();
         }
    );
}