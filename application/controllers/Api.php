<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    // Example: Get all users
    public function users() {
        // Load the database and query
        $this->load->database();
        $query = $this->db->get('users'); // Assuming you have a 'users' table

        // Return JSON response
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($query->result())); 
    }

    // Example: Get a single user by ID
    public function user($id) {
        $this->load->database();
        $query = $this->db->get_where('users', array('id' => $id));

        if ($query->num_rows() > 0) {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($query->row()));
        } else {
            $this->output 
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Resource not found')));
        }
    }

    // Example: Create a new user
    public function create_user() {
        $this->load->database();
        $this->load->library('form_validation');

        // Read raw JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Validate input
        $this->form_validation->set_data($data); // Set validation data
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');

        if($this->form_validation->run() == FALSE) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => validation_errors())));
        } else {
            // Insert into database
            $this->db->insert('users', $data);

            // Check for database errors (e.g., race condition duplicates)
            if ($this->db->error()['code'] == 1062) {
                // MySQL duplicate error code
                $this->output
                    ->set_status_header(409)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => 'Email already exists')));
                return;
            }

            // Return success message as JSON
            $this->output
                 ->set_status_header(201)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(array('message' => 'User created successfully')));
        }
    }
}

?>