<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="span12" id="maindiv">
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
    <?php echo form_open('requests/past_requests'); ?>
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
    <a class="btn btn-success" href="<?php echo base_url('requests/add_request') ?>"><i class="icon-plus icon-white"></i> Додати заявку</a>
    <br /><br/>
    <?php if (!empty($requests)): ?>
    <h6>Всього заявок: <?php echo $requests_count; ?></h6>    
    <br/>
    <table class="table table-striped" width="100%" id="table_requests" style="font-size: 16px">
        <thead>
        <tr>
            <th>#</th>
            <th>Дата подачі</th>
            <th>ПІБ відвідувача</th>
            <th>№ заявки</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $key => $request): ?>
        <tr onclick="location.href = '<?php echo base_url('requests/show/' . $request->request_id); ?>'" style="cursor:pointer" title="Редагувати заявку">
            <td><?php echo $page_num * 10 + $key + 1; ?></td>
            <td><strong><?php echo $request->request_date; ?></strong></td>
            <td><strong><?php if ($request->visitorname !== '') echo $request->visitorname; elseif (($request->patent_agent !== null)) echo $request->patent_agent; else echo $request->service_employee ?></strong></td>
            <td><strong><?php echo $request->request_number; ?></strong></td>
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
    <?php else: ?>
    <div class="span"><h2>Заявки на перепустки відсутні!<h2></div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $("#table_requests tr").each(function(){

        if (this.innerHTML.indexOf('Очікується') > -1)
            this.style.color = "blue";
        if (this.innerHTML.indexOf('Видано') > -1)
            this.style.color = "red";

    });
</script>