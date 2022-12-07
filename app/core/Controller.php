<?php

namespace app\core;

/**
 * Clase controladora que devuelve una vista o devuelve datos
 */
class Controller extends BaseClass
{
    private string $main_view;
    private string $folder_view;
    private string $folder_model;
    private string $ext_view;
    private array $conf;
    // Se declara la pagina de inicio de la aplicación

    function __construct(string $folder_view, string $folder_model, string $main_view, string $ext_view, array $conf)
    {
        // Configuración variables de la aplicación
        $this->main_view = $main_view;
        $this->folder_view = $folder_view;
        $this->folder_model = $folder_model;
        $this->ext_view = $ext_view;
        $this->conf = $conf;
    }

    /**
     * Decide cual es la vista que hay que cargar
     */
    public function route($request = null): void
    {
        if (!$request) {
            // Inicio de loginç
            $this->conf['error_login'] = '';
            $this->load_view($this->main_view, $this->conf);
        } else {
            if ($_POST) {
                if (in_array("action", array_keys($request))) {
                    // Se solicita una petición de login
                    $user = new User($request['user']);
                    if (empty($user->pass())){
                        $user->pass($request['pass']);
                        $this->load_view('savedpass');
                    } else {
                        // Login y password
                        if ($user->pass() === $request['pass']) {
                            // ZONA AUTORIZADA
                            $this->log('Autorizado!');
                            $user->load_record($request['action']);
                            $user->save_record();
                            $user->send_email();
                            $action_name  = $request['action'] == 'singin' ? 'Entrada' : 'Salida';
                            $this->load_view('main', ['ACTION' => $action_name]);
                        } else {
                            // NO AUTORIZADO
                            $_REQUEST = [];
                            $this->conf['error_login'] = 'inherit';
                            $this->load_view($this->main_view, $this->conf);
                        }
                    }
                }
            } else {
                var_dump('Solicita vista o datos');
            }
        }
    }

    /**
     * Carga la vista
     */
    public function load_view(string $view, $data = null): void
    {
        if ($data) {
            foreach ($data as $key => $value) {
                define($key, $value);
            }
        }
        $path = $this->folder_view . $view . '.' . $this->ext_view;
        include($path);
    }

    /**
     * Sale a la pagina inicial y destruimos la session actual
     */
    public function exit(): void
    {
        $this->auth = false;
        session_destroy();
    }
    /** 
     * GETTERS AND SETTERS
     */
    public function main_view(string $value = null)
    {
        $name_fun = explode('::', __METHOD__)[1];
        if ($value) $this->{$name_fun} = $value;
        return $this->{$name_fun};
    }
    public function ext_view(string $value = null)
    {
        $name_fun = explode('::', __METHOD__)[1];
        if ($value) $this->{$name_fun} = $value;
        return $this->{$name_fun};
    }
    public function folder_view(string $value = null)
    {
        $name_fun = explode('::', __METHOD__)[1];
        if ($value) $this->{$name_fun} = $value;
        return $this->{$name_fun};
    }
    function getAuth()
    {
        return $this->auth;
    }
}
