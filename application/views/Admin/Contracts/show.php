<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="span12">
    <div style="width: 100%">
            <div style="float: left; width: 49%;">    
                <?php echo form_open('admin/contracts/edit_contract', 'class="form-horizontal"'); ?>    
                <fieldset>           
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
                        <div class="req-div1">№ трудової угоди</div> 
                        <div class="req-div2">
                            <input type="hidden" id="contract_id" value="<?php echo $contract->contract_id; ?>">
                            <strong style="font-size: 18px"><?php echo $contract->contract_number; ?></strong>            
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Дата створення</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $contract->issue_date; ?></strong>        
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">ПІБ особи, що створила</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $contract->administrator; ?></strong>        
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Дата з</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $contract->date_from; ?></strong>        
                        </div>
                    </div>

                    <?php if ((int)$contract->status_id === 2 and $role_id === 1):?>
                        <div class="control-group">
                            <div class="req-div1">Дата по</div> 
                            <div class="req-div2">
                                <input type="text" id="date_to" name="date_to" class="input-xlarge" value="<?php echo set_value('date_to', $contract->date_to) ?>">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="control-group">
                            <div class="req-div1">Дата по</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $contract->date_to; ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ((int)$contract->status_id === 2 and $role_id === 1):?>
                        <div class="control-group">
                            <div class="req-div1">ПІБ відвідувача</div> 
                            <div class="req-div2">
                                <input type="text" id="visitor" name="visitor" class="input-xlarge" value="<?php echo set_value('visitor', $contract->contract_visitor) ?>">
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="control-group">
                            <div class="req-div1">ПІБ відвідувача</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $contract->contract_visitor; ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ((int)$contract->status_id === 2 and $role_id === 1):?>
                        <div class="control-group">
                            <div class="req-div1">ПІБ начальника</div> 
                            <div class="req-div2">
                                <select name="applicant_id" id="applicant_id" style="width: 280px">
                                    <?php foreach ($applicants as $applicant): ?>
                                    <option value="<?php echo $applicant->applicant_id; ?>" <?php echo set_select('applicant_id', $applicant->applicant_id, $applicant->applicant_id === $contract->applicant_id) ?>><?php echo $applicant->name; ?></option>>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="control-group">
                            <div class="req-div1">ПІБ начальника</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $contract->applicant; ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>    

                    <div class="control-group">
                        <div class="req-div1">№ перепустки</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $contract->pass_number; ?></strong>
                        </div>
                    </div>

                    <?php if ((int)$contract->status_id === 2 and $role_id === 1):?>
                    <div class="control-group">
                        <div class="req-div1"><b>Ваш пароль</b></div> 
                        <div class="req-div2">
                            <input type="password" name="password" id="password" class="input-xlarge">
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ((int)$contract->status_id === 3): ?>
                    <div class="control-group">
                        <div class="req-div1">Прийняв</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $contract->pass_administrator; ?></strong>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Дата прийому</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $contract->pass_date; ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>

                    <input type="hidden" id="contract_id" name="contract_id" value="<?php echo $contract->contract_id; ?>">
                    <?php if (isset($error) and trim($error) !== ''): ?>
                        <?php echo $error; ?>
                    <?php endif; ?>
                    <div class="form-actions" style="padding-left: 60px;">
                        <?php if ((int)$contract->status_id === 2 and $role_id === 1): ?>
                            <input type="hidden" name="take_pass_subm">
                            <button type="submit" class="btn btn-success"><i class="icon-ok-sign icon-white"></i> Редагувати</button>
                            <a class="btn btn-primary" onclick="TakePass(); return false;"><i class="icon-minus-sign icon-white"></i> Здати пропуск</a>
                            <a onclick="$('#dialog_lost_pass').dialog('open'); return false;" class="btn btn-danger"><i class="icon-trash icon-white"></i> Пропуск загублено</a> 
                        <?php endif; ?>
                        <div>
                            <a href="#" onclick="$('#history').show('slow'); return false;"><i class="icon-download"></i> Показати історію угоди</a>
                            <div id="history" style="display: none">
                                <p><?php echo $contract->history; ?></p>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div style="float: right; width: 49%;">
                <div class="control-group">
                    <h6>Фото відвідувача</h6>
                    <?php if ($contract->photo_id !== null): ?>
                        <img id="photo_img" src="<?php echo base_url('photos/get_image/' . $contract->photo_id); ?>">
                    <?php else: ?>
                        <img id="photo_img" src="<?php echo base_url('images/photo_missed.jpeg'); ?>">
                    <?php endif; ?>
                    <img id="loading" src="<?php echo base_url('images/loading.gif'); ?>" style="display:none;">
                    <div id="cancel_photo_btn_div"></div>
                    <input type="hidden" id="photo_filename" name="photo_filename" value="<?php echo set_value('photo_filename', ''); ?>">
                </div>
                <div class="control-group">
                    <?php if ((int)$contract->status_id === 2): // Либо охранник(админ) либо начальник и это его заявка?>
                        <label for="photo" style="width: 170px; text-align: left" class="control-label"><b>Нове фото відвідувача</b></label>
                        <input type="file" id="photo" name="photo" class="input-xlarge" onchange="return ajaxFileUpload();">
                    <?php endif; ?>
                </div>
                <?php if ((int)$contract->status_id !== 3): ?>
                    <div class="control-group">
                        <?php echo $video; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clear"></div>    
        <?php echo form_close(); ?>   
    </div>   
</div>

<div id="dialog_lost_pass" title="Загублено перепустку" style="display: none">
	<p>Виберіть новий номер перепустки та введіть Ваш пароль.</p>
    <div id="error_dialog" style="color: red"></div>
    <b>Пароль*:</b><br/> <input type="password" id="lost_pass_password" style="width: 273px;">
    <b>Новий номер перепустки*:</b><br/><select id="new_pass_number" name="new_pass_number" class="input-xlarge" style="width: 277px"></select><br/>
    <button onclick="LostPass()" id="change_pass_num" class="btn btn-success" type="button" value="ok"><i class="icon-ok icon-white"></i> Змінити номер перепустки</button>
</div>


<script type="text/javascript">
        $(function(){
           UpdatePasses('#new_pass_number');   
        });
</script>
