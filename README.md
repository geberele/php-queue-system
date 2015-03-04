#An example of a Queue System in PHP
When we want to increase the user experience on our website it’s better to not let the user wait long time for a big process that happen in the back-end and let him free to surf our website having a great experience.
For doing that we need to add the job tasks that require long process to a queue list of processes that will be executed in a second time.

![queue list](http://25.io/mou/Mou_128.png)

We can decide when is the best time to execute the job tasks for the items in the queue list, it's up to us, for example it could be during the night if the tasks require long time and we don't want many people using the website in that time.

The structure of the workflow that represent the execution of a queue list looks like:

![flowchart](http://25.io/mou/Mou_128.png)

Translating in php code the above flowchart we get something like:

```
<?php

/**
 * Example of a Queue System.
 */
function queue_system_example() {
  require_once(dirname(__FILE__) . '/lib/QueueClass.php');
  $queue = new QueueClass();
  // Populate the queue.
  for ($i = 0; $i < 100; $i++) {
    $data = range(0, $i + 1);
    $queue->createItem($data);
  }
  $jobs_to_do = TRUE;
  $start = microtime(true);

  try {
    while ($jobs_to_do) {
      $item = $queue->claimItem();

      if ($item) {
        echo 'Processing the item ' . $item->item_id . '...' . PHP_EOL;

        // Execute the job task in a different function.
        if (execute_the_job_task($item)) {
          // Delete the item if the.
          $queue->deleteItem($item);
          echo 'Item ' . $item->item_id . ' processed.' . PHP_EOL;
        }
        else {
          // Release the item to execute the job task again later.
          $queue->releaseItem($item);
          echo 'Item ' . $item->item_id . ' NOT processed.' . PHP_EOL;
          $jobs_to_do = FALSE;
          echo 'Queue not completed. Job task not executed.' . PHP_EOL;
        }
      }
      else {
        $jobs_to_do = FALSE;
        $time_elapsed_us = microtime(true) - $start;
        $number_of_items = $queue->numberOfItems();
        if ($number_of_items == 0) {
          echo 'Queue completed in ' . $time_elapsed_us . ' seconds.' . PHP_EOL;
        }
        else {
          echo 'Queue not completed, there are ' . $number_of_items . ' items left.' . PHP_EOL;
        }
      }
    }
  }
  catch (Exception $e) {
    echo $e->getMessage();
  }
}

/**
 * Execute the job task.
 */
function execute_the_job_task($item) {
  // Do something with the item.
  return TRUE;
}

```

For this example the QueueClass created and used is:

```
<?php

/**
 * @file
 * QueueClass Class.
 */

/**
 * Static queue implementation.
 */
class QueueClass {
  /**
   * The queue data.
   *
   * @var array
   */
  protected $queue;

  /**
   * Counter for item ids.
   *
   * @var int
   */
  protected $id_sequence;

  /**
   * Start working with a queue.
   */
  public function __construct() {
    $this->queue = array();
    $this->id_sequence = 0;
  }

  /**
   * Add a queue item and store it directly to the queue.
   */
  public function createItem($data) {
    $item = new stdClass();
    $item->item_id = $this->id_sequence++;
    $item->data = $data;
    $item->created = time();
    $item->expire = 0;
    $this->queue[$item->item_id] = $item;
  }

  /**
   * Retrieve the number of items in the queue.
   */
  public function numberOfItems() {
    return count($this->queue);
  }

  /**
   * Claim an item of the queue for a specific time.
   */
  public function claimItem($lease_time = 3600) {
    foreach ($this->queue as $key => $item) {
      if ($item->expire == 0) {
        $item->expire = time() + $lease_time;
        $this->queue[$key] = $item;
        return $item;
      }
    }
    return FALSE;
  }

  /**
   * Delete a finished item from the queue.
   */
  public function deleteItem($item) {
    unset($this->queue[$item->item_id]);
  }

  /**
   * Release an item that the worker could not process, so another
   * worker can come in and process it before the timeout expires.
   */
  public function releaseItem($item) {
    if (isset($this->queue[$item->item_id]) && $this->queue[$item->item_id]->expire != 0) {
      $this->queue[$item->item_id]->expire = 0;
      return TRUE;
    }
    return FALSE;
  }

}
```

This queue class is good for static implementation, for a dynamic implementation it's necessary to store the queue list in a database.

The example could be found [here](github).
