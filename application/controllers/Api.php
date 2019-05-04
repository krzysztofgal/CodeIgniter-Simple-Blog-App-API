<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    private $loggedIn = false;
    private $user = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('blog_model');

        $this->output->set_content_type('application/json');

        if ($this->loggedIn = isset($this->session->userdata['logged_in'])) {
            $this->user = $this->session->userdata['logged_in'];
        } else {
            $this->access_denied();
        };
    }

    private function access_denied() {
        $this->output
            ->set_status_header(403)
            ->set_output(json_encode([
                'error' => 'Access Denied'
            ]))
            ->_display();
        exit;
    }

    private function not_found() {
        $this->output
            ->set_status_header(404)
            ->set_output(json_encode([
                'error' => 'Not Found'
            ]))
            ->_display();
        exit;
    }

    public function getPosts()
    {
        $this->output->set_output(json_encode(
            $this->blog_model->loadPosts()
        ));
    }

    public function getPost($id) {
        if (!$post = $this->blog_model->getPostById($id)) {
            return $this->not_found();
        }

        $this->output->set_output(json_encode($post));
    }
}
