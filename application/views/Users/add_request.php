<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="maindiv" class="span12">
    <?php if (FALSE !== $this->session->flashdata('message')) $message = $this->session->flashdata('message'); ?>
    <?php if (isset($message)): ?>
        <script type="text/javascript">
            $(document).ready(function()
            {
                $('#visitor_lastname').val('');
                $('#visitor_firstname').val('');
                $('#visitor_middlename').val('');
                $('#document_type').val(0);
                $('#document').val('');
                $('#date_to').val('');
                $('#date_from').val('');
                $('#reason').val('');
                $('#show_date').removeAttr("checked");
            });
       </script>
        <div class="span12" id="message" style="margin-bottom: 0px; margin-left: 0px;">
            <div class="control-group">
                <div class="span alert alert-success fade in" style="margin-bottom: 10px; margin-left: 0px">
                    <a class="close" data-dismiss="alert">×</a>
                    <?php echo $message; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
       <h6>Для оформлення заявки, будь-ласка, заповніть наступні поля:</h6>
       <br />
    <?php echo form_open_multipart('requests/add_request_to_db', 'id="add_form" class="form-horizontal"'); ?>
        <div style="float: left; width: 49%;">
            <fieldset>    
                <div class="control-group">
                    <div class="req-div1">ПІБ відправника заявки</div> 
                    <div class="req-div2">
                        <strong style="font-size: 18px"><?php echo $username ?></strong>
                    </div>
                </div>            
                <div class="control-group">
                    <div class="req-div1">Прізвище відвідувача*</div> 
                    <div class="req-div2">
                        <input type="text" id="visitor_lastname" name="visitor_lastname" class="input-xlarge" value="<?php echo html_entity_decode(set_value('visitor_lastname')); ?>">
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">Ім'я відвідувача</div> 
                    <div class="req-div2">
                        <input type="text" id="visitor_firstname" name="visitor_firstname" class="input-xlarge" value="<?php echo html_entity_decode(set_value('visitor_firstname')); ?>">
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">По-батькові відвідувача</div> 
                    <div class="req-div2">
                        <input type="text" id="visitor_middlename" name="visitor_middlename" class="input-xlarge" value="<?php echo html_entity_decode(set_value('visitor_middlename')); ?>">
                    </div>
                </div>            
                <div class="control-group">
                    <div class="req-div1">Тип документа*</div> 
                    <div class="req-div2">
                        <select name="document_type" id="document_type" class="input-xlarge" style="width: 280px">
                            <option value="0"></option>
                            <?php foreach ($document_types as $document_type): ?>
                            <option value="<?php echo $document_type->document_type_id; ?>" <?php echo set_select('document_type', $document_type->document_type_id); ?>><?php echo $document_type->type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div id="document_attrs" style="display: none">
                    <div class="control-group">
                        <div class="req-div1">Серія</div> 
                        <div class="req-div2">
                            <input type="text" id="document_series" name="document_series" class="input-xlarge" value="<?php echo html_entity_decode(set_value('document_series')); ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Номер*</div> 
                        <div class="req-div2">
                            <input type="text" id="document_number" name="document_number" class="input-xlarge" value="<?php echo html_entity_decode(set_value('document_number')); ?>">
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">Поверх</div> 
                    <div class="req-div2">
                        <select id="room_number" name="room_number" style="width: 280px">
                            <option value=""></option>
                            <?php foreach ($rooms as $room): ?>
                                <?php if (!isset($user_room_id)) $user_room_id = 0; ?>
                                <option value="<?php echo $room->room_id; ?>" <?php echo set_select('room_number', $room->room_id, ($room->room_id === $user_room_id)); ?>><?php echo $room->number; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">Дата відвідування*</div> 
                    <div class="req-div2">
                        <input type="text" id="date_from" name="date_from" class="input-xlarge" value="<?php echo set_value('date_from') ?>">
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">Заявка на декілька днів</div> 
                    <div class="req-div2">
                        <input type="checkbox" id="show_date" name="show_date" value="1" <?php echo set_checkbox('show_date', 1, FALSE); ?>>
                        <div id="show_date_check" style="color: red"></div>
                    </div>
                </div>
                <div id="datepickers" style="display: none">
                    <div class="control-group">
                        <div class="req-div1">Дата по (включно)*</div> 
                        <div class="req-div2">
                            <input onchange="ShowReason($('#date_from').val(), this.value)" type="text" id="date_to" name="date_to" class="input-xlarge" value="<?php echo set_value('date_to', '') ?>">
                        </div>
                    </div>
                    <div class="control-group" id="div_reason" style="display: none">
                        <div class="req-div1">Підстава*</div> 
                        <div class="req-div2">
                            <input type="text" id="reason" name="reason" class="input-xlarge" value="<?php echo html_entity_decode(set_value('reason', '')) ?>">
                        </div>
                    </div>
                </div>
                <div id="error_messages">
                    <?php if (isset($error)): ?>
                        <?php echo $error; ?>
                    <?php endif; ?>
                </div>
                <div class="form-actions" style="padding-left: 60px;">
                    <button type="submit" name="send_request" value="1" onclick="EnableFields();" class="btn btn-success" title="Зберегти дані заявки"><i class="icon-ok icon-white"></i> Зберегти</button>
                    <button type="submit" name="send_request" value="2" onclick="EnableFields();" class="btn btn-warning" title="Зберегти дані заявки та вийти до списку Ваших заявок"><i class="icon-ok icon-white"></i> Зберегти та вийти</button>
                    <p><br/><button type="reset" class="btn btn-info" title="Скинути дані форми"><i class="icon-refresh icon-white"></i> Скинути дані форми</button></p>
                </div>
            </fieldset>
        </div>
        <div style="float: right; width: 49%;">
            <div class="control-group">
                <h6>Фото відвідувача</h6>
                <img id="photo_img" src="<?php echo base_url('images/photo_missed.jpeg'); ?>">
                <img id="loading" src="<?php echo base_url('images/loading.gif'); ?>" style="display:none;">
                <input type="hidden" id="photo_filename" name="photo_filename" value="<?php echo set_value('photo_filename', ''); ?>">
                <div id="cancel_photo_btn_div"></div>
            </div>
            <div class="control-group">
                <label for="photo" style="width: 170px; text-align: left" class="control-label"><b>Нове фото відвідувача</b></label>
                <input type="file" id="photo" name="photo" class="input-xlarge" onchange="return ajaxFileUpload();">
                <input type="hidden" id="photo_id" name="photo_id" value="<?php echo set_value('photo_id', '0'); ?>">
            </div>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="from_template" id="from_template" value="<?php echo set_value('from_template', '0'); ?>">
    </form>
