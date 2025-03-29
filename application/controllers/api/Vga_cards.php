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
        // Delete vga_card
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

            // Delete the photo file if exists
            $upload_path = './public/img/vga_cards/';
            if(!empty($vga_card->photo) && file_exists($upload_path.$vga_card->photo)) {
                unlink($upload_path.$vga_card->photo);
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
                ->set_output(json_encode(['message' => 'VGA Card deleted successfully']));
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

    // Create a new vga_card
    public function add_vgacard() {
        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $created_date = $date->format('Y-m-d H:i:s');

        // get and convert price first
        $price = $this->input->post('price');
        $price = preg_replace('/[^0-9]/', '', $price);
        $price = (int)$price; 

        // Validate input (including uniqueness)
        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[vga_cards.name]');

        $this->form_validation->set_rules('brand', 'Brand', 'required|in_list[Radeon,Nvidia,Intel]');

        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        $this->form_validation->set_rules('release_date', 'Release Date', 'required');

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
            'price' => $price,
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

    public function adding_vgacard() {
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

        // Validate input (including uniqueness)
        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[vga_cards.name]');
        
        $this->form_validation->set_rules('brand', 'Brand', 'required|in_list[Radeon,Nvidia,Intel]');
        
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');

        $this->form_validation->set_rules('release_date', 'Release Date', 'required');

        // Custom error message for duplicate data

        $this->form_validation->set_message('is_unique', 'The %s field must be unique.');

        if($this->form_validation->run() === FALSE) {
            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => validation_errors())));
            return;
        }
        
        // Handle base64 image
        $photo = null;
        if(!empty($data['photo'])) {
            $upload_path = './public/img/vga_cards/';

            // Ensure directory exists
            if(!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            // Process base64 image
            $base64_string = $data['photo'];

            // Check if base64 string has data URI prefix
            if(strpos($base64_string, 'data:') === 0) {
                $parts = explode(',', $base64_string);
                $base64_string = $parts[1];
                $mime_type = explode(';', explode(':', $parts[0])[1])[0];

                // Validate MIME type
                $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!in_array($mime_type, $allowed_mimes)) {
                    $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'error' => 'Invalid image type',
                            'allowed_types' => 'jpg, jpeg, png'
                        ]));
                    return;
                }

                // Detemine file extension from MIME type
                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png'
                ];
                $extension = $extensions[$mime_type];
            } else {
                // If no data URI, assume jpg as fallback
                $extension = 'jpg';
            }

            // Decode base64 data
            $file_data = base64_decode($base64_string);

            // Validate base64 decoding
            if ($file_data === false) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Invalid base64 image data']));
                return;
            }

            // Validate image size (2048KB = 2MB)
            $file_size = strlen($file_data); 
            if ($file_size > 2048 * 1024 ) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'error' => 'Image too large',
                        'max_size' => '2048KB'
                    ]));
                return;
            }

            // Additional image content validation
            $image_info = @getimagesizefromstring($file_data);
            if (!$image_info || !in_array($image_info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
                $this->output 
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'error' => 'Invalid image content',
                        'allowed_types' => 'jpg, jpeg, png'
                    ]));
                return;
            }

            // Generate unique filename with proper extension
            $file_name = uniqid().'.'.$extension;
            $file_path = $upload_path.$file_name;

            // Save the file 
            if (file_put_contents($file_path, $file_data)) {
                $photo = $file_name;
            } else {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => 'Failed to save image']));
                return;
            }
        } else {
            $this->output 
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Photo is required']));
            return;
        }
        
        // Prepare data for insertion
        $insert_data = [
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
            'photo' => $photo,
            'release_date' => $data['release_date'],
            'created_date' => $created_date,
            'updated_date' => null
        ];

        // Insert into database
        $this->db->insert('vga_cards', $insert_data);

        if ($this->db->error()['code']) {
            // Clean up uploaded file if database insert fails
            if($photo && file_exists($upload_path.$photo)) {
                unlink($upload_path.$photo);
            }
            $this->output 
                ->set_status_header(409)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Name already exists']));
            return;
        }

        // Success Response
        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'message' => 'VGA CARD created successfully',
                'data' => [
                    'id' => $this->db->insert_id(),
                    'photo_url' => base_url('public/img/vga_cards/'.$photo)
                ]
            ]));
    }

    // PUT/PATCH: Update Vga Card
    public function update_vgacard($id) {

        // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        
        // Format the date and time
        $update_date = $date->format('Y-m-d H:i:s');
        
        // Get existing card
        $existing_card = $this->db->get_where('vga_cards', ['id_card' => $id])->row();
        if (!$existing_card) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Vga Card not found']));
            return;
        }

        // Initialize variables
        $photo = $existing_card->photo;
        
        // Handle form-data for PUT/PATCH
        $input = [];

        $upload_path = './public/img/vga_cards/';

        if (strpos($this->input->server('CONTENT_TYPE'), 'multipart/form-data') !== false) {
            $input = $this->input->post();
            // Handle file upload
            if (!empty($_FILES['photo']['name'])) {
                // Ensure directory exists
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }

                // Configure upload settings
                $config = [
                    'upload_path' => $upload_path,
                    'allowed_types' => 'jpg|jpeg|png',
                    'max_size' => 2048, 
                    'file_name' => uniqid(),
                    'overwrite' => false 
                ];
    
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    return $this->output
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'error' => 'File upload failed',
                            'details' => $this->upload->display_errors()
                        ]));
                }

                $upload_data = $this->upload->data();
                $photo = $upload_data['file_name'];
            
                // Delete old photo if exists
                if (!empty($existing_card->photo)) {
                    $old_file = $upload_path . $existing_card->photo;
 
                    if(file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }
            }

            // For PUT/PATCH form-data, we need to manually parse the input
            $putdata = fopen("php://input", "r");
            $raw_data = '';

            while ($chunk = fread($putdata, 1024)) {
                $raw_data .= $chunk;
            }
        
            fclose($putdata);
        
            // Parse the raw data to get form fields
            $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));
            $parts = array_slice(explode($boundary, $raw_data), 1);
        
            foreach ($parts as $part) {
                if ($part == "--\r\n") break;
    
                $part = ltrim($part, "\r\n");
                list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);            
                $raw_headers = explode("\r\n", $raw_headers);
                $headers = array();
    
                foreach ($raw_headers as $header) {
                    list($name, $value) = explode(':', $header);
                    $headers[strtolower($name)] = ltrim($value, ' ');
                }
    
    
                if (isset($headers['content-disposition'])) {
                    $filename = null;
                    preg_match(
                        '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', 
                        $headers['content-disposition'], 
                        $matches
                    );
                    $name = $matches[2];
        
                    if (isset($matches[4])) {
                        // File upload
                        $filename = $matches[4];
                        // File data is already in $_FILES
                    } else {
                        // Regular field
                        $input[$name] = substr($body, 0, strlen($body) - 2);
                    }
                }
            }
        } else {
            // Handle raw JSON input
            $json_input = file_get_contents('php://input');
            $input = json_decode($json_input, true);
        }

        // Validate input
        $this->form_validation->set_data($input);
        $this->form_validation->set_rules('name', 'Name', 'required|max_length[100]');
        $this->form_validation->set_rules('brand', 'Brand', 'required|in_list[Radeon,Nvidia,Intel]');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('release_date', 'Release Date', 'required');

        // get and convert price first
        $price = $this->input->post('price');
        $price = preg_replace('/[^0-9]/', '', $price);
        $price = (int)$price; 

        if ($this->form_validation->run() === FALSE) {

            if (isset($upload_data)) {
                unlink($upload_data['full_path']);
            }

            $this->output->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'error' => 'Validation failed',
                            'details' => validation_errors(),
                            'received_data' => $input
                        ]));
            return;
        }

        // Prepare update data
        $update_data = [
            'name' => $input['name'],
            'brand' => $input['brand'],
            'price' => $input['price'],
            'photo' => $photo,
            'release_date' => $input['release_date'],
            'updated_date' => $update_date
        ];

        // Update database
        $this->db->where('id_card', $id);
        $this->db->update('vga_cards', $update_data);

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'message' => 'VGA Card updated successfully',
                'data' => $update_data
            ]));
    }

    // Update an applicant
    public function update($id) {
        
        // 1. Input Validation
        $json_input = file_get_contents('php://input');

        // Check if input is empty
        if(empty($json_input)) {
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

        // 2. Fetch existing Record
        $existing_vga = $this->db->get_where('vga_cards', ['id_card' => $id])->row();

        // Check if the vgacard exists
        if (!$existing_vga) {
            return $this->output
                        ->set_status_header(404)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'VGA Card not found']));
        }

        // 3. Initialize variables
        $upload_path = './public/img/vga_cards/';
        $photo = $existing_vga->photo;
        $file_name = null;

        // 4. Handle Image Upload (if provided)
        if (!empty($data['photo'])) {
            $image_result = $this->handle_image_upload($data['photo'], $upload_path, $existing_vga->photo);

            if(isset($image_result['error'])) {
                return $this->output 
                            ->set_status_header($image_result['status'])
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['error' => $image_result['error']]));
            }

            $photo = $image_result['file_name'];
            $file_name = $photo;
        }

        // 5. Validate Input Data
        $validation_result = $this->validate_update_data($data, $existing_vga, $id);

        if(isset($validation_result['error'])) {
            // Clean up uploaded file if validation fails
            if($file_name && file_exists($upload_path.$file_name)) {
                @unlink($upload_path.$file_name);
            }

            return $this->output 
                        ->set_status_header(400)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => $validation_result['error']]));
        }

        // 6. Prepare Update Data
        $update_data = $this->prepare_update_data($data, $existing_vga, $photo);

        if (empty($update_data)) {
            return $this->output
                        ->set_status_header(200)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['message' => 'No changes detected']));
        }

        // 7. Perform Database Update
        $this->db->where('id_card', $id);
        $this->db->update('vga_cards', $update_data);

        if ($this->db->affected_rows() === 0) {
            // Clean up uploaded file if database update fails
            if ($file_name && file_exists($upload_path.$file_name)) {
                @unlink($upload_path.$file_name);
            }
        
            return $this->output 
                        ->set_status_header(500)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Failed to update VGA card']));
        }

        // 8. Return Success Response
        return $this->output 
                    ->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'message' => 'VGA Card updated successfully',
                        'data' => [
                            'id' => $id,
                            'photo_url' => base_url('public/img/vga_cards/'.$photo)
                        ]
                        ]));

    }

    // Helper method to handle image upload
    private function handle_image_upload($base64_string, $upload_path, $existing_photo) {
        // 1. Validate Base64 Format
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64_string)) {
            return ['error' => 'Invalid base64 format', 'status' => 400];
        }

        // 2. Check for Data URI
        $is_data_uri = (strpos($base64_string, 'data:') === 0);
        $mime_type = null;
        $extension = 'jpg'; // default

        if ($is_data_uri) {
            $parts = explode(',', $base64_string);
            $base64_string = $parts[1];
            $mime_info = explode(';', explode(':', $parts[0])[1]);
            $mime_type = $mime_info[0];

            // 3. Validate MIME Type
            $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($mime_type, $allowed_mimes)) {
                return [
                    'error' => 'Invalid image type. Allowed types: jpg, jpeg, png',
                    'status' => 400
                ];
            }

            // Set extension based on MIME type
            $extension_map = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png'
            ];
            $extension = $extension_map[$mime_type];
        }

        // 4. Decode and Validate
        $file_data = base64_decode($base64_string);
        if ($file_data === false) {
            return ['error' => 'Invalid base64 image data', 'status' => 400];
        }

        // 5. Validate Image Size (2MB max)
        $file_size = strlen($file_data);
        if ($file_size > 2048 * 1024) {
            return [
                'error' => 'Image too large. Max size: 2048KB',
                'status' => 400
            ];
        }

        // 6. Validate Image Content
        $image_info = @getimagesizefromstring($file_data);

        if (!$image_info || !in_array($image_info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return [
                'error' => 'Invalid image content. Allowed types: jpg, jpeg, png',
                'status' => 400
            ];
        }

        // 7. Ensure directory exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        // 8. Save the new image
        $file_name = uniqid().'.'.$extension;
        $file_path = $upload_path.$file_name;

        if (!file_put_contents($file_path, $file_data)) {
            return ['error' => 'Failed to save image', 'status' => 500];
        }

        // 9. Delete old photo if exists
        if (!empty($existing_photo) && file_exists($upload_path.$existing_photo)) {
            @unlink($upload_path.$existing_photo);
        }

        return ['file_name' => $file_name];
    }

    // Helper method to validate update data
    private function validate_update_data($data, $existing_vga, $id) {
        $this->form_validation->set_data($data);

        // Validate name only if it's changed
        if (isset($data['name']) && $data['name'] != $existing_vga->name) {
            $this->form_validation->set_rules(
                'name', 
                'Name', 
                'required|is_unique[vga_cards.name.id.'.$id.']'
            );
        }

        if (isset($data['brand'])) {
            $this->form_validation->set_rules(
                'brand', 
                'Brand', 
                'required|in_list[Radeon,Nvidia,Intel]'
            );
        }

        if (isset($data['price'])) {
            $this->form_validation->set_rules(
                'price', 
                'Price', 
                'required|numeric'
            );
        }

        if (isset($data['release_date'])) {
            $this->form_validation->set_rules(
                'release_date', 
                'Release Date', 
                'required'
            );
        }

        if ($this->form_validation->run() == FALSE) {
            return ['error' => validation_errors()];
        }

        return [];
    }

    // Helper method to prepare update data
    private function prepare_update_data($data, $existing_vga, $photo) {
        $update_data = [];
        $has_changes = false;
         // Create a DateTime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        // Format the date and time
        $created_date = $date->format('Y-m-d H:i:s');

        $fields = ['name', 'brand', 'price', 'release_date'];
    
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field] != $existing_vga->$field) {
                $update_data[$field] = $field === 'price' ? (int)$data[$field] : $data[$field];
                $has_changes = true;
            }
        }

        // Handle photo separately
        if ($photo != $existing_vga->photo) {
            $update_data['photo'] = $photo;
            $has_changes = true;
        }

        if ($has_changes) {
            $update_data['updated_date'] = $created_date;
            return $update_data;
        }

        return [];
    }
}

?>