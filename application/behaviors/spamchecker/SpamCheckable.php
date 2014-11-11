<?php

namespace app\behaviors\spamchecker;

interface SpamCheckable
{
    public function check();
    public function getType();
}
