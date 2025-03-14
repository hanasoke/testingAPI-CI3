<?php 
defined('BASEPATH') OR exit('No direct script access alloowed');

class Car extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    // Example: Get all cars
    public function cars() {
        // Load the database and query
        $this->load->database();
        $query = $this->db->get('cars'); // Assuming you have a 'users' table

        // Return JSON response
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get a single car by ID
    public function car($id) {
        $this->load->database();
        $method = $this->input->method(); // Get HTTP method (get, put, patch)


    }


}


?>