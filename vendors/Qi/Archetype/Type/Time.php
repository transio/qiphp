<?php
namespace Qi\Archetype\Type;

/**
 * CLASS DESCRIPTION
 * @author smoseley
 */
class Time extends \DateTime
{
    /**
     * Override DateTime::__construct to always create an object with today's date and the time given
     * Whether or not the time_string includes any date information
     * @param null $timestring
     */
    public function __construct($timestring = null) {
        parent::__construct();
        $this->_setTime($timestring);
    }

    public function __toString() {
        return $this->mysqlFormat();
    }

    public function mysqlFormat() {
        return $this->format('H:i:s');
    }

    /**
     * Override DateTime::setTimestamp to always create an object with today's date, no matter what date
     * the timestamp corresponds to
     * @param int $timestamp
     * @return \DateTime|void
     */
    public function setTimestamp($timestamp) {
        if (!$timestamp || !is_numeric($timestamp)) {
            $timestring = '00:00:00';
        } else {
            $timestring = date('H:i:s', $timestamp);
        }
        $this->_setTime($timestring);
    }

    /**
     * Timestamp is number of seconds since today at midnight
     * @return int|void
     */
    public function getTimestamp() {
        $dt = new \DateTime();
        $dt->setTime(0,0,0);
        return parent::getTimestamp() - $dt->getTimestamp();
    }

    private function _setTime($timestring = null) {
        $time = explode(':', date('H:i:s', strtotime($timestring)));
        if (count($time) != 3) {
            $this->setTime(0, 0, 0);
        }
        $this->setTime((int) $time[0], (int) $time[1], (int) $time[2]);
    }
}
