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

    <h6>Всього заявок: <?php echo $long_requests_count; ?></h6>
    <?php if (!empty($long_requests)): ?>
    <br />
        <table class="table table-striped" width="100%" id="table_requests" style="font-size: 16px; font-weight: bold">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ПІБ відвідувача</th>
                    <th>ПІБ начальника</th>
                    <th>Дата з</th>
                    <th>Дата по</th>
                    <?php if ($role_id === 1): ?>
                    <th>Дії</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($long_requests as $key => $long_request): ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $long_request->visitorname; ?></td>
                        <td><?php echo $long_request->username; ?></td>
                        <td><?php echo $long_request->date_from; ?></td>
                        <td> <?php echo $long_request->date_to; ?></td>   
                        <?php if ($role_id === 1): ?>
                            <td><a title="Видалити" href="<?php echo base_url('admin/long_requests/delete/' . $long_request->long_request_id); ?>" onclick="return confirm('Ви дійсно бажаєте видалити цю заявку?');"><img src="<?php echo base_url('images/delete.PNG'); ?>" /></a></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="span"><h2>Інформація відсутня!<h2></div>
    <?php endif; ?>
</div>