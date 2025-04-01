<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Motorcycle extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->database();
    }

    // Example: Get all motorcycles
    public function all() {
        // load the database and query 
        $this->load->database();
        $query = $this->db->get('motorcycles'); 
        $motorcycles = $query->result();

        foreach ($motorcycles as $motorcycle) {
            // Formet the data
            $motorcycle->Volume = $motorcycle->volume . ' cc';

            // Remove original fields to avoid duplication
            unset($motorcycle->volume);
            unset($motorcycle->created_date);
        }

        // Return JSON response
        $this->output 
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get a single car by ID
    public function detail($id) {

        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $updated_date = $date->format('Y-m-d H:i:s');

        $method = $this->input->method(); // Get HTTP method (get, put, patch, delete)

        // Get: View motorcycle
        if($method === 'get') {
            $motorcycle = $this->db->get_where('motorcycles', ['id_motor' => $id])->row();

            if($motorcycle) {
                // Format the data
                $motorcycle->Volume = $motorcycle->volume . ' cc';
                $motorcycle->Created_date = date('Y-m-d', strtotime(str_replace('/', '-', $motorcycle->created_date)));

                // Remove original fields to avoid duplication
                unset($motorcycle->volume);
                unset($motorcycle->created_date);

                $this->output 
                    ->set_content_type('application/json')
                    ->set_output(json_encode($motorcycle));
            } else {
                $this->output 
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Motorcycle not found']));
            }
        }

        // PUT/PATCH: Update motorcycle
        elseif ($method === 'put' || $method === 'patch') {
            $json_input = file_get_contents('php://input');

            // Check if input is empty
            if(empty($json_input)) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Empty request body']));
                return;
            }

            // Decode JSON input
            $data = json_decode($json_input, true);

            // Check if JSON is valid
            if(json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid JSON format']));
                return;
            }

            // Fetch existing motorcycle data 
            $existing_motorcycle = $this->db->get_where('motorcycles', ['id_motor' => $id])->row();

             // Check if the vgacard exists
            if (!$existing_motorcycle) {
                return $this->output
                            ->set_status_header(404)
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['error' => 'Motorcycle not found']));
            }

            // Validate input
            $this->form_validation->set_data($data);

            // Validate "name" only if it's new
            if(isset($data['name']) && $data['name'] != $existing_motorcycle->name) {
                $this->form_validation->set_rules('name', 'Name', 'required|is_unique[motorcycles.name.id.'.$id.']');
            }

            // Validate "color" if present
            if(isset($data['color'])) {
                $this->form_validation->set_rules('color', 'Color', 'required');
            }

            // Validate "brand" if present
            if(isset($data['brand'])) {
                $this->form_validation->set_rules('brand', 'Brand', 'required');
            }

            // Validate "type" if present
            if(isset($data['type'])) {
                $this->form_validation->set_rules('type', 'Type', 'required');
            }

            // Validate "machine" if present
            if(isset($data['machine'])) {
                $this->form_validation->set_rules('machine', 'Machine', 'required');
            }

            // Validate "volume" if present
            if(isset($data['volume'])) {
                $this->form_validation->set_rules('volume', 'Volume', 'required');
            }

            // Run validation
            if($this->form_validation->run() == FALSE) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => validation_errors()]));
                return;
            }

            // Convert numeric fields to float
            if(isset($data['volume'])) $data['volume'] = (float) $data['volume'];

            $update_data = [
                'name' => $data['name'],
                'brand' => $data['brand'],
                'color' => $data['color'],
                'type' => $data['type'],
                'machine' => $data['machine'],
                'volume' => $data['volume'],
                'updated_date' => $updated_date
            ];

            // Update the provided fields 
            $this->db->where('id_motor', $id);
            $this->db->update('motorcycles', $update_data);

            // Success response
            $this->output 
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'Motorcycle Updated']));
        }
        
        // Delete motorcycle (existing code)
        elseif ($method === 'delete') {
            // Check if car exists
            $motorcycle = $this->db->get_where('motorcycles', ['id_motor' => $id])->row();

            if (!$motorcycle) {
                $this->output 
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Motorcycle not found']));
                return;
            }

            // Delete the motorcycle
            $this->db->where('id_motor', $id);
            $this->db->delete('motorcycles');

            // Check for database errors 
            if($this->db->affected_rows() == 0) {
                $this->output 
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Failed to delete a motorcycle']));
                return;
            }

            // Success response
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'Motorcycle deleted successfully']));
        }

        // Handle Invalid methods
        else {
            $this->output 
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method Not Allowed']));
        }
    }

    // Example: Create a new motorcycle
    public function add_motorcycle() {
        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $created_date = $date->format('Y-m-d H:i:s');

        // Read JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Check if input is empty 
        if(empty($json_input)) {
            $this->output 
                ->set_status_header(400) // Bad Request
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Empty request body']));
            return;
        }

        // Validate JSON input 
        if(json_last_error() !== JSON_ERROR_NONE || $data === null) {
            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid JSON input']));
            return;
        }

        // Validate input (including uniqueness)
        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[motorcycles.name]');

        $this->form_validation->set_rules('brand', 'Brand', 'required');

        $this->form_validation->set_rules('color', 'Color', 'required');

        $this->form_validation->set_rules('type', 'Type', 'required');

        $this->form_validation->set_rules('machine', 'Machine', 'required');

        $this->form_validation->set_rules('volume', 'Volume', 'required|numeric');

        // Convert numeric fields to float
        if(isset($data['volume'])) {
            $data['volume'] = (float)$data['volume'];
        }

        // Custom error message for duplicate data
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if($this->form_validation->run() == FALSE) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => validation_errors())));
                return;
        } else {
            $insert_data = [
                'name' => $data['name'],
                'color' => $data['color'],
                'brand' => $data['brand'],
                'type' => $data['type'],
                'machine' => $data['machine'],
                'volume' => $data['volume'],
                'created_date' => $created_date,
                'updated_date' => null
            ];
            // Insert into database 
            $this->db->insert('motorcycles', $insert_data);

            // check for database errors (e.g., race condition duplicates)
            if ($this->db->error()['code']) {
                // MySQL error code for duplicates
                $this->output 
                    ->set_status_header(409)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => 'Name already exists')));
                return;
            }

            // Success Response
            $this->output
                ->set_status_header(201)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('message' => 'Motorcycle created successfully')));
        }
    }
}


?>