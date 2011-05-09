<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * Queue interface
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

interface Queue
{
    /**
     * Push an element onto the end of the queue
     *
     * @param mixed $element
     * @return bool
     */
    public function push($element);

    /**
     * Pop an element from the front of the queue
     *
     * @return mixed
     */
    public function pop();

    /**
     * Remove an element from the queue
     *
     * @param int $index
     * @return mixed
     */
    public function remove($index);

    /**
     * Return all elements
     *
     * @return array
     */
    public function get();

    /**
     * Is the queue empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the number of elements in the queue
     *
     * @return int
     */
    public function size();

    /**
     * Clear the queue
     *
     * @return void
     */
    public function clear();

    /**
     * Print the queue info
     *
     * @return void
     */
    public function dump();
}

?>