</div>

<script type="text/javascript">
        $(document).ready(function() {
            $.datepicker.setDefaults(
                $.extend($.datepicker.regional["uk"])
            );
            $("#date_from").datepicker();
            $("#date_to").datepicker();  
            $("#document_date").datepicker();
            
            if ($('#show_date').prop("checked") == true)
             $('#datepickers').slideDown('slow');
            else
            {                
             $('#datepickers').hide();
             $("#date_to").val("");
            }
            
            ShowReason($("#date_from").val(), $("#date_to").val());
            
            if ('0' != $("#document_type").val())
                $("#document_attrs").show();
            
            if ('1' == $("#from_template").val())
            {
                 $('#visitor_lastname').attr("disabled", true);
                 $('#visitor_firstname').attr("disabled", true);
                 $('#visitor_middlename').attr("disabled", true);
                 $('#photo').attr("disabled", true);
            }
            
            if ($("#photo_filename").val() == "" && $("#photo_id").val() != '0'){
                $("#photo_img").attr("src", "photos/get_image/" + $("#photo_id").val());
            }
        });
</script>

<script type="text/javascript">
    $('#show_date').click(function() {
            if ($('#show_date').prop("checked") == true){
                if ($("#date_from").val() != ''){
                    $('#datepickers').slideDown('slow');
                    $("#show_date_check").text('');
                } else {
                    $("#show_date_check").text('Спочатку оберіть дату відвідування!');
                    $('#show_date').attr("checked", false);
                }
            }
            else
                {                
                $("#div_reason").hide(); 
                $('#datepickers').slideUp('slow');
                $("#date_to").val("");
                }
    });
     $('#document_type').change(function() {
         var value = $("#document_type").val()*1;
         if (value != 0){
            $("#document_attrs").show("slow");
         } else {
            $("#document_attrs").hide("slow");
            $("#document_series").val("");
            $("#document_number").val("");
            $("#document_date").val("");
         }
     });
</script>

<?php if (isset($template_data)): ?>
    <script type="text/javascript">
        $('#visitor_lastname').val("<?php echo $template_data['visitor_last_name'] ?>");
        $('#visitor_lastname').attr("disabled", true);
        $('#visitor_firstname').val("<?php echo $template_data['visitor_first_name'] ?>");
        $('#visitor_firstname').attr("disabled", true);
        $('#visitor_middlename').val("<?php echo $template_data['visitor_middle_name'] ?>");
        $('#visitor_middlename').attr("disabled", true);
        $('#room_number').val("<?php echo $template_data['room_id'] ?>");
        <?php if (isset($template_data['document_series'])): ?>
            $('#document_series').val("<?php echo $template_data['document_series'] ?>");
        <?php endif; ?>
        <?php if (isset($template_data['document_number'])): ?>
            $('#document_number').val("<?php echo $template_data['document_number'] ?>");
        <?php endif; ?>
        <?php if (isset($template_data['document_date'])): ?>
            $('#document_date').val("<?php echo $template_data['document_date'] ?>");
        <?php endif; ?>
        $('#document_type').val("<?php echo $template_data['document_type_id'] ?>");
        // Фотография
        <?php if (isset($template_data['photo_id'])): ?>
            $("#photo_img").attr("src", "<?php echo base_url('photos/get_image/' . $template_data['photo_id']) ?>");
            $("#photo_id").val(<?php echo $template_data['photo_id']; ?>);
        <?php endif; ?>
        $('#photo').attr("disabled", true);    
        $("#from_template").val("1");
    </script>
<?php endif; ?>