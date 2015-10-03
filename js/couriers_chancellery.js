function SendAddData () {
        var str = $("#add_form").serialize();
        $.post("/pass_request/couriers/add", str, function(data) {
            $("#add_form_info").html(data);
            });
    }
    
function SendEditData () {
        var str = $("#edit_form").serialize();
        $.post("/pass_request/couriers/edit", str, function(data) {
            $("#edit_form_info").html(data);
            });
    }
    
function FillEditForm(id){
        id = parseInt(id.substring(5, id.length));
        $("#edit_form_courier_id").val(id);

        var name = $("#courier_name_" + id).text();
        $("#edit_form_courier_name").val($.trim(name));
}