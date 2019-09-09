<?php
class IndexController extends CommonController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $res = \SyModule\SyModuleUser::getInstance()->sendApiReq('/Index/Index/index', $_GET);
        $this->sendRsp($res);
    }
}
