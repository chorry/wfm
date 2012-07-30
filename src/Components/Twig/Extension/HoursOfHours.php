<?php
/**
 * This file is part of Twig.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author chorry@rbc.ru
 * @based on work of Francisco Ancona Lopes <chico.lopes@gmail.com>
 * @from https://github.com/falmp/Twig-extensions
 */
class Twig_Extension_HoursOfHours extends Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array('hoursofhours' => new Twig_Filter_Function('twig_hoursofhours_filter'));
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'Hours';
    }
}

function twig_hoursofhours_filter($seconds, $secondsof, $show_seconds = false, $padHours = false)
{
  $hours   = intval(intval($seconds) / 3600);
  $hoursof = intval(intval($secondsof) / 3600);
  $hms = ($padHours)
         ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
         : $hours. ":";
  $minutes = intval(($seconds / 60) % 60);
  $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT);
  return $hms;
}