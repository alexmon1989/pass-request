<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="span12">
    <h2>Редагування тимчасової заяки</h2>
    
    <div style="width: 100%">
            <div>    
                <?php echo form_open(NULL, 'class="form-horizontal"'); ?>    
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
                        <div class="req-div1">№ Тимачової заявки</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $temp_request->number; ?></strong>            
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Дата створення</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo date('d.m.Y H:i:s', $temp_request->created_at); ?></strong>        
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="req-div1">Дата з</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo date('d.m.Y', $temp_request->date_from); ?></strong>        
                        </div>
                    </div>

                    <?php if ((int)$temp_request->status_id === 2 and $role_id === 1):?>
                        <div class="control-group">
                            <div class="req-div1">Дата по</div> 
                            <div class="req-div2">
                                <input type="text" id="date_to" name="date_to" class="input-xlarge" value="<?php echo set_value('date_to', date('d.m.Y', $temp_request->date_to)) ?>">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="control-group">
                            <div class="req-div1">Дата по</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo date('d.m.Y', $temp_request->date_to); ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ((int)$temp_request->status_id === 2 and $role_id === 1):?>
                        <div class="control-group">
                            <div class="req-div1">ПІБ відвідувача</div> 
                            <div class="req-div2">
                                <input type="text" id="visitor_name" name="visitor_name" class="input-xlarge" value="<?php echo set_value('visitor_name', $temp_request->visitor_name) ?>">
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="control-group">
                            <div class="req-div1">ПІБ відвідувача</div> 
                            <div class="req-div2">
                                <strong style="font-size: 18px"><?php echo $temp_request->visitor_name; ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>   

                    <div class="control-group">
                        <div class="req-div1">№ перепустки</div> 
                        <div class="req-div2">
                            <strong style="font-size: 18px"><?php echo $temp_request->pass_number; ?></strong>
                        </div>
                    </div>
                    
                    <?php if ((int)$temp_request->status_id === 3):?>
                    <div class="control-group">
                            <div class="req-div1">Здано</div> 
                            <div class="req-div2">
                                <?php if ($temp_request->pass_date):?>
                                <strong style="font-size: 18px"><?php echo date('d.m.Y H:i:s', $temp_request->pass_date); ?></strong>
                                <?php endif; ?>  
                            </div>
                        </div>
                    <?php endif; ?>   

                    <?php if ((int)$temp_request->status_id === 2 and $role_id === 1):?>
                    <div class="control-group">
                        <div class="req-div1"><b>Ваш пароль</b></div> 
                        <div class="req-div2">
                            <input type="password" name="password" id="password" class="input-xlarge">
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div id="validation_errors">
                        <?php if (isset($error) and trim($error) !== ''): ?>
                            <?php echo $error; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions" style="padding-left: 60px;">
                        <?php if ((int)$temp_request->status_id === 2 and $role_id === 1): ?>
                            <input type="hidden" name="take_pass_subm">
                            <button type="submit" class="btn btn-success"><i class="icon-ok-sign icon-white"></i> Редагувати</button>
                            <a class="btn btn-primary" onclick="TakePass(<?php echo $temp_request->id; ?>); return false;"><i class="icon-minus-sign icon-white"></i> Здати пропуск</a>
                            <a onclick="$('#dialog_lost_pass').dialog('open'); return false;" class="btn btn-danger"><i class="icon-trash icon-white"></i> Пропуск загублено</a> 
                        <?php endif; ?>
                        <div>
                            <a href="#" onclick="$('#history').show('slow'); return false;"><i class="icon-download"></i> Показати історію угоди</a>
                            <div id="history" style="display: none">
                                <p><?php echo $temp_request->history; ?></p>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>           
            <div class="clear"></div>    
        <?php echo form_close(); ?>   
    </div>   
</div>

<div id="dialog_lost_pass" title="Загублено перепустку" style="display: none">
	<p>Виберіть новий номер перепустки та введіть Ваш пароль.</p>
    <div id="error_dialog" style="color: red"></div>
    <b>Пароль*:</b><br/> <input type="password" id="lost_pass_password" style="width: 273px;">
    <b>Новий номер перепустки*:</b><br/><select id="new_pass_number" name="new_pass_number" class="input-xlarge" style="width: 270px"></select><br/>
    <button onclick="LostPass(<?php echo $temp_request->id; ?>)" id="change_pass_num" class="btn btn-success" type="button" value="ok"><i class="icon-ok icon-white"></i> Змінити номер перепустки</button>
</div>


<script type="text/javascript">
        $(function(){
           UpdatePasses('#new_pass_number');   
        });
</script>
