<?php
class StringVo {
  private $value;
  function __construct(string $value) {
    $this->value = $value;
  }
  function __get($name){
    if($name == 'value') {
      return $this->value;
    }
  }
}

class IntVo {
  private $value;
  function __construct(int $value) {
    $this->value = $value;
  }
  function __get($name){
    if($name == 'value') {
      return $this->value;
    }
  }
}