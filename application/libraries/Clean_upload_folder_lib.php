<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Clean_upload_folder_lib
 *
 * Очитска папки uploads
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Clean_upload_folder_lib
{
    private $folder = './uploads/';
    
    /**
     * Конструктор класса 
     */
    public function __construct() 
    {
        $path = $this->folder;
        $files = scandir($path); 
        foreach ($files as $file)
        {
            if ($file == '.' || $file == '..') 
                continue;
            if (is_dir($path.$file)) 
                continue;
            $data = stat($path.$file);
            if (mktime() - $data['mtime']  > 3600) 
                unlink($path.$file);
        }
    }
}