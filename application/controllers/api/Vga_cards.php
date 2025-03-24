<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Vga_cards extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->database();
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
        
    }

    
}

?>