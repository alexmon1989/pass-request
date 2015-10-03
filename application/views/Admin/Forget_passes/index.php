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

    <h6>Всього виданих пропусків: <?php echo $forget_passes_count; ?></h6>
    <br />
    <?php if ($role_id === 1 or $role_id === 2): ?>
        <a class="btn btn-success" href="#add_modal" data-toggle="modal" onclick="$('#form_pass').trigger( 'reset' );"><i class="icon-plus icon-white"></i> Створити забутий пропуск</a>
        <br /><br />
    <?php endif; ?>
    <?php if (!empty($forget_passes)): ?>
    Фільтр:&nbsp; <select id="filter" name="filter" class="input-xlarge" onchange="Filter(this.value);">
                        <option value="0">Усі</option>
                        <?php foreach ($employees as $employee): ?>
                            <option  value="<?php echo $employee->employee_id; ?>"><?php echo $employee->name; ?></option>
                        <?php endforeach; ?>
                  </select>
        <table class="table table-striped" width="100%" id="table_requests" style="font-size: 15px; font-weight: bold">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Номер</th>
                    <th>ПІБ співробітника</th>
                    <th>Видав</th>  
                    <th>Дата видачі</th>    
                    <th>Прийняв</th>      
                    <th>Дата прийому</th>
                    <th>Номер пропуска</th>                    
                    <?php if ($role_id === 1 or $role_id === 2): ?>
                        <th>Дії</th>
                    <?php endif; ?>    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forget_passes as $key => $forget_pass): ?>
                    <tr>
                        <td><?php echo $page_num * 15 + $key + 1; ?></td>
                        <td><strong><?php echo $forget_pass->forget_pass_number; ?></strong></td>
                        <td><strong><?php echo $forget_pass->employee_name; ?></strong></td>
                        <td><strong><?php echo $forget_pass->admin_name; ?></strong></td>
                        <td><strong><?php echo $forget_pass->issue_date; ?></strong></td>
                        <td><strong><?php echo $forget_pass->pass_admin_name; ?></strong></td>
                        <td><strong><?php echo $forget_pass->pass_date; ?></strong></td>
                        <td><strong><?php echo $forget_pass->pass_number; ?></strong></td>
                        <?php if ($role_id === 1 or $role_id === 2): ?>
                        <td>
                            <?php if ($forget_pass->pass_date === NULL): ?>
                                <a title="Здати пропуск" href="#" onclick="$('#dialog_take_pass').dialog('open'); $('#dialog_take_pass_forget_pass_id').val(<?php echo $forget_pass->forget_pass_id; ?>); $('#dialog_take_pass_password').val('');"><img src="<?php echo base_url('images/down.PNG'); ?>" /></a> 
                                <a title="Здати та відмітити пропуск як загублений" href="#" onclick="$('#dialog_lost_pass').dialog('open'); $('#forget_pass_id').val(<?php echo $forget_pass->forget_pass_id; ?>); $('#password').val('');"><img src="<?php echo base_url('images/down_red.PNG'); ?>" /></a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            <div class="span4 offset4">
                <div class="pagination">
                    <?php echo $pages; ?>
                </div>
            </div>
    <?php else: ?>
        <div class="span"><h2>Забуті пропуски відсутні!<h2></div>
    <?php endif; ?>
</div>

<div class="modal hide fade in" id="add_modal" style="display: none;">
    <div class="modal-header">
        <button data-dismiss="modal" class="close">×</button>
        <h3>Для видачі пропуска необхідно заповнити форму</h3>
    </div>
    <?php echo form_open('', 'id="form_pass"') ?>
        <div class="modal-body" id="modal_body">
            <div class="control-group">
                <label for="employee_name" class="control-label">ПІБ співробітника</label>
                <div class="controls">
                    <input type="text" id="employee_name" name="employee_name" class="input-xlarge">
                </div>
                <label for="pass_number" class="control-label">Номер пропуска</label>
                <div class="controls">
                    <select id="pass_number" name="pass_number" class="input-xlarge" style="width: 275px"></select>
                </div>
                <label for="form_pass_password" class="control-label"><b>Ваш пароль</b></label>
                <div class="controls">
                    <input type="password" id="form_pass_password" name="form_pass_password" class="input-xlarge">
                </div>
            </div>
            <div id="info_pass"></div>
        </div>
        <div class="modal-footer">
            <button onclick="SendPassData();" class="btn btn-success" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Видати пропуск</button>
            <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
        </div>
    <?php echo form_close(); ?>
</div>

<div id="dialog_lost_pass" title="Загублено перепустку" style="display: none">
	<p>Ввведіть, будь-ласка, Ваш пароль.</p>
    <div id="error_dialog" style="color: red"></div>
    <input type="hidden" id="forget_pass_id">
    <b>Пароль*:</b><br/> <input type="password" id="password" style="width: 273px;">
    <button onclick="LostPass()" id="change_pass_num" class="btn btn-success" type="button" value="ok"><i class="icon-ok icon-white"></i> Закрити заявку</button>
</div>

<div id="dialog_take_pass" title="Підтвердження паролю" style="display: none">
	<p>Ввведіть, будь-ласка, Ваш пароль.</p>
    <div id="dialog_take_pass_error" style="color: red"></div>
    <input type="hidden" id="dialog_take_pass_forget_pass_id">
    <b>Пароль*:</b><br/> <input type="password" id="dialog_take_pass_password" style="width: 273px;">
    <button onclick="TakePass()" id="take_pass_btn" class="btn btn-success" type="button" value="ok"><i class="icon-ok icon-white"></i> Здати пропуск</button>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#filter [value=" + <?php echo $filter ?> + "]").attr("selected", "selected");
        UpdatePasses('#pass_number');
    });
    
    $("#employee_name").autocomplete({
			source: "<?php echo base_url('admin/forget_passes/search_visitor') ?>",
           select: function(event, ui) {}
		});
</script>