/**
 * Послать POST-запрос для "Використовувати як шаблон"
 */
function UseAsTemplate()
{
    var request_id = $("#request_id").val();
    var cct = $.cookie("csrf_cookie_name");
    
    // Извлекаем данные по ID заявки
    $.post('requests/get_request_json',
          {
              'request_id' : request_id,
              'csrf_secure': cct
          }, 
          function(data){
              // Добавляем данные в кукисы
              data = (jQuery.parseJSON(data));
              $.cookie("visitor_last_name", data.visitor_last_name, {path: "/"});
              $.cookie("visitor_first_name", data.visitor_first_name, {path: "/"});
              $.cookie("visitor_middle_name", data.visitor_middle_name, {path: "/"});
              $.cookie("room_id", data.room_id, {path: "/"});
              $.cookie("document_series", data.document_series, {path: "/"});
              $.cookie("document_number", data.document_number, {path: "/"});
              $.cookie("document_type_id", data.document_type_id, {path: "/"});
              $.cookie("photo_id", data.photo_id, {path: "/"});
              
              // Переадресовываем на страницу оформления заявки
              window.location = 'requests/add_request';
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
            url:'requests/doAjaxFileUpload',
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

$(function(){
    if (($('#filter_name').val() != '') || ($('#request_num_filter').val() !=''))
        $('#filters').show(); 
    
    // Изображение
    var img_url = $("#photo_filename").val();
    if (img_url != ""){
        img_url ="uploads/" + img_url;
        $("#photo_img").attr("src", img_url);
        $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-danger" onclick="DoNotUseThisPhoto(); return false"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
    }
});

function DoNotUseThisPhoto()
{
    // request_id
    var request_id = $("#request_id").val();
    
    // Ajax запрос на получение id фото
    $.get('requests/get_photo_id/' + request_id,
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
 * Убрать фото со страницы
 */
function DeletePhoto(){    
    $("#photo_id").val("");
    $("#photo_div").hide("slow");
    $("#photo_img").attr("src", "");
    $("#photo_remove_btn").hide("slow");
    $("#photo_remove_btn").html("");
}

function EnableFields()
{
    $('#visitor_lastname').attr("disabled", false);
    $('#visitor_firstname').attr("disabled", false);
    $('#visitor_middlename').attr("disabled", false);
    return false;
}