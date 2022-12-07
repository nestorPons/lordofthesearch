<?php namespace app\core;
class BaseClass
{
	protected const LOGON = FALSE;
    protected $return;
	protected string $FOLDER_ROOT;

	static protected function FOLDER_ROOT() : string
	{
		$arr = explode('/', $_SERVER['DOCUMENT_ROOT']);
        return str_replace(array_pop($arr), '', $_SERVER['DOCUMENT_ROOT']);
	}
	public function print(): self
	{
		$this->log('Imprimiendo resultados...');
		if (is_array($this->return)) {
			foreach ($this->return as $key => $value) {
				print('<pre>');
				print('KEY: ');
				print_r($key);
				print('</pre>');
				print('<pre>');
				print('VALUE: ');
				print_r($value);
				print('</pre>');
			}
		}else{
            print($this->return);
        }
		return $this;
	}
    public function get() : mixed
    {
        return $this->return;
    }
	protected function log(...$menssages): void
    {
        if (self::LOGON) {
            foreach ($menssages as $mens) {
                print('<pre>');
                print('log:');
				print_r($mens);
                print('</pre>');
            }
        }
    }
	public function save_log(string $file = 'src/tmp.log'): void {
		$result = file_put_contents($file, print_r($this->return, true));
		if ($result === false) 
			throw new \Exception("Error guardado el archivo!!");
		
	}
	public function return(){
		return $this->return; 
	}
}
