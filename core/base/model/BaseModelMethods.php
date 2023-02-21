<?php

namespace core\base\model;

abstract class BaseModelMethods {

    protected function createFields($set, $table = false) {

        $set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) ? $set['fields']  : ['*'];

        $table = $table ? $table . '.' : '';

        $fields = '';
        
        foreach($set['fields'] as $field) {
            $fields .= $table . $field . ',';
        }

        return $fields;

    }


    protected function createOrder($set, $table = false) {
        
        $table = $table ? $table . '.' : '';

        $orderBy = '';

        if(is_array($set['order']) && !empty($set['order'])) {

            $set['order_direction'] = (is_array($set['order_direction']) && !empty($set['order_direction'])) ? $set['order_direction']  : ['ASC'];

            $orderBy = 'ORDER BY ';
            $directCount = 0;

            foreach($set['order'] as $order) {

                if($set['order_direction'][$directCount]) {
                    $orderDirection = strtoupper($set['order_direction'][$directCount]);
                    $directCount++;
                }
                else {
                    $orderDirection = strtoupper($set['order_direction'][$directCount-1]);
                }

                if (is_int($order)) {
                    $orderBy .= $order . ' ' . $orderDirection . ',';
                }
                else {
                    $orderBy .= $table . $order . ' ' . $orderDirection . ',';
                }
            }

            $orderBy = rtrim($orderBy, ',');
        }

        return $orderBy;
    }


    protected function createWhere($set, $table = false, $instruction = 'WHERE') {

        $table = $table ? $table . '.' : '';

        $where = '';

        if(is_array($set['where']) && !empty($set['where'])) {

            $set['operand'] = (is_array($set['operand']) && !empty($set['operand'])) ? $set['operand']  : ['='];
            $set['condition'] = (is_array($set['condition']) && !empty($set['condition'])) ? $set['condition']  : ['AND'];

            $where = $instruction;

            $operandCount = 0;
            $conditionCount = 0;

            foreach($set['where'] as $key => $item) {

                $where .= ' ';

                if($set['operand'][$operandCount]) {
                    $operand = $set['operand'][$operandCount];
                    $operandCount++;
                }
                else {
                    $operand = $set['operand'][$operandCount - 1];
                }

                if($set['condition'][$conditionCount]) {
                    $condition = $set['condition'][$conditionCount];
                    $conditionCount++;
                }
                else {
                    $condition = $set['condition'][$conditionCount - 1];
                }

                if($operand === 'IN' || $operand === 'NOT IN') {
                    if(is_string($item) && strpos($item, 'SELECT') === 0) {
                        $inStr = $item;
                    }
                    else {
                        if(is_array($item)) {
                            $tempItem = $item;
                        }
                        else {
                            $tempItem = explode(',', $item);
                        }

                        $inStr = '';

                        foreach($tempItem as $v) {
                            $inStr .= "'" . addslashes(trim($v)) . "',";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' . trim($inStr, ',') . ') ' . $condition;

                }
                elseif(strpos($operand, 'LIKE') !== false){

                    $likeTemplate = explode('%', $operand);

                    foreach ($likeTemplate as $ltKey => $lt) {
                        if(!$lt) {
                            if(!$ltKey) {
                                $item = '%' . $item;
                            }
                            else {
                                $item .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . addslashes($item) . "' $condition"; 

                }
                else {
                    
                    if (strpos($item , 'SELECT') === 0) {
                        $where .= $table . $key . ' ' . $operand . ' ' . '(' . $item . ") $condition";
                    }
                    else {
                        $where .= $table . $key . ' '  .  $operand . ' '  . "'" . addslashes($item) . "' $condition";
                    }
                }
            }

            $where = substr($where, 0, strrpos($where, $condition));
        }
        return $where;
    }



    protected function createJoin($set, $table, $newWhere = false) {

        $fields = '';
        $join = '';
        $where = '';

        if($set['join']) {

            $joinTable = $table;

            foreach($set['join'] as $key => $item) {

                if(is_int($key)) {
                    if(!$item['table']) {
                        continue;
                    }
                    else {
                        $key = $item['table'];
                    }
                }

                if($join) {
                    $join .= ' ';
                }

                if($item['on']) {
                    $joinFields = [];

                    switch(2) {
                        case count($item['on']['fields']):
                            $joinFields = $item['on']['fields'];
                            break;
                        
                        case count($item['on']):
                            $joinFields = $item['on'];
                            break;
                        default:
                            continue 2;
                            break;
                    }

                    if(!$item['type']) {
                        $join .= 'LEFT JOIN ';
                    }
                    else {
                        $join .= trim(strtoupper($item['type'])) . ' JOIN ';
                    } 

                    $join .= $key . ' ON ';

                    if($item['on']['table']) {
                        $join .= $item['on']['table'];
                    }
                    else {
                        $join .= $joinTable;
                    }

                    $join .= '.' . $joinFields[0] . '=' . $key . '.' . $joinFields[1];

                    $joinTable = $key;

                    if ($newWhere){
                        if($item['where']) {
                            $newWhere = false;
                        }

                        $groupCondition = 'WHERE';
                    }
                    else {
                        $groupCondition = $item['group_condition'] ? strtoupper($item['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($item, $key);
                    $where .= $this->createWhere($item, $key, $groupCondition);
                }
            }

            return compact('fields', 'join', 'where');
        }

    }


    protected function createInsert($fields, $files, $except) {

        if(!$fields) {
            $fields = $_POST;
        }

        $insertArr = [];

        if($fields) {

            $sqlFunc = ['NOW()'];

            foreach($fields as $row => $value) {

                if($except && in_array($row, $except)) {
                    continue;
                }

                $insertArr['fields'] .= $row . ',';

                if(in_array($value, $sqlFunc)) {
                    $insertArr['values'] .= $value . ','; 
                }
                else {
                    $insertArr['values'] .="'" . addslashes($value) . "'" . ','; 
                }
            }
        }

        if($files) {

            foreach($files as $row => $file) {

                $insertArr['fields'] .= $row . ',';
                
                if(is_array($file)) {
                    $insertArr['values'] .= "'" . addslashes(json_encode($file)) . "'" . ',';
                }
                else {
                    $insertArr['values'] .= "'" . addslashes($file) . "'" . ',';
                }
            }
        }

        if($insertArr) {
            foreach($insertArr as $key => $str) {
                $insertArr[$key] = rtrim($str, ',');
            }    
        }

        return $insertArr;

    }
}