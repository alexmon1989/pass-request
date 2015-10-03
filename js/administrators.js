function SendAddData () {
        var str = $("#add_form").serialize();
        $.post("administrators/add", str, function(data) {
            $("#add_form_info").html(data);
            });
    }
    
function SendEditData () {
        var str = $("#edit_form").serialize();
        $.post("administrators/edit", str, function(data) {
            $("#edit_form_info").html(data);
            });
    }
    
function FillEditForm(id){
        $("#errors").hide();
        $("#edit_form_administrator_password").val("");
        $("#login").text("");

        id = parseInt(id.substring(5, id.length));
        $("#edit_form_administrator_id").val(id);

        $.post("administrators/get_login_by_id/" + id, function(data) {
            $("#login").text(data);
            });

        var name = $.trim($("#user_name_" + id).text());
        $("#edit_form_administrator_name").val(name);
}

function FillAddForm(){
        $("#add_form").trigger("reset");
}