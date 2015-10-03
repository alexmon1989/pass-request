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

    <h6>Всього патентних повірених: <?php echo $patent_agents_count; ?></h6>
    <br />
    <?php if ($role_id === 1): ?>
        <a class="btn btn-success" href="#add_patent_agent_modal" data-toggle="modal"><i class="icon-plus icon-white"></i> Додати особу</a>
        <br /><br />
    <?php endif; ?>
    <?php if (!empty($patent_agents)): ?>
    <h4>Натисніть на особу для редагування її даних або формування заявки.</h4>
    <table class="table table-striped" width="100%" id="table_requests">
        <?php $third_part = ceil($patent_agents_count / 3); for($key=0; $key < $third_part; $key++): ?>
            <tr>
                <td>
                    <?php if (isset($patent_agents[$key]->name)): ?>
                        <div class="btn-group">
                            <button id="<?php echo 'patent_agent_name_' . $patent_agents[$key]->patent_agent_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $patent_agents[$key]->name; ?></b> <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <?php if ($role_id === 1 or $role_id === 2): ?>
                                    <li><a href="<?php echo base_url('admin/patent_agents/add_request/' . $patent_agents[$key]->patent_agent_id) ?>"><i class="icon-plus"></i> Видати пропуск</a></li>
                                <?php endif; ?> 
                                <?php if ($role_id === 1): ?>
                                    <li><a id="<?php echo 'edit_' . $patent_agents[$key]->patent_agent_id; ?>" href="#edit_patent_agent_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                                    <li><a href="<?php echo base_url('admin/patent_agents/delete/' . $patent_agents[$key]->patent_agent_id) ?>"  onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити особу</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if (isset($patent_agents[$key + $third_part]->name)): ?>
                    <div class="btn-group">
                        <button id="<?php echo 'patent_agent_name_' . $patent_agents[$key + $third_part]->patent_agent_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $patent_agents[$key + $third_part]->name; ?></b> <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <?php if ($role_id === 1 or $role_id === 2): ?>
                                <li><a href="<?php echo base_url('admin/patent_agents/add_request/' . $patent_agents[$key + $third_part]->patent_agent_id) ?>"><i class="icon-plus"></i> Видати пропуск</a></li>
                            <?php endif; ?> 
                            <?php if ($role_id === 1): ?>
                                <li><a id="<?php echo 'edit_' . $patent_agents[$key + $third_part]->patent_agent_id; ?>" href="#edit_patent_agent_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                                <li><a href="<?php echo base_url('admin/patent_agents/delete/' . $patent_agents[$key + $third_part]->patent_agent_id) ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити особу</a></li>
                            <?php endif; ?>    
                        </ul>
                    </div>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if (isset($patent_agents[$key + 2 * $third_part]->name)): ?>
                    <div class="btn-group">
                        <button id="<?php echo 'patent_agent_name_' . $patent_agents[$key + 2 * $third_part]->patent_agent_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $patent_agents[$key + 2 * $third_part]->name; ?></b> <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <?php if ($role_id === 1 or $role_id === 2): ?>
                                <li><a href="<?php echo base_url('admin/patent_agents/add_request/' . $patent_agents[$key + 2 * $third_part]->patent_agent_id) ?>"><i class="icon-plus"></i> Видати пропуск</a></li>
                            <?php endif; ?> 
                            <?php if ($role_id === 1): ?>
                                <li><a id="<?php echo 'edit_' . $patent_agents[$key + 2 * $third_part]->patent_agent_id; ?>" href="#edit_patent_agent_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                                <li><a href="<?php echo base_url('admin/patent_agents/delete/' . $patent_agents[$key + 2 * $third_part]->patent_agent_id) ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити особу</a></li>
                            <?php endif; ?>      
                        </ul>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endfor; ?>
    </table>
    <?php else: ?>
        <div class="span"><h2>Патентні повірені відсутні!<h2></div>
    <?php endif; ?>
</div>


<?php if ($role_id === 1): ?>
    <div class="modal hide fade in" id="add_patent_agent_modal" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3>Заповніть дані форми</h3>
        </div>
        <?php echo form_open('', 'id="add_form"') ?>
            <div class="modal-body">
                <div class="control-group">
                    <label for="add_form_patent_agent_name" class="control-label">ПІБ патентного повіренного</label>
                    <div class="controls">
                        <input type="text" id="add_form_patent_agent_name" name="add_form_patent_agent_name" class="input-xlarge" value="<?php echo set_value('add_form_patent_agent_name'); ?>">
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

    <div class="modal hide fade in" id="edit_patent_agent_modal" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3>Заповніть дані форми</h3>
        </div>
        <?php echo form_open('', 'id="edit_form"') ?>
        <div class="modal-body">
            <div class="control-group">
                <label for="edit_form_patent_agent_name" class="control-label">ПІБ патентного повіренного</label>
                <div class="controls">
                    <input type="text" id="edit_form_patent_agent_name" name="edit_form_patent_agent_name" class="input-xlarge" value="<?php echo set_value('edit_form_patent_agent_name'); ?>">
                    <input type="hidden" id="edit_form_patent_agent_id" name="edit_form_patent_agent_id" class="input-xlarge" value="<?php echo set_value('edit_form_patent_agent_id'); ?>">
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
<?php endif; ?>

<script type="text/javascript">
        $(document).ready(function() {
            $.datepicker.setDefaults(
                $.extend($.datepicker.regional["uk"])
            );
            $("#document_date").datepicker();
        });
</script>