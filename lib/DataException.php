<?php

class DataException {
  function error_message(){
    debug(sprintf('Error on line %d of %s: %s', $this->getLine(), $this->getFile(), $this->getMessage()));
  }
}