<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

$data = Timber::get_context();
Timber::render($data['template'], $data);//, 600);