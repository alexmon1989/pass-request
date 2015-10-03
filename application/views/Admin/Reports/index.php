<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
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
    
            <?php echo form_open('', 'id="form_report" class="form-horizontal"') ?>
            <fieldset>
                <div class="control-group">
                    <label class="control-label" style=" text-align: left">Дата</label>
                    з <input type="text" id="datepicker_from" name="datepicker_from" class="input-xlarge">
                    по <input type="text" id="datepicker_to" name="datepicker_to" class="input-xlarge">
                </div>
                <div class="control-group">
                    <label for="applicant_name" class="control-label" style=" text-align: left">ПІБ заявника</label>
                    <select class="input-xlarge" id="applicant_name" name="applicant_name">
                        <option value="0"></option>
                        <?php foreach ($applicants as $applicant): ?>
                            <option value="<?php echo $applicant->user_id; ?>"><?php echo $applicant->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group">
                    <label for="room_number" class="control-label" style=" text-align: left">№ кабінета</label>
                    <select class="input-xlarge" id="room_number" name="room_number">
                        <option value="0"></option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room->room_id; ?>"><?php echo $room->number; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group">
                    <label for="visitor_name" class="control-label" style=" text-align: left">ПІБ відвідувача</label>
                    <select class="input-xlarge" id="visitor_name" name="visitor_name">
                        <option value="0"></option>
                        <?php foreach ($visitors as $visitor): ?>
                            <option value="<?php echo $visitor->visitor_id; ?>"><?php echo $visitor->visitorname; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group">
                    <label for="patent_agent_name" class="control-label" style=" text-align: left">ПІБ патентного повіренного</label>
                    <select class="input-xlarge" id="patent_agent_name" name="patent_agent_name">
                        <option value="0"></option>
                        <?php foreach ($patent_agents as $patent_agent): ?>
                        <option value="<?php echo $patent_agent->patent_agent_id; ?>"><?php echo $patent_agent->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group">
                    <label for="courier_name" class="control-label" style=" text-align: left">ПІБ кур'єра</label>
                    <select class="input-xlarge" id="courier_name" name="courier_name">
                        <option value="0"></option>
                        <?php foreach ($couriers as $courier): ?>
                        <option value="<?php echo $courier->courier_id; ?>"><?php echo $courier->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group">
                    <label for="service_employee_name" class="control-label" style=" text-align: left">ПІБ співробітника сторонньої організації</label>
                    <select class="input-xlarge" id="service_employee_name" name="service_employee_name">
                        <option value="0"></option>
                        <?php foreach ($services_employees as $service_employee): ?>
                        <option value="<?php echo $service_employee->service_employee_id; ?>"><?php echo $service_employee->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group">
                    <label for="pass_number" class="control-label" style=" text-align: left">№ пропуска</label>
                    <select class="input-xlarge" id="pass_number" name="pass_number">
                        <option value="0"></option>
                        <?php foreach ($passes as $pass): ?>
                            <option value="<?php echo $pass->pass_id; ?>"><?php echo $pass->number; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="info_pass"></div>
                <div class="form-actions" style="padding-left: 60px;">
                    <button onclick="GenerateReport();" class="btn btn-primary" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Генерувати звіт</button>
                    <button onclick="GenerateReportExcel();" class="btn btn-primary" type="button" value="ok"><i class="icon-plus-sign icon-white"></i> Експорт в Excel</button>                    
                </div>
            </fieldset>
    
    <div id="print" class="span12" style="display: none; margin-left: 0px">
        <button onclick="PrintTable()" style="float: right" class="btn btn-success" type="button"><i class="icon-print icon-white"></i> Друкувати звіт</button>
    </div>
    <div id="report" class="span12" style="overflow: auto; margin-left: 0px"></div>
</div>

    <script type="text/javascript">
        $(function(){
            $.datepicker.setDefaults(
                $.extend($.datepicker.regional["uk"])
            );
            $("#datepicker_from").datepicker();
            $("#datepicker_to").datepicker();
            });
    </script>

</div>