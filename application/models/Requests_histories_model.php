<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// !!! Нарушение соглашений CI, но работает и ничего страшного
require_once APPPATH . 'models/Contracts_histories_model.php';

/**
 * Requests_histories_model
 *
 * Модель для работы с таблицей Contracts_histories 
 * (там хранятся логи действий по контрактам)
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Requests_histories_model extends Contracts_histories_model
{
    /**
     * Таблица БД со списком начальников
     * @var string
     */
    protected $table = 'requests_histories';

    /**
     * Первичный ключ таблицы со списком начальников
     * @var string
     */
    protected $table_id = 'request_history_id';
}