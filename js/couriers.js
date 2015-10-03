function SendAddData () {
        var str = $("#add_form").serialize();
        $.post("/pass_request/admin/couriers/add", str, function(data) {
            $("#add_form_info").html(data);
            });
    }
    
function SendEditData () {
        var str = $("#edit_form").serialize();
        $.post("/pass_request/admin/couriers/edit", str, function(data) {
            $("#edit_form_info").html(data);
            });
    }
    
function FillEditForm(id){
        id = parseInt(id.substring(5, id.length));
        $("#edit_form_courier_id").val(id);

        var name = $("#courier_name_" + id).text();
        $("#edit_form_courier_name").val($.trim(name));
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
                    var photo = OldPhoto();
                    $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-primary"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
                    
                    $("#cancel_photo_btn").click(function(){
                        $("#cancel_photo_btn").hide(); 
                        $("#photo_img").attr("src", photo); 
                        $("#photo_filename").val(""); 
                        return false;
                    });
                    
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

/**
 * Возвращает путь к старому фото
 */
function OldPhoto(){
    var photo_id = $("#photo_id").val();
    var src = '/pass_request/images/photo_missed.jpeg';
    if (photo_id != "0")
        src = '/pass_request/photos/get_image/' + photo_id;
    return src;
}

function DoNotUseThisPhoto()
{
    var photo = OldPhoto();
    
    $("#cancel_photo_btn").hide(); 
    $("#photo_img").attr("src", photo); 
    $("#photo_filename").val(""); 
    return false;
}

$(function() {
    // Изображение
    var img_url = $("#photo_filename").val();
    if (img_url != ""){
        img_url = "/pass_request/uploads/" + img_url;
        $("#photo_img").attr("src", img_url);
        $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" onclick="DoNotUseThisPhoto()" class="btn btn-primary"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
    }
});