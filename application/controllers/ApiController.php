<?php

/**
 * ApiController
 *
 * @version 1.0
 * @copyright Copyright (C) 2011 Diogo Oliveira de Melo. All rights reserved.
 * @author Diogo Oliveira de Melo <dmelo87@gmail.com>
 * @license GPL
 *
 */
class ApiController extends DZend_Controller_Action
{
    protected $_error = array(
            'error' => 'Parameter "q" must be specified');
    /**
     * searchAction API search call.
     *
     * @return void
     *
     */
    public function searchAction()
    {
        $q = $this->_request->getParam('q');
        $list = array();
        if (null !== $q) {
            $limit = $this->_request->getParam('limit') ? $this->_request->getParam('limit') : 9;
            $offset = $this->_request->getParam('offset') ? $this->_request->getParam('offset') : 1;
            $cache = Zend_Registry::get('cache');
            $key = sha1($q . $limit . $offset);
            if (($list = $cache->load($key)) === false) {
                $youtube = new Youtube();
                $resultSet = $youtube->search($q, $limit, $offset);
                $item = array();
                foreach ($resultSet as $result)
                    $list[] = $result->getArray();
                $cache->save($list, $key);
            }

            $this->view->output = $list;
        }
        else
            $this->view->output = $this->_error;
    }

    public function autocompleteAction()
    {
        $q = $this->getRequest()->getParam('q');
        $list = array();
        if (null !== $q) {
            $lastfm = new Lastfm();
            $resultSet = $lastfm->search($q);
            foreach ($resultSet as $result)
                $list[] = $result->getArray();
            $this->view->output = $list;
        }
        else
            $this->view->output = $this->_error;
    }

    /**
     * postDispatch Facilitates output using Json
     *
     * @return void
     *
     */
    public function postDispatch()
    {
        if (isset( $this->view->output ))
            echo Zend_Json::encode($this->view->output);
    }
}
