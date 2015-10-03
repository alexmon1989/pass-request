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

    <h6>Всього заявок: <?php echo $contracts_count; ?></h6>
    <br />
    <?php if ($role_id === 1): ?>
        <a class="btn btn-success" href="<?php echo base_url('admin/contracts/add') ?>"><i class="icon-plus icon-white"></i> Створити угоду</a>
        <br /><br />
    <?php endif; ?>
    <?php if (!empty($contracts_old_count)): ?>
        <h3>Угоди, що закінчуються:</h3>
        <table class="table table-striped" width="100%" id="table_requests" style="font-size: 16px; font-weight: bold">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Номер угоди</th>
                    <th>ПІБ відвідувача</th>
                    <th>ПІБ начальника</th>
                    <th>Дата з</th>
                    <th>Дата по</th>
                    <th>№ перепустки</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts_old as $key => $contract): ?>
                    <tr onclick="location.href = '<?php echo base_url('admin/contracts/show/' . $contract->contract_id); ?>'" style="cursor:pointer; color: red" title="Редагувати трудову угоду">
                        <td><?php echo $page_num * 15 + $key + 1; ?></td>                        
                        <td><?php echo $contract->contract_number; ?></td>     
                        <td><?php echo $contract->visitorname; ?></td>
                        <td><?php echo $contract->username; ?></td>
                        <td><?php echo $contract->date_from; ?></td>
                        <td id="date_to_<?php echo $contract->contract_id; ?>"><?php echo $contract->date_to; ?></td>                        
                        <td><?php echo $contract->pass_number; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    
    <?php if (!empty($contracts_count)): ?>    
        <h3>Усі угоди:</h3>
        <table class="table table-striped" width="100%" id="table_requests" style="font-size: 16px; font-weight: bold">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Номер угоди</th>
                    <th>ПІБ відвідувача <a href="#" onclick="Sort('visitorname', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('visitorname', 'DESC'); return false;" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>ПІБ начальника <a href="#" onclick="Sort('username', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('username', 'DESC'); return false;" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>Дата з <a href="#" onclick="Sort('date_from', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('date_from', 'DESC'); return false;" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>Дата по <a href="#" onclick="Sort('date_to', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('date_to', 'DESC'); return false;" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>№ перепустки <a href="#" onclick="Sort('pass_number', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('pass_number', 'DESC');" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $key => $contract): ?>
                    <tr id="tr_<?php echo $contract->contract_id; ?>" onclick="location.href = '<?php echo base_url('admin/contracts/show/' . $contract->contract_id); ?>'" style="cursor:pointer; color: black" title="Редагувати трудову угоду">
                        <td><?php echo $page_num * 15 + $key + 1; ?></td>
                        <td><?php echo $contract->contract_number; ?></td>                        
                        <td><?php echo $contract->visitorname; ?></td>
                        <td><?php echo $contract->username; ?></td>
                        <td><?php echo $contract->date_from; ?></td>
                        <td id="date_to_<?php echo $contract->contract_id; ?>"><?php echo $contract->date_to; ?></td>
                        <script type="text/javascript">
                                $(function(){
                                    // -1 - Уже прошло
                                    // 0 - Заканчивается
                                    // 1 - Актуально
                                    var actual = <?php echo $contract->actual; ?>;
                                    if (actual == 1)
                                    {
                                        // Красим шрифт в синий
                                        $("#tr_<?php echo $contract->contract_id; ?>").css('color', 'blue');

                                    }
                                    else if (actual == 0)
                                    {
                                        // Красим шрифт в красный   
                                        $("#tr_<?php echo $contract->contract_id; ?>").css('color', 'red');
                                    }
                                });
                         </script>
                        <td><?php echo $contract->pass_number; ?></td>
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
        <div class="span"><h2>Інформація відсутня!<h2></div>
    <?php endif; ?>
</div>