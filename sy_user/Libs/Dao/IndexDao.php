<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/7 0007
 * Time: 13:43
 */
namespace Dao;

use SyTrait\SimpleDaoTrait;

class IndexDao
{
    use SimpleDaoTrait;

    public static function index(array $data)
    {
        return [
            'msg' => 'hello, ' . $data['name'],
        ];
    }

    public static function index2(array $data)
    {
        return [
            'msg' => 'hello, ' . $data['name'] . ',age is ' . $data['age'],
        ];
    }
}
