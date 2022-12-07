<?php 
namespace app\core;
class Confiles
{
    /**
     * Get data from .env files
     */
    static function get_env(string $root_path) : array 
    {
        $result = [];
        $file = fopen($root_path . '.env','r');
        while ($row = fgets($file)) {
            $components = explode('=', $row); 
            if (isset($components[1]))
            {
                $result[$components[0]] = trim($components[1]);
            }
        }
        fclose($file);
        return $result;
    }
}