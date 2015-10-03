<div>
    <SCRIPT LANGUAGE="JavaScript">// Set the BaseURL to the url of your camera
    // Example: var BaseURL = "http://172.21.1.122/";
    // Since this file is located inside the unit itself, no base url is specified here

    var BaseURL = "http://<?php echo $camera_url; ?>/";

    // DisplayWidth & DisplayHeight specifies the displayed width & Height of the image.
    // You may change these numbers, the effect will be a stretch or a shrink of the image

    var DisplayWidth = "400";
    var DisplayHeight = "300";

    // This is the filepath to the image generating file inside the camera itself

    var File = "axis-cgi/mjpg/video.cgi?resolution=320x240";

    // No changes required below this point

    var output = "";
    if ((navigator.appName == "Microsoft Internet Explorer") &&
    (navigator.platform != "MacPPC") && (navigator.platform != "Mac68k"))
    {

    // If Internet Explorer for Windows then use ActiveX
    output = '<OBJECT ID="Player" width='
    output += DisplayWidth;
    output += ' height=';
    output += DisplayHeight;
    output += ' CLASSID="CLSID:745395C8-D0E1-4227-8586-624CA9A10A8D" ';
    output += 'CODEBASE="';
    output += BaseURL;
    output += 'activex/AMC.cab#version=2,0,21,0">';
    output += '<PARAM NAME="MediaURL" VALUE="';
    output += BaseURL;
    output += File + '">';
    output += '<param name="MediaType" value="mjpeg-unicast">';
    output += '<param name="ShowStatusBar" value="1">';
    output += '<param name="ShowToolbar" value="1">';
    output += '<param name="AutoStart" value="1">';
    output += '<param name="StretchToFit" value="1">';
    output += '<BR><B>Axis Media Control</B><BR>';
    output += 'The AXIS Media Control, which enables you ';
    output += 'to view live image streams in Microsoft Internet';
    output += ' Explorer, could not be registered on your computer.';
    output += '<BR></OBJECT>';
    } else {

    // If not IE for Windows use the browser itself to display
    theDate = new Date();
    output = '<IMG SRC="';
    output += BaseURL;
    output += File;
    output += '&dummy=' + theDate.getTime().toString(10);
    output += '" HEIGHT="';
    output += DisplayHeight;
    output += '" WIDTH="';
    output += DisplayWidth;
    output += '" ALT="Відео з камери">';
    }
    document.write(output);
    //document.Player.ToolbarConfiguration = "play,+snapshot,+fullscreen"
    //document.Player.UIMode = "MDConfig";
    // document.Player.MotionConfigURL = "/axis-cgi/operator/param.cgi?ImageSource=0"
    // document.Player.MotionDataURL = "/axis-cgi/motion/motiondata.cgi";
    </SCRIPT>
    <div style="padding-top: 5px">
        <button class="btn btn-primary" onclick="MakePhoto(); return false;">&nbsp&nbsp&nbsp</button>
        <script type="text/javascript">
            function MakePhoto()
            {
                $("#loading")
                    .ajaxStart(function(){
                        $(this).show();
                    })
                    .ajaxComplete(function(){
                        $(this).hide();
                    });
                // Запрос на получение фото и сохранение его в папку
                $.get('admin/requests/make_photo',
                        function(data){
                            var obj = jQuery.parseJSON(data);
                            $("#photo_img").attr("src", obj.url);
                            $("#photo_filename").val(obj.file_name);                            
                            $("#cancel_photo_btn_div").html('<br /><button id="cancel_photo_btn" class="btn btn-danger" onclick="DoNotUseThisPhoto(); return false"><i class="icon-remove icon-white"></i> Не використовувати це фото</button>');
                            $("#cancel_photo_btn_div").show();
                        });                
            }
       </script>
    </div>
</div>