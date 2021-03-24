<?php


namespace Meleshun;


use SplObserver;
use SplSubject;


class HumanResourcesManager implements SplObserver
{
    /**
     * @param SplSubject $subject
     * @param null $message
     */
    public function update(SplSubject $subject, $message = null): void
    {
        if ($subject instanceof TeamLeader) {
            echo 'HR узнал(а), что ' . $message . PHP_EOL;
        }
    }
}