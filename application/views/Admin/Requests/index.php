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

    <h6>Всього заявок: <?php echo $requests_count; ?></h6>
    <br />
    <?php if ($role_id === 1 or $role_id === 2 or $role_id === 3): ?>
        <a class="btn btn-success" href="<?php echo base_url('/admin/requests/add') ?>"><i class="icon-plus icon-white"></i> Створити заявку</a>
        <br /><br />
    <?php endif; ?>
        
    <?php if ($role_id === 3): ?>
        <input type="checkbox" id="show_only_my_reqs" name="show_only_my_reqs" onclick="ShowOnlyMyReqs(<?php echo $security_id ?>)" <?php if (FALSE !== $this->session->userdata('applicant_id')) echo 'checked="checked"'; ?>> Показувати тільки мої заявки
        <br /><br />
    <?php endif; ?>
    <?php if (!empty($requests)): ?>
        <?php echo form_open('admin/requests'); ?>
        <div class="control-group">
            <a href="#" onclick="ShowFilters()"><i class="icon-download"></i> Фільтри:</a>
            <div id="filters" style="display: none">
                <table>                
                        <tr>
                            <td>ПІБ відвідувача:</td> 
                            <td><input type="text" id="filter_name" name="filter_name" value="<?php echo set_value('filter_name', $this->session->userdata('filter_name')) ?>"></td>
                        </tr>
                        <tr>
                            <td>№ заявки:</td>
                            <td><input type="text" id="request_num_filter" name="request_num_filter" value="<?php echo set_value('request_num_filter', $this->session->userdata('request_num_filter')) ?>"></td>
                        </tr>
                        <tr>
                            <td><button class="btn btn-mini" type="submit"><i class="icon-ok icon-white"></i> Фільтрувати</button></td>
                            <td>&nbsp;</td>
                        </tr>
                </table>
            </div>
        </div>
        </form>
        <table class="table table-striped" width="100%" id="table_requests" style="font-size: 16px">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Номер заявки</th>
                    <th>Дата <a href="#" onclick="Sort('date', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('date', 'DESC'); return false;" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>ПІБ відвідувача <a href="#" onclick="Sort('name', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('name', 'DESC'); return false;" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>№ перепустки <a href="#" onclick="Sort('pass_number', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('pass_number', 'DESC');" title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                    <th>Статус <a href="#" onclick="Sort('status', 'ASC'); return false;" title="Сортувати за зростанням"><img src="<?php echo base_url('images/sort-asc.gif') ?>"></a>  <a href="#" onclick="Sort('status', 'DESC');"  title="Сортувати за спаданням"><img src="<?php echo base_url('images/sort-desc.gif') ?>"></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $key => $request): ?>
                    <tr onclick="location.href = '<?php echo base_url('admin/requests/show/' . $request->request_id); ?>'" style="cursor:pointer" title="Редагувати заявку">
                        <td><?php echo $page_num * 15 + $key + 1; ?></td>
                        <td><strong><?php echo $request->request_number; ?></strong></td>
                        <td><strong><?php echo $request->request_date; ?></strong></td>
                        <td><strong><?php if ($request->visitorname !== '') echo $request->visitorname; elseif (($request->patent_agent !== null)) echo $request->patent_agent; elseif (($request->courier !== null)) echo $request->courier; else echo $request->service_employee ?></strong></td>
                        <td><strong><?php echo $request->pass_number; ?></strong></td>
                        <td><strong><?php echo $request->status; ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            <div class="span6 offset4">
                <div class="pagination">
                    <?php echo $pages; ?>
                </div>
            </div>

        <script type="text/javascript">
            $("#table_requests tr").each(function(){

                if (this.innerHTML.indexOf('Очікується') > -1)
                    this.style.color = "blue";
                if (this.innerHTML.indexOf('Видано') > -1)
                    this.style.color = "red";
            });
        </script>
    <?php else: ?>
        <div class="span"><h2>Заявки на перепустки відсутні!<h2></div>
    <?php endif; ?>
</div>