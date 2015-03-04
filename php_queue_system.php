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
