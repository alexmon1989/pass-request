function SendAddData () {
        var str = $("#add_form").serialize();
        $.post("admin/chiefs/add", str, function(data) {
            $("#add_form_info").html(data);
            });
    }
    
function SendEditData () {
        var str = $("#edit_form").serialize();
        $.post("admin/chiefs/edit", str, function(data) {
            $("#edit_form_info").html(data);
            });
    }
    
function FillEditForm(id){
        $("#errors").hide();
        $("#edit_form_chief_password").val("");
        $("#login").text("");

        id = parseInt(id.substring(5, id.length));
        $("#edit_form_chief_id").val(id);

        $.post("admin/chiefs/get_login_by_id/" + id, function(data) {
            $("#login").text(data);
            });

        var name = $.trim($("#user_name_" + id).text());
        $("#edit_form_chief_name").val(name);
}

function FillAddForm(){
        $("#add_form").trigger("reset");
}