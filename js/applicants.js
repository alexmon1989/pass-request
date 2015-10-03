function SendAddData () {
        var str = $("#add_form").serialize();
        $.post("applicants/add", str, function(data) {
            $("#add_form_info").html(data);
            });
    }
    
function SendEditData () {
        var str = $("#edit_form").serialize();
        $.post("applicants/edit", str, function(data) {
            $("#edit_form_info").html(data);
            });
    }
    
function FillEditForm(id){
        $("#errors").hide();
        $("#edit_form_applicant_password").val("");
        $("#login").text("");

        id = parseInt(id.substring(5, id.length));
        $("#edit_form_applicant_id").val(id);

        $.post("applicants/get_login_by_id/" + id, function(data) {
            $("#login").text(data);
            });
        $.post("applicants/is_chancelerry/" + id, function(data) {
            if (data == "yes")
                $("#edit_form_applicant_chancellery").attr("checked", true);
            else
                $("#edit_form_applicant_chancellery").attr("checked", false);
          });

        var name = $.trim($("#user_name_" + id).text());
        $("#edit_form_applicant_name").val(name);
}

function FillAddForm(){
        $("#add_form").trigger("reset");
}