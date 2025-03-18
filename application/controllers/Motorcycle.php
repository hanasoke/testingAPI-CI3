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
        }

        // Return JSON response
        $this->output 
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get a single car by ID
    public function motorcycle($id) {
        $this->load->database();
        $method = $this->input->method(); // Get HTTP method (get, put, patch, delete)

        // Get: View motorcycle
        if($method === 'get') {
            $motorcycle = $this->db->get_where('motorcycles', ['id' => $id])->row();

            if($car) {
                // Format the data
                $motorcycle->Volume = $motorcycle->volume . ' cc';
                $motorcycle->Created_date = date('Y-m-d', strtotime(str_replace('/', '-', $motorcycle->created_date)));

                $this->output 
                    ->set_content_type('application/json')
                    ->set_output(json_encode($motorcycle));
            } else {
                $this->output 
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Motorcycle not found']));
            }
        }
    }

    // Example: Create a new motorcycle
    public function add_motorcycle() {

        // Read JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

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

        // Custom error message for duplicate name
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
                'created_date' => date('Y-m-d H:i:s')
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