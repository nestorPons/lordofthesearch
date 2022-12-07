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

    function __construct(string $folder_view, string $folder_model, string $main_view, string $ext_view)
    {
        // Configuración variables de la aplicación
        $this->main_view = $main_view;
        $this->folder_view = $folder_view;
        $this->folder_model = $folder_model;
        $this->ext_view = $ext_view;
    }

    /**
     * Decide cual es la vista que hay que cargar
     */
    public function route(mixed $request = null): self
    { 
        return $this;
    }

    /**
     * Carga la vista
     */
    public function load_view(string $view, $data = null): self
    {
        if ($data) {
            foreach ($data as $key => $value) {
                define($key, $value);
            }
        }
        $path = $this->folder_view . $view . '.' . $this->ext_view;
        include($path);
        return $this;
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
}
