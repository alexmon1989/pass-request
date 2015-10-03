/**
* Обновление списка не выданных пропусков
* 
* @param int id id элемента <select>
*/
function UpdatePasses(id, selected_id){
    $(id).empty();    
    $.post("/pass_request/admin/requests/get_free_passes/", function(data){
            $(id).append('<option></option>');
            data = jQuery.parseJSON(data);
            for(var i = 0; i <= data.length - 1; i++){
                $(id).append('<option value=' + data[i].value + '>' + data[i].text + '</option>');
            }      
            if (selected_id != undefined){
                // Делаем активным
                $(id + " [value='" + selected_id + "']").attr("selected", "selected");
            }
            $(id).combobox();
        });
}

/**
 * Удаление пропуска
 * 
 * @param int id id пропуска 
 */
function DeletePass(id){
        var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/delete/", 
          {
              'pass_id' : id,
              'csrf_secure': cct
          },
          function(data) {
              window.location.reload();
          });
}

/**
 * Отправка пропуска в список не выданных
 * 
 * @param int id id пропуска 
 */
function SendToNotIssued(id){
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/send_to_not_issued/", 
          {
              'pass_id' : id,
              'csrf_secure': cct
          },
          function(data) {
              window.location.reload();
          });
}

/**
 * Отправка статуса в список утерянных
 * 
 * @param int id id пропуска 
 */
function SendToLost(id){
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/send_to_lost/", 
          {
              'pass_id' : id,
              'csrf_secure': cct
          },
          function(data) {
              window.location.reload();
          });
}

/**
 * Заполнение формы редактирования
 * 
 *  @param int id id пропуска 
 */
function FillEditForm(id){
    $('#new_number').val('');
    $('#edit_form_pass_id').val('');
    $('#edit_form_room').val('');
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/get_pass_data/", 
          {
              'pass_id' : id,
              'csrf_secure': cct
          },
          function(data) {
              data = jQuery.parseJSON(data);
              $('#header_pass_number').text('Редагування перепустки № ' + data.number);
              $('#edit_form_pass_id').val(data.pass_id);
              $('#edit_form_room').val(data.room_id);
          });
}

/**
 * Редактирование пропуска
 * 
 *  @param int id id пропуска 
 */
function EditPass(){
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/edit/", 
          {
              'pass_id' : $('#edit_form_pass_id').val(),
              'number' :  $('#new_number').val(),
              'room_id' : $('#edit_form_room').val(),
              'csrf_secure': cct
          },
          function(data) {
              if ($.trim(data) == '')
                window.location.reload();
              else
                 $("#edit_form_info").html(data);
          });
}

function AddPass(){
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/add/", 
          {
              'number' :  $('#number').val(),
              'room_id' : $('#room').val(),
              'csrf_secure': cct
          },
          function(data) {
              if (!isNaN(data*1))
                window.location.reload();
              else
                 $("#add_form_info").html(data);
          });
}

function AddPassFromRequestForm(){
    var cct = $.cookie("csrf_cookie_name");
    $.post("/pass_request/admin/passes/add/", 
          {
              'number' :  $('#number').val(),
              'room_id' : $('#room').val(),
              'csrf_secure': cct
          },
          function(data) {
              if (!isNaN(data*1)){
                  // Добавляем в селект
                  UpdatePasses("#pass_number", data*1);
                  
                  // Закрываем окно
                  $('#dialog_add_pass').dialog('close');
              }
          });
}