<?php

class Collection extends \Macroable implements \Iterator {

    public $var = array();

    public function __construct($array) {
        $this->var = $array;
    }

    static function makeInstance($array) {
        return new static($array);
    }

    /**
     * Duyệt qua thành phần mảng và chỉnh sửa các item trong mảng bằng callback
     * @param callable $cb
     * @return $this
     */
    function map($cb) {
        foreach ($this->var as $k => $item) {
            $this->var[$k] = call_user_func($cb, $item);
        }

        return $this;
    }

    /**
     * Loại bỏ phần tử khỏi mảng bằng hàm callback
     * @param callable $cb
     * @return static
     */
    function reject($cb) {
        $newArr = [];
        foreach ($this->var as $k => $item) {
            $reject = call_user_func($cb, $item);
            if (!$reject) {
                $newArr[$k] = $item;
            }
        }

        return static::makeInstance($newArr);
    }

    public function rewind() {
        reset($this->var);
    }

    public function current() {
        $var = current($this->var);
        return $var;
    }

    public function key() {
        $var = key($this->var);
        return $var;
    }

    public function next() {
        $var = next($this->var);
        return $var;
    }

    function end() {
        return end($this->var);
    }

    function count() {
        return count($this->var);
    }

    public function valid() {
        $key = key($this->var);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    function toArray() {
        return $this->var;
    }

    function toJson() {
        return json_encode($this->var);
    }
}
