<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpu extends CI_Controller {

    // Constants for file handling
    const ALLOWED_VIDEO_MIME_TYPES = [
        'video/mp4',
        'video/webm',
        'video/ogg'
    ];

    const DENIED_MIME_TYPES = [
        'image/png', 'image/jpeg', 'image/jpg',        
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint', 
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel', 
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    public function __construct() {
        parent::__construct();
        // Load any necessary libraries or helpers
        $this->load->helper('url', 'file');
        $this->load->library('form_validation', 'upload');
        $this->load->database();
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
            $cpu = $this->db->get_where('cpus', ['cpu_id' => $id])->row();

            if(!$cpu) {
                $this->output 
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'CPU not found']));
                return;
            }

            // Delete the video if exists
            $upload_path = './public/video/cpus/';

            if (!empty($cpu->video) && file_exists($upload_path.$cpu->video)) {
                unlink($upload_path.$cpu->video);
            }

            // Delete the cpu
            $this->db->where('cpu_id', $id);
            $this->db->delete('cpus');

            // Check for database errors
            if ($this->db->affected_rows() == 0) {
                $this->output 
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Failed to delete a cpu']));
                return;
            }

            // Success response
            $this->output 
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'Cpu deleted successfully']));
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
        $video_format = null;
        if (!empty($data['video'])) {
            $allowed_formats = ['mp4', 'webm', 'ogg'];
            $allowed_mime_types = ['video/mp4', 'video/webm', 'video/ogg'];

            // Validate video_format if provided 
            if (empty($data['video_format'])) {
                return $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Video format is required when uploading video']));
            }

            $video_format = strtolower($data['video_format']);

            if (!in_array($video_format, $allowed_formats)) {
                return $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Invalid video format. Allowed: mp4, webm, ogg']));
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

        // Prepare data for insertion
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
            'video_format' => $video_format,
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
        try {
            // Start transaction
            $this->db->trans_start();

            // Get current timestamp
            $updated_date = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');

            // Get and validate input 
            $json_input = file_get_contents('php://input');
            $data = $this->validate_input($json_input, $id);

            // Check if CPU exists
            $existing_cpu = $this->db->get_where('cpus', ['cpu_id' => $id])->row();

            if (!$existing_cpu) {
                return $this->send_response(404, ['error' => 'CPU not found']);
            }

            // Check if any data actually changed
            $current_data = $this->db->get_where('cpus', ['cpu_id' => $id])->row_array();
            $changed_data = array_diff_assoc($data, $current_data);

            // If nothing changed (except possibility updated_date), return success
            if (empty($changed_data) || (count($changed_data) === 1 && isset($changed_data['updated_date']))) {
                return $this->send_response(200, [
                    'success' => 'No changes detected',
                    'data' => $current_data
                ]);
            }

            // Process video if present and valid
            if (!empty($data['video'])) {
                if (empty($data['video_format'])) {
                    throw new Exception("Video format is required when updating video");
                }

                $filename = $this->process_video($data['video'], $data['video_format']);
                $data['video'] = $filename;
                $data['video_format'] = strtolower($data['video_format']);
            } else {
                // Keep existing video if not updating 
                $data['video'] = $existing_cpu->video;
                $data['video_format'] = $existing_cpu->video_format;
            }

            // Prepare data for update 
            $data['updated_date'] = $updated_date;
            $filtered_data = $this->filter_fields($data);

            // Update database
            $this->db->where('cpu_id', $id);
            $this->db->update('cpus', $filtered_data);

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database update failed');
            }

            return $this->send_response(200, [
                'success' => 'CPU updated successfully',
                'data' => $filtered_data
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $this->send_response(400, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Remove in production
            ]);
        }
    }

    private function validate_input($json_input, $id) {
        if (empty($json_input)) {
            throw new Exception('Empty request body');
        }

        // Clean and validate JSON
        $json_input = trim($json_input);
        if ($json_input[0] !== '{') {
            throw new Exception('Invalid JSON format - must be an object');
        }

        $data = json_decode($json_input, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        // Get current CPU data
        $current_cpu = $this->db->get_where('cpus', ['cpu_id' => $id])->row_array();

        if (!$current_cpu) {
            throw new Exception('CPU not found');
        }

        // Remove unchanged fields
        $changed_data = array_diff_assoc($data, $current_cpu);

        // If nothing changed, return the current data
        if (empty($changed_data)) {
            return $current_cpu;
        }

        // Merge with current data to ensure all fields exist
        $data = array_merge($current_cpu, $data);

        $this->form_validation->set_data($data);
        $this->set_validation_rules($id, $current_cpu);

        if (!$this->form_validation->run()) {
            throw new Exception(json_encode($this->form_validation->error_array()));
        }

        return $data;
    }

    private function set_validation_rules($id, $current_data = []) {
        // Ensure current_data is an array
        $current_data = is_array($current_data) ? $current_data : [];

        // Name validation with uniqueness check
        $this->form_validation->set_rules('name', 'Name', [
            function($value) use ($current_data, $id) {
                // Skip if name hasn't changed
                if (isset($current_data['name']) && $value === $current_data['name']) {
                    return true;
                }

                // Validate required
                if (empty($value)) {
                    $this->form_validation->set_message('name', 'The {field} field is required');
                    return false;
                }

                // Validate length
                if (strlen($value) > 200) {
                    $this->form_validation->set_message('name', 'The {field} cannot exceed 200 characters');
                    return false;
                }

                // Check uniqueness only if changed
                $exists = $this->db->where('name', $value)
                                    ->where('cpu_id !=', $id)
                                    ->get('cpus')
                                    ->row();
                return !$exists;
            }
        ]);

        // Add video format validation
        $this->form_validation->set_rules('video_format', 'Video Format', [
            function($value) use ($current_data) {
                if (empty($this->input->post('video'))) {
                    return true;
                }
                
                $allowed = ['mp4', 'webm', 'ogg'];
                $value = strtolower(trim($value));
                
                if (!in_array($value, $allowed)) {
                    $this->form_validation->set_message('video_format', 
                        'Invalid video format. Allowed: ' . implode(', ', $allowed));
                    return false;
                }
                return true;
            }
        ]);

        // Define validation rules as arrays
        $conditional_rules = [
            'brand' => ['required','max_length[50]'],
            'core' => ['required','integer'],
            'thread' => ['required','integer'],
            'serie' => ['required','max_length[100]'],
            'memory' => ['required','max_length[100]'],
            'manufacturing_node' => ['required','integer'],
            'integrated_graphic' => ['required','max_length[200]'],
            'boost_clock' => ['required','numeric'],
            'total_cache' => ['required','integer'],
            'price' => ['required', 'numeric', 'max_length[200]']
        ];

        // Add validation for other fields
        foreach ($conditional_rules as $field => $rules) {
            $this->form_validation->set_rules($field, ucfirst(str_replace('_', ' ', $field)), [
                function($value) use ($field, $rules, $current_data) {
                    // Skip validation if field hasn't changed
                    if (isset($current_data[$field]) && $value == $current_data[$field]) {
                        return true;
                    }

                    // Apply all rules
                    foreach ($rules as $rule) {
                        if ($rule === 'integer' && !filter_var($value, FILTER_VALIDATE_INT)) {
                            $this->form_validation->set_message($field, 'The {field} must be an integer');
                            return false;
                        }
                        
                        if (strpos($rule, 'max_length') === 0) {
                            $max = (int) str_replace(['max_length[', ']'], '', $rule);
                            if (strlen($value) > $max) {
                                $this->form_validation->set_message($field, 'The {field} cannot exceed '.$max.' characters');
                                return false;
                            }
                        }
                        
                        if ($rule === 'numeric' && !is_numeric($value)) {
                            $this->form_validation->set_message($field, 'The {field} must be a number');
                            return false;
                        }

                        if ($rule === 'required' && empty($value)) {
                            $this->form_validation->set_message($field, 'The {field} field is required');
                            return false;
                        }
                    }

                    return true;
                }
            ]);
        }
    }

    private function process_video($video_data, $format = null) {
        // Check if the video is empty or null
        if (empty($video_data)) {
            return null;
        }

        // Create directory if it doesn't exist
        $upload_path = './public/video/cpus/';
        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0755, true)) {
                throw new Exception('Failed to create video directory');
            }
        }

        // Validate format if provided 
        if ($format) {
            $format = strtolower($format);
            $allowed_formats = ['mp4', 'webm', 'ogg'];
            if (!in_array($format, $allowed_formats)) {
                throw new Exception('Invalid video format. Allowed: mp4, webm, ogg');
            }
        }

        // If it's a data URI, extract the base64 part
        if (strpos($video_data, 'data:video/') === 0) {
            if (!preg_match('/^data:video\/([^;]+);base64,(.+)$/', $video_data, $matches)) {
                throw new Exception('Invalid video format. Expected: data:video/<format>;base64,<data> or plain base64');
            }
            $mime_type = 'video/' . $matches[1];
            $video_data = $matches[2];
        } else {
            // For plain base64, we need to detect the MIME type
            $decoded = base64_decode($video_data, true);

            if ($decoded === false) {
                throw new Exception('Invalid base64 data');
            }

            // Create a temporary file to detect MIME type
            $tmp_file = tempnam(sys_get_temp_dir(), 'vid');
            file_put_contents($tmp_file, $decoded);
            $mime_type = mime_content_type($tmp_file);
            unlink($tmp_file);
        }

        // Validate MIME type
        if (in_array($mime_type, self::DENIED_MIME_TYPES)) {
            throw new Exception('File type not allowed(images or documents detected)');
        }

        // Validates MIME type matches format
        if (!in_array($mime_type, self::ALLOWED_VIDEO_MIME_TYPES)) {
            throw new Exception('Only video files are allowed (MP4, WebM, Ogg)');
        }

        // Decode base64 if we haven't already
        $video_binary = isset($decoded) ? $decoded : base64_decode($video_data);
        if ($video_binary === false) {
            throw new Exception('Failed to decode video data');
        }

        // Create directory if it doesn't exist
        $upload_path = './public/video/cpus/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        // Determine extension from MIME type or provided format
        $ext_map = [
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/ogg' => 'ogv'
        ];

        $ext = $format ?: ($ext_map[$mime_type] ?? 'mp4');
        $filename = 'cpu_video_' . time() . '.' . $ext;
        $filepath = $upload_path . $filename;

        if (!write_file($filepath, $video_binary)) {
            throw new Exception('Failed to save video file. check directory permissions.');
        }

        return $filename;
    }

    private function filter_fields($data) {
        $allowed_fields = [
            'name', 'brand', 'core', 'thread', 'serie', 'memory', 
            'manufacturing_node', 'integrated_graphic', 'boost_clock', 
            'total_cache', 'video', 'video_format', 'price', 'updated_date'
        ];

        return array_intersect_key($data, array_flip($allowed_fields));
    }

    private function send_response($status_code, $data) {
        $this->output 
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));

        // Return the output object for method chaining if needed
        return $this->output;
    }
}