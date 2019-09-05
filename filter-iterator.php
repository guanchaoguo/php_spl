<?php

/*************************************************/
/******************** FILTERS ********************/
/*************************************************/
class Order extends FilterIterator
{
    private $_value = null;

    public function __construct(Iterator $iterator, $value)
    {
        $this->_value = $value;
        parent::__construct($iterator);
    }

    public function accept()
    {
        return $this->current()->order === $this->_value;
    }
}

class Future extends FilterIterator
{
    public function __construct(Iterator $iterator)
    {
        parent::__construct($iterator);
    }

    public function accept()
    {
        return $this->current()->date > new DateTime();
    }
}

class Date extends FilterIterator
{
    private $_value = null;

    public function __construct(Iterator $iterator, $value)
    {
        $this->_value = $value;
        parent::__construct($iterator);
    }

    public function accept()
    {
        return $this->current()->date === $this->_value;
    }
}

/*************************************************/
/*************** COLLECTION HELPER ***************/
/*************************************************/
class Helper
{
    private $_items = [];
    private $_filters = [];

    public function __construct(array $items)
    {
        $this->_items = (new ArrayObject($items))->getIterator();
    }

    public function filter($filter, $value = null)
    {
        $this->_filters[] = [$filter => $value];

        return $this;
    }

    public function get()
    {
        foreach ($this->_filters as $filter) {
            foreach ($filter as $name => $value) {

                if ($value) {
                    $this->_items = new $name($this->_items, $value);
                } else {
                    $this->_items = new $name($this->_items);
                }
            }
        }

        return iterator_to_array($this->_items);
    }
}

/*************************************************/
/****************** TEST CASES *******************/
/*************************************************/
class Reservation
{
    public $order = null;
    public $date = null;
    public $status = null;

    public function __construct($order, $date, $status)
    {
        $this->order = $order;
        $this->date = $date;
        $this->status = $status;
    }
}

$reservations = new Helper([
    new Reservation(123, new DateTime('+1Day'), 'Active'),
    new Reservation(512, new DateTime('+2Month'), 'Active'),
    new Reservation(456, new DateTime('-6Hour'), 'Pending'),
    new Reservation(789, new DateTime('-17Day'), 'Cancelled'),
    new Reservation(264, new DateTime('+1Hour'), 'Pending'),
    new Reservation(151, new DateTime('+1Year'), 'Active'),
]);

$filtered = $reservations
    ->filter('Future')
    ->filter('Order', 123)
    ->get();

var_dump($filtered);
