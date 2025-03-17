<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Post_category_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->database();
    }

    // Get all categories
    public function categories() {
        $query = $this->db->get('post_category');
        $categories = $query->result();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($categories));
    }

    // Get single category by ID
    public function category($category_id) {
        $method = $this->input->method();

        // GET: View category
        if ($method === 'get') {
            $category = $this->db->get_where('post_category', ['category_id' => $category_id])->row();
            
            if($category) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($category));
            } else {
                $this->output
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Category not found']));
            }
            return;
        }
        
        // PUT: Update category
        if ($method === 'put') {
            $json_input = file_get_contents('php://input');
            $data = json_decode($json_input, true);
            
            // Get existing category
            $existing = $this->db->get_where('post_category', ['category_id' => $category_id])->row();
            
            $this->form_validation->set_data($data);
            
            // Validate name if changed
            if(isset($data['name']) && $data['name'] != $existing->name) {
                $this->form_validation->set_rules('name', 'Name', 'required|is_unique[post_category.name]');
            }

            // Validate slug if changed
            if(isset($data['slug']) && $data['slug'] != $existing->slug) {
                $this->form_validation->set_rules('slug', 'Slug', 'required|is_unique[post_category.slug]');
            }
            
            if($this->form_validation->run()) {
                $update_data = [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'updated_date' => date('Y-m-d H:i:s'),
                    'updated_by' => $data['updated_by']
                ];
                
                $this->db->where('category_id', $category_id);
                $this->db->update('post_category', $update_data);
                
                $this->output
                    ->set_status_header(200)
                    ->set_output(json_encode(['message' => 'Category updated']));
            } else {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode(['error' => validation_errors()]));
            }
            return;
        }
        
        // DELETE: Remove category
        if ($method === 'delete') {
            $this->db->where('category_id', $category_id);
            $this->db->delete('post_category');
            
            if($this->db->affected_rows() > 0) {
                $this->output
                    ->set_status_header(200)
                    ->set_output(json_encode(['message' => 'Category deleted']));
            } else {
                $this->output
                    ->set_status_header(404)
                    ->set_output(json_encode(['error' => 'Category not found']));
            }
            return;
        }
        
        $this->output
            ->set_status_header(405)
            ->set_output(json_encode(['error' => 'Method Not Allowed']));
    }

    // Create new category
    public function add_category() {
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        $this->form_validation->set_data($data);
        
        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[post_category.name]');
        $this->form_validation->set_rules('slug', 'Slug', 'required|is_unique[post_category.slug]');
        $this->form_validation->set_rules('created_by', 'Created By', 'required');

        if ($this->form_validation->run()) {
            $insert_data = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'created_date' => date('Y-m-d H:i:s'),
                'created_by' => $data['created_by'],
                'updated_date' => null,
                'updated_by' => null
            ];
            
            $this->db->insert('post_category', $insert_data);
            
            $this->output
                ->set_status_header(201)
                ->set_output(json_encode([
                    'message' => 'Category created',
                    'category_id' => $this->db->insert_id()
                ]));
        } else {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => validation_errors()]));
        }
    }
}
?>