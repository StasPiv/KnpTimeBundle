<?php

namespace Knp\Bundle\TimeBundle;

use Symfony\Component\Translation\TranslatorInterface;
use DateTime;

class DateTimeFormatter
{
    protected $translator;

    /**
     * Constructor
     *
     * @param  TranslatorInterface $translator Translator used for messages
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a formatted diff for the given from and to datetimes
     *
     * @param  DateTime $from
     * @param  DateTime $to
     *
     * @return string
     */
    public function formatDiff(DateTime $from, DateTime $to)
    {
        static $units = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        );

        $diff = $to->diff($from);

        $chunk = [];
        $alreadyInvert = false;

        foreach ($units as $attribute => $unit) {
            $count = $diff->$attribute;
            if (0 !== $count) {
                $chunk[] = $this->doGetDiffMessage($count, $diff->invert, $unit, $alreadyInvert);
                $alreadyInvert = true;
            }
        }
        return implode(' ', $chunk);
    }

    /**
     * Returns the diff message for the specified count and unit
     *
     * @param  integer $count  The diff count
     * @param  boolean $invert Whether to invert the count
     * @param  integer $unit   The unit must be either year, month, day, hour,
     *                         minute or second
     *
     * @return string
     */
    public function getDiffMessage($count, $invert, $unit)
    {
        if (0 === $count) {
            throw new \InvalidArgumentException('The count must not be null.');
        }

        $unit = strtolower($unit);

        if (!in_array($unit, array('year', 'month', 'day', 'hour', 'minute', 'second'))) {
            throw new \InvalidArgumentException(sprintf('The unit \'%s\' is not supported.', $unit));
        }

        return $this->doGetDiffMessage($count, $invert, $unit);
    }

    protected function doGetDiffMessage($count, $invert, $unit, $withoutInvert = false)
    {
        $id = $withoutInvert ? sprintf('diff.%s.%s', 'and', $unit) : sprintf('diff.%s.%s', $invert ? 'ago' : 'in', $unit);

        return $this->translator->transChoice($id, $count, array('%count%' => $count), 'time');
    }

    /**
     * Returns the message for an empty diff
     *
     * @return string
     */
    public function getEmptyDiffMessage()
    {
        return $this->translator->trans('diff.empty', array(), 'time');
    }
}