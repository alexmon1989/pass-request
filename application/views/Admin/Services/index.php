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

    <h6>Всього : <?php echo $services_employees_count; ?> осіб у <?php echo $services_count; ?> організаціях</h6>
    <br />
    <?php if ($role_id === 1): ?>
        <a id="service_add" class="btn btn-success" href="#modal_add_service" data-toggle="modal"><i class="icon-plus icon-white"></i> Додати організацію</a>
        <br /><br />
    <?php endif; ?>
    <?php if (!empty($services)): ?>
    <?php foreach ($services as $service): ?>
        <h3 id="organization_<?php echo $service->service_id; ?>">Співробітники організації "<?php echo $service->service_name; ?>":</h3>
        <?php if ($role_id === 1): ?>
            <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Дії над організацією <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a id="employee_add_<?php echo $service->service_id; ?>" href="#modal_add_employee" onclick="FillAddEmployeeForm(this.id)" data-toggle="modal"><i class="icon-plus"></i> Додати користувача</a></li>
                    <li><a id="service_<?php echo $service->service_id; ?>>" onclick="FillServiceEditForm(this.id);" href="#modal_edit_service" data-toggle="modal"><i class="icon-edit"></i> Редагувати назву</a></li>
                    <li><a href="<?php echo base_url('admin/service/delete_service/' . $service->service_id); ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю організацію?');"><i class="icon-remove"></i> Видалити</a></li>
            </ul>
            </div>
        <?php endif; ?>  

        <table class="table table-striped" width="100%" id="table_services">
            <thead>
            <tr>
                <th width="10%">#</th>
                <th width="50%">ПІБ особи</th>
                <th width="25%">№ пропуска</th>
                <?php if ($role_id === 1): ?>
                    <th width="15%">Дії</th>
                    <th width="0%" style="display: none;">Останній кабінет</th>
                    <th width="0%" style="display: none;">Останній документ</th>
                    <th width="0%" style="display: none;">Останній заявник</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($service->employees as $key => $employee): ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td id="<?php echo 'name_' . $employee->service_employee_id; ?>"><?php echo $employee->name; ?></td>
                        <td id="<?php echo 'passnumber_' . $employee->service_employee_id; ?>"><?php echo $employee->pass_number; ?></td>
                        <?php if ($role_id === 1 or $role_id === 2): ?>
                        <td>
                            <div class="btn-group">
                                <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Дії над особою <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo base_url('admin/service/add_request/' . $employee->service_employee_id) ?>"><i class="icon-plus"></i> Видати пропуск</a></li>
                                    <?php if ($role_id === 1): ?>
                                        <li><a id="<?php echo 'edit_' . $employee->service_employee_id; ?>" href="#modal_edit" data-toggle="modal" onclick="FillEditForm(this.id);"><i class="icon-edit"></i> Редагувати</a></li>
                                        <li><a id="<?php echo 'delete_' . $employee->service_employee_id; ?>" href="<?php echo base_url('admin/service/delete_employee/' . $employee->service_employee_id); ?>"><i class="icon-remove"></i> Видалити</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td style="display: none;" id="<?php echo 'last_room_' . $employee->service_employee_id; ?>"><?php echo $employee->last_room_id; ?></td>
                        <td style="display: none;" id="<?php echo 'document_id_' . $employee->service_employee_id; ?>"><?php echo $employee->document_id; ?></td>
                        <td style="display: none;" id="<?php echo 'applicant_id_' . $employee->service_employee_id; ?>"><?php echo $employee->applicant_id; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
    <?php else: ?>
        <div class="span"><h2>Сторонні фірми відсутні!<h2></div>
    <?php endif; ?>
</div>

