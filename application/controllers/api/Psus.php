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
                    'message' => 'PSU created successfully',
                    'psu_id' => $this->db->insert_id(),
                    'license_url' => base_url('public/img/psus/'. $license_filename)
                ]));
            return;
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

    // Helper method to validate and save resume
    private function validate_and_save_license($base64_string) {

        $upload_path = './public/img/psus/';

        // Check if it's a data URI
        $is_data_uri = (strpos($base64_string, 'data:') === 0);
        $mime_type = null;
        $extension = null;

        // 1. Check if it's a data URI 
        if ($is_data_uri) {
            $parts = explode(',', $base64_string);
            $base64_string = $parts[1];
            $mime_info = explode(';', explode(':', $parts[0])[1]);
            $mime_type = $mime_info[0];

            $powerpoint_mimes = [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' //.pptx
            ];

            if (in_array($mime_type, $powerpoint_mimes)) {
                return [
                    'error' => 'PowerPoint files (.pptx) are not allowed.',
                    'status' => 400
                ];
            }

            // 2. Validate MIME type (PDF or Word)
            $allowed_mimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain', // Added for text files
                'application/octet-stream' // Fallback for text files
            ];

            if(!in_array($mime_type, $allowed_mimes)) {
                return [
                    'error' => 'Invalid file type. Only PDF, Word, or TEXT documents are allowed',
                    'status' => 400
                ];
            }

            // Set extension based on MIME type
            $mime_to_extension = [
                'application/pdf' =>'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'text/plain' => 'txt',
                'application/octet-stream' => 'txt'
            ];
            $extension = $mime_to_extension[$mime_type] ?? null;
        }

        // 3. Decode base64 data
        $file_data = base64_decode($base64_string);
        if ($file_data === false) {
            return ['error' => 'Invalid base64 data', 'status' => 400];
        }

        // 4. Check file size (5MB = 5* 1024 * 1024 bytes)
        $file_size = strlen($file_data);
        if ($file_size > 5242880 ) {
            return [
                'error' => 'File too large. Maximum size is 5MB',
                'status' => 400
            ];
        }

        // 5. Validate file content by checking magic numbers
        $file_signature = substr($file_data, 0, 4);
        $pdf_signature = "%PDF";
        $doc_signature = "\xD0\xCF\x11\xE0"; // DOC file singnature
        $docx_signature = "PK\x03\x04"; // DOCX file signature (ZIP format)
        $pptx_signature = "PK\x03\x04"; // PPTX also uses ZIP format

        // Additional check for Power point files 

        if(strncmp($file_signature, $pptx_signature, strlen($pptx_signature)) === 0) {
            // Check for PowerPoint specific files in the ZIP
            if (strpos($file_data, 'ppt/') !== false ||
                strpos($file_data, '[Content_Types].xml') !== false &&
                strpos($file_data, 'application/vnd.openxmlformats-officedocument.presentationml') !== false) {
                    return [
                        'error' => 'PowerPoint files (.pptx) are not allowed',
                        'status' => 400    
                    ];
            }
        }

        // If extension wasn't set from MIME type, detect it 
        if (!$extension) {
            // Check for PDF
            if (strncmp($file_signature, $pdf_signature, strlen($pdf_signature)) === 0) {
                $extension = 'pdf';
            }
            
            // Check for DOC
            elseif (strncmp($file_signature, $doc_signature, strlen($doc_signature)) === 0) {
                $extension = 'doc';
            }
            
            // Check for DOCX
            elseif (strncmp($file_signature, $docx_signature, strlen($docx_signature)) === 0) {
                // Additional check to distinguish DOCX from PPTX
                if (strpos($file_data, 'word/') !== false ||
                    (strpos($file_data, '[Content_Types].xml') !== false && 
                    strpos($file_data, 'application/vnd.openxmlformats-officedocument.wordprocessingml') !== false)) {
                    $extension = 'docx';
                }
            }

            //Check for TXT (no specific signature, but safe to assume if not others)
            else {
                $extension = 'txt';
            }
        }

        // Additional validation for text files
        if ($extension === 'txt') {
            // Simple check for non-binary content
            if (preg_match('/[^\x20-\x7E\x0A\x0D]/', $file_data)) {
                return [
                    'error' => "Invalid text file content",
                    'status' => 400
                ];
            }
        }

        // 6. Ensure directory exits 
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        // 7. Generate unique filename
        $filename = uniqid().'.'.$extension;
        $file_path = $upload_path.$filename;

        // 8. Save the file 
        if (!file_put_contents($file_path, $file_data)) {
            return [
                'error' => 'Failed to save resume file',
                'status' => 500
            ];
        }

        return ['filename' => $filename];
    }

    // Delete a psu
    public function delete_psu($id) {
        // Check if the psu exists
        $existing_psu = $this->db->get_where('psus', ['psu_id' => $id])->row();

        if (!$existing_psu) {
            $this->output 
                ->set_status_header(404) // Not Found
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'PSU not found']));
            return;
        }

        $upload_path = './public/img/psus/';

        // Delete the license file if exists
        if (!empty($existing_psu->license) && file_exists($upload_path.$existing_psu->license)) {
            unlink($upload_path.$existing_psu->license);
        }

        // Delete the psu
        $this->db->where('psu_id', $id);
        $this->db->delete('psus');

        // Check for database errors
        if ($this->db->affected_rows() > 0) {
            $this->output 
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'PSU deleted successfully']));
        } else {
            $this->output 
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error'=>'Failed to delete A PSU']));
        }
    }

    // Update a psu
    public function update_psu($id) {

        // Create a Datetime object with the GMT+7 timezone
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Format the date and time
        $updated_date = $date->format('Y-m-d H:i:s');

        // Read JSON input
        $json_input = file_get_contents('php://input');

        // Check if input is empty 
        if(empty($json_input)) {
            return $this->output 
                        ->set_status_header(400) // Bad Request
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'Empty request body']));
        }

        // Decode JSON input
        $data = json_decode($json_input, true);

        // Check if JSON input
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $this->output 
                ->set_status_header(400) // Bad Request
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid JSON format']));
            return;
        }

        // Fetch existing applicant data 
        $existing_psu = $this->db->get_where('psus', ['psu_id' => $id])->row();
        if (!$existing_psu) {
            return $this->output 
                        ->set_content_type(404) // Not Found
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['error' => 'PSU not found']));
        }

        // Initialize variables
        $license_filename = $existing_psu->license;
        $upload_path = './public/img/psus/'; // Added trail slash for consistency
        $new_license_uploaded = false;

        // Handle license update if provided
        if (!empty($data['license'])) {
            $license_result = $this->validate_and_save_license($data['license']);
            if (isset($license_result['error'])) {
                return $this->output 
                            ->set_status_header($license_result['status'])
                            ->set_content_type('application/json')
                            ->set_output(json_encode(['error' => $license_result['error']]));
            }

            $license_filename = $license_result['filename'];
            $new_license_uploaded = true;
        }

        // Validate input 
        $this->form_validation->set_data($data);

        // Validate "name" only if it's new
        if (isset($data['name']) && $data['name'] != $existing_psu->name) {
            $this->form_validation->set_rules('name', 'Name', 'required|is_unique[psus.name.id.'.$id.']');            
        }

        // Validate "type" only if it's new
        if(isset($data['type'])) {
            $this->form_validation->set_rules('type', 'Type', 'required');
        }

        // Validate "series" only if it's new
        if(isset($data['series'])) {
            $this->form_validation->set_rules('series', 'Series', 'required');
        }

        // Validate "models" only if it's new
        if(isset($data['models'])) {
            $this->form_validation->set_rules('models', 'Models', 'required');
        }

        // Validate "power" only if it's new
        if(isset($data['power'])) {
            $this->form_validation->set_rules('power', 'Power', 'required');
        }

        // Run Validation
        if ($this->form_validation->run() == FALSE) {
            // Clean up uploaded file if validation fails 
            if ($new_license_uploaded && file_exists($upload_path.$license_filename)) {
                @unlink($upload_path.$license_filename);
            }

            $this->output 
                ->set_status_header(400) // Bad Request
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => validation_errors()]));
            return;
        }

        // Prepare data for update
        $hasChanges = false;
        $update_data = [];

        // Check each field for changes
        $fields_to_check = ['name', 'type', 'series', 'models', 'power'];
        foreach($fields_to_check as $field) {
            if (isset($data[$field])) {
                if ($data[$field] != $existing_psu->$field) {
                    $update_data[$field] = $data[$field];
                    $hasChanges = true;
                }
            }
        }

        // Handle license update 
        if ($new_license_uploaded) {
            $update_data['license'] = $license_filename;
            $hasChanges = true;
        }

        // If no changes except possibly the timestamp
        if (!$hasChanges && !$new_license_uploaded) {
            $this->output 
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['message' => 'No Changes detected']));
            return;
        }

        // Always update the timestamp if we're making changes
        if ($hasChanges) {
            $update_data['updated_date'] = $updated_date;
        }

        // If we have a new license but no other changes, we still want to update
        if ($new_license_uploaded) {
            $update_data['updated_date'] = $updated_date;
            $this->db->where('psu_id', $id);
            $this->db->update('psus', $update_data);
                
            // Delete old license file after successful update
            if ($this->db->affected_rows() > 0 && !empty($existing_psu->license)) {
                @unlink($upload_path.$existing_psu->license);
            }

            return $this->output
                        ->set_status_header(200)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'message' => 'PSU Updated successfully',
                            'license_url' => $new_license_uploaded ? base_url('public/img/psus/'. $license_filename) : null
                        ]));
        }

        // Update the psu data
        $this->db->where('psu_id', $id);
        $this->db->update('psus', $update_data);

        if ($this->db->affected_rows() > 0) {
            return $this->output
                        ->set_status_header(200)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'message' => 'PSU updated successfully'
                        ]));
        } else {
            // Clean up uploaded file if database update fails 
            if ($new_license_uploaded && file_exists($upload_path.$license_filename)) {
                @unlink($upload_path.$license_filename);
            }

            return $this->output
                        ->set_status_header(500)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['message' => 'Failed to update PSU']));
        }
    }
}

?>