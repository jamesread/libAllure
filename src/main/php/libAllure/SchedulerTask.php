<?php

namespace libAllure;

abstract class SchedulerTask
{
    abstract public function execute();
    public $lastExecuted;

    public function getName()
    {
        return get_class($this);
    }
}
