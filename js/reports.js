/**
 * Експорт в Excell
 */
function GenerateReportExcel() {
    var str = $("#form_report").serialize();
    $.post("/pass_request/admin/reports/generate_report_excel/", str, function(data) {
        $("#info_pass").html(data);
        });
}
    
/**
 * Вывод на экран
 */    
function GenerateReport() {
    var str = $("#form_report").serialize();
    $.post("/pass_request/admin/reports/generate_report/", str, function(data) {
        $("#report").html(data);
        $("#print").show();
    });
}

function PrintTable(){
    var table_content = $("#table_report").html();
    var table_html = "<table id='frame_table'>" + table_content + "</table>";
    
    var strFrameName = ("printer-" + (new Date()).getTime());
 	var jFrame = jQuery( "<iframe name='" + strFrameName + "'>" );
 
	jFrame
		.css( "width", "1px" )
		.css( "height", "1px" )
		.css( "position", "absolute" )
		.css( "left", "-9999px" )
		.appendTo( jQuery( "body:first" ) )
	;
    
   var objFrame = window.frames[ strFrameName ];
   var objDoc = objFrame.document;
    
   objDoc.open();
   objDoc.write( "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">" );
   objDoc.write( "<html>" );
   objDoc.write( "<head>" );
   objDoc.write( "</head>" );
   objDoc.write( "<body>" );
   objDoc.write( table_html );
   objDoc.write( "</body>" );
   objDoc.write( "</html>" );
   objDoc.close(); 
   
   objFrame.focus();
   objFrame.print();
    
   setTimeout(
		function(){
			jFrame.remove();
		},
		(60 * 1000)
		); 
}
    
    