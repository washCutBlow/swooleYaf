<?php
class IndexController extends CommonController
{
    public function init()
    {
        parent::init();
    }

    /**
     * @SyFilter-{"field": "name","explain": "名称","type": "string","rules": {"min": 1}}
     */
    public function indexAction()
    {
        $needParams = [
            'name' => (string)\Request\SyRequest::getParams('name', 'jw'),
        ];
        $res = \Dao\IndexDao::index($needParams);
        $this->SyResult->setData($res);
        $this->sendRsp();
    }

    /**
     * @SyFilter-{"field": "name","explain": "名称","type": "string","rules": {"required": 1,"min": 1}}
     * @SyFilter-{"field": "age","explain": "年龄","type": "int","rules": {"required": 1,"min": 1}}
     */
    public function index2Action()
    {
        $needParams = [
            'name' => (string)\Request\SyRequest::getParams('name'),
            'age' => (int)\Request\SyRequest::getParams('age'),
        ];
        $res = \Dao\IndexDao::index2($needParams);
        $this->SyResult->setData($res);
        $this->sendRsp();
    }
}
