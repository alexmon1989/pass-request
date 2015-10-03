function parseDMY(str) {return new Date(str.slice(6, 10), str.slice(3, 5), str.slice(0, 2));}
    
function ActivateStatusField(){
    var d_f = $("#date_from").val();
    d_f = d_f.split(".");
    d_f = d_f[1] + "/" + d_f[0] + "/" + d_f[2];
    d_f = Date.parse(d_f);
    d_f = d_f*1;
    if (isNaN(d_f)){
        $("#status").val("1");
        $("#status").attr("disabled", true);
        return false;
    }

    var cur_d = new Date();
    cur_d = Date.parse(cur_d);
    cur_d = cur_d * 1;

    if (d_f > cur_d) {
        $("#status").val("1");
        $("#status").attr("disabled", true);
    }
    else
        $("#status").attr("disabled", false);
    return true;
}

/**
* Отправка формы на сервер
*/
function SendPassFrom() { 
     var password = $("#password").val();
     $.get("admin/requests/check_password/", {password: password}, function(data) {
            if (data == "valid") {
                $("#form_password").val(password);
                $("#edit_form").submit();
            } else
            {
                $("#error_password").text("Невірний пароль!");
                $("#password").val("");
            }   
        });
}

/**
 * Пометить пропуск как утерянный и закрыть заявку
 */
function LostPass(request_id) { 
    var password = $("#dialog_lost_pass_password").val();
    var cct = $.cookie("csrf_cookie_name");
    $.get("admin/requests/check_password/", {password: password}, function(data) {
        if (data == "valid") {
               $.post("admin/requests/lost_pass/",
                {
                    'request_id' : request_id,
                    'csrf_secure': cct
                }, 
                    function(data) { 
                        window.location.reload();
                    }
                );
        } else {
            $("#dialog_lost_pass_error_password").text("Невірний пароль!");
            $("#dialog_lost_pass_password").val("");
        }   
    });
}

function ShowReasonAddForm(date_from, date_to){
    var s = date_to.split(".");
    s = s[1] + "/" + s[0] + "/" + s[2];
    var d_t = Date.parse(s);
    d_t = d_t * 1;
    var cur_d = new Date();
    cur_d = Date.parse(cur_d);
    cur_d = cur_d * 1;

    s = date_from.split(".");
    s = s[1] + "/" + s[0] + "/" + s[2];
    var d_f = Date.parse(s);
    d_f = d_f * 1;

    if (d_t > cur_d && d_t > d_f)
    {
        $("#tr_reason").slideDown("slow");
        $("#modal_body").scrollTop(300);                        
    }
    else
    {
        $("#tr_reason").slideUp("slow");
        $("#reason").val("");
    }
}

/**
 * Обработчик нажатия на show_only_my_reqs
 */
function ShowOnlyMyReqs(applicant_id){
    var checked = $("#show_only_my_reqs").attr("checked");
    var cct = $.cookie("csrf_cookie_name");    
    if (checked){        
        $.post("/admin/requests/set_applicant_id/",
                    {
                        'applicant_id' : applicant_id,
                        'csrf_secure': cct
                    }, 
                        function(data) { 
                            window.location = 'admin/requests/';
                        }
                    );
    } else {
        $.post("/admin/requests/unset_applicant_id/",
                    {
                        'csrf_secure': cct
                    }, 
                        function(data) { 
                            window.location = 'admin/requests/';
                        }
                    );
      }
}

function FillPassForm() {
    $("#info_pass").html("");
    //$("#pass_number").attr("disabled", true);
    $("#pass_div").hide("slow");
}

