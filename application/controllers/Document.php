<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Document extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        dd('Document');
        $this->load->view('welcome_message');
    }
}
