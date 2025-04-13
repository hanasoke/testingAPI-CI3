<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Computer extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation', 'upload');
        $this->load->database();
    }

    // Example: Get all Computer 
    public function all() {
        // Load the database and query
        $this->load->database();

         

    }

}

?>