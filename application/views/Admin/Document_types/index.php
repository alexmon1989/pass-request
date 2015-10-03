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

    <h6>Всього типів документів: <?php echo $document_types_count; ?></h6>
    <br />
    <?php if ($role_id === 1): ?>
        <a class="btn btn-success" href="#add_document_type_modal" data-toggle="modal"><i class="icon-plus icon-white" onclick="FillAddForm(); return false;"></i> Додати тип</a>
        <br /><br />
    <?php endif; ?>
    <?php if ($document_types_count > 0): ?>
    <h4>Натисніть на тип документу для редагування даних або видалення.</h4>
    <table class="table table-striped" width="100%" id="table_requests">
        <?php $third_part = ceil($document_types_count / 3); for($key=0; $key < $third_part; $key++): ?>
        <tr>
            <td>
                <?php if (isset($document_types[$key]->type)): ?>
                <div class="btn-group">
                    <button id="<?php echo 'doc_type_' . $document_types[$key]->document_type_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $document_types[$key]->type; ?></b> <span class="caret"></span></button>
                    <?php if ($role_id === 1): ?>
                    <ul class="dropdown-menu">
                        <li><a id="<?php echo 'edit_' . $document_types[$key]->document_type_id; ?>" href="#edit_document_type_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                        <li><a href="<?php echo base_url('admin/document_types/delete/' . $document_types[$key]->document_type_id) ?>"  onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити</a></li>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </td>

            <td>
                <?php if (isset($document_types[$key + $third_part]->type)): ?>
                <div class="btn-group">
                    <button id="<?php echo 'doc_type_' . $document_types[$key + $third_part]->document_type_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $document_types[$key + $third_part]->type; ?></b> <span class="caret"></span></button>
                    <?php if ($role_id === 1): ?>
                    <ul class="dropdown-menu">
                        <li><a id="<?php echo 'edit_' . $document_types[$key + $third_part]->document_type_id; ?>" href="#edit_document_type_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                        <li><a href="<?php echo base_url('admin/document_types/delete/' . $document_types[$key + $third_part]->document_type_id) ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити</a></li>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </td>

            <td>
                <?php if (isset($document_types[$key + 2 * $third_part]->type)): ?>
                <div class="btn-group">
                    <button id="<?php echo 'doc_type_' . $document_types[$key + 2 * $third_part]->document_type_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $document_types[$key + 2 * $third_part]->type; ?></b> <span class="caret"></span></button>
                    <?php if ($role_id === 1): ?>
                    <ul class="dropdown-menu">
                        <li><a id="<?php echo 'edit_' . $document_types[$key + 2 * $third_part]->document_type_id; ?>" href="#edit_document_type_modal" data-toggle="modal" onclick="FillEditForm(this.id); return false;"><i class="icon-edit"></i> Редагувати дані</a></li>
                        <li><a href="<?php echo base_url('admin/document_types/delete/' . $document_types[$key + 2 * $third_part]->document_type_id) ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю особу?')"><i class="icon-remove"></i> Видалити</a></li>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php endfor; ?>
    </table>
    <?php else: ?>
    <div class="span"><h2>Типи документів відсутні!<h2></div>
    <?php endif; ?>
</div>


<div class="modal hide fade in" id="add_document_type_modal" style="display: none;">
    <div class="modal-header">
        <button data-dismiss="modal" class="close">×</button>
        <h3>Заповніть дані форми</h3>
    </div>
    <?php echo form_open('', 'id="add_form"') ?>
    <div class="modal-body">
        <div class="control-group">
            <label for="add_doc_type" class="control-label">Тип документа</label>
            <div class="controls">
                <input type="text" id="add_doc_type" name="add_doc_type" class="input-xlarge">
            </div>
        </div>
        <div id="add_form_info"></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success" onclick="SendAddData(); return false" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Додати</button>
        <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
    </div>
    <?php echo form_close(); ?>
</div>

<div class="modal hide fade in" id="edit_document_type_modal" style="display: none;">
    <div class="modal-header">
        <button data-dismiss="modal" class="close">×</button>
        <h3>Заповніть дані форми</h3>
    </div>
    <?php echo form_open('', 'id="edit_form"') ?>
    <div class="modal-body">
        <div class="control-group">
            <label for="edit_doc_type" class="control-label">Змінити тип на:</label>
            <div class="controls">
                <input type="text" id="edit_doc_type" name="edit_doc_type" class="input-xlarge">
                <input type="hidden" id="edit_doc_type_id" name="edit_doc_type_id" class="input-xlarge">
            </div>
        </div>
        <div id="edit_form_info"></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success" onclick="SendEditData(); return false" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Редагувати</button>
        <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
    </div>
    <?php echo form_close(); ?>
</div>