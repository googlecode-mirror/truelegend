<?php defined('IN_TRUELEGEND') or die('No direct script access.');
/**
 * FIFO(First In,First Out) queue, Queue data structure
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id:$
 */

class QueueStruct_FIFO extends Queue_Abstract
{
    /**
     * Pop an element from the front of the queue
     *
     * @return mixed
     */
    public function pop()
    {
        return array_shift($this->elements);
    }
}

?>