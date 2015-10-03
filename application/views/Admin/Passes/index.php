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
    
    <div class="tabbable"> <!-- Only required for left/right tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#not_issued" data-toggle="tab"><b>Не видані</b></a></li>
            <li><a href="#issued" data-toggle="tab"><b>Видані</b></a></li>
            <li><a href="#lost" data-toggle="tab"><b>Загублені</b></a></li>
        </ul>
        <div class="tab-content" style="overflow: inherit">
            <div class="tab-pane active" id="not_issued">
                <h6>Всього не виданних перепусток: <?php echo $not_issued_passes_count; ?></h6>
                <br />
                <?php if ($role_id === 1): ?>
                    <a class="btn btn-success" href="#add_pass_modal" data-toggle="modal" onclick="$('#number').val('');"><i class="icon-plus icon-white"></i> Додати</a>
                    <br /><br />
                <?php endif; ?>
                <?php if (!empty($not_issued_passes)): ?>
                <h4>Натисніть на перепустку для редагування її даних або видалення.</h4>
                <table class="table table-striped" width="100%" id="table_not_issued">
                    <?php $third_part = ceil($not_issued_passes_count / 3); for($key=0; $key < $third_part; $key++): ?>
                        <tr>
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($not_issued_passes[$key]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $not_issued_passes[$key]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $not_issued_passes[$key]->number . ' (' . $not_issued_passes[$key]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                        <ul class="dropdown-menu">                                            
                                            <li><a id="<?php echo 'edit_pass_' . $not_issued_passes[$key]->pass_id; ?>" href="#modal_edit_pass" data-toggle="modal" onclick="FillEditForm(<?php echo $not_issued_passes[$key]->pass_id; ?>)"><i class="icon-edit"></i> Редагувати номер</a></li>
                                            <li><a id="<?php echo 'send_to_lost_' . $not_issued_passes[$key]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список загублених?')) SendToLost(<?php echo $not_issued_passes[$key]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список загублених</a></li>
                                            <li><a href="#" onclick="if (confirm('Ви дійсно бажаєте видалити цей пропуск?')) DeletePass(<?php echo $not_issued_passes[$key]->pass_id ?>)"><i class="icon-remove"></i> Видалити</a></li>
                                        </ul>
                                        <?php endif; ?>
                                    <?php endif; ?> 
                                </div>
                            </td>
                            
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($not_issued_passes[$key + $third_part]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $not_issued_passes[$key + $third_part]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $not_issued_passes[$key + $third_part]->number . ' (' . $not_issued_passes[$key + $third_part]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                        <ul class="dropdown-menu">
                                            <li><a id="<?php echo 'edit_pass_' . $not_issued_passes[$key + $third_part]->pass_id; ?>" href="#modal_edit_pass" data-toggle="modal" onclick="FillEditForm(<?php echo $not_issued_passes[$key + $third_part]->pass_id; ?>)"><i class="icon-edit"></i> Редагувати номер</a></li>
                                            <li><a id="<?php echo 'send_to_lost_' . $not_issued_passes[$key + $third_part]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список загублених?')) SendToLost(<?php echo $not_issued_passes[$key]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список загублених</a></li>
                                            <li><a href="#" onclick="if (confirm('Ви дійсно бажаєте видалити цей пропуск?')) DeletePass(<?php echo $not_issued_passes[$key + $third_part]->pass_id ?>)"><i class="icon-remove"></i> Видалити</a></li>
                                        </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>    
                                </div>
                            </td>
                            
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($not_issued_passes[$key + 2 * $third_part]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $not_issued_passes[$key + 2*$third_part]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $not_issued_passes[$key + 2*$third_part]->number . ' (' . $not_issued_passes[$key + 2*$third_part]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                            <ul class="dropdown-menu">                                            
                                                <li><a id="<?php echo 'edit_pass_' . $not_issued_passes[$key + 2*$third_part]->pass_id; ?>" href="#modal_edit_pass" data-toggle="modal" onclick="FillEditForm(<?php echo $not_issued_passes[$key + 2*$third_part]->pass_id; ?>)"><i class="icon-edit"></i> Редагувати номер</a></li>
                                                <li><a id="<?php echo 'send_to_lost_' . $not_issued_passes[$key + 2*$third_part]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список загублених?')) SendToLost(<?php echo $not_issued_passes[$key + 2*$third_part]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список загублених</a></li>
                                                <li><a href="#" onclick="if (confirm('Ви дійсно бажаєте видалити цей пропуск?')) DeletePass(<?php echo $not_issued_passes[$key + 2*$third_part]->pass_id ?>)"><i class="icon-remove"></i> Видалити</a></li>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>  
                                </div>
                            </td>                            
                        </tr>
                    <?php endfor; ?>
                </table>
                <?php else: ?>
                    <div class="span"><h2>Не видані перепустки відсутні!<h2></div>
                <?php endif; ?>    
            </div>
            
            <div class="tab-pane" id="issued">
                <h6>Всього виданних перепусток: <?php echo $issued_passes_count; ?></h6>
                <br />
                <?php if (!empty($issued_passes)): ?>
                <h4>Натисніть на перепустку для редагування її даних.</h4>
                <table class="table table-striped" width="100%" id="table_issued">
                    <?php $third_part = ceil($issued_passes_count / 3); for($key=0; $key < $third_part; $key++): ?>
                        <tr>
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($issued_passes[$key]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $issued_passes[$key]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $issued_passes[$key]->number . ' (' . $issued_passes[$key]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                        <ul class="dropdown-menu">                                            
                                            <li><a id="<?php echo 'send_to_lost_' . $issued_passes[$key]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список загублених?')) SendToLost(<?php echo $issued_passes[$key]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список загублених</a></li>
                                        </ul>
                                        <?php endif; ?>
                                    <?php endif; ?> 
                                </div>
                            </td>
                            
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($issued_passes[$key + $third_part]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $issued_passes[$key + $third_part]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $issued_passes[$key + $third_part]->number . ' (' . $issued_passes[$key + $third_part]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                        <ul class="dropdown-menu">                                            
                                            <li><a id="<?php echo 'send_to_lost_' . $issued_passes[$key + $third_part]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список загублених?')) SendToLost(<?php echo $issued_passes[$key + $third_part]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список загублених</a></li>
                                        </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>    
                                </div>
                            </td>
                            
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($issued_passes[$key + 2 * $third_part]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $issued_passes[$key + 2*$third_part]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $issued_passes[$key + 2*$third_part]->number . ' (' . $issued_passes[$key + 2*$third_part]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                            <ul class="dropdown-menu">                                            
                                                <li><a id="<?php echo 'send_to_lost_' . $issued_passes[$key + 2*$third_part]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список загублених?')) SendToLost(<?php echo $issued_passes[$key + 2*$third_part]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список загублених</a></li>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>  
                                </div>
                            </td>                            
                        </tr>
                    <?php endfor; ?>
                </table>
                <?php else: ?>
                    <div class="span"><h2>Видані перепустки відсутні!<h2></div>
                <?php endif; ?> 
            </div>
            
            <div class="tab-pane" id="lost">
                <h6>Всього загублених перепусток: <?php echo $lost_passes_count; ?></h6>
                <br />
                <?php if (!empty($lost_passes)): ?>
                <h4>Натисніть на перепустку для редагування її даних.</h4>
                <table class="table table-striped" width="100%" id="table_issued">
                    <?php $third_part = ceil($lost_passes_count / 3); for($key=0; $key < $third_part; $key++): ?>
                        <tr>
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($lost_passes[$key]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $lost_passes[$key]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $lost_passes[$key]->number . ' (' . $lost_passes[$key]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                            <ul class="dropdown-menu">                                            
                                                <li><a id="<?php echo 'send_to_lost_' . $lost_passes[$key]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список не виданних?')) SendToNotIssued(<?php echo $lost_passes[$key]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список не виданних</a></li>
                                                <li><a href="#" onclick="if (confirm('Ви дійсно бажаєте видалити цей пропуск?')) DeletePass(<?php echo $lost_passes[$key]->pass_id ?>)"><i class="icon-remove"></i> Видалити</a></li>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?> 
                                </div>
                            </td>
                            
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($lost_passes[$key + $third_part]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $lost_passes[$key + $third_part]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $lost_passes[$key + $third_part]->number . ' (' . $lost_passes[$key + $third_part]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                            <ul class="dropdown-menu">
                                                <li><a id="<?php echo 'send_to_lost_' . $lost_passes[$key + $third_part]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список не виданних?')) SendToNotIssued(<?php echo $lost_passes[$key + $third_part]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список не виданних</a></li>
                                                <li><a href="#" onclick="if (confirm('Ви дійсно бажаєте видалити цей пропуск?')) DeletePass(<?php echo $lost_passes[$key + $third_part]->pass_id ?>)"><i class="icon-remove"></i> Видалити</a></li>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>    
                                </div>
                            </td>
                            
                            <td>
                                <div class="btn-group">
                                    <?php if (isset($lost_passes[$key + 2 * $third_part]->pass_id)): ?>
                                        <button id="<?php echo 'pass_id_' . $lost_passes[$key + 2*$third_part]->pass_id; ?>" data-toggle="dropdown" class="btn dropdown-toggle"><b><?php echo $lost_passes[$key + 2*$third_part]->number . ' (' . $lost_passes[$key + 2*$third_part]->room . ')'; ?></b> <span class="caret"></span></button>
                                        <?php if ($role_id === 1): ?>
                                            <ul class="dropdown-menu">
                                                <li><a id="<?php echo 'send_to_lost_' . $lost_passes[$key + 2*$third_part]->pass_id; ?>" href="#" onclick="if (confirm('Ви дійсно бажаєте перемістити цей пропуск у список не виданних?')) SendToNotIssued(<?php echo $lost_passes[$key + 2*$third_part]->pass_id; ?>)"><i class="icon-share-alt"></i> Помістити у список не виданних</a></li>
                                                <li><a href="#" onclick="if (confirm('Ви дійсно бажаєте видалити цей пропуск?')) DeletePass(<?php echo $lost_passes[$key + 2*$third_part]->pass_id ?>)"><i class="icon-remove"></i> Видалити</a></li>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>  
                                </div>
                            </td>                            
                        </tr>
                    <?php endfor; ?>
                </table>
                <?php else: ?>
                    <div class="span"><h2>Загублені перепустки відсутні!<h2></div>
                <?php endif; ?> 
            </div>
        </div>
    </div>
</div>

<?php if ($role_id === 1): ?>
    <div class="modal hide fade in" id="modal_edit_pass" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3 id="header_pass_number">Редагування перепустки №</h3>
        </div>
        <?php echo form_open('', 'id="edit_form"') ?>
        <div class="modal-body">
            <div class="control-group">
                <label for="new_number" class="control-label">Новий номер</label>
                <div class="controls">
                    <input type="text" id="new_number" name="new_number" class="input-xlarge">
                    <input type="hidden" id="edit_form_pass_id" name="edit_form_pass_id">
                </div>
            </div>
            <div class="control-group">
                <label for="edit_form_room" class="control-label">Поверх</label>
                <div class="controls">
                    <select id="edit_form_room" name="edit_form_room" class="input-xlarge" style="width: 280px">
                        <option value="0" selected="selected"></option>
                        <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room->room_id; ?>"><?php echo $room->number ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="edit_form_info"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="EditPass(); return false" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Редагувати дані</button>
            <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
        </div>
        <?php echo form_close(); ?>
    </div>

    <div class="modal hide fade in" id="add_pass_modal" style="display: none;">
        <div class="modal-header">
            <button data-dismiss="modal" class="close">×</button>
            <h3 id="header_pass_number">Додання нової перепустки</h3>
        </div>
        <?php echo form_open('', 'id="add_form"') ?>
        <div class="modal-body">
            <div class="control-group">
                <label for="number" class="control-label">Номер перепустки</label>
                <div class="controls">
                    <input type="text" id="number" name="number" class="input-xlarge">
                </div>
            </div>
            
            <div class="control-group">
                <label for="room" class="control-label">Поверх</label>
                <div class="controls">
                    <select id="room" name="room" class="input-xlarge" style="width: 280px">
                        <option value="0" selected="selected"></option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room->room_id; ?>"><?php echo $room->number ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="add_form_info"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" onclick="AddPass(); return false" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Додати перепустку</button>
            <a data-dismiss="modal" class="btn btn-danger" href="#"><i class="icon-remove-sign icon-white"></i> Закрити</a>
        </div>
        <?php echo form_close(); ?>
    </div>
<?php endif; ?>