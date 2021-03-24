<?php


namespace Meleshun;


use InvalidArgumentException;
use SplObserver;
use SplSubject;


class TeamLeader implements SplSubject
{
    /**
     * Текущее настроение.
     * @var int
     */
    protected int $currentMood;

    /**
     * Существующие настроения.
     * @var array
     */
    protected array $moods;

    /**
     * Количество выговоров.
     * @var int
     */
    protected int $counterRebuke = 0;

    /**
     * Количество похвал.
     * @var int
     */
    protected int $counterPraise = 0;

    /**
     * Наблюдатели.
     * @var array
     */
    private array $observers;

    /**
     * @param array $moods
     */
    public function __construct(array $moods)
    {
        $this->observers["*"] = [];

        $this->updateMoods($moods);

        $this->setRandMood();
    }

    /**
     * Проверить выполнение работы.
     * В зависимости от начального настроения тимлид может поругать или похвалить программиста за выполнение работы и
     * уведомить наблюдателей.
     * @param bool $workStatus
     */
    public function checkTaskCompletion(bool $workStatus): void
    {
        if (!$this->changeCurrentMood($workStatus)) {
            if ($workStatus) {
                $this->counterPraise++;
                $event = 'developer:praise';
                $message = 'TM похвалил(а) программиста ' . $this->getCounterPraise() . ' раз(а)';
            } else {
                $this->counterRebuke++;
                $event = 'developer:rebuke';
                $message = 'TM поругал(а) программиста ' . $this->getCounterRebuke() . ' раз(а)';
            }

            $this->notify($event, $message);
        }
    }

    /**
     * Изменить настроение тимлида, в зависимости от выполнения работы.
     * @param bool $workStatus
     * @return bool
     */
    public function changeCurrentMood(bool $workStatus): bool
    {
        $nextMood = $this->currentMood + ($workStatus ? -1 : 1);
        if (isset($this->moods[$nextMood])) {
            echo 'Настроение TM изменилось с "' . $this->getCurrentMood() . '" на "' . $this->moods[$nextMood] . '"' . PHP_EOL;
            $this->currentMood = $nextMood;
            return true;
        }
        echo 'Настроение TM уже и так "' . $this->getCurrentMood() . '"' . PHP_EOL;
        return false;
    }

    /**
     * Получить количество выговоров от тимлида.
     * @return int
     */
    public function getCounterRebuke(): int
    {
        return $this->counterRebuke;
    }

    /**
     * Получить количество похвалы то тимлида.
     * @return int
     */
    public function getCounterPraise(): int
    {
        return $this->counterPraise;
    }

    /**
     * Добавить новое настроение.
     * Настроение добавляется в конец массива, если не указано предшествующее настроение.
     * В случае попытки добавления после несуществующего настроения выбрасывается исключение.
     * @param string $mood
     * @param string|null $after
     * @throws InvalidArgumentException
     */
    public function addMood(string $mood, string $after = null): void
    {
        if (!is_null($after)) {
            array_splice($this->moods, ($this->getMoodPosition($after) + 1), 0, $mood);
        } else {
            $this->moods[] = $mood;
        }
    }

    /**
     * Задать текущее настроение.
     * При попытке изменить текущее настроение на несуществующие выбрасывается исключение.
     * @param string $mood
     * @throws InvalidArgumentException
     */
    public function setCurrentMood(string $mood): void
    {
        $this->currentMood = $this->getMoodPosition($mood);
    }

    /**
     * Получить текущее настроение.
     * Если текущее настроение не задано выбрасывается исключение.
     * @return string
     * @throws InvalidArgumentException
     */
    public function getCurrentMood(): string
    {
        if (isset($this->moods[$this->currentMood])) {
            return $this->moods[$this->currentMood];
        } else {
            throw new InvalidArgumentException('Perhaps the Team Leader has apathy?');
        }
    }

    /**
     * Обновить существующее настроение.
     * Если настроения не существует выбрасывается исключение.
     * @param string $needle
     * @param string $mood
     * @throws InvalidArgumentException
     */
    public function updateMood(string $needle, string $mood): void
    {
        $this->moods[$this->getMoodPosition($needle)] = $mood;
    }

    /**
     * Удалить существующее настроение.
     * Если настроения не существует выбрасывается исключение.
     * @param string $mood
     * @throws InvalidArgumentException
     */
    public function removeMood(string $mood): void
    {
        unset($this->moods[$this->getMoodPosition($mood)]);
    }

    /**
     * Получить все настроения.
     * @return array
     */
    public function getMoods(): array
    {
        return $this->moods;
    }

    /**
     * Обновить все настроения.
     * Если значения массива не являются строками выбрасывается исключение.
     * @param array $moods
     * @throws InvalidArgumentException
     */
    public function updateMoods(array $moods): void
    {
        foreach ($moods as $mood) {
            if (!is_string($mood)) {
                throw new InvalidArgumentException('Mood should be of text type.');
            }
        }
        $this->moods = array_values($moods);
    }


    /**
     *  Задать текущим случайное настроение.
     */
    public function setRandMood(): void
    {
        $this->currentMood = mt_rand(0, ($this->getCountMoods() - 1));
    }

    /**
     * Инициировать событие, если оно не существует
     * @param string $event
     */
    private function initEventGroup(string $event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    /**
     * Получить список наблюдателей за событием
     * @param string $event
     * @return array
     */
    private function getEventObservers(string $event = "*"): array
    {
        $this->initEventGroup($event);
        $group = $this->observers[$event];
        $all = $this->observers["*"];

        return array_merge($group, $all);
    }

    /**
     * Реализация интерфейса SplSubject
     * @param SplObserver $observer
     * @param string $event
     */
    public function attach(SplObserver $observer, string $event = "*"): void
    {
        $this->initEventGroup($event);

        $this->observers[$event][] = $observer;
    }

    /**
     * Реализация интерфейса SplSubject
     * @param SplObserver $observer
     * @param string $event
     */
    public function detach(SplObserver $observer, string $event = "*"): void
    {
        foreach ($this->getEventObservers($event) as $key => $s) {
            if ($s === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    /**
     * Реализация интерфейса SplSubject
     * @param string $event
     * @param string $message
     */
    public function notify(string $event = "*", string $message = ''): void
    {
        foreach ($this->getEventObservers($event) as $observer) {
            $observer->update($this, $message);
        }
    }

    /**
     * Получить количество настроений в массиве.
     * @return int
     */
    protected function getCountMoods(): int
    {
        return count($this->moods);
    }

    /**
     * Получить позицию настроения в массиве.
     * Если настроения не существует выбрасывается исключение.
     * @param string $mood
     * @return int
     * @throws InvalidArgumentException
     */
    protected function getMoodPosition(string $mood): int
    {
        if (($key = array_search($mood, $this->moods)) !== false) {
            return $key;
        } else {
            throw new InvalidArgumentException('I can not do that! First, create the mood ' . $mood);
        }
    }
}