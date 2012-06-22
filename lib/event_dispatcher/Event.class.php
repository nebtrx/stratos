<?php

/**
 * Event
 * 
 * @author     Neb
 */
class Event implements ArrayAccess 
{

    protected
    $value = null,
    $processed = false,
    $sender = null,
    $name = '',
    $time = null,
    $parameters = null;

    /**
     * Constructs a new Event.
     *
     * @param mixed   $sender       The sender
     * @param string  $name         The event name
     * @param array   $parameters   An array of parameters
     */
    public function __construct($sender, $name, $parameters = array()) 
    {
        $this->sender = $sender;
        $this->name = $name;
        $this->time = time();

        $this->parameters = $parameters;
    }

    /**
     * Returns the subject.
     *
     * @return mixed The subject
     */
    public function getSender() {
        return $this->sender;
    }

    /**
     * Returns the event time
     * 
     * @return DateTime   The event time
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * Returns the event name.
     *
     * @return string The event name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the return value for this event.
     *
     * @param mixed $value The return value
     */
    public function setReturnValue($value) {
        $this->value = $value;
    }

    /**
     * Returns the return value.
     *
     * @return mixed The return value
     */
    public function getReturnValue() {
        return $this->value;
    }

    /**
     * Sets the processed flag.
     *
     * @param Boolean $processed The processed flag value
     */
    public function setProcessed($processed) {
        $this->processed = (boolean) $processed;
    }

    /**
     * Returns whether the event has been processed by a listener or not.
     *
     * @return Boolean true if the event has been processed, false otherwise
     */
    public function isProcessed() {
        return $this->processed;
    }

    /**
     * Returns the event parameters.
     *
     * @return array The event parameters
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Returns true if the parameter exists (implements the ArrayAccess interface).
     *
     * @param  string  $name  The parameter name
     *
     * @return Boolean true if the parameter exists, false otherwise
     */
    public function offsetExists($name) {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Returns a parameter value (implements the ArrayAccess interface).
     *
     * @param  string  $name  The parameter name
     *
     * @return mixed  The parameter value
     */
    public function offsetGet($name) 
    {
        if (!array_key_exists($name, $this->parameters)) 
        {
            throw new InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->name, $name));
        }

        return $this->parameters[$name];
    }

    /**
     * Sets a parameter (implements the ArrayAccess interface).
     *
     * @param string  $name   The parameter name
     * @param mixed   $value  The parameter value 
     */
    public function offsetSet($name, $value) {
        $this->parameters[$name] = $value;
    }

    /**
     * Removes a parameter (implements the ArrayAccess interface).
     *
     * @param string $name    The parameter name
     */
    public function offsetUnset($name) {
        unset($this->parameters[$name]);
    }

}