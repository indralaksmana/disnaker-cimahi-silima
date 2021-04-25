<?php


namespace App\TwigExtension;
use \DateTime;
use \Twig_SimpleFilter;
class AgeExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('age', array($this, 'ageCalculate')),
        );
    }

    public function ageCalculate(\DateTime $bithdayDate)
    {
        $now = new \DateTime();
        $interval = $now->diff($bithdayDate);

        return $interval->y;
    }

    public function getName()
    {
        return 'age_extension';
    }
}
