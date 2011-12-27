<?php

class Diogo_Model_DbTable extends Zend_Db_Table_Abstract
{
    protected $_db;
    protected $_session;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_db = $this->getAdapter();
        $this->_session = new Zend_Session_Namespace('session');
    }

    protected function _transform($items)
    {
        $ret = array();
        foreach($items as $item) {
            $n = strtolower($item[0]);

            for($i = 1; $i < strlen($item); $i++) {
                if(preg_match('/[A-Z]/', $item[$i]))
                    $n .= '_' . strtolower($item[$i]);
                else
                    $n .= $item[$i];
            }

            $ret[] = $n;
        }

        return $ret;
    }

    protected function _funcToQuery($funcName, $args)
    {
        $funcName = str_replace('findBy', '', $funcName);
        $funcName = str_replace('findRowBy', '', $funcName);
        $items = explode('And', $funcName);
        $items = $this->_transform($items);
        $where = '';
        foreach($items as $key => $item) {
            if($key)
                $where .= ' AND ';
            $where .= $this->_db->quoteInto($item . ' = ?', $args[$key]);
        }

        return $where;
    }

    public function __call($funcName, $args)
    {
        if(preg_match('/^findBy.*/', $funcName)) {
            $where = $this->_funcToQuery($funcName, $args);
            return $this->fetchAll($where);
        } elseif(preg_match('/^findRowBy.*/', $funcName)) {
            $where = $this->_funcNameToQuery($funcName);
            return $this->fetchRow($where);
        }
    }
}