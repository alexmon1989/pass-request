function SendAddData () {
        var str = $("#add_form").serialize();
        $.post("patent_agents/add", str, function(data) {
            $("#add_form_info").html(data);
            });
    }
    
function SendEditData () {
        var str = $("#edit_form").serialize();
        $.post("patent_agents/edit", str, function(data) {
            $("#edit_form_info").html(data);
            });
    }
    
function FillEditForm(id){
        id = parseInt(id.substring(5, id.length));
        $("#edit_form_patent_agent_id").val(id);

        var name = $("#patent_agent_name_" + id).text();
        $("#edit_form_patent_agent_name").val($.trim(name));
}