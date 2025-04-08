<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpu extends CI_Controller {

    public function __construct() {
        parent::__construct();
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

        $method = $this->input->method(); // Get HTTP method (get & delete) 

        // Get: Cpu 
        if ($method === 'get') {
            $cpu = $this->db->get_where('cpus', ['cpu_id' => $id])->row();

            if ($cpu) {
                $this->output 
                    ->set_content_type('application/json')
                    ->set_output(json_encode($cpu));
            } else {
                $this->output 
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Cpu not found']));
            }

        }

        // Delete cpu 
        elseif ($method === 'delete') {
            // Check if cpu exists
        }

        // Handle Invalid methods
        else {
            $this->output 
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Method Not Allowed']));
            return;
        }
    }

    // Create a new Cpu 
    public function add_cpu() {
        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $created_date = $date->format('Y-m-d H:i:s');

        // Read JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Check if input is empty 
        if (empty($json_input)) {
            $this->output 
                ->set_status_header(400) // Bad Request
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Empty request body']));
            return;
        }

        // Validate JSON input
        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid JSON input']));
            return;
        }

        // Validate input (including uniqueness)
        $this->form_validation->set_rules($data);
        $this->form_validation->set_rules("name", "Name", "required|is_unique[cpus.name]");
        $this->form_validation->set_rules('brand', 'Brand', 'required');
        $this->form_validation->set_rules('core', 'Core', 'required|numeric');
        $this->form_validation->set_rules('thread', 'Thread', 'required|numeric');
        $this->form_validation->set_rules('serie', 'Serie', 'required');
        $this->form_validation->set_rules('memory', 'Memory', 'required');
        $this->form_validation->set_rules('manufacturing_node', 'Manufacture Node', 'required');
        $this->form_validation->set_rules('integrated_graphic', 'Integrated Graphic', 'required');
        $this->form_validation->set_rules('boost_clock', 'Boost Clock', 'required');
        $this->form_validation->set_rules('total_cache', 'Total Cache', 'required');
        $this->form_validation->set_rules('price', 'Price', 'required');

        // Custom error message for duplicate data
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if ($this->form_validation->run() === FALSE) {
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => validation_errors()]));
        }

        // Handle base64 video
        $video_filename = null;
        if(!empty($_FILES['video'])) {
            $video_data = $data['video'];

            // Extract video format and data
            if (preg_match('/^data:video\/(\w+);base64,/', $video_data, $matches)) {
                $video_format = $matches[1];
                $video_data = substr($video_data, strpos($video_data, ',') + 1);
                $video_data = base64_decode($video_data);

                if ($video_data === false) {
                    return $this->output 
                                ->set_status_header(400)
                                ->set_content_type('application/json')
                                ->set_output(json_encode(['error' => 'Invalid base64 video data']));
                }

                // Generate unique filename
                $video_filename = uniqid('cpu_video_') . '.' . $video_format;

                $video_path = './public/video/cpus/' . $video_filename;

                // Save video file
                if (!file_put_contents($video_path, $video_data)) {
                    return $this->output 
                                ->set_status_header(500)
                                ->set_content_type('application/json')
                                ->set_output(json_encode(['error' => 'Failed to save video file']));
                }
            } else {
                return $this->output
                            ->set_status_header(400)
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['error' => 'Invalid video format. Expected base64 encoded video']));
            }

        } else {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Video is required as base64')));
            return;
        }

        // Prepare data for insertion
        $insert_data = [
            'name' => $data['name'],
            'brand' => $data['brand'],
            'core' => $data['core'],
            'thread' => $data['thread'],
            'serie' => $data['serie'],
            'memory' => $data['memory'],
            'manufacturing_code' => $data['manufacturing_node'],
            'integrated_graphic' => $data['integrated_graphic'],
            'boost_clock' => $data['boost_clock'],
            'total_cache' => $data['total_cache'],
            'video' => $video_filename, 
            'created_date' => $created_date,
            'updated_date' => null,
            'price' => $price
        ];

        // Insert into database
        $this->db->insert('cpus', $insert_data);

        // check for database errors (e.g., race condition duplicates)
        if ($this->db->error()['code']) {
            // Clean up uploaded file if database insert fails
            if ($video_filename && file_exists('./public/video/cpus/' . $video_filename)) {
                unlink('./public/video/cpus/' . $video_filename);
            }
            return $this->output
                        ->set_status_header(409)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'CPU already exists']));
        }

        // Success Response 
        return $this->output
                    ->set_status_header(201)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'message' => 'CPU created successfully',
                        'data' => [
                            'id' => $this->db->insert_id(),
                            'video_url' => base_url('public/video/cpus/'. $video_filename)
                        ]
            ]));
    }
}

?>