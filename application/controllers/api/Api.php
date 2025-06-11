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
        $method = $this->input->method(); // Get HTTP method (get, put, patch, delete)

        // GET: View user
        if ($method === 'get') {
            $user = $this->db->get_where('users', ['id' => $id])->row();
            if($user) {
                $this->output 
                    ->set_content_type('application/json')
                    ->set_output(json_encode($user));
            } else {
                $this->output 
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'User not found']));
            }
        }

        // PUT/PATCH: Update user
        elseif ($method === 'put' || $method === 'patch') {
            $json_input = file_get_contents('php://input');
            $data = json_decode($json_input, true);

            // Fetch existing user data
            $existing_user = $this->db->get_where('users', ['id' => $id])->row();

            // Check existing user data
            if(!$existing_user) {
                return $this->output 
                            ->set_status_header(404)
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['error' => 'Car not found']));
            }

            // Validate input
            $this->form_validation->set_data($data);

            // Check if JSON is valid
            if(json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid JSON format']));
                return;
            }

            // Validate "name" only if it's new 
            if (isset($data['name']) && $data['name'] != $existing_user->name ) {
                $this->form_validation->set_rules('name', 'Name', 'required|is_unique[users.name.id.'.$id.']');
            }

            // Validate "email" if present
            if (isset($data['email']) && $data['email'] != $existing_user->email) {
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email.id.'.$id.']');
            }

            // Validate "age" if present
            if(isset($data['age'])) {
                $this->form_validation->set_rules('age', 'Age', 'required|numeric');
            }

            // Validate "phone" if present (FIXED SYNTAX)
            if(isset($data['phone']) && $data['phone'] != $existing_user->phone) {
                $this->form_validation->set_rules('phone', 'Phone', 'required|numeric|is_unique[users.phone.id.'.$id.']');
            }

            // Validate "address" if present (no uniqueness check)
            if(isset($data['address'])) {
                $this->form_validation->set_rules('address', 'Address', 'required');
            }

            // Run validation
            if ($this->form_validation->run() == FALSE) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => validation_errors()]));
                return;
            }

            // Convert numeric fields to integers
            if(isset($data['age'])) {
                $data['age'] = (int)$data['age'];
            }

            if(isset($data['phone'])) {
                $data['phone'] = (int)$data['phone'];
            }

            // Update the provided fields
            $this->db->where('id', $id);
            $this->db->update('users', $data);

            // Success response
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'User updated']));
        }

        // Delete user (existing code)
        elseif($method === 'delete') {
            // Check if user exists
            $user = $this->db->get_where('users', ['id' => $id])->row();
            if(!$user) {
                $this->output
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'User not found']));
                return;
            }

            // Delete the user
            $this->db->where('id', $id);
            $this->db->delete('users');

            // Check for database errors 
            if($this->db->affected_rows() == 0) {
                $this->output 
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Failed to delete user']));
                return;
            }

            // Success response 
            $this->output 
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'User deleted sucessfully']));
        }

        // Handle Invalid methods
        else {
            $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method Not Allowed']));
        }
    }

    // Example: Create a new user
    public function create_user() {
        $this->load->database();
        $this->load->library('form_validation');

        // Read JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Validate input (including uniqueness)
        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[users.name]');

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');

        $this->form_validation->set_rules('age', 'Age', 'required');

        $this->form_validation->set_rules('phone', 'Phone', 'required|numeric|is_unique[users.phone]');
        
        $this->form_validation->set_rules('address', 'Address', 'required');

        // Convert numeric fields to integers
        if(isset($data['age'])) {
            $data['age'] = (int)$data['age'];
        }
        if(isset($data['phone'])) {
            $data['phone'] = (int)$data['phone'];
        }

        // Custom error message for duplicate name
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if($this->form_validation->run() == FALSE) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => validation_errors())));
                return;
        } else {
            // Insert into database
            $this->db->insert('users', $data);

            // Check for database errors (e.g., race condition duplicates)
            if ($this->db->error()['code'] == 1062) {
                // MySQL error code for duplicates
                $this->output
                    ->set_status_header(409)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => 'Name, email, or phone already exists')));
                return;
            }
            // Success Response
            $this->output
                 ->set_status_header(201)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(array('message' => 'User created successfully')));
        }
    }
}

?>