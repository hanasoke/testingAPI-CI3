<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

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
        $cars = $query->result(); // Fetch all cars

        // Loop through each car to format the data 
        foreach($cars as $car) {
            // Format the data
            $car->Seat = $car->seat . ' seat';
            $car->Machine = $car->machine . ' cc';
            $car->Power = $car->power . ' hp';
            $car->Price = 'Rp ' . number_format($car->price, 0, ',', '.');
            $car->Manufacture = date('Y-m-d', strtotime(str_replace('/', '-', $car->manufacture)));
        }

        // Return JSON response
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($query->result()));
    }

    // Example: Get a single car by ID
    public function car($id) {
        $this->load->database();
        $method = $this->input->method(); // Get HTTP method (get, put, patch, delete)

        // Get: View user
        if($method === 'get') {
            $car = $this->db->get_where('cars', ['id' => $id])->row();
            if($car) {
                // Format the data
                $car->Seat = $car->seat . ' seat';
                $car->Machine = $car->machine . ' cc';
                $car->Power = $car->power . ' hp';
                $car->Price = 'Rp ' . number_format($car->price, 0, ',', '.');
                $car->Manufacture = date('Y-m-d', strtotime(str_replace('/', '-', $car->manufacture)));
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($car));
            } else {
                $this->output
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Car not found']));
            }
        }

        // PUT/PATCH: Update car
        elseif($method === 'put' || $method === 'patch') {
            $this->load->library('form_validation');
            $json_input = file_get_contents('php://input');
            $data = json_decode($json_input, true);

            // Fetch existing car data 
            $existing_car = $this->db->get_where('cars', ['id' => $id])->row();

            // Validate input 
            $this->form_validation->set_data($data);

            // Validate "name" only if it's new
            if (isset($data['name']) && $data['name'] != $existing_car->name) {
                $this->form_validation->set_rules('name', 'Name', 'required|is_unique[cars.name.id.'.$id.']');
            }

            // Validate "color" if present
            if(isset($data['color'])) {
                $this->form_validation->set_rules('color', 'Color', 'required');
            }

            // Validate "brand" if present
            if(isset($data['brand'])) {
                $this->form_validation->set_rules('brand', 'Brand', 'required');
            }

            // Validate "transmission" if present
            if(isset($data['transmission'])) {
                $this->form_validation->set_rules('transmission', 'Transmission', 'required');
            }

            // Validate "seat" if present
            if(isset($data['seat'])) {
                $this->form_validation->set_rules('seat', 'Seat', 'required|numeric');
            }

            // Validate "power" if present
            if(isset($data['power'])) {
                $this->form_validation->set_rules('power', 'Power', 'required|numeric');
            }

            // Validate "price" if present
            if(isset($data['price'])) {
                $this->form_validation->set_rules('price', 'Price', 'required|numeric');
            }

            // Validate "stock" if present
            if(isset($data['stock'])) {
                $this->form_validation->set_rules('stock', 'Stock', 'required|numeric');
            }

             // Validate "machine" if present
            if(isset($data['machine'])) {
                $this->form_validation->set_rules('machine', 'Machine', 'required');
            }

            // Validate "manufacture" if present
            if(isset($data['manufacture'])) {
                $this->form_validation->set_rules('manufacture', 'Manufacture', 'required');
            }

            // Run validation
            if($this->form_validation->run() == FALSE) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => validation_errors()]));
                return;
            }

            // Convert numeric fields to integers
            if(isset($data['price'])) $data['price'] = (int)$data['price'];
            if (isset($data['seat'])) $data['seat'] = (int)$data['seat'];
            if(isset($data['machine'])) $data['machine'] = (int)$data['machine'];
            if(isset($data['power'])) $data['power'] = (int)$data['power'];
            if(isset($data['stock'])) $data['stock'] = (int)$data['stock'];

            // Update the provided fields
            $this->db->where('id', $id);
            $this->db->update('cars', $data);

            // Success response
            $this->output 
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'Car updated']));
        }

        // Delete car (existing code)
        elseif ($method === 'delete') {
            // Check if user exists
            $car = $this->db->get_where('cars', ['id' => $id])->row();
            if(!$car) {
                $this->output
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Car not found']));
                return;
            }

            // Delete the car
            $this->db->where('id', $id);
            $this->db->delete('cars');

            // Check for database errors
            if ($this->db->affected_rows() == 0) {
                $this->output 
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Failed to delete a car']));
                return;
            }

            // Success response
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'Car deleted successfully']));
        }

        // Handle Invalid methods
        else {
            $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method Not Allowed']));
        }
    }

    // Example: Create a new car
    public function add_car() {
        $this->load->database();
        $this->load->library('form_validation');

        // Read JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Validate input (including uniqueness)
        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[cars.name]');

        $this->form_validation->set_rules('color', 'Color', 'required');

        $this->form_validation->set_rules('brand', 'Brand', 'required');

        $this->form_validation->set_rules('transmission', 'Transmission', 'required');

        $this->form_validation->set_rules('seat', 'Seat', 'required|numeric');

        $this->form_validation->set_rules('machine', 'Machine', 'required|numeric');

        $this->form_validation->set_rules('power', 'Power', 'required|numeric');

        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        $this->form_validation->set_rules('stock', 'Stock', 'required|numeric');

        $this->form_validation->set_rules(
            'manufacture', 
            'Manufacture', 
            'required|regex_match[/^\d{2}\/\d{2}\/\d{4}$/]');
        // day/month/year

        // Convert numeric fields to integers
        if(isset($data['price'])) {
            $data['price'] = (int)$data['price'];
        }
        if(isset($data['seat'])) {
            $data['seat'] = (int)$data['seat'];
        }
        if(isset($data['machine'])) {
            $data['machine'] = (int)$data['machine'];
        }
        if(isset($data['power'])) {
            $data['power'] = (int)$data['power'];
        }
        if(isset($data['stock'])) {
            $data['stock'] = (int)$data['stock'];
        }

        // Custom error message for duplicate name
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if ($this->form_validation->run() == FALSE) {
            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => validation_errors())));
                return;
        } else {
            // Insert into database
            $this->db->insert('cars', $data);

            // Check for database errors (e.g., race condition duplicates)
            if ($this->db->error()['code'] == 1062) {
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
                ->set_output(json_encode(array('message' => 'Car created successfully')));
        }
    }
}


?>