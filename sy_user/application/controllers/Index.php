<?php
class IndexController extends CommonController
{
    public function init()
    {
        parent::init();
    }

    /**
     * 测试1
     * @api {get} /Index/Index/index 测试1
     * @apiDescription 测试1
     * @apiGroup Index
     * @apiParam {string} name 名称
     * @SyFilter-{"field": "name","explain": "名称","type": "string","rules": {"min": 1}}
     * @apiUse CommonSuccess
     * @apiUse CommonFail
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
     * 测试2
     * @api {get} /Index/Index/index2 测试2
     * @apiDescription 测试2
     * @apiGroup Index
     * @apiParam {string} name 名称
     * @apiParam {number} age 年龄
     * @SyFilter-{"field": "name","explain": "名称","type": "string","rules": {"required": 1,"min": 1}}
     * @SyFilter-{"field": "age","explain": "年龄","type": "int","rules": {"required": 1,"min": 1}}
     * @apiUse CommonSuccess
     * @apiUse CommonFail
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
