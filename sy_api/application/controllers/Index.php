<?php
class IndexController extends \Yaf\Controller_Abstract
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

        $result = new \Response\Result();
        $result->setData($res);
        $this->getResponse()->setBody($result->getJson());
    }
}
