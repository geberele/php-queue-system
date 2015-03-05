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
   * Claim an item in the queue for processing for a specific time.
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

  /**
   * Create a queue.
   */
  public function createQueue() {
    // Nothing needed here.
  }

  /**
   * Delete a queue.
   */
  public function deleteQueue() {
    $this->queue = array();
    $this->id_sequence = 0;
  }

}
