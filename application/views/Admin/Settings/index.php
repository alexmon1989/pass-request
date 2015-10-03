<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<div class="span12">
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
    
    <?php echo form_open('admin/settings/save'); ?>
        <div class="control-group">
            IP камери&nbsp;&nbsp;<input type="text" name="ip_cam" value="<?php echo set_value('ip_cam', $settings->ip_cam) ?>">
        </div>
        <div class="control-group">
            IP проксі-сервера&nbsp;&nbsp;<input type="text" name="ip_proxy" value="<?php echo set_value('ip_proxy', $settings->ip_proxy) ?>">
            Формат: xxx.xxx.xxx.xxx:yyyy, де yyyy - номер порта
        </div>
        <div class="control-group">
            <button class="btn btn-success" type="submit"><i class="icon-hdd icon-white"></i> Зберегти</button>
        </div>
        <div class="control-group">
            <?php if (isset($error)) echo $error ?>
        </div>
    <?php echo form_close(); ?>
</div>