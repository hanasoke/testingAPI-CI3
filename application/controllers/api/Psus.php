<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class Psus extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        // Form Validation
        $this->load->library('form_validation');
        // Load the database and query
        $this->load->database();
        $this->load->library('upload'); // Load the upload library
    }

    // Example: Get all psus 
    public function all() {
        $query = $this->db->get('psus');

        // Return JSON reponse
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get all response
    public function all() {
        $query = $this->db->get('psus');

        // Return JSON response
        $this->output 
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Get psus by ID
    public function detail($id) {
        $query = $this->db->get_where('psus', ['psu_id' => $id]);

        if($query->num_rows() > 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($query->result()));
        } else {
            $this->output 
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'PSU not found']));
        }
    }
    
    // Add a new psu 
    public function add_psu() {

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
                ->set_output(json_encode(['error' => 'Invalid JSON format']));
            return;
        }

        // check if JSON is valid
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $this->output
                ->set_status_header(400) // Bad Request
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Empty request body']));
            return;
        }

        // Validate license first (before other validations)
        if (empty($data['license'])) {
            return $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'License is required']));
        }

        // Process and validate license
        $license_result = $this->validate_and_save_license($data['license']);

        if (isset($license_result['error'])) {
            return $this->output 
                        ->set_status_header($license_result['status'])
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => $license_result['error']]));
        }

        $license_filename = $license_result['filename'];

        // Validate input data
        $this->form_validation->set_data($data);

        // Set validate rules
        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[psus.name]');

        $this->form_validation->set_rules('type', 'Type', 'required');

        $this->form_validation->set_rules('series', 'Series', 'required');

        $this->form_validation->set_rules('models', 'Models', 'required');

        $this->form_validation->set_rules('power', 'Power', 'required|numeric');

        $this->form_validation->set_rules('license', 'License', 'required');

        // Custom error message for duplicate variable
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        // Run Validation 
        if($this->form_validation->run() == FALSE) {
            // Clean up uploaded file if validation fails
            if(isset($license_filename) && file_exists('./public/img/psus/'.$license_filename)) {
                @unlink('./public/img/psus/'.$license_filename);
            }

            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => validation_errors()]));
            return;
        }

        // Prepare data form insertion
        $insert_data = [
            'name' => $data['name'],
            'type' => $data['type'],
            'series' => $data['series'],
            'models' => $data['models'],
            'power' => $data['power'],
            'license' => $license_filename,
            'created_date' => $created_date
        ];

        // Insert data into the database
        $this->db->insert('psus', $insert_data);
        
        // Check for database errors 
        if ($this->db->affected_rows() > 0) {
            // Success response
            $this->output 
                ->set_status_header(201) // Created
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'message' => 'Applicant created successfully',
                    'psu_id' => $this->db->insert_id(),
                    'license_url' => base_url('public/img/psus/'. $license_filename)
                ]));
        } else {
            // Clean up uploaded file if database insert fails
            if(isset($license_filename) && file_exists('./public/img/psus/'.$license_filename)) {
                @unlink('./public/img/psus/'.$license_filename);
            }

            return $this->output 
                        ->set_status_header(500) // Internal Server Error
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Failed to create PSU']));
        }
    }
}

?>