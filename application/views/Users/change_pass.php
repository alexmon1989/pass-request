<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="maindiv" class="span12">
    <?php if ($this->auth_lib->is_first_time($this->session->userdata('login'))): ?>
    <h3>З метою попередження подачі заявок від Вашого імені просимо змінити пароль:</h3>
    <?php else: ?>
    <h3>Змінити пароль:</h3>
    <?php endif; ?>
    <br />
    <?php echo form_open('', 'class="form-horizontal"'); ?>
    <fieldset>
        <?php if (FALSE !== $this->session->flashdata('message')): ?>
            <div class="control-group" id="message">
                <div class="span alert alert-success" style="margin-bottom: 0px">
                    <a class="close" data-dismiss="alert" onclick="$('#message').hide();">×</a>
                    <?php echo $this->session->flashdata('message');; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="control-group">
            <label for="old_password" class="control-label">Старий пароль*</label>
            <div class="controls">
                <input type="password" id="old_password" name="old_password" class="input-xlarge" value="<?php echo set_value('old_password'); ?>">
            </div>
        </div>
        <div class="control-group">
            <label for="new_password" class="control-label">Новий пароль*</label>
            <div class="controls">
                <input type="password" id="new_password" name="new_password" class="input-xlarge" value="<?php echo set_value('new_password'); ?>">
            </div>
        </div>
        <div class="control-group">
            <label for="confirm_password" class="control-label">Підтвердження нового паролю*</label>
            <div class="controls">
                <input type="password" id="confirm_password" name="confirm_password" class="input-xlarge" value="<?php echo set_value('confirm_password'); ?>">
            </div>
        </div>
        <?php if (isset($errors)): ?>
            <div class="control-group" id="errors">
                <div class="span alert alert-danger" style="margin-left: 0">
                    <a class="close" data-dismiss="alert">×</a> <?php echo $errors; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Змінити</button>
        </div>
    </fieldset>
    </form>
</div>