function SendPassData() { 
        var str = $("#form_pass").serialize();
        $.post("/admin/requests/add_request", str, function(data) {
                $("#info_pass").html(data);
                $("#modal_body").scrollTop(300);
            });              
}

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
            url:'admin/requests/doAjaxFileUpload',
            secureuri:false,
            fileElementId:'photo',
            dataType: 'json',
            data:{name:'logan', id:'id'},
            success: function (data, status)
            {
                if (data.url.length > 0){
                    $("#photo_img").attr("src", data.url);
                    $("#photo_filename").val(data.file_name);
                    $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-danger" onclick="DoNotUseThisPhoto(); return false"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
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

$(function() {
		$("#dialog").dialog({autoOpen: false, modal: true, width: 245, resizable: false});
       $('#password').keyup(function(e) {
        if(e.keyCode == 13){
          $('#check_password').click();
        }
      });    
	});
    
$(function() {
		$("#dialog_add_pass").dialog({autoOpen: false, modal: true, width: 245, resizable: false});
       $('#add_pass_number').keyup(function(e) {
        if(e.keyCode == 13){
          $('#add_pass_btn').click();
        }
      });    
	});
    
$(function() {    
    $("#dialog_lost_pass").dialog({autoOpen: false, modal: true, width: 245, resizable: false});
       $('#dialog_lost_pass_password').keyup(function(e) {
        if(e.keyCode == 13){
          $('#dialog_lost_pass_check_password').click();
        }
      });
});

$(function(){
    $.datepicker.setDefaults(
        $.extend($.datepicker.regional["uk"])
    );
    $("#date_from").datepicker();
    $("#date_to").datepicker();  
    });

function ShowFilters()
{
    if ($('#filters').css('display') == 'none'){
        $('#filters').show('slow'); 
        return false;
    } else {
            $('#filters').hide('slow'); 
            return false;    
    }
}

$(function(){
    if (($('#filter_name').val() != '') || ($('#request_num_filter').val() !=''))
        $('#filters').show(); 
    
    // Активность поля "№ пропуска"
    var value = $('#status').val();
    value = value * 1;
    if (value == 1){
        //$('#pass_number').attr('disabled', true);
        //$('#add_pass_anchor').hide();
        $("#pass_div").hide("slow");
    }
    else if (value == 2){
        //$('#pass_number').attr('disabled', false);
        //$('#add_pass_anchor').show();
        $("#pass_div").show("slow");
    }
    
    $('#show_date').click(function() {
            if ($('#show_date').prop("checked") == true){
                if ($("#date_from").val() != ''){
                    $('#datepickers').slideDown('slow');
                    $("#show_date_check").text('');
                } else {
                    $("#show_date_check").text('Спочатку оберіть дату відвідування!');
                    $('#show_date').attr("checked", false);
                }
            }
            else
                {                
                $("#div_reason").hide(); 
                $('#datepickers').slideUp('slow');
                $("#date_to").val("");
                }
    });
    
    $("#status").change(function(){
            var value = $('#status').val();
            value = value * 1;
            if (value == 1){
                //$('#pass_number').val(0);
                //$('#pass_number').attr('disabled', true);
                //$('#add_pass_anchor').hide();
                $("#pass_div").hide("slow");
            }
            else if (value == 2){
                //$('#pass_number').attr('disabled', false);
                //$('#add_pass_anchor').show();
                $("#pass_div").show("slow");
            }
        });
        
    // Изображение
    var img_url = $("#photo_filename").val();
    if (img_url != ""){
        img_url ="/uploads/" + img_url;
        $("#photo_img").attr("src", img_url);
        $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-danger" onclick="DoNotUseThisPhoto(); return false"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
    }
});

function DoNotUseThisPhoto()
{
    // request_id
    var request_id = $("#request_id").val() * 1;
    
    // Ajax запрос на получение id фото
    $.get('admin/requests/get_photo_id/' + request_id,
         function(data){
             var photo_id = data * 1;
             // Назначаем URL изображения
             if (photo_id != 0)
                var img_url = 'photos/get_image/' + photo_id;
             else
                var img_url = 'images/photo_missed.jpeg';
             $("#photo_img").attr("src", img_url);
             // Очищаем photo_filename
             $('#photo_filename').val('');
             $("#cancel_photo_btn_div").hide();
         }
    );
}
    
/**
 * Сохранение данных заявки
 */
function SaveRequestData(){
    var str = $("#edit_form").serialize();
        $.post("/admin/requests/save_request_data", str, function(data) {
                $("#save_request_data_errs").html(data);
            });
}

/**
 * Сохранение данных заявки
 */
function SendRequest(exit)
{
    var str = $("#add_form").serialize();
    $.post('admin/requests/add_request_to_db_from_chief',
          str,
          function(data){
              if (data=='long_request'){
                  window.location.reload();
              }                  
              else{        
                var id = parseInt(data) 
                if (!isNaN(id)){
                    if (exit)
                        window.location = 'admin/requests';
                    else
                        window.location = 'admin/requests/show/' + id;
                }
                else
                    $("#error_messages").html(data);
              }
          });
}

function ShowReason(date_from, date_to){
    var s = date_to.split(".");
    s = s[1] + "/" + s[0] + "/" + s[2];
    var d_t = Date.parse(s);s
    d_t = d_t * 1;
    var cur_d = new Date();
    cur_d = Date.parse(cur_d);
    cur_d = cur_d * 1;

    s = date_from.split(".");
    s = s[1] + "/" + s[0] + "/" + s[2];
    var d_f = Date.parse(s);
    d_f = d_f * 1;

    if (d_t > cur_d && d_t > d_f)
        $("#div_reason").slideDown("slow");  
    else
        $("#div_reason").slideUp("slow");
}