<?php

class DataException extends Exception {
  function errorMessage(){
    debug(sprintf('Error on line %d of %s: %s', $this->getLine(), $this->getFile(), $this->getMessage()));
  }
}