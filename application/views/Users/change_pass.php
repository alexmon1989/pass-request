<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="maindiv" class="span12">
    <h3>Ви увійшли в АС "Бюро перепусток" вперше. Для подальшої роботи та безпеки даних, змініть, будь-ласка, Ваш пароль.</h3>
    <br />
    <?php echo form_open('auth/change_pass/') ?>
        <fieldset>           
            <div class="control-group">
                <div>Новий пароль*</div> 
                <div>
                    <input type="password" name="password" id="password" class="input-xlarge">
                </div>
            </div>
            <div class="control-group">
                <div>Підтвердження паролю*</div> 
                <div>
                    <input type="password" name="confirm_password" id="confirm_password" class="input-xlarge">
                </div>
            </div>
            <div class="control-group">
                <div>
                    <button type="submit" class="btn btn-success"><i class="icon-ok icon-white"></i> Зберегти</button>
                </div>
            </div>
        </fieldset>
    <?php echo form_close() ?>
    <?php if (isset($errors)): ?>
        <div class="control-group" id="errors">
            <div class="span alert alert-danger" style="margin-left: 0">
                <a class="close" data-dismiss="alert">×</a> <?php echo $errors; ?>
            </div>
        </div>        
    <?php endif; ?>
</div>