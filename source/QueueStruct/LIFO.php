<?php
/**
 * LIFO(Last In,First Out) queue, Stack data structure
 *
 * @author zendzhang<zendzhang@hotmail.com>
 * @version $Id$
 */

class QueueStruct_LIFO extends Queue_Abstract
{
    /**
     * Pop an element from the end of the queue
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->elements);
    }
}

?>