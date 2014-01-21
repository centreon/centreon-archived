<?php

namespace Centreon\Core;

class Controller
{
    protected $request;

    public function __construct($request)
    {
        $this->init();
        $this->request = $request;
    }

    /**
     * Get params
     *
     * @param string $type
     */
    protected function getParams($type = "")
    {
        if (strtolower($type) == "get") {
            return $this->request->paramsGet();
        }
        return $this->request->paramsPost();
    }

    /**
     *
     */
    protected function init()
    {

    }

    /**
     *
     * @return array
     */
    public static function getRoutes()
    {
        $tempo = array();
        $ref = new \ReflectionClass(get_called_class());
        foreach ($ref->getMethods() as $method) {
            $methodName = $method->getName();
            if (substr($methodName, -6) == 'Action') {
                foreach (explode("\n", $method->getDocComment()) as $line) {
                    $str = trim(str_replace("* ", '', $line));
                    if (substr($str, 0, 6) == '@route') {
                        $route = substr($str, 6);
                        $tempo[$methodName]['route'] = trim($route);
                    }
                    if (substr($str, 0, 7) == '@method') {
                        $method_type = strtoupper(substr($str, 7));
                        $tempo[$methodName]['method_type'] = trim($method_type);
                    }
                }
            }
        }
        return $tempo;
    }
}