<?php if ($role_id === 1): ?>
    <div class="modal hide fade in" id="modal_edit" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3>Заповніть дані користувача</h3>
        </div>
        <?php echo form_open('', 'id="form_edit"') ?>
        <div class="modal-body">
                <div class="control-group">
                    <label for="service_employee_name" class="control-label">ПІБ особи</label>
                    <div class="controls">
                        <input type="text" id="service_employee_name" name="service_employee_name" class="input-xlarge" value="<?php echo set_value('service_employee_name'); ?>">
                    </div>
                    <label for="service_employee_pass_number" class="control-label">№ порпуска</label>
                    <div class="controls">
                        <select id="service_employee_pass_number" name="service_employee_pass_number" class="input-xlarge" style="width: 280px" onfocus="UpdatePasses('#service_employee_pass_number')" onchange="this.blur();"></select>
                        <input type="hidden" id="service_employee_id" name="service_employee_id" class="input-xlarge">
                    </div>
                </div>
                <div id="info_edit"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info" onclick="SendEditData(); return false" type="button" value="ok"><i class="icon-edit icon-white"></i> Редагувати</button>
                <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
            </div>
            <?php echo form_close(); ?>
    </div>

    <div class="modal hide fade in" id="modal_edit_service" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3>Заповніть дані організації</h3>
        </div>
        <?php echo form_open('', 'id="form_edit_service"') ?>
        <div class="modal-body">
            <div class="control-group">
                <label for="service_name" class="control-label">Назва організації</label>
                <div class="controls">
                    <input type="text" id="service_name" name="service_name" class="input-xlarge" value="<?php echo set_value('service_name'); ?>">
                    <input type="hidden" id="service_id" name="service_id">
                </div>
            </div>
            <div id="info_edit_service"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-info" onclick="SendEditServiceData(); return false" type="button" value="ok"><i class="icon-edit icon-white"></i> Редагувати</button>
            <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
        </div>
        <?php echo form_close(); ?>
    </div>

    <div class="modal hide fade in" id="modal_add_employee" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3>Заповніть дані співробітника організації</h3>
        </div>
        <?php echo form_open('', 'id="form_add_employee"') ?>
        <div class="modal-body">
            <div class="control-group">
                <label for="form_add_employee_name" class="control-label">Ім'я співробітника організації</label>
                <div class="controls">
                    <input type="text" id="form_add_employee_name" name="form_add_employee_name" class="input-xlarge" value="<?php echo set_value('form_add_employee_name'); ?>">
                </div>
                <label for="form_add_pass_number" class="control-label">Номер пропуска</label>
                <div class="controls">
                    <select id="form_add_pass_number" name="form_add_pass_number" class="input-xlarge" style="width: 280px" onfocus="UpdatePasses('#form_add_pass_number')" onchange="this.blur();"></select>
                    <input type="hidden" id="form_add_employee_org_id" name="form_add_employee_org_id" class="input-xlarge" value="<?php echo set_value('form_add_employee_org_id'); ?>">
                </div>
            </div>
            <div id="info_add_employee"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-info" onclick="SendAddEmployeeData(); return false" type="button" value="ok"><i class="icon-plus icon-white"></i> Додати користувача</button>
            <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
        </div>
        <?php echo form_close(); ?>
    </div>

    <div class="modal hide fade in" id="modal_add_service" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3>Заповніть дані організації</h3>
        </div>
        <?php echo form_open('', 'id="form_add_service"') ?>
        <div class="modal-body">
            <div class="control-group">
                <label for="form_service_name" class="control-label">Назва організації</label>
                <div class="controls">
                    <input type="text" id="form_service_name" name="form_service_name" class="input-xlarge" value="<?php echo set_value('form_service_name'); ?>">
                </div>
            </div>
            <div id="info_add_service"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-info" onclick="SendAddServiceData(); return false" type="button" value="ok"><i class="icon-plus icon-white"></i> Додати організацію</button>
            <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
        </div>
        <?php echo form_close(); ?>
    </div>
<?php endif; ?>

<script type="text/javascript">
        $(document).ready(function() {
            $.datepicker.setDefaults(
                $.extend($.datepicker.regional["uk"])
            );
            $("#document_date").datepicker();
        });
</script>