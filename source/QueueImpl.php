<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue implemention class
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class QueueImpl implements Queue
{
    /**
     * Stores elements as array
     *
     * @var array
     */
    private $elements = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($elements = array())
    {
        if (!empty($elements))
        {
            $this->elements = $elements;
        }
    }

    /**
     * Push an element onto the end of the queue
     *
     * @param mixed $element
     * @return bool
     */
    public function push($element)
    {
        return (array_push($this->elements, $element)) ? true : false;
    }

    /**
     * Pop an element from the front of the queue
     *
     * @return mixed
     */
    public function pop()
    {
        return array_shift($this->elements);
    }

    /**
     * Remove an element from the queue
     *
     * @param int $index
     * @return mixed
     */
    public function remove($index)
    {
        $element = isset($this->elements[$index]) ? $this->elements[$index] : null;

        if (!is_null($element))
        {
            array_splice($this->elements, $index, 1);
        }

        return $element;
    }

    /**
     * Return all elements
     *
     * @return array
     */
    public function get()
    {
        return $this->elements;
    }

    /**
     * Is the queue empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * Returns the number of elements in the queue
     *
     * @return int
     */
    public function size()
    {
        return count($this->elements);
    }

    /**
     * Clear the queue
     *
     * @return void
     */
    public function clear()
    {
        $this->elements = array();
    }

    /**
     * Print the queue info
     *
     * @return void
     */
    public function dump()
    {
        var_export($this->elements);
    }
}

?>