<?php
class IndexController extends CommonController
{
    public function init()
    {
    }

    public function indexAction()
    {
        $needParams = [
            'name' => (string)\Request\SyRequest::getParams('name', 'jw'),
        ];
        $res = \Dao\IndexDao::index($needParams);
        $this->SyResult->setData($res);
        $this->sendRsp();
    }
}
