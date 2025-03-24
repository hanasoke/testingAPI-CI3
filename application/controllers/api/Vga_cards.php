<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Vga_cards extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->library('upload'); // Load the upload library
    }

    // Example: Get all Vga_cards
    public function all() {
        // Load the database and query 
        $this->load->database();
        $query = $this->db->get('vga_cards');
        $vga_cards = $query->result();

        // Return JSON response
        $this->output 
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get a single vga_card by ID
    public function detail($id) {

        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $update_date = $date->format('Y-m-d H:i:s');

        $method = $this->input->method(); // Get HTTP method (get, put, patch, delete)

        // Get: Vga Card
        if($method === 'get') {
            $vga_card = $this->db->get_where('vga_cards', ['id_card' => $id])->row();

            if($vga_card) {
                $this->output 
                    ->set_content_type('application/json')
                    ->set_output(json_encode($vga_card));
            } else {
                $this->output 
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Vga Card not found']));
            }
        }

        // PUT/PATCH: Update Vga Card
        elseif ($method === 'put' || $method === 'patch') {
            $json_input = file_get_contents('php://input');

            // Check if input is empty
            if (empty($json_input)) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Empty request body']));
                return;
            }
        }

        // Delete vga_card (existing_code)
        elseif ($method === 'delete') {
            // Check if vga_card exists
            $vga_card = $this->db->get_where('vga_cards', ['id_card' => $id])->row();

            if (!$vga_card) {
                $this->output 
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Vga Card not found']));
                return;
            }

            // Delete the vga_card
            $this->db->where('id_card', $id);
            $this->db->delete('vga_cards');

            // Check for database errors 
            if($this->db->affected_rows() == 0) {
                $this->output 
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Failed to delete a vga_card']));
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

    // Example: Create a new vga_card
    public function add_vgacard() {
        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $created_date = $date->format('Y-m-d H:i:s');

        // Validate input (including uniqueness)
        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[vga_cards.name]');

        $this->form_validation->set_rules('brand', 'Brand', 'required|in_list[Radeon,Nvidia,Intel]');

        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        $this->form_validation->set_rules('release_date', 'Release Date', 'required');

        // Convert numeric fields to int
        if(isset($data['price'])) {
            $data['price'] = (int)$data['price'];
        }

        // Custom error message for duplicate name
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if($this->form_validation->run() === FALSE) {
            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => validation_errors())));
            return;
        } 

        // Handle file upload
        if(!empty($_FILES['photo']['name'])) {
            // Configure upload settings
            $config['upload_path'] = './public/img/vga_cards'; // Ensure this directory exists and is writable

            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048; // 2MB max size
            $config['file_name'] = uniqid(); // Generate a unique file name

            $this->upload->initialize($config);

            if(!$this->upload->do_upload('photo')) {
                // If file upload fail, return an error
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => $this->upload->display_errors())));
                return;
            } else {
                // Get the uploaded file data
                $upload_data = $this->upload->data();
                $photo = $upload_data['file_name']; // Store the file name in the database
            }
        } else {
            // If no file is uploaded, return an error
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Photo is required')));
            return;
        }
        
        // Prepare data for insertion
        $insert_data = [
            'name' => $this->input->post('name'),
            'brand' => $this->input->post('brand'),
            'price' => $this->input->post('price'),
            'photo' => $photo, // Store the file name
            'release_date' => $this->input->post('release_date'),
            'created_date' => $created_date,
            'updated_date' => null 
        ];

        // Insert into database
        $this->db->insert('vga_cards', $insert_data);

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
            ->set_output(json_encode(array('message' => 'VGA Card created successfully')));
    }
}

?>