<?php


namespace Meleshun;


use SplObserver;
use SplSubject;


class Manager implements SplObserver
{
    /**
     * @param SplSubject $subject
     * @param null $message
     */
    public function update(SplSubject $subject, $message = null): void
    {
        if ($subject instanceof TeamLeader) {
            echo 'Manager узнал(а), что ' . $message . PHP_EOL;
        }
    }
}