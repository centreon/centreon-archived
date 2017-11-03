<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 31/10/17
 * Time: 11:53
 */

interface iFileManager
{
    /**
     * @return mixed
     */
    public function upload();

    /**
     * @param $id
     * @param $name
     * @return mixed
     */
    public function update($id, $name);

}