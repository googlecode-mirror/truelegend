<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Priority queue, Heap data structure
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class QueueStruct_PRIO extends Queue_Abstract
{
    /**
     * Push an element onto the queue by priority
     *
     * @param int $priority
     * @param mixed $element
     * @return bool
     */
    public function push($priority, $element)
    {
        if (isset($this->elements[$priority]))
        {
            return false;
        }

        $this->elements[$priority] = $element;

        return true;
    }

    /**
     * Pop element with highest or lowest priority
     *
     * @param bool $ highest
     * @return mixed $element
     */
    public function pop($highest)
    {
        return $highest ? array_shift($this->elements): array_pop($this->elements);
    }
}

?>