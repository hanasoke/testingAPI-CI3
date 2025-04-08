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
        // Set timezone and create date
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $created_date = $date->format('Y-m-d H:i:s');

        // Read and Validate JSON input
        $json_input = file_get_contents('php://input'); 
        if (empty($json_input)) {
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Empty request body']));
        }

        $data = json_decode($json_input, true);
        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid JSON input']));
        }

        // Validate input (including uniqueness)
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules("name", "Name", "required|is_unique[cpus.name]");
        $this->form_validation->set_rules('brand', 'Brand', 'required');
        $this->form_validation->set_rules('core', 'Core', 'required|numeric');
        $this->form_validation->set_rules('thread', 'Thread', 'required|numeric');
        $this->form_validation->set_rules('serie', 'Serie', 'required');
        $this->form_validation->set_rules('memory', 'Memory', 'required');
        $this->form_validation->set_rules('manufacturing_node', 'Manufacture Node', 'required|numeric');
        $this->form_validation->set_rules('integrated_graphic', 'Integrated Graphic', 'required');
        $this->form_validation->set_rules('boost_clock', 'Boost Clock', 'required');
        $this->form_validation->set_rules('total_cache', 'Total Cache', 'required|numeric');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        // Convert numeric fields to int
        if (isset($data['price'])) {
            $data['price'] = (int)$data['price'];
        }

        // Custom error message for duplicate data
        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if ($this->form_validation->run() === FALSE) {
            $errors = str_replace(["\n", "\r", "\t"], '', strip_tags(validation_errors()));
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => $errors]));
        }

        // Handle base64 video without prefix
        $video_filename = null;
        if (!empty($data['video']) && !empty($data['video_format'])) {
            $allowed_formats = ['mp4', 'webm', 'ogg'];
            $allowed_mime_types = ['video/mp4', 'video/webm', 'video/ogg'];

            $video_format = strtolower($data['video_format']);

            if (!in_array($video_format, $allowed_formats)) {
                return $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Invalid or unsupported video format']));
            }

            $video_data = base64_decode($data['video']);

            if ($video_data === false) {
                return $this->output 
                            ->set_status_header(400)
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['error' => 'Invalid base64 video data']));
            }

            // Detect MIME type using finfo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->buffer($video_data);

            if (!in_array($mime_type, $allowed_mime_types)) {
                return $this->output 
                            ->set_status_header(400)
                            ->set_content_type('application/json')
                            ->set_output(json_encode([
                                'error' => 'Uploaded file is not a valid video. Detected MIME: ' . $mime_type
                            ]));
            }

            if (!is_dir('./public/video/cpus/')) {
                mkdir('./public/video/cpus/', 0777, true);
            }
            
            $video_filename = uniqid('cpu_video_'). '.' . $video_format;
            $video_path = './public/video/cpus/' . $video_filename;

            if (!file_put_contents($video_path, $video_data)) {
                return $this->output 
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => "Failed to save video file"]));
            }

        }

        // Preepare data for insertion
        $insert_data = [
            'name' => $data['name'],
            'brand' => $data['brand'],
            'core' => $data['core'],
            'thread' => $data['thread'],
            'serie' => $data['serie'],
            'memory' => $data['memory'],
            'manufacturing_node' => $data['manufacturing_node'],
            'integrated_graphic' => $data['integrated_graphic'],
            'boost_clock' => $data['boost_clock'],
            'total_cache' => $data['total_cache'],
            'video' => $video_filename, 
            'created_date' => $created_date,
            'updated_date' => null,
            'price' => $data['price']
        ];

        // Insert into database
        $this->db->insert('cpus', $insert_data);

        // check for database errors
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
                            'video_url' => $video_filename ? base_url('public/video/cpus/'. $video_filename) : null
                        ]
            ]));
    }

    // PUT/PATCH: Update CPU
    public function update_cpu($id) {

        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $updated_date = $date->format('Y-m-d H:i:s');

        // 1. Input Validation
        $json_input = file_get_contents('php://input');

        // Check if input is empty
        if (empty($json_input)) {
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Empty request body']));
        }

        // Decode JSON input
        $data = json_decode($json_input, true);
 
        // Check if JSON input is valid
        if(json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid JSON format']));
        }

        // 2. Fetch existing cpu 
        $existing_cpu = $this->db->get_where('cpus', ['cpu_id' => $id])->row();

        // Check if the cpu exists
        if (!$existing_cpu) {
            $this->output 
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Vga  not found']));
            return;
        }

        // 3. Validate
        // Form Validate
        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('name', 'Name', 'required|max_length[200]');

        $this->form_validation->set_rules('brand','Brand', 'required|max_length[50]');

        $this->form_validation->set_rules('core','Core', 'required|integer');

        $this->form_validation->set_rules('thread','Thread', 'required|integer');

        $this->form_validation->set_rules('serie','Serie', 'required|max_length[100]');

        $this->form_validation->set_rules('memory','Memory', 'required|max_length[100]');

        $this->form_validation->set_rules('manufacturing_node','Manufacturing Node', 'required|integer');

        $this->form_validation->set_rules('integrated_graphic','Integrated Graphic', 'required|max_length[200]');

        $this->form_validation->set_rules('boost_clock','Boost Clock', 'required|numeric');

        $this->form_validation->set_rules('total_cache','Total Cache', 'required|integer');

        $this->form_validation->set_rules('price','Price', 'required|max_length[200]');

        if(!$this->form_validation->run()) {
            return $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'error' => 'Validation failed',
                    'details' => $this->form_validation->error_array()
                ]));
        }

        // Video Validation
        if (!empty($data['video'])) {
            $video_base64 = $data['video'];

            // Separate base64 header and content
            if (preg_match('/^data:(.*);base64,(.*)$/', $video_base64, $matches)) {
                $mime_type = $matches[1];
                $base64_data = $matches[2];

                $allowed_mime_types = [
                    'video/mp4',
                    'video/webm',
                    'video/ogg'
                ];

                // Deny other types (image, doc, ppt, xls)
                $denied_mimes = [
                    'image/png', 'image/jpeg', 'image/jpg',
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];

                if (in_array($mime_type, $denied_mimes)) {
                    return $this->output 
                                ->set_status_header(400)
                                ->set_content_type('application/json')
                                ->set_output(json_encode(['error' => 'File type not allowed']));
                }

                if (!in_array($mime_type, $allowed_video_mimes)) {
                    return $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Only video files are allowed']));
                }

                // Save the video file
                $video_data = base64_decode($base64_data);
                $ext = explode('/', $mime_type)[1]; //e.g. mp4
                $filename = 'cpu_video_' . time() . '.' . $ext;
                $filepath = './public/video/cpus/' . $filename;

                file_put_contents($filepath, $video_data);

                // Store the file name in DB
                $data['video'] = $filename;
            } else {
                return $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid base64 video format']));
            }
        }
        
        // 4. Set updated_date
        $data['updated_date'] = $updated_date;

        // 5. Whitelist allowed fields
        $allowed_fields = [
            'name', 'brand', 'core', 'thread', 'serie', 'memory', 'manufacturing_node', 'integrated_graphic', 'boost_clock', 'total_cache', 'video', 'price', 'updated_date'
        ];

        $filtered_data = array_intersect_key($data, array_flip($allowed_fields));

        // 6. Update the database
        $this->db->where('cpu_id', $id);
        $this->db->update('cpus', $filtered_data);

        // 7. Success Response
        return $this->output 
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => 'CPU updated successfully']));
    }
}

?>