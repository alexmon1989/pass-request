function SendEditData() {
        var str = $("#form_edit").serialize();
        $.post("admin/service/edit_employee/", str, function(data) {
            $("#info_edit").html(data);
            });
    }
    
function SendEditServiceData() {
        var str = $("#form_edit_service").serialize();
        $.post("admin/service/edit_service/", str, function(data) {
            $("#info_edit_service").html(data);
            });
    }
    
function SendAddEmployeeData(){
        var str = $("#form_add_employee").serialize();
        $.post("admin/service/add_employee/", str, function(data) {
            $("#info_add_employee").html(data);
            });
        }
        
function SendAddServiceData(){
        var str = $("#form_add_service").serialize();
        $.post("admin/service/add_service/", str, function(data) {
            $("#info_add_service").html(data);
            });
        }
        
function FillEditForm(id) {
        id = parseInt(id.substring(5, id.length));
        var employeeName = $("#name_" + id).text();
        $("#service_employee_name").val($.trim(employeeName));
        var employeePassNumber = $("#passnumber_" + id).text();
        $("#service_employee_pass_number").val(employeePassNumber);
        $("#service_employee_id").val(id);
        }
        
function FillServiceEditForm(id){
        id = parseInt(id.substring(8, id.length));
        var organization_name = $("#organization_" + id).text();
        organization_name = organization_name.substring(27, organization_name.length);
        organization_name = organization_name.split("").reverse().join("");
        organization_name = organization_name.substring(2, organization_name.length);
        organization_name = organization_name.split("").reverse().join("");

        $("#service_name").val($.trim(organization_name));
        $("#service_id").val(id);
        };
function FillAddEmployeeForm(id){
        id = parseInt(id.substring(13, id.length));
        $("#form_add_employee_org_id").val(id);
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
    var src = 'images/photo_missed.jpeg';
    if (photo_id != "0")
        src = 'photos/get_image/' + photo_id;
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
        img_url = "/uploads/" + img_url;
        $("#photo_img").attr("src", img_url);
        $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" onclick="DoNotUseThisPhoto()" class="btn btn-primary"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
    }
});