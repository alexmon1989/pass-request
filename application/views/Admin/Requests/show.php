<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="span12">
    <div style="width: 100%">
            <?php echo form_open('admin/requests/edit/' . $request->request_id, 'class="form-horizontal" id="edit_form"'); ?>
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

                <div class="control-group">
                    <div class="req-div1">№ заявки</div> 
                    <div class="req-div2"><strong style="font-size: 18px"><?php echo $request->request_number; ?></strong></div>
                </div>
                <div class="control-group">
                    <div class="req-div1">Дата та час подачі</div> 
                    <div class="req-div2"><strong style="font-size: 18px"><?php echo $request->request_date; ?></strong></div>
                </div>
                <div class="control-group">
                    <div class="req-div1">ПІБ заявника</div> 
                    <div class="req-div2"><strong style="font-size: 18px"><?php echo $request->username; ?></strong></div>
                </div>
                <?php if ((($role_id === 1 or $role_id === 2) or ($role_id === 3 and $is_chiefs_req === TRUE)) and $request->status_id === 1): // Либо охранник(админ) либо начальник и это его заявка?>
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
                        <div class="req-div2"><strong style="font-size: 18px"><?php echo $request->room_number; ?></strong></div>
                    </div>
                <?php endif; ?>

                <?php if ((int)$request->request_reason_id > 0): ?>
                    <div class="control-group">
                        <div class="req-div1">Примітка</div> 
                        <div class="req-div2"><strong style="font-size: 18px"><?php echo $request->reason; ?></strong></div>
                    </div>
                <?php endif; ?>

                <?php if ((($role_id === 1 or $role_id === 2) or ($role_id === 3 and $is_chiefs_req === TRUE)) and $request->status_id === 1): // Либо охранник(админ) либо начальник и это его заявка?>
                    <div class="control-group">
                        <div class="req-div1">Прізвище відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_last_name" name="visitor_last_name" class="input-xlarge" value="<?php echo set_value('visitor_last_name', $request->visitor_last_name); ?>">
                            <input type="hidden" id="visitor_id" name="visitor_id" value="<?php echo set_value('visitor_id', $request->visitor_id); ?>">
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
                            <strong style="font-size: 18px"><?php if ($request->visitor_last_name !== null) echo $request->visitor_last_name . ' ' . $request->visitor_first_name . ' ' . $request->visitor_middle_name; elseif ($request->patent_agent !== null) echo $request->patent_agent; elseif (($request->courier !== null)) echo $request->courier; else echo $request->service_employee ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ((($role_id === 1 or $role_id === 2) or ($role_id === 3 and $is_chiefs_req === TRUE)) and $request->status_id === 1): // Либо охранник(админ) либо начальник и это его заявка?>
                    <div class="control-group">
                        <div class="req-div1">Тип документа</div> 
                        <div class="req-div2">
                            <select name="document_type" style="width: 280px">
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
                
                <?php if ((($role_id === 1 or $role_id === 2) or ($role_id === 3 and $is_chiefs_req === TRUE)) and $request->status_id === 1): // Либо охранник(админ) либо начальник и это его заявка?>
                    <div id="document_attrs" style="display: none">
                        <div class="control-group">
                            <div class="req-div1">Серія</div> 
                            <div class="req-div2">
                                <input type="hidden" id="document_id" name="document_id" value="<?php echo set_value('document_id', $request->document_id); ?>">
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

                <?php if ($role_id === 1 or $role_id === 2): //Охранник или админ?>
                    <?php if ($request->status_id === 1): ?>
                        <div class="control-group">
                            <div class="req-div1">№ пропуска</div> 
                            <div class="req-div2">    
                                <select id="pass_number" name="pass_number" class="input-xlarge" style="width: 280px">
                                    <option value=""></option>
                                    <?php foreach ($passes as $pass): ?>
                                    <option value="<?php echo $pass['value']; ?>" <?php echo set_select('pass_number', $pass['value']);?>><?php echo $pass['text']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <br />
                                <!--<a href="#" onclick="$('#dialog_add_pass').dialog('open'); return false;">+ Додати</a>-->
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="control-group">
                            <div class="req-div1">Видав</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->issue_security_name; ?></strong>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="req-div1">№ перепустки</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->pass_number; ?></strong>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="req-div1">Дата та час видачі</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->issue_date; ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php  if ($role_id === 3): // Начальник?>
                    <?php if ($request->status_id === 2 or $request->status_id === 3): ?>
                        <div class="control-group">
                            <div class="req-div1">Видав</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->issue_security_name; ?></strong>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="req-div1">№ пропуска</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->pass_number; ?></strong>
                            </div>
                        </div>       
                        <div class="control-group">
                            <div class="req-div1">Дата та час видачі</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->issue_date; ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($request->status_id === 3): ?>
                        <div class="control-group">
                            <?php if ((int)$request->lost_pass !== 0): ?>
                                <p><b><i>Перепустку було загублено. Відмітку було здійснено особою:</i></b></p>
                            <?php endif; ?>    
                            <div class="req-div1">Прийняв</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->pass_security_name; ?></strong>
                            </div>    
                        </div>
                        <div class="control-group">
                            <div class="req-div1">Дата та час здачі</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $request->pass_date; ?></strong>
                            </div>
                        </div>
                <?php endif; ?>
                
                <?php if (isset($error) and trim($error) !== ''): ?>
                    <?php echo $error; ?>
                <?php endif; ?>
                <div id="save_request_data_errs"></div>

                <div class="form-actions" style="padding-left: 60px;">
                    <?php if ((int)$request->status_id === 2 and ($role_id === 1 or $role_id === 2)): ?>
                        <input type="hidden" name="take_pass_subm">
                        <button class="btn btn-primary" type="submit" onclick="$('#dialog').dialog('open'); return false;"><i class="icon-minus-sign icon-white"></i> Здати пропуск</button>
                    <?php elseif ((int)$request->status_id === 1 and ($role_id === 1 or $role_id === 2)): ?>
                        <input type="hidden" name="give_pass_subm">                 
                        <button class="btn btn-success" type="submit" onclick="$('#dialog').dialog('open'); return false;"><i class="icon-plus-sign icon-white"></i> Видати пропуск</button>
                    <?php endif; ?>
                        
                    <?php if ($request->status_id === 1 and ($role_id === 2 or $role_id === 1)): ?>    
                        <button class="btn btn-info" title="Зберегти дані заявки" id="save_request_data" onclick="SaveRequestData(); return false;"><i class="icon-ok icon-white"></i> Зберегти дані</button>
                    <?php endif; ?>
                        
                    <?php if ($role_id === 1 and $request->status_id === 2): ?>
                            <a onclick="$('#dialog_lost_pass').dialog('open'); return false;" class="btn btn-danger"><i class="icon-trash icon-white"></i> Пропуск загублено</a> 
                    <?php endif; ?>

                    <?php if ($role_id === 3 and $request->status_id === 1 and $is_chiefs_req === TRUE): ?>
                         <button type="submit" name="submit_save" onclick="Save()" class="btn btn-success" title="Зберегти дані заявки"><i class="icon-ok icon-white"></i> Зберегти</button>
                         <button type="submit" name="submit_save_exit" onclick="Save()" class="btn btn-warning" title="Зберегти дані заявки та вийти до списку Ваших заявок"><i class="icon-ok icon-white"></i> Зберегти та вийти</button>
                    <?php endif; ?>  
                    <div>
                        <br/>
                        <a href="#" onclick="$('#history').show('slow'); return false;"><i class="icon-download"></i> Показати історію угоди</a>
                        <div id="history" style="display: none">
                            <p><?php echo $request->history; ?></p>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="form_password" id="form_password">
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
                    <div id="cancel_photo_btn_div"></div>
                    <input type="hidden" id="photo_filename" name="photo_filename" value="<?php echo set_value('photo_filename', ''); ?>">
                </div>
                <div class="control-group">
                    <?php if ((($role_id === 1 or $role_id === 2) or ($role_id === 3 and $is_chiefs_req === TRUE)) and $request->status_id === 1): // Либо охранник(админ) либо начальник и это его заявка?>
                        <label for="photo" style="width: 170px; text-align: left" class="control-label"><b>Нове фото відвідувача</b></label>
                        <input type="file" id="photo" name="photo" class="input-xlarge" onchange="return ajaxFileUpload();">
                    <?php endif; ?>
                </div>
                <?php if ((int)$request->status_id === 1): ?>
                    <div class="control-group">
                        <?php if (isset($video)) echo $video; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clear"></div>
            </form>
        </div>        
</div>

<div id="dialog" title="Пароль" style="display: none">
	<p>Для підтвердження дії, будь-ласка, введіть Ваш пароль!</p>
    <div id="error_password" style="color: red"></div>
    <input type="password" id="password">
    <button onclick="SendPassFrom();" id="check_password" class="btn btn-info" type="button" value="ok"><i class="icon-ok icon-white"></i> OK</button>
</div>

<div id="dialog_lost_pass" title="Пароль" style="display: none">
	<p>Для підтвердження того, що пропуск загублено, будь-ласка, введіть Ваш пароль!</p>
    <div id="dialog_lost_pass_error_password" style="color: red"></div>
    <input type="password" id="dialog_lost_pass_password">
    <button onclick="LostPass(<?php echo $request->request_id; ?>);" id="dialog_lost_pass_check_password" class="btn btn-info" type="button" value="ok"><i class="icon-ok icon-white"></i> OK</button>
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
        $("#document_date").datepicker();
        if ('0' != $("#document_type").val())
            $("#document_attrs").show();
        
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