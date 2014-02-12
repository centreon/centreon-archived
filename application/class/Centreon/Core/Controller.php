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
        switch(strtolower($type)) {
            default:
                return $this->request->params();
                
            case 'get':
                return $this->request->paramsGet();
            
            case 'post':
                return $this->request->paramsPost();
                
            case 'named':
                return $this->request->paramsNamed();
        }
    }

    /**
     *
     */
    protected function init()
    {

    }

    /**
     * Get routes
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
                    } elseif (substr($str, 0, 7) == '@method') {
                        $method_type = strtoupper(substr($str, 7));
                        $tempo[$methodName]['method_type'] = trim($method_type);
                    } elseif (substr($str, 0, 4, '@acl')) {
                        $aclFlags = explode(",", trim(substr($str,4)));
                        $tempo[$methodName]['acl'] = Acl::convertAclFlags($aclFlags);
                    }
                }
            }
        }
        return $tempo;
    }
}
