<?php
class IndexController extends CommonController
{
    public function init()
    {
        parent::init();
    }

    /**
     * @SyAspectBefore-\SyAspect\Test
     */
    public function indexAction()
    {
        $res = \SyModule\SyModuleUser::getInstance()->sendApiReq('/Index/Index/index', $_GET);
        $this->sendRsp($res);
    }

    /**
     * @SyAspect-\SyAspect\Test
     * @SyFilter-{"field": "_ignoresign","explain": "签名标识","type": "string","rules": {"min": 0}}
     */
    public function index2Action()
    {
        $res = \SyModule\SyModuleUser::getInstance()->sendApiReq('/Index/Index/index2', $_GET);
        $this->sendRsp($res);
    }
}
