<?php

namespace Countries;

/*
 * Unlike the countries portion of this package I am not sure I want to correct an address state,
 * it feels that that would be better accomplished by a full blown address validator which is
 * where I think this project is going to head.
 */

class US
{
    private $strict = true;
    private $states;

    public function __construct($strict = true)
    {
        if (!is_bool($strict)) {
            throw new \Exception('strict must be boolean');
        }

        $this->strict = $strict;
        $this->states = include '3166_2_US.php';
    }

    public function getAllStates()
    {
        return $this->states;
    }

    public function getTypicalStates()
    {
        $return = [];

        foreach ($this->states as $state) {
            if (in_array($state['type'], ['state', 'district', 'us armed forces'])) {
                $return[] = $state;
            }
        }

        return $return;
    }

    public function getState($term)
    {
        if (!preg_match('/[a-z]/i', $term)) {
            return null;
        }

        if ($results = $this->getExactMatch($term)) {
            return $results;
        }

        if ($this->strict) {
            return null;
        }

        return null;
    }

    public function getExactMatch($term)
    {
        $term = strtolower($term);

        foreach ($this->states as $state) {
            $checkAgainst = [
                strtolower($state['iso']),
                strtolower($state['usps']),
                strtolower($state['name']),
                strtolower($state['ap']),
            ];
            if (in_array($term, $checkAgainst)) {
                return $state;
            }
        }

        return false;
    }

    /**
     * Part of the lower 48?
     *
     * @param $term
     *
     * @return bool|null
     */
    public function isCONUS($term)
    {
        if ($state = $this->getState($term)) {
            return $state['CONUS'];
        } else {
            return $state;
        }
    }
}
