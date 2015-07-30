<?php

namespace app\behaviors\spamchecker;

// @todo Implement markAsSpam method

interface SpamCheckable
{
    public function check();
    public function getType();
}