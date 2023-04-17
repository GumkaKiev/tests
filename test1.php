<?php

class Transport
{

    protected int $speed = 0;

    public function __toString()
    {
        return static::class . ': ' . $this->getSpeed();
    }

    public function setSpeed(int $speed)
    {
        $this->speed = $speed;
    }

    public function getSpeed()
    {
        return $this->speed;
    }


}

class Bus extends Transport
{
}

class Bike extends Transport
{
}

class Car extends Transport
{
}

$bus = new Bus();
$bus->setSpeed(50);
echo $bus . "<br/>\n";

$car = new Car();
$car->setSpeed(100);
echo $car . "<br/>\n";

$bike = new Bike();
$bike->setSpeed(85);
echo $bike . "<br/>\n";


