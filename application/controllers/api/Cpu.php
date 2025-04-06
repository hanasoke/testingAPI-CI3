<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpu extends CI_Controller {

    public function __construct() {
        parent::construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->library('upload'); // Load the upload library
    }

    // Example: Get all Cpus
    public function all() {
        // Load the database and query
        $this->load->database();
        $query = $this->db->get('cpus');
        $cpus = $query->result();

        // Return JSON response
        $this->output 
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get a single cpus by ID
    public function detail($id) {

        $method = $this->input->method(); // Get HTTP method (get, put, patch, delete) 

        

    }



}

?>