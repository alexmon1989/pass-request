<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="span12">
    <?php echo form_open('requests/save_request', 'class="form-horizontal" id="edit_form"'); ?>
        <div style="float: left; width: 49%;">
            <fieldset>        
                <input type="hidden" id="request_id" name="request_id" value="<?php echo $request->request_id; ?>">
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
                <?php if ($request->status_id === 1 and $request->reason === null): ?>
                    <h6>Ви можете змінити дані заявки</h6>
                <?php else: ?>
                    <h6>Дані заявки</h6>
                <?php endif; ?>
                <br />
                <div class="control-group">
                    <div class="req-div1">№ заявки</div> 
                    <div class="req-div2">
                        <strong style="font-size: 18px"><?php echo $request->request_number; ?></strong>
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">Дата та час подачі</div> 
                    <div class="req-div2">
                        <strong style="font-size: 18px"><?php echo $request->request_date; ?></strong>
                    </div>
                </div>
                <div class="control-group">
                    <div class="req-div1">ПІБ відправника заявки</div> 
                    <div class="req-div2">
                        <strong style="font-size: 18px"><?php echo $request->username; ?></strong>
                    </div>
                </div>    
                <?php if ((int)$request->request_reason_id > 0): ?>
                <div class="control-group">
                    <div class="req-div1">Примітка</div> 
                    <div class="req-div2">
                        <strong style="font-size: 18px"><?php echo $request->reason; ?></strong>
                    </div>
                </div>  
                <?php endif; ?>

                <?php if ($request->status_id === 1 and $request->reason === null): ?>
                    <div class="control-group">
                        <div class="req-div1">Приміщення</div> 
                        <div class="req-div2">
                            <select name="room_number" style="width: 280px">
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room->room_id; ?>" <?php echo set_select('room_number', $room->room_id, $room->number === $request->room_number); ?>><?php echo $room->number; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div> 
                <?php else: ?>
                    <div class="control-group">
                        <div class="req-div1">Приміщення</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $request->room_number; ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($request->status_id === 1 and $request->reason === null): ?>
                    <div class="control-group">
                        <div class="req-div1">Прізвище відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_last_name" name="visitor_last_name" class="input-xlarge" value="<?php echo set_value('visitor_last_name', $request->visitor_last_name); ?>">
                            <input type="hidden" id="visitor_id" name="visitor_id" class="input-xlarge" value="<?php echo set_value('visitor_id', $request->visitor_id); ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Ім'я відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_first_name" name="visitor_first_name" class="input-xlarge" value="<?php echo set_value('visitor_first_name', $request->visitor_first_name); ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">По-батькові відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_middle_name" name="visitor_middle_name" class="input-xlarge" value="<?php echo set_value('visitor_middle_name', $request->visitor_middle_name); ?>">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="control-group">
                        <div class="req-div1">ПІБ відвідувача</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php if ($request->visitor_last_name !== NULL) echo $request->visitor_last_name . ' ' . $request->visitor_first_name . ' ' . $request->visitor_middle_name; elseif (($request->patent_agent !== null)) echo $request->patent_agent; else echo $request->service_employee ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($request->status_id === 1 and $request->reason === null): ?>
                    <div class="control-group">
                        <div class="req-div1">Тип документа</div> 
                        <div class="req-div2">
                            <select id="document_type" name="document_type" style="width: 280px">
                                <option value="0" <?php echo set_select('document_type', 0); ?>></option>
                                <?php foreach ($document_types as $document_type): ?>
                                    <option value="<?php echo $document_type->document_type_id; ?>" <?php echo set_select('document_type', $document_type->document_type_id, $document_type->type === $request->document_type); ?>><?php echo $document_type->type; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="first_document_type" name="first_document_type" value="<?php echo set_value('document_type', $request->document_type_id); ?>">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="control-group">
                        <div class="req-div1">Тип документа</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $request->document_type; ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($request->status_id === 1 and $request->reason === null): ?>
                    <div id="document_attrs" style="display: none">
                        <div class="control-group">
                            <div class="req-div1">Серія</div> 
                            <div class="req-div2">
                                <input type="hidden" id="document_id" name="document_id" class="input-xlarge" value="<?php echo set_value('document_id', $request->document_id); ?>">
                                <input type="text" id="document_series" name="document_series" class="input-xlarge" value="<?php echo set_value('document_series', $request->document_series); ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="req-div1">Номер*</div> 
                            <div class="req-div2">
                                <input type="text" id="document_number" name="document_number" class="input-xlarge" value="<?php echo set_value('document_number', $request->document_number); ?>">
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="control-group">
                        <div class="req-div1">Серія документа</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $request->document_series; ?></strong>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Номер документа</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $request->document_number; ?></strong>
                        </div>
                    </div>
                <?php endif; ?>       

                <?php if (isset($error)): ?>
                    <?php echo $error; ?>
                <?php endif; ?>

                <div class="form-actions" style="padding-left: 60px;">
                    <?php if ((int)$request->status_id === 1 and $request->reason === null): ?>               
                        <button type="submit" name="send_request" value="1" class="btn btn-success" title="Зберегти дані заявки"><i class="icon-ok icon-white"></i> Зберегти</button>
                        <button type="submit" name="send_request" value="2" class="btn btn-warning" title="Зберегти дані заявки та вийти до списку Ваших заявок"><i class="icon-ok icon-white"></i> Зберегти та вийти</button>
                    <?php endif; ?>
                        <p><br/><a class="btn btn-primary" title="Використовувати дані заявки для оформлення нової" onclick="UseAsTemplate(); return false;"><i class="icon-plus-sign icon-white"></i> Використовувати як шаблон</a></p>
                </div>
            </fieldset>
        </div>
        <div style="float: right; width: 49%;">
            <div class="control-group">
                <h6>Фото відвідувача</h6>
                <?php if ($request->photo_id !== null): ?>
                    <img id="photo_img" src="<?php echo base_url('photos/get_image/' . $request->photo_id); ?>">
                <?php else: ?>
                    <img id="photo_img" src="<?php echo base_url('images/photo_missed.jpeg'); ?>">
                <?php endif; ?>
                <img id="loading" src="<?php echo base_url('images/loading.gif'); ?>" style="display:none;">    
                <input type="hidden" id="photo_filename" name="photo_filename" value="<?php echo set_value('photo_filename', ''); ?>">
                <div id="cancel_photo_btn_div"></div>
            </div>
            <div class="control-group">
                <?php if ((int)$request->status_id === 1 and $request->reason === null): ?> 
                    <label for="photo" style="width: 170px; text-align: left" class="control-label"><b>Нове фото відвідувача</b></label>
                    <input type="file" id="photo" name="photo" class="input-xlarge" onchange="return ajaxFileUpload();">
                <?php endif; ?>
            </div>
        </div>
        <div class="clear">
    </form>
</div>
    
<script type="text/javascript">
    $(document).ready(function() {
        $.datepicker.setDefaults(
                $.extend($.datepicker.regional["uk"])
            );
        $("#document_date").datepicker();
        if ('0' != $("#document_type").val())
            $("#document_attrs").show();
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