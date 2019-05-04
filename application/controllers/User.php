<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->helper('url');
    }

    public function login()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            if(isset($this->session->userdata['logged_in'])){
                redirect('/', 'refresh');
            }else{
                $this->load->view('login');
            }
        } else {
            $post = array(
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password')
            );

            $loggedIn = $this->user_model->login($post['username'], $post['password']);

            if ($loggedIn !== false) {
                $this->session->set_userdata('logged_in', [
                    'id' => $this->user_model->getId(),
                    'username' => $this->user_model->getUsername(),
                ]);

                redirect('/', 'refresh');
            } else {
                $this->load->view('login', [
                    'error_message' => 'Invalid Username or Password'
                ]);
            }
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('/', 'refresh');
    }
}
