<?php


namespace Meleshun;


class JuniorDeveloper
{
    /**
     * Абстракция от реализации JuniorDeveloper
     * @return bool
     */
    public static function getWorkStatus(): bool
    {
        return (bool)mt_rand(0, 1);
    }
}