<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="maindiv" class="span12">
    <div style="width: 100%">
        <div style="float: left; width: 49%;">
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
            <?php echo form_open('admin/requests/add_request', 'id="add_form" class="form-horizontal"'); ?>
                <fieldset>           
                    <div class="control-group">
                        <div class="req-div1">ПІБ заявника</div> 
                        <div class="req-div2">
                            <select id="applicant_name" name="applicant_name" style="width: 280px">
                                <option value=""></option>
                                <?php foreach ($applicants as $applicant): ?>
                                <option value="<?php echo $applicant->user_id; ?>" <?php echo set_select('applicant_name', $applicant->user_id) ?>><?php echo $applicant->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Прізвище відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_last_name" name="visitor_last_name" class="input-xlarge ui-widget" value="<?php echo html_entity_decode(set_value('visitor_last_name')); ?>">
                        </div>
                    </div>                            
                    <div class="control-group">
                        <div class="req-div1">Ім'я відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_first_name" name="visitor_first_name" class="input-xlarge" value="<?php echo html_entity_decode(set_value('visitor_first_name')); ?>">
                        </div>
                    </div>                            
                    <div class="control-group">
                        <div class="req-div1">По-батькові відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_middle_name" name="visitor_middle_name" class="input-xlarge" value="<?php echo html_entity_decode(set_value('visitor_middle_name')); ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Приміщення</div> 
                        <div class="req-div2">
                            <select id="room_number" name="room_number" style="width: 280px">
                                <option value="0"></option>
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room->room_id; ?>" <?php echo set_select('room_number', $room->room_id); ?>><?php echo $room->number; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Тип документа</div> 
                        <div class="req-div2">
                            <select id="document_type" name="document_type" style="width: 280px">
                                <option value="0"></option>
                                <?php foreach ($document_types as $document_type): ?>
                                    <option value="<?php echo $document_type->document_type_id; ?>"<?php echo set_select('document_type', $document_type->document_type_id); ?>><?php echo $document_type->type; ?></option>
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
                            <div class="req-div1">Дата по (включно)</div> 
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
                    <div class="control-group">
                        <div class="req-div1">Статус заявки</div> 
                        <div class="req-div2">    
                            <select name="status" id="status" style="width: 280px" disabled="disabled">
                            <option id="status1" value="1" <?php echo set_select('status', '1'); ?>>Очікується</option>
                            <option id="status2" value="2" <?php echo set_select('status', '2'); ?>>Видано</option>
                        </select>
                        </div>
                    </div>
                    <div class="control-group" id="pass_div" style="display: none">
                        <div class="req-div1">№ пропуска</div> 
                        <div class="req-div2">    
                            <select id="pass_number" name="pass_number" class="input-xlarge" style="width: 277px">
                                <option value=""></option>
                                <?php foreach ($passes as $pass): ?>
                                <option value="<?php echo $pass['value']; ?>" <?php echo set_select('pass_number', $pass['value']);?>><?php echo $pass['text']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <br />
                            <a href="#" id="add_pass_anchor" style="display: none" onclick="$('#dialog_add_pass').dialog('open'); return false;">+ Додати</a>
                        </div>
                    </div> 
                    <div class="control-group">
                        <div class="req-div1"><b>Ваш пароль</b></div> 
                        <div class="req-div2">    
                            <input type="password" id="password" name="password" class="input-xlarge">
                        </div>
                    </div>            

                    <?php if (isset($error) and trim($error) !== ''): ?>
                        <?php echo $error; ?>
                    <?php endif; ?>
                    <div class="form-actions" style="padding-left: 60px;">
                        <button class="btn btn-success" type="submit" value="ok"><i class="icon-plus-sign icon-white"></i> Оформити заявку</button>
                    </div>
                </fieldset>
        </div>
        <div style="float: right; width: 49%;">
            <div class="control-group">
                <h6>Фото відвідувача</h6>
                <img id="photo_img" src="<?php echo base_url('images/photo_missed.jpeg'); ?>">
                <img id="loading" src="<?php echo base_url('images/loading.gif'); ?>" style="display:none;">
                <input type="hidden" id="photo_filename" name="photo_filename" value="<?php echo set_value('photo_filename', ''); ?>">
                <input type="hidden" id="photo_id" name="photo_id" value="<?php echo set_value('photo_id', ''); ?>">
                <div id="cancel_photo_btn_div"></div>
            </div>
            <div class="control-group">
                <label for="photo" style="width: 170px; text-align: left" class="control-label"><b>Нове фото відвідувача</b></label>
                <input type="file" id="photo" name="photo" class="input-xlarge" onchange="$('#photo_id').val(''); return ajaxFileUpload();">
            </div>
            <div class="control-group">
                <?php echo $video; ?>
            </div>
        </div>
        </form>
        <div class="clear"></div>
    </div>
</div>

<div id="dialog_add_pass" title="Додати перепустку" style="display: none">    
    <div id="dialog_add_pass_error" style="color: red"></div>
	<p>№ перепустки</p>
    <input type="text" id="number">
    <p>Поверх</p>
    <select id="room">
        <option value="0" selected="selected"></option>
        <?php foreach ($rooms as $room): ?>
            <option value="<?php echo $room->room_id; ?>"><?php echo $room->number ?></option>
        <?php endforeach; ?>
    </select>
    <button onclick="AddPassFromRequestForm(); return false;" id="add_pass_btn" class="btn btn-info" type="button" value="ok"><i class="icon-ok icon-white"></i> OK</button>
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
            
            ActivateStatusField();
            
            var photo_id = $("#photo_id").val();
            if (photo_id != '')
                $("#photo_img").attr("src", "<?php echo base_url('photos/get_image/'); ?>/" + photo_id);
            
            $( "#pass_number" ).combobox();
        });
        
        $("#date_from").change(ActivateStatusField);
        
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

<script type="text/javascript">
    	$("#visitor_last_name").autocomplete({
			source: "<?php echo base_url('admin/requests/search_user') ?>",
           select: function(event, ui) {
               //$("#applicant_name [value=" + ui.item.applicant_id + "]").attr("selected", "selected");
               $('#visitor_first_name').val(ui.item.first_name);
               $('#visitor_middle_name').val(ui.item.middle_name);
               $('#document_series').val(ui.item.doc_ser);
               $('#document_number').val(ui.item.doc_num);
               $('#document_date').val(ui.item.doc_issue_date);
               $("#room_number [value=" + ui.item.room_id + "]").attr("selected", "selected");
               $("#document_type [value=" + ui.item.document_type_id + "]").attr("selected", "selected");
               $("#document_attrs").show("slow"); 
               if (ui.item.photo_id != null) {
                   $("#photo_img").attr("src", "<?php echo base_url('photos/get_image/'); ?>/" + ui.item.photo_id);
                   $("#photo_id").val(ui.item.photo_id);
               }
               else {
                   $("#photo_img").attr("src", "<?php echo base_url('images/photo_missed.jpeg'); ?>");
                   $("#photo_id").val('');
               }
           }
		});
</script>