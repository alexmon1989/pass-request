<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="maindiv" class="span12">
    <div style="width: 100%">
        <div style="float: left; width: 49%;">
            <?php if (FALSE !== $this->session->flashdata('message')) $message = $this->session->flashdata('message'); ?>
            <?php if (isset($message)): ?>
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
            <?php echo form_open('admin/couriers/add_request_to_db/' . $courier->courier_id, 'id="add_form" class="form-horizontal"'); ?>
                <fieldset> 
                    <div class="control-group">
                        <div class="req-div1">ПІБ заявника</div>
                        <div class="req-div2">
                            <select id="applicant_name" name="applicant_name" style="width: 280px">
                                <?php foreach ($applicants as $applicant): ?>
                                <option value="<?php echo $applicant->applicant_id; ?>" <?php echo set_select('applicant_name', $applicant->applicant_id, (int) $applicant->applicant_id === $courier->applicant_id) ?>><?php echo $applicant->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">ПІБ відвідувача</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $courier->name; ?></strong>
                            <input type="hidden" id="courier_id" name="courier_id">
                        </div>
                    </div>     
                    <div class="control-group">
                        <div class="req-div1">Приміщення</div> 
                        <div class="req-div2">
                            <select id="room_number" name="room_number" style="width: 280px">
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room->room_id; ?>" <?php echo set_select('room_number', $room->room_id, (int)$room->room_id === $courier->room_id); ?>><?php echo $room->number; ?></option>
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
                                    <option value="<?php echo $document_type->document_type_id; ?>"<?php echo set_select('document_type', $document_type->document_type_id, (int) $document_type->document_type_id === (int) $courier->document['document_type_id']); ?>><?php echo $document_type->type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="document_attrs" style="display: none">
                        <div class="control-group">
                            <div class="req-div1">Серія</div> 
                            <div class="req-div2">
                                <input type="text" id="document_series" name="document_series" class="input-xlarge" value="<?php echo set_value('document_series', $courier->document['document_series']); ?>">                       
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="req-div1">Номер*</div> 
                            <div class="req-div2">
                                <input type="text" id="document_number" name="document_number" class="input-xlarge" value="<?php echo set_value('document_number', $courier->document['document_number']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">№ пропуска</div> 
                        <div class="req-div2">    
                            <select id="pass_number" name="pass_number" class="input-xlarge" style="width: 280px">
                                <option value=""></option>
                                <?php foreach ($passes as $pass): ?>
                                <option value="<?php echo $pass['value']; ?>" <?php echo set_select('pass_number', $pass['value']);?>><?php echo $pass['text']; ?></option>
                                <?php endforeach; ?>
                         </select>
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
                    <div id="error_file_downloading">
                    </div>
                    <div class="form-actions" style="padding-left: 60px;">
                        <button class="btn btn-success" type="submit" value="ok"><i class="icon-plus-sign icon-white"></i> Видати пропуск</button>
                    </div>
                </fieldset>
        </div>
        <div style="float: right; width: 49%;">
            <div class="control-group">
                <h6>Фото відвідувача</h6>
                <?php if ($courier->photo_id !== null): ?>
                    <img id="photo_img" src="<?php echo base_url('photos/get_image/' . $courier->photo_id); ?>">
                <?php else: ?>
                    <img id="photo_img" src="<?php echo base_url('images/photo_missed.jpeg'); ?>">
                <?php endif; ?>
                <input type="hidden" id="photo_id" name="photo_id" value="<?php if ($courier->photo_id === NULL) echo 0; else echo $courier->photo_id; ?>">    
                <img id="loading" src="<?php echo base_url('images/loading.gif'); ?>" style="display:none;">
                <div id="cancel_photo_btn_div"></div>
                <input type="hidden" id="photo_filename" name="photo_filename" value="<?php echo set_value('photo_filename', ''); ?>">
            </div>
            <div class="control-group">
                <label for="photo" style="width: 170px; text-align: left" class="control-label"><b>Нове фото відвідувача</b></label>
                <input type="file" id="photo" name="photo" class="input-xlarge" onchange="return ajaxFileUpload();">
            </div>
            <div class="control-group">
                <?php echo $video; ?>
            </div>
        </div>
        </form>
        <div class="clear"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        if ('0' != $("#document_type").val())
                $("#document_attrs").show();
        $.datepicker.setDefaults(
            $.extend($.datepicker.regional["uk"])
        );
        $("#document_date").datepicker();
        $("#pass_number").combobox();
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