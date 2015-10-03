<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="maindiv" class="span12">
    <h2>Створення тимчасової заяки</h2>
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
            <?php echo form_open('admin/temp_requests/add', 'id="add_form" class="form-horizontal"'); ?>
                <fieldset> 
                    <div class="control-group">
                        <div class="req-div1">Прізвище відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="last_name" name="last_name" class="input-xlarge" value="<?php echo html_entity_decode(set_value('last_name')); ?>">
                        </div>
                    </div>                            
                    <div class="control-group">
                        <div class="req-div1">Ім'я відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="first_name" name="first_name" class="input-xlarge" value="<?php echo html_entity_decode(set_value('first_name')); ?>">
                        </div>
                    </div>                            
                    <div class="control-group">
                        <div class="req-div1">По-батькові відвідувача</div> 
                        <div class="req-div2">
                            <input type="text" id="middle_name" name="middle_name" class="input-xlarge" value="<?php echo html_entity_decode(set_value('middle_name')); ?>">
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
                            <select id="pass_number" name="pass_number" class="input-xlarge" style="width: 280px; height: 100px;">
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
        </form>
        <div class="clear"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {        
        $("#pass_number").combobox();
    });
</script>