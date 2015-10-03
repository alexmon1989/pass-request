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

    <h6>Всього охоронників: <?php echo $security_count; ?></h6>
    <br />
    <?php if ($role_id === 1): ?>
        <a class="btn btn-success" href="#add_security_modal" data-toggle="modal"><i class="icon-plus icon-white" onclick="FillAddForm(); return false;"></i> Додати особу</a>
        <br /><br />
    <?php endif; ?>
    <?php if (!empty($securities)): ?>
    <h4>Натисніть на особу для редагування її даних або видалення.</h4>
    <table class="table table-striped" width="100%" id="table_requests">
        <?php $third_part = ceil($security_count / 3); for($key=0; $key < $third_part; $key++): ?>
        <tr>
            <td>
                <?php if (isset($securities[$key]->name)): ?>
                <div class="btn-group">
                    <button id="<?php echo 'user_name_' . $securities[$key]->security_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $securities[$key]->name; ?></b> <span class="caret"></span></button>
                    <?php if ($role_id === 1): ?>
                    <ul class="dropdown-menu">
                        <li><a id="<?php echo 'edit_' . $securities[$key]->security_id; ?>" href="#edit_security_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                        <li><a href="<?php echo base_url('admin/security/delete/' . $securities[$key]->security_id) ?>"  onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити особу</a></li>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </td>

            <td>
                <?php if (isset($securities[$key + $third_part]->name)): ?>
                <div class="btn-group">
                    <button id="<?php echo 'user_name_' . $securities[$key + $third_part]->security_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $securities[$key + $third_part]->name; ?></b> <span class="caret"></span></button>
                    <?php if ($role_id === 1): ?>
                    <ul class="dropdown-menu">
                        <li><a id="<?php echo 'edit_' . $securities[$key + $third_part]->security_id; ?>" href="#edit_security_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                        <li><a href="<?php echo base_url('admin/security/delete/' . $securities[$key + $third_part]->security_id) ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити особу</a></li>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </td>

            <td>
                <?php if (isset($securities[$key + 2 * $third_part]->name)): ?>
                <div class="btn-group">
                    <button id="<?php echo 'user_name_' . $securities[$key + 2 * $third_part]->security_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $securities[$key + 2 * $third_part]->name; ?></b> <span class="caret"></span></button>
                    <?php if ($role_id === 1): ?>
                    <ul class="dropdown-menu">
                        <li><a id="<?php echo 'edit_' . $securities[$key + 2 * $third_part]->security_id; ?>" href="#edit_security_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                        <li><a href="<?php echo base_url('admin/security/delete/' . $securities[$key + 2 * $third_part]->security_id) ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити особу</a></li>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php endfor; ?>
    </table>
    <?php else: ?>
    <div class="span"><h2>Користувачі відсутні!<h2></div>
    <?php endif; ?>
</div>


<div class="modal hide fade in" id="add_security_modal" style="display: none;">
    <div class="modal-header">
        <button data-dismiss="modal" class="close">×</button>
        <h3>Заповніть дані форми</h3>
    </div>
    <?php echo form_open('', 'id="add_form"') ?>
    <div class="modal-body">
        <div class="control-group">
            <label for="add_form_security_name" class="control-label">ПІБ особи</label>
            <div class="controls">
                <input type="text" id="add_form_security_name" name="add_form_security_name" class="input-xlarge">
            </div>
            <label for="add_form_security_login" class="control-label">Логін особи</label>
            <div class="controls">
                <input type="text" id="add_form_security_login" name="add_form_security_login" class="input-xlarge">
            </div>
            <label for="add_form_security_password" class="control-label">Пароль особи</label>
            <div class="controls">
                <input type="password" id="add_form_security_password" name="add_form_security_password" class="input-xlarge">
            </div>
        </div>
        <div id="add_form_info"></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success" onclick="SendAddData(); return false" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Додати особу</button>
        <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
    </div>
    <?php echo form_close(); ?>
</div>

<div class="modal hide fade in" id="edit_security_modal" style="display: none;">
    <div class="modal-header">
        <button data-dismiss="modal" class="close">×</button>
        <h3>Заповніть дані форми</h3>
    </div>
    <?php echo form_open('', 'id="edit_form"') ?>
    <div class="modal-body">
        <p>Логін особи: <strong id="login"></strong></p>
        <div class="control-group">
            <label for="edit_form_security_name" class="control-label">ПІБ особи</label>
            <div class="controls">
                <input type="text" id="edit_form_security_name" name="edit_form_security_name" class="input-xlarge">
                <input type="hidden" id="edit_form_security_id" name="edit_form_security_id" class="input-xlarge">
            </div>
            <label for="edit_form_security_password" class="control-label">Новий пароль</label>
            <div class="controls">
                <input type="password" id="edit_form_security_password" name="edit_form_security_password" class="input-xlarge">
            </div>
        </div>
        <div id="edit_form_info"></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success" onclick="SendEditData(); return false" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Редагувати дані</button>
        <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
    </div>
    <?php echo form_close(); ?>
</div>