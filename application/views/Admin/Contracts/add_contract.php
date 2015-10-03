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
            <?php echo form_open('admin/contracts/add_contract_to_db/', 'id="add_form" class="form-horizontal"'); ?>
                <fieldset> 
                    <div class="control-group">
                        <div class="req-div1">ПІБ заявника</div> 
                        <div class="req-div2">
                            <select id="applicant_name" name="applicant_name" style="width: 280px">
                                <option value="0"></option>
                                <?php foreach ($applicants as $applicant): ?>
                                <option value="<?php echo $applicant->applicant_id; ?>" <?php echo set_select('applicant_name', $applicant->applicant_id) ?>><?php echo $applicant->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Прізвище відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="visitor_last_name" name="visitor_last_name" class="input-xlarge" value="<?php echo html_entity_decode(set_value('visitor_last_name')); ?>">
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
                        <div class="req-div1">Дата з</div> 
                        <div class="req-div2">    
                            <input type="text" id="date_from" name="date_from" class="input-xlarge" value="<?php echo set_value('date_from') ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Дата по</div> 
                        <div class="req-div2">    
                            <input type="text" id="date_to" name="date_to" class="input-xlarge" value="<?php echo set_value('date_to') ?>">
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
                    <div class="form-actions" style="padding-left: 60px;">
                        <button class="btn btn-success" type="submit" value="ok"><i class="icon-plus-sign icon-white"></i> Видати пропуск</button>
                    </div>
                </fieldset>
        </div>
        <div style="float: right; width: 49%;">
            <div class="control-group">
                <h6>Фото відвідувача</h6>
                <img id="photo_img" src="<?php echo base_url('images/photo_missed.jpeg'); ?>">
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
        $("#pass_number").combobox();
    });
</script>