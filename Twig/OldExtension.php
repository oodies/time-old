<?php
/**
 * @copyright  Copyright (c) 2017  The OODIE Team
 * @author Sébastien CHOMY <sebastien.chomy@gmail.com>
 * @filesource OldExtension.php
 * @since 2017/8
 */

namespace Ood\AppBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface as Translator;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Class OldExtension
 * @package Ood\AppBundle\Twig\Extension
 */
class OldExtension extends Twig_Extension
{

    /**
     * @var array
     */
    public static $units = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    /**
     * @var Translator
     */
    private $translator;

    /**
     * OldExtension constructor.
     *
     * @param Translator|null $translator
     */
    public function __construct(Translator $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('old', [$this, 'diff'], ['needs_environment' => true]),
        ];
    }

    /**
     * Filter to converting date to old birthday
     *
     * @param \Twig_Environment $env a Twig_Environment instance
     * @param string|\DateTime $date a string or DateTime object to convert
     * @param string|\DateTime $now  A string or DateTime object to compare with. If none given,
     *                              the current time will be used.
     *
     * @return string the converted time
     */
    public function diff(\Twig_Environment $env, $date, $now = null)
    {
        // Convert both dates to DateTime instances.
        $date = twig_date_converter($env, $date);
        $now = twig_date_converter($env, $now);

        // Get the difference between the two DateTime objects.
        $diff = $now->diff($date);

        // Check for each interval if it appears in the $diff object.
        foreach (self::$units as $attribute => $unit) {
            $count = $diff->$attribute;

            if (0 !== $count) {
                return $this->getPluralizedInterval($count, $diff->invert, $unit);
            }
        }

        return '';
    }

    /**
     * @param integer $count
     * @param boolean $invert
     * @param string $unit
     *
     * @return string
     */
    protected function getPluralizedInterval($count, $invert, $unit)
    {
        if ($this->translator) {
            if (0 == $invert) {
                return $this->translator->trans('diff.old.empty', [], 'date');
            }

            $id = sprintf('diff.old.%s', $unit);

            return $this->translator->transChoice($id, $count, ['%count%' => $count], 'date');
        }

        if ($invert == 0) {
            return 'not old';
        }

        if (1 !== $count) {
            $unit .= 's';
        }

        return "$count $unit";
    }
}
