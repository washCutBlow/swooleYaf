<?php
class IndexController extends CommonController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->SyResult->setData([
            'msg' => 111,
        ]);
        $this->sendRsp();
//        $res = \SyModule\SyModuleUser::getInstance()->sendApiReq('/Index/Index/index', $_GET);
//        $this->sendRsp($res);
    }
}
