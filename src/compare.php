<?php

/**
 * PHP benchmark script
 * @author 8ctopus <hello@octopuslabs.io>
 */

// add assertions support
ini_set('zend.assertions', true);
ini_set('assert.exception', true);
//assert(false, __METHOD__ .'() unhandled situation');

// set error reporting
error_reporting(E_ERROR /*| E_WARNING | E_PARSE*/